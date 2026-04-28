<?php
declare(strict_types=1);

require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/AuditLogger.php';

final class EvidenceAndInvestigation
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

    public function createEvidence(array $data): int
    {
        $this->auth->enforce('CREATE', 'evidence');

        $crimeReportId = isset($data['crime_report_id']) ? (int)$data['crime_report_id'] : 0;
        if ($crimeReportId <= 0) {
            throw new RuntimeException('Evidence must be linked to a crime report.');
        }

        $officer = $this->auth->currentOfficer();
        if ($officer === null) {
            throw new RuntimeException('No authenticated officer.');
        }

        $checkStmt = $this->db->prepare('SELECT id FROM crime_reports WHERE id = :id');
        $checkStmt->execute(['id' => $crimeReportId]);
        if ($checkStmt->fetchColumn() === false) {
            throw new RuntimeException('Crime report not found.');
        }

        $this->db->beginTransaction();

        try {
            $sql = 'INSERT INTO evidence (
                        evidence_code, crime_report_id, evidence_type, title, description, file_path,
                        collected_at, collected_by_officer_id, storage_location,
                        chain_status, integrity_hash
                    ) VALUES (
                        :evidence_code, :crime_report_id, :evidence_type, :title, :description, :file_path,
                        :collected_at, :collected_by_officer_id, :storage_location,
                        :chain_status, :integrity_hash
                    )';

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'evidence_code' => $data['evidence_code'],
                'crime_report_id' => $crimeReportId,
                'evidence_type' => strtoupper(trim($data['evidence_type'])),
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'file_path' => $data['file_path'] ?? null,
                'collected_at' => $data['collected_at'] ?? null,
                'collected_by_officer_id' => $data['collected_by_officer_id'] ?? (int)$officer['id'],
                'storage_location' => $data['storage_location'] ?? null,
                'chain_status' => strtoupper(trim($data['chain_status'] ?? 'COLLECTED')),
                'integrity_hash' => $data['integrity_hash'] ?? null,
            ]);

            $id = (int)$this->db->lastInsertId();
            $this->auditLogger->logActivity((int)$officer['id'], 'EVIDENCE_UPLOAD', 'evidence', $id, 'Evidence item created/uploaded');

            $this->linkEvidenceToCrime($crimeReportId, $id, $data['relation_note'] ?? null);

            $this->db->commit();

            return $id;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateEvidenceChainStatus(int $evidenceId, string $chainStatus): void
    {
        $this->auth->enforce('UPDATE', 'evidence');

        $officer = $this->auth->currentOfficer();
        if ($officer === null) {
            throw new RuntimeException('No authenticated officer.');
        }

        $sql = 'UPDATE evidence
                SET chain_status = :chain_status, updated_at = NOW()
                WHERE id = :id';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'chain_status' => strtoupper(trim($chainStatus)),
            'id' => $evidenceId,
        ]);

        $this->auditLogger->logActivity((int)$officer['id'], 'UPDATE', 'evidence', $evidenceId, 'Evidence chain status updated');
    }

    public function linkEvidenceToCrime(int $crimeReportId, int $evidenceId, ?string $relationNote = null): int
    {
        $this->auth->enforce('CREATE', 'crime_report_evidence');

        $officer = $this->auth->currentOfficer();
        if ($officer === null) {
            throw new RuntimeException('No authenticated officer.');
        }

        $sql = 'INSERT INTO crime_report_evidence (
                    crime_report_id, evidence_id, linked_by_officer_id, relation_note
                ) VALUES (
                    :crime_report_id, :evidence_id, :linked_by_officer_id, :relation_note
                )';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'crime_report_id' => $crimeReportId,
            'evidence_id' => $evidenceId,
            'linked_by_officer_id' => (int)$officer['id'],
            'relation_note' => $relationNote,
        ]);

        $id = (int)$this->db->lastInsertId();

        $this->auditLogger->logActivity((int)$officer['id'], 'UPDATE', 'crime_report_evidence', $id, 'Linked evidence to crime report');

        return $id;
    }

    public function deleteEvidence(int $evidenceId): void
    {
        $this->auth->enforce('DELETE', 'evidence');

        $stmt = $this->db->prepare('DELETE FROM evidence WHERE id = :id');
        $stmt->execute(['id' => $evidenceId]);

        if ($stmt->rowCount() === 0) {
            throw new RuntimeException('Evidence not found.');
        }

        $officer = $this->auth->currentOfficer();
        if ($officer !== null) {
            $this->auditLogger->logActivity((int)$officer['id'], 'DELETE', 'evidence', $evidenceId, 'Evidence deleted');
        }
    }

    public function linkEvidenceToSuspect(int $suspectId, int $evidenceId, string $relevanceReason): int
    {
        $this->auth->enforce('CREATE', 'suspect_evidence');

        $officer = $this->auth->currentOfficer();
        if ($officer === null) {
            throw new RuntimeException('No authenticated officer.');
        }

        $sql = 'INSERT INTO suspect_evidence (
                    suspect_id, evidence_id, relevance_reason, linked_by_officer_id
                ) VALUES (
                    :suspect_id, :evidence_id, :relevance_reason, :linked_by_officer_id
                )';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'suspect_id' => $suspectId,
            'evidence_id' => $evidenceId,
            'relevance_reason' => $relevanceReason,
            'linked_by_officer_id' => (int)$officer['id'],
        ]);

        $id = (int)$this->db->lastInsertId();

        $this->auditLogger->logActivity((int)$officer['id'], 'UPDATE', 'suspect_evidence', $id, 'Linked evidence to suspect');

        return $id;
    }

    public function addInvestigationUpdate(
        int $crimeReportId,
        string $updateType,
        string $noteText,
        ?int $progressPercent = null
    ): int {
        $this->auth->enforce('UPDATE', 'investigation_updates');

        $officer = $this->auth->currentOfficer();
        if ($officer === null) {
            throw new RuntimeException('No authenticated officer.');
        }

        if ($progressPercent !== null && ($progressPercent < 0 || $progressPercent > 100)) {
            throw new InvalidArgumentException('Progress percent must be between 0 and 100.');
        }

        $sql = 'INSERT INTO investigation_updates (
                    crime_report_id, update_type, progress_percent, note_text, created_by_officer_id
                ) VALUES (
                    :crime_report_id, :update_type, :progress_percent, :note_text, :created_by_officer_id
                )';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'crime_report_id' => $crimeReportId,
            'update_type' => strtoupper(trim($updateType)),
            'progress_percent' => $progressPercent,
            'note_text' => $noteText,
            'created_by_officer_id' => (int)$officer['id'],
        ]);

        $id = (int)$this->db->lastInsertId();

        $this->auditLogger->logActivity((int)$officer['id'], 'UPDATE', 'investigation_updates', $id, 'Investigation progress update added');

        return $id;
    }

    public function addInterview(
        int $crimeReportId,
        string $intervieweeName,
        string $intervieweeRole,
        string $interviewDatetime,
        string $summary,
        ?int $suspectId = null
    ): int {
        $this->auth->enforce('UPDATE', 'interviews');

        $officer = $this->auth->currentOfficer();
        if ($officer === null) {
            throw new RuntimeException('No authenticated officer.');
        }

        $sql = 'INSERT INTO interviews (
                    crime_report_id, suspect_id, interviewee_name, interviewee_role,
                    interview_datetime, summary, conducted_by_officer_id
                ) VALUES (
                    :crime_report_id, :suspect_id, :interviewee_name, :interviewee_role,
                    :interview_datetime, :summary, :conducted_by_officer_id
                )';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'crime_report_id' => $crimeReportId,
            'suspect_id' => $suspectId,
            'interviewee_name' => $intervieweeName,
            'interviewee_role' => strtoupper(trim($intervieweeRole)),
            'interview_datetime' => $interviewDatetime,
            'summary' => $summary,
            'conducted_by_officer_id' => (int)$officer['id'],
        ]);

        $id = (int)$this->db->lastInsertId();

        $this->auditLogger->logActivity((int)$officer['id'], 'UPDATE', 'interviews', $id, 'Interview record added');

        return $id;
    }

    public function getCaseTimeline(int $crimeReportId): array
    {
        $this->auth->enforce('READ', 'crime_reports');

        $updatesStmt = $this->db->prepare('SELECT id, update_type AS type, note_text AS details, created_at AS event_time
                                           FROM investigation_updates
                                           WHERE crime_report_id = :crime_report_id');
        $updatesStmt->execute(['crime_report_id' => $crimeReportId]);
        $updates = $updatesStmt->fetchAll();

        $interviewsStmt = $this->db->prepare('SELECT id, "INTERVIEW" AS type, summary AS details, interview_datetime AS event_time
                                              FROM interviews
                                              WHERE crime_report_id = :crime_report_id');
        $interviewsStmt->execute(['crime_report_id' => $crimeReportId]);
        $interviews = $interviewsStmt->fetchAll();

        $timeline = array_merge($updates, $interviews);

        usort($timeline, static fn(array $a, array $b): int => strcmp((string)$a['event_time'], (string)$b['event_time']));

        return $timeline;
    }
}
