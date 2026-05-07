<?php
declare(strict_types=1);

require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/AuditLogger.php';

// Officer application workflow service.
// Responsibilities:
// - Accept public signup submissions and validate fields.
// - Allow Grade 1 officers to approve or reject pending applications.
// - Create active officer accounts on approval and record audit entries.

final class OfficerApplicationManager
{
    private PDO $db;
    private Auth $auth;
    private AuditLogger $auditLogger;

    public function __construct(PDO $db, Auth $auth, AuditLogger $auditLogger)
    {
        $this->db = $db;
        $this->auth = $auth;
        $this->auditLogger = $auditLogger;
    }

    public function submitApplication(array $data): void
    {
        $firstName = trim((string)($data['first_name'] ?? ''));
        $lastName = trim((string)($data['last_name'] ?? ''));
        $email = strtolower(trim((string)($data['email'] ?? '')));
        $badgeNumber = strtoupper(trim((string)($data['badge_number'] ?? '')));
        $phone = trim((string)($data['phone'] ?? ''));
        $requestedRank = strtoupper(trim((string)($data['requested_rank'] ?? Auth::ROLE_GRADE_3)));
        $password = (string)($data['password'] ?? '');
        $confirmPassword = (string)($data['confirm_password'] ?? '');

        if ($firstName === '' || $lastName === '' || $email === '' || $badgeNumber === '') {
            throw new RuntimeException('All required fields must be provided.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Invalid email address.');
        }

        if (!in_array($requestedRank, [Auth::ROLE_GRADE_1, Auth::ROLE_GRADE_2, Auth::ROLE_GRADE_3], true)) {
            throw new RuntimeException('Requested rank must be Grade 1, Grade 2, or Grade 3.');
        }

        if ($phone !== '' && !preg_match('/^\+880\d+$/', $phone)) {
            throw new RuntimeException('Invalid phone number. Use +880 followed by digits.');
        }

        if (strlen($password) < 8) {
            throw new RuntimeException('Password must be at least 8 characters long.');
        }

        if ($password !== $confirmPassword) {
            throw new RuntimeException('Passwords do not match.');
        }

        $passwordHash = Auth::hashPassword($password);

        $stmt = $this->db->prepare('SELECT id FROM officers WHERE email = :email OR badge_number = :badge_number');
        $stmt->execute(['email' => $email, 'badge_number' => $badgeNumber]);
        if ($stmt->fetchColumn() !== false) {
            throw new RuntimeException('An officer with this email or badge number already exists.');
        }

        $stmt = $this->db->prepare('SELECT id, status FROM officer_applications WHERE email = :email OR badge_number = :badge_number LIMIT 1');
        $stmt->execute(['email' => $email, 'badge_number' => $badgeNumber]);
        $existing = $stmt->fetch();

        if ($existing) {
            $status = strtoupper((string)($existing['status'] ?? ''));
            if ($status === 'PENDING') {
                throw new RuntimeException('An application is already pending for these credentials.');
            }
            if ($status === 'APPROVED') {
                throw new RuntimeException('This application was already approved. Please log in.');
            }

            $update = $this->db->prepare('UPDATE officer_applications
                                          SET first_name = :first_name,
                                              last_name = :last_name,
                                              email = :email,
                                              badge_number = :badge_number,
                                              phone = :phone,
                                              requested_rank = :requested_rank,
                                              password_hash = :password_hash,
                                              status = :status,
                                              review_note = NULL,
                                              reviewed_by_officer_id = NULL,
                                              reviewed_at = NULL
                                          WHERE id = :id');
            $update->execute([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'badge_number' => $badgeNumber,
                'phone' => $phone === '' ? null : $phone,
                'requested_rank' => $requestedRank,
                'password_hash' => $passwordHash,
                'status' => 'PENDING',
                'id' => (int)$existing['id'],
            ]);

            return;
        }

        $insert = $this->db->prepare('INSERT INTO officer_applications (
                                          badge_number, first_name, last_name, email, phone,
                                          requested_rank, password_hash, status
                                      ) VALUES (
                                          :badge_number, :first_name, :last_name, :email, :phone,
                                          :requested_rank, :password_hash, :status
                                      )');
        $insert->execute([
            'badge_number' => $badgeNumber,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone === '' ? null : $phone,
            'requested_rank' => $requestedRank,
            'password_hash' => $passwordHash,
            'status' => 'PENDING',
        ]);
    }

    public function listApplications(?string $status = null): array
    {
        $reviewer = $this->auth->currentOfficer();
        if ($reviewer === null || ($reviewer['rank'] ?? null) !== Auth::ROLE_GRADE_1) {
            throw new RuntimeException('Permission denied. Officer applications are restricted to Grade 1 officer.');
        }

        $sql = 'SELECT * FROM officer_applications';
        $params = [];

        if ($status !== null && $status !== '') {
            $sql .= ' WHERE status = :status';
            $params['status'] = strtoupper(trim($status));
        }

        $sql .= ' ORDER BY created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function approveApplication(int $applicationId, string $finalRank, ?string $note): void
    {
        $reviewer = $this->auth->currentOfficer();
        if ($reviewer === null || ($reviewer['rank'] ?? null) !== Auth::ROLE_GRADE_1) {
            throw new RuntimeException('Permission denied. Only Grade 1 officer can approve applications.');
        }

        $this->auth->enforce('CREATE', 'officers');

        if ($reviewer === null) {
            throw new RuntimeException('No authenticated officer.');
        }

        $finalRank = strtoupper(trim($finalRank));
        if (!in_array($finalRank, [Auth::ROLE_GRADE_1, Auth::ROLE_GRADE_2, Auth::ROLE_GRADE_3], true)) {
            throw new RuntimeException('Final rank must be Grade 1, Grade 2, or Grade 3.');
        }

        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare('SELECT * FROM officer_applications WHERE id = :id AND status = :status');
            $stmt->execute(['id' => $applicationId, 'status' => 'PENDING']);
            $application = $stmt->fetch();

            if (!$application) {
                throw new RuntimeException('Pending application not found.');
            }

            $check = $this->db->prepare('SELECT id FROM officers WHERE email = :email OR badge_number = :badge_number');
            $check->execute([
                'email' => $application['email'],
                'badge_number' => $application['badge_number'],
            ]);
            if ($check->fetchColumn() !== false) {
                throw new RuntimeException('An officer with this email or badge number already exists.');
            }

            $insert = $this->db->prepare('INSERT INTO officers (
                                             badge_number, first_name, last_name, email, phone, rank,
                                             password_hash, is_active
                                          ) VALUES (
                                             :badge_number, :first_name, :last_name, :email, :phone, :rank,
                                             :password_hash, :is_active
                                          )');
            $insert->execute([
                'badge_number' => $application['badge_number'],
                'first_name' => $application['first_name'],
                'last_name' => $application['last_name'],
                'email' => $application['email'],
                'phone' => $application['phone'],
                'rank' => $finalRank,
                'password_hash' => $application['password_hash'],
                'is_active' => 1,
            ]);

            $update = $this->db->prepare('UPDATE officer_applications
                                          SET status = :status,
                                              review_note = :review_note,
                                              reviewed_by_officer_id = :reviewed_by,
                                              reviewed_at = NOW()
                                          WHERE id = :id');
            $update->execute([
                'status' => 'APPROVED',
                'review_note' => $note === '' ? null : $note,
                'reviewed_by' => (int)$reviewer['id'],
                'id' => $applicationId,
            ]);

            $this->auditLogger->logActivity(
                (int)$reviewer['id'],
                'APPROVE',
                'officer_applications',
                $applicationId,
                'Officer application approved for ' . (string)$application['email']
            );

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function rejectApplication(int $applicationId, ?string $note): void
    {
        $reviewer = $this->auth->currentOfficer();
        if ($reviewer === null || ($reviewer['rank'] ?? null) !== Auth::ROLE_GRADE_1) {
            throw new RuntimeException('Permission denied. Only Grade 1 officer can reject applications.');
        }

        $this->auth->enforce('CREATE', 'officers');

        if ($reviewer === null) {
            throw new RuntimeException('No authenticated officer.');
        }

        $stmt = $this->db->prepare('SELECT id, status, email FROM officer_applications WHERE id = :id');
        $stmt->execute(['id' => $applicationId]);
        $application = $stmt->fetch();

        if (!$application || strtoupper((string)($application['status'] ?? '')) !== 'PENDING') {
            throw new RuntimeException('Pending application not found.');
        }

        $update = $this->db->prepare('UPDATE officer_applications
                                      SET status = :status,
                                          review_note = :review_note,
                                          reviewed_by_officer_id = :reviewed_by,
                                          reviewed_at = NOW()
                                      WHERE id = :id');
        $update->execute([
            'status' => 'REJECTED',
            'review_note' => $note === '' ? null : $note,
            'reviewed_by' => (int)$reviewer['id'],
            'id' => $applicationId,
        ]);

        $this->auditLogger->logActivity(
            (int)$reviewer['id'],
            'REJECT',
            'officer_applications',
            $applicationId,
            'Officer application rejected for ' . (string)($application['email'] ?? '')
        );
    }
}
