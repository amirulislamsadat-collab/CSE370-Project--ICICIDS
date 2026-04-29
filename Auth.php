<?php
declare(strict_types=1);

require_once __DIR__ . '/Database.php';

// Authentication and authorization service.
// Responsibilities:
// - Session-based login/logout and officer context management.
// - Role-based access control enforcement for table/operation pairs.
// - Security audit inserts for login attempts and officer activity.

final class Auth
{
    public const ROLE_GRADE_1 = 'GRADE_1';
    public const ROLE_GRADE_2 = 'GRADE_2';
    public const ROLE_GRADE_3 = 'GRADE_3';

    private const SESSION_KEY = 'auth_officer';

    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public static function hashPassword(string $plainPassword): string
    {
        // Use PHP default password algorithm for safe long-term hashing.
        return password_hash($plainPassword, PASSWORD_DEFAULT);
    }

    public function login(
        string $email,
        string $plainPassword,
        string $userAgent,
        string $ipAddress,
        ?string $ipLocation = null,
        ?string $deviceFingerprint = null
    ): bool {
        // Authenticate an active officer account and store a minimal session identity.
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $sql = 'SELECT id, badge_number, first_name, last_name, email, rank, password_hash, is_active
                FROM officers
                WHERE email = :email
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        $officer = $stmt->fetch();

        if (!$officer || (int)$officer['is_active'] !== 1) {
            $this->logLoginAttempt(null, $email, 'FAILED', $ipAddress, $userAgent, $ipLocation, $deviceFingerprint, 'Invalid account');
            return false;
        }

        if (!password_verify($plainPassword, (string)$officer['password_hash'])) {
            $this->logLoginAttempt((int)$officer['id'], $email, 'FAILED', $ipAddress, $userAgent, $ipLocation, $deviceFingerprint, 'Invalid credentials');
            return false;
        }

        session_regenerate_id(true);

        $_SESSION[self::SESSION_KEY] = [
            'id' => (int)$officer['id'],
            'badge_number' => $officer['badge_number'],
            'name' => $officer['first_name'] . ' ' . $officer['last_name'],
            'email' => $officer['email'],
            'rank' => $officer['rank'],
        ];

        $updateStmt = $this->db->prepare('UPDATE officers SET last_login_at = NOW() WHERE id = :id');
        $updateStmt->execute(['id' => (int)$officer['id']]);

        $this->logLoginAttempt((int)$officer['id'], $email, 'SUCCESS', $ipAddress, $userAgent, $ipLocation, $deviceFingerprint, null);
        $this->logOfficerActivity((int)$officer['id'], 'LOGIN', 'officers', (int)$officer['id'], 'Officer logged in', $ipAddress, $ipLocation, $userAgent);

        return true;
    }

    public function logout(?string $ipAddress = null, ?string $userAgent = null, ?string $ipLocation = null): void
    {
        // Record logout activity before clearing session state.
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $officer = $this->currentOfficer();
        if ($officer !== null) {
            $this->logOfficerActivity(
                (int)$officer['id'],
                'LOGOUT',
                'officers',
                (int)$officer['id'],
                'Officer logged out',
                $ipAddress,
                $ipLocation,
                $userAgent
            );
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();

        // Re-open an empty session so subsequent login calls in the same request can regenerate ID safely.
        session_start();
    }

    public function isAuthenticated(): bool
    {
        return isset($_SESSION[self::SESSION_KEY]);
    }

    public function currentOfficer(): ?array
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        return $_SESSION[self::SESSION_KEY];
    }

    public function requireAuthentication(): void
    {
        if (!$this->isAuthenticated()) {
            throw new RuntimeException('Authentication required.');
        }
    }

