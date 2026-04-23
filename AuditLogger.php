<?php
declare(strict_types=1);

require_once __DIR__ . '/Auth.php';

final class AuditLogger
{
    private PDO $db;
    private Auth $auth;

    public function __construct(PDO $db, Auth $auth)
    {
        $this->db = $db;
        $this->auth = $auth;
    }

    public function logActivity(
        int $officerId,
        string $activityType,
        ?string $targetTable = null,
        ?int $targetRecordId = null,
        ?string $actionDetails = null,
        ?string $ipAddress = null,
        ?string $ipLocation = null,
        ?string $userAgent = null
    ): int {
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

        return (int)$this->db->lastInsertId();
    }

    public function logLogin(
        ?int $officerId,
        ?string $emailAttempted,
        string $status,
        string $ipAddress,
        string $userAgent,
        ?string $ipLocation = null,
        ?string $deviceFingerprint = null,
        ?string $failureReason = null
    ): int {
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

        return (int)$this->db->lastInsertId();
    }

    public function submitSchemaRequest(
        string $requestType,
        string $objectName,
        string $reason,
        string $sqlProposal
    ): int {
        $this->auth->enforce('CREATE', 'schema_requests');

        $officer = $this->auth->currentOfficer();
        if ($officer === null) {
            throw new RuntimeException('No authenticated officer.');
        }

        $sql = 'INSERT INTO schema_requests (
                    requested_by_officer_id, request_type, object_name, reason, sql_proposal
                ) VALUES (
                    :requested_by_officer_id, :request_type, :object_name, :reason, :sql_proposal
                )';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'requested_by_officer_id' => (int)$officer['id'],
            'request_type' => strtoupper(trim($requestType)),
            'object_name' => $objectName,
            'reason' => $reason,
            'sql_proposal' => $sqlProposal,
        ]);

        $id = (int)$this->db->lastInsertId();

        $this->logActivity(
            (int)$officer['id'],
            'SCHEMA_REQUEST_SUBMIT',
            'schema_requests',
            $id,
            'Schema request submitted'
        );

        return $id;
    }

    public function reviewSchemaRequest(int $requestId, string $status, ?string $reviewNotes = null): void
    {
        $this->auth->enforce('UPDATE', 'schema_requests');

        $officer = $this->auth->currentOfficer();
        if ($officer === null) {
            throw new RuntimeException('No authenticated officer.');
        }

        $normalized = strtoupper(trim($status));
        if (!in_array($normalized, ['APPROVED', 'REJECTED', 'IMPLEMENTED'], true)) {
            throw new InvalidArgumentException('Invalid schema request status.');
        }

        $sql = 'UPDATE schema_requests
                SET status = :status,
                    reviewed_by_officer_id = :reviewed_by_officer_id,
                    review_notes = :review_notes,
                    reviewed_at = NOW(),
                    implemented_at = CASE WHEN :status_impl = "IMPLEMENTED" THEN NOW() ELSE implemented_at END
                WHERE id = :id';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'status' => $normalized,
            'reviewed_by_officer_id' => (int)$officer['id'],
            'review_notes' => $reviewNotes,
            'status_impl' => $normalized,
            'id' => $requestId,
        ]);

        $this->logActivity(
            (int)$officer['id'],
            $normalized === 'IMPLEMENTED' ? 'SCHEMA_CHANGE_EXECUTED' : 'UPDATE',
            'schema_requests',
            $requestId,
            'Schema request reviewed: ' . $normalized
        );
    }

    public function submitFeedback(string $moduleName, string $category, string $message): int
    {
        $this->auth->enforce('CREATE', 'feedbacks');

        $officer = $this->auth->currentOfficer();
        if ($officer === null) {
            throw new RuntimeException('No authenticated officer.');
        }

        $sql = 'INSERT INTO feedbacks (
                    submitted_by_officer_id, module_name, category, message
                ) VALUES (
                    :submitted_by_officer_id, :module_name, :category, :message
                )';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'submitted_by_officer_id' => (int)$officer['id'],
            'module_name' => strtoupper(trim($moduleName)),
            'category' => strtoupper(trim($category)),
            'message' => $message,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function listOfficerActivity(int $limit = 100): array
    {
        $this->auth->enforce('READ', 'officer_activity');

        $stmt = $this->db->prepare('SELECT * FROM officer_activity ORDER BY created_at DESC LIMIT :limit');
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function listLoginLogs(int $limit = 100): array
    {
        $this->auth->enforce('READ', 'login_logs');

        $stmt = $this->db->prepare('SELECT * FROM login_logs ORDER BY login_time DESC LIMIT :limit');
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