    public function enforce(string $operation, ?string $table = null): void
    {
        // Central RBAC policy matrix for Grade 1, 2, and 3 officers.
        $this->requireAuthentication();

        $officer = $this->currentOfficer();
        if ($officer === null) {
            throw new RuntimeException('No authenticated officer context.');
        }

        $rank = (string)$officer['rank'];
        $normalizedOperation = strtoupper(trim($operation));
        $normalizedTable = $table !== null ? strtolower(trim($table)) : null;

        if (
            $normalizedOperation === 'READ'
            && in_array($normalizedTable, ['officer_activity', 'login_logs'], true)
            && $rank !== self::ROLE_GRADE_1
        ) {
            throw new RuntimeException('Permission denied. Audit logs are restricted to Grade 1 officer.');
        }

        if ($rank === self::ROLE_GRADE_1) {
            return;
        }

        if ($rank === self::ROLE_GRADE_2) {
            $createTables = [
                'crime_reports',
                'suspects',
                'criminals',
                'evidence',
                'crime_report_suspects',
                'crime_report_evidence',
                'suspect_evidence',
                'criminal_history',
                'criminal_aliases',
            ];
            $deleteTables = [
                'crime_reports',
                'suspects',
                'criminals',
                'evidence',
                'crime_report_suspects',
                'crime_report_evidence',
                'suspect_evidence',
                'criminal_history',
                'criminal_aliases',
            ];

            if ($normalizedOperation === 'READ' || $normalizedOperation === 'UPDATE') {
                return;
            }

            if (
                $normalizedOperation === 'CREATE'
                && in_array($normalizedTable, array_merge($createTables, ['schema_requests', 'feedbacks']), true)
            ) {
                return;
            }

            if ($normalizedOperation === 'DELETE' && in_array($normalizedTable, $deleteTables, true)) {
                return;
            }

            throw new RuntimeException('Permission denied for Grade 2 officer.');
        }

        if ($rank === self::ROLE_GRADE_3) {
            $createTables = [
                'crime_reports',
                'suspects',
                'criminals',
                'evidence',
                'crime_report_suspects',
                'crime_report_evidence',
                'suspect_evidence',
                'criminal_history',
                'criminal_aliases',
            ];

            if ($normalizedTable === 'schema_requests') {
                throw new RuntimeException('Permission denied. Grade 3 cannot access schema requests.');
            }

            if ($normalizedOperation === 'READ') {
                return;
            }

            if ($normalizedOperation === 'DELETE') {
                throw new RuntimeException('Permission denied. Grade 3 cannot delete records.');
            }

            if (
                $normalizedOperation === 'CREATE'
                && in_array($normalizedTable, array_merge($createTables, ['feedbacks']), true)
            ) {
                return;
            }

            throw new RuntimeException('Permission denied for Grade 3 officer.');
        }

        throw new RuntimeException('Unknown role. Access denied.');
    }

    public function can(string $operation, ?string $table = null): bool
    {
        try {
            $this->enforce($operation, $table);
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    private function logLoginAttempt(
        ?int $officerId,
        ?string $emailAttempted,
        string $status,
        string $ipAddress,
        string $userAgent,
        ?string $ipLocation,
        ?string $deviceFingerprint,
        ?string $failureReason
    ): void {
        // Internal helper used by login() to persist all authentication outcomes.
        $sql = 'INSERT INTO login_logs (
                    officer_id, email_attempted, login_status, ip_address, ip_location,
                    user_agent, device_fingerprint, failure_reason
                ) VALUES (
                    :officer_id, :email_attempted, :login_status, :ip_address, :ip_location,
                    :user_agent, :device_fingerprint, :failure_reason
                )';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':officer_id', $officerId, $officerId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':email_attempted', $emailAttempted);
        $stmt->bindValue(':login_status', $status);
        $stmt->bindValue(':ip_address', $ipAddress);
        $stmt->bindValue(':ip_location', $ipLocation);
        $stmt->bindValue(':user_agent', $userAgent);
        $stmt->bindValue(':device_fingerprint', $deviceFingerprint);
        $stmt->bindValue(':failure_reason', $failureReason);
        $stmt->execute();
    }

    private function logOfficerActivity(
        int $officerId,
        string $activityType,
        ?string $targetTable,
        ?int $targetRecordId,
        ?string $actionDetails,
        ?string $ipAddress,
        ?string $ipLocation,
        ?string $userAgent
    ): void {
        // Internal helper for audit trail writes originating from auth actions.
        $sql = 'INSERT INTO officer_activity (
                    officer_id, activity_type, target_table, target_record_id,
                    action_details, ip_address, ip_location, user_agent
                ) VALUES (
                    :officer_id, :activity_type, :target_table, :target_record_id,
                    :action_details, :ip_address, :ip_location, :user_agent
                )';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':officer_id', $officerId, PDO::PARAM_INT);
        $stmt->bindValue(':activity_type', $activityType);
        $stmt->bindValue(':target_table', $targetTable);
        $stmt->bindValue(':target_record_id', $targetRecordId, $targetRecordId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':action_details', $actionDetails);
        $stmt->bindValue(':ip_address', $ipAddress);
        $stmt->bindValue(':ip_location', $ipLocation);
        $stmt->bindValue(':user_agent', $userAgent);
        $stmt->execute();
    }
}
