<?php
declare(strict_types=1);

require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/AuditLogger.php';

// Core case workflow service.
// Responsibilities:
// - Manage crime reports and suspect lifecycle.
// - Confirm suspects as criminals with closed/referred case guards.
// - Write audit events for all mutating actions.

final class CrimeAndSuspectManager
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

    public function createCrimeReport(array $data): int
    {
        // Auto-generate crime name based on the crime date (CRIME-YYYY/MM).
        $this->auth->enforce('CREATE', 'crime_reports');

        $crimeDatetime = trim((string)($data['crime_datetime'] ?? ''));
        if ($crimeDatetime === '') {
            throw new RuntimeException('Crime date/time is required.');
        }

        try {
            $crimeDate = new DateTime($crimeDatetime);
        } catch (Throwable $e) {
            throw new RuntimeException('Invalid crime date/time format.');
        }

        $caseNumber = 'CRIME-' . $crimeDate->format('Y') . '/' . $crimeDate->format('m');

        $officer = $this->auth->currentOfficer();
        if ($officer === null) {
            throw new RuntimeException('No authenticated officer.');
        }

        $sql = 'INSERT INTO crime_reports (
                    case_number, crime_type, location_text, latitude, longitude,
                    crime_datetime, description, investigation_status,
                    reported_by_officer_id, assigned_officer_id
                ) VALUES (
                    :case_number, :crime_type, :location_text, :latitude, :longitude,
                    :crime_datetime, :description, :investigation_status,
                    :reported_by_officer_id, :assigned_officer_id
                )';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':case_number', $caseNumber);
        $stmt->bindValue(':crime_type', $data['crime_type']);
        $stmt->bindValue(':location_text', $data['location_text']);
        $stmt->bindValue(':latitude', $data['latitude'] ?? null);
        $stmt->bindValue(':longitude', $data['longitude'] ?? null);
        $stmt->bindValue(':crime_datetime', $data['crime_datetime']);
        $stmt->bindValue(':description', $data['description']);
        $stmt->bindValue(':investigation_status', $data['investigation_status'] ?? 'OPEN');
        $stmt->bindValue(':reported_by_officer_id', (int)$officer['id'], PDO::PARAM_INT);
        $stmt->bindValue(':assigned_officer_id', $data['assigned_officer_id'] ?? null, isset($data['assigned_officer_id']) ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->execute();

        $id = (int)$this->db->lastInsertId();

        $this->auditLogger->logActivity((int)$officer['id'], 'CREATE', 'crime_reports', $id, 'Crime report created');

        return $id;
    }

    public function updateCrimeReportStatus(int $crimeReportId, string $status, ?string $noteText = null): void
    {
        // Update case status and optionally append an investigation timeline note.
        $this->auth->enforce('UPDATE', 'crime_reports');

        $officer = $this->auth->currentOfficer();
        if ($officer === null) {
            throw new RuntimeException('No authenticated officer.');
        }

        $statusSql = 'UPDATE crime_reports
                      SET investigation_status = :investigation_status, updated_at = NOW()
                      WHERE id = :id';

        $statusStmt = $this->db->prepare($statusSql);
        $statusStmt->execute([
            'investigation_status' => strtoupper(trim($status)),
            'id' => $crimeReportId,
        ]);

        if ($noteText !== null && trim($noteText) !== '') {
            $noteSql = 'INSERT INTO investigation_updates (crime_report_id, update_type, note_text, created_by_officer_id)
                        VALUES (:crime_report_id, :update_type, :note_text, :created_by_officer_id)';
            $noteStmt = $this->db->prepare($noteSql);
            $noteStmt->execute([
                'crime_report_id' => $crimeReportId,
                'update_type' => 'STATUS_CHANGE',
                'note_text' => $noteText,
                'created_by_officer_id' => (int)$officer['id'],
            ]);
        }

        $this->auditLogger->logActivity((int)$officer['id'], 'REPORT_UPDATE', 'crime_reports', $crimeReportId, 'Crime report status changed to ' . strtoupper(trim($status)));
    }

    public function getCrimeReportById(int $crimeReportId): ?array
    {
        $this->auth->enforce('READ', 'crime_reports');

        $sql = 'SELECT cr.*, o1.first_name AS reported_by_first_name, o1.last_name AS reported_by_last_name,
                       o2.first_name AS assigned_first_name, o2.last_name AS assigned_last_name
                FROM crime_reports cr
                INNER JOIN officers o1 ON o1.id = cr.reported_by_officer_id
                LEFT JOIN officers o2 ON o2.id = cr.assigned_officer_id
                WHERE cr.id = :id
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $crimeReportId]);
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    public function listCrimeReports(?string $status = null, ?string $crimeType = null, int $limit = 100): array
    {
        $this->auth->enforce('READ', 'crime_reports');

        $sql = 'SELECT * FROM crime_reports WHERE 1=1';
        $params = [];

        if ($status !== null) {
            $sql .= ' AND investigation_status = :status';
            $params['status'] = strtoupper(trim($status));
        }

        if ($crimeType !== null) {
            $sql .= ' AND crime_type = :crime_type';
            $params['crime_type'] = $crimeType;
        }

        $sql .= ' ORDER BY crime_datetime DESC LIMIT :limit';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function listCrimeReportsFiltered(array $filters, int $limit = 200): array
    {
        $this->auth->enforce('READ', 'crime_reports');

        $sql = 'SELECT * FROM crime_reports WHERE 1=1';
        $params = [];

        $query = trim((string)($filters['query'] ?? ''));
        if ($query !== '') {
            $sql .= ' AND id = :exact_id';
            $params['exact_id'] = is_numeric($query) ? (int)$query : -1;
        }

        $status = trim((string)($filters['status'] ?? ''));
        if ($status !== '') {
            $sql .= ' AND investigation_status = :status';
            $params['status'] = strtoupper($status);
        }

        $type = trim((string)($filters['type'] ?? ''));
        if ($type !== '') {
            $sql .= ' AND crime_type LIKE :crime_type';
            $params['crime_type'] = '%' . $type . '%';
        }

        $from = trim((string)($filters['from'] ?? ''));
        if ($from !== '') {
            $sql .= ' AND crime_datetime >= :from_date';
            $params['from_date'] = $from . ' 00:00:00';
        }

        $to = trim((string)($filters['to'] ?? ''));
        if ($to !== '') {
            $sql .= ' AND crime_datetime <= :to_date';
            $params['to_date'] = $to . ' 23:59:59';
        }

        $sql .= ' ORDER BY crime_datetime DESC LIMIT :limit';

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function createSuspect(array $data): int
    {
        // New suspects must belong to a case and pass phone/NID validation rules.
        $this->auth->enforce('CREATE', 'suspects');

        $crimeReportId = isset($data['crime_report_id']) ? (int)$data['crime_report_id'] : 0;
        if ($crimeReportId <= 0) {
            throw new RuntimeException('Suspects must be linked to a crime report.');
        }

        $officer = $this->auth->currentOfficer();
        if ($officer === null) {
            throw new RuntimeException('No authenticated officer.');
        }

        $phone = trim((string)($data['phone'] ?? ''));
        if ($phone !== '' && !preg_match('/^\+880\d+$/', $phone)) {
            throw new RuntimeException('Invalid phone number. Use +880 followed by digits.');
        }
        $phone = $phone === '' ? null : $phone;

        $nationalId = trim((string)($data['national_id'] ?? ''));
        $nationalId = $nationalId === '' ? null : strtoupper($nationalId);
        if ($nationalId !== null && !preg_match('/^NID-ICI-\d{4}$/', $nationalId)) {
            throw new RuntimeException('Invalid NID. Use NID-ICI-XXXX format.');
        }

        $checkStmt = $this->db->prepare('SELECT id FROM crime_reports WHERE id = :id');
        $checkStmt->execute(['id' => $crimeReportId]);
        if ($checkStmt->fetchColumn() === false) {
            throw new RuntimeException('Crime report not found.');
        }

        $this->db->beginTransaction();

        try {
            $sql = 'INSERT INTO suspects (
                        first_name, last_name, crime_report_id, date_of_birth, gender, national_id,
                        address_line, phone, reason_for_suspicion, suspect_status, created_by_officer_id
                    ) VALUES (
                        :first_name, :last_name, :crime_report_id, :date_of_birth, :gender, :national_id,
                        :address_line, :phone, :reason_for_suspicion, :suspect_status, :created_by_officer_id
                    )';

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'crime_report_id' => $crimeReportId,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => strtoupper(trim($data['gender'] ?? 'UNKNOWN')),
                'national_id' => $nationalId,
                'address_line' => $data['address_line'] ?? null,
                'phone' => $phone,
                'reason_for_suspicion' => $data['reason_for_suspicion'],
                'suspect_status' => strtoupper(trim($data['suspect_status'] ?? 'PERSON_OF_INTEREST')),
                'created_by_officer_id' => (int)$officer['id'],
            ]);

            $id = (int)$this->db->lastInsertId();
            $this->auditLogger->logActivity((int)$officer['id'], 'CREATE', 'suspects', $id, 'Suspect profile created');

            $this->linkSuspectToCrime(
                $crimeReportId,
                $id,
                $data['relation_type'] ?? 'PRIMARY',
                $data['relation_notes'] ?? null
            );

            $this->db->commit();

            return $id;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateSuspectStatus(int $suspectId, string $suspectStatus): void
    {
        $this->auth->enforce('UPDATE', 'suspects');

        $officer = $this->auth->currentOfficer();
        if ($officer === null) {
            throw new RuntimeException('No authenticated officer.');
        }

        $sql = 'UPDATE suspects SET suspect_status = :suspect_status, updated_at = NOW() WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'suspect_status' => strtoupper(trim($suspectStatus)),
            'id' => $suspectId,
        ]);

        $this->auditLogger->logActivity((int)$officer['id'], 'SUSPECT_UPDATE', 'suspects', $suspectId, 'Suspect status updated');
    }

    public function unlinkSuspectFromCrime(int $suspectId, int $crimeReportId): void
    {
        // Remove explicit bridge link and clear primary link when it points to the same case.
        $this->auth->enforce('DELETE', 'crime_report_suspects');

        $officer = $this->auth->currentOfficer();
        if ($officer === null) {
            throw new RuntimeException('No authenticated officer.');
        }

        $this->db->beginTransaction();

        try {
            $deleteStmt = $this->db->prepare('DELETE FROM crime_report_suspects WHERE suspect_id = :suspect_id AND crime_report_id = :crime_report_id');
            $deleteStmt->execute([
                'suspect_id' => $suspectId,
                'crime_report_id' => $crimeReportId,
            ]);

            $clearPrimaryStmt = $this->db->prepare('UPDATE suspects
                                                   SET crime_report_id = CASE WHEN crime_report_id = :crime_report_id THEN NULL ELSE crime_report_id END,
                                                       updated_at = NOW()
                                                   WHERE id = :suspect_id');
            $clearPrimaryStmt->execute([
                'crime_report_id' => $crimeReportId,
                'suspect_id' => $suspectId,
            ]);

            $this->db->commit();

            $this->auditLogger->logActivity((int)$officer['id'], 'DELETE', 'crime_report_suspects', null, 'Unlinked suspect from crime report');
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function linkSuspectToCrime(int $crimeReportId, int $suspectId, string $relationType = 'PRIMARY', ?string $notes = null): int
    {
        // Persist case-suspect association metadata in bridge table.
        $this->auth->enforce('CREATE', 'crime_report_suspects');

        $officer = $this->auth->currentOfficer();
        if ($officer === null) {
            throw new RuntimeException('No authenticated officer.');
        }

        $sql = 'INSERT INTO crime_report_suspects (
                    crime_report_id, suspect_id, relation_type, notes, linked_by_officer_id
                ) VALUES (
                    :crime_report_id, :suspect_id, :relation_type, :notes, :linked_by_officer_id
                )';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'crime_report_id' => $crimeReportId,
            'suspect_id' => $suspectId,
            'relation_type' => strtoupper(trim($relationType)),
            'notes' => $notes,
            'linked_by_officer_id' => (int)$officer['id'],
        ]);

        $id = (int)$this->db->lastInsertId();

        $this->auditLogger->logActivity((int)$officer['id'], 'CREATE', 'crime_report_suspects', $id, 'Linked suspect to crime report');

        return $id;
    }

    public function createCriminalFromSuspect(
        int $suspectId,
        int $crimeReportId,
        string $criminalCode,
        string $profileSummary,
        string $riskLevel,
        string $currentStatus
    ): int
    {
        // Confirm suspect as criminal only for CLOSED or REFERRED cases.
        $this->auth->enforce('CREATE', 'criminals');

        $officer = $this->auth->currentOfficer();
        if ($officer === null) {
            throw new RuntimeException('No authenticated officer.');
        }

        if ($crimeReportId <= 0) {
            throw new RuntimeException('Crime report is required for confirming a criminal.');
        }

        $criminalCode = strtoupper(trim($criminalCode));
        if ($criminalCode === '') {
            throw new RuntimeException('Criminal ID is required.');
        }

        if (preg_match('/^\d+$/', $criminalCode)) {
            $criminalCode = 'CRIM-' . $criminalCode;
        }

        if (!preg_match('/^CRIM-\d+$/', $criminalCode)) {
            throw new RuntimeException('Invalid criminal ID. Use digits only or CRIM-09 format.');
        }

        $statusStmt = $this->db->prepare('SELECT investigation_status FROM crime_reports WHERE id = :id');
        $statusStmt->execute(['id' => $crimeReportId]);
        $status = $statusStmt->fetchColumn();
        if ($status === false) {
            throw new RuntimeException('Crime report not found.');
        }

        $status = strtoupper((string)$status);
        if (!in_array($status, ['CLOSED', 'REFERRED'], true)) {
            throw new RuntimeException('Criminals can only be linked to closed or referred cases.');
        }

        $sql = 'INSERT INTO criminals (
                    suspect_id, crime_report_id, criminal_code, profile_summary, risk_level, current_status, added_by_officer_id
                ) VALUES (
                    :suspect_id, :crime_report_id, :criminal_code, :profile_summary, :risk_level, :current_status, :added_by_officer_id
                )';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'suspect_id' => $suspectId,
            'crime_report_id' => $crimeReportId,
            'criminal_code' => $criminalCode,
            'profile_summary' => $profileSummary,
            'risk_level' => strtoupper(trim($riskLevel)),
            'current_status' => strtoupper(trim($currentStatus)),
            'added_by_officer_id' => (int)$officer['id'],
        ]);

        $id = (int)$this->db->lastInsertId();

        $this->auditLogger->logActivity((int)$officer['id'], 'CREATE', 'criminals', $id, 'Confirmed suspect as criminal');

        return $id;
    }

    public function addCriminalAlias(int $criminalId, string $aliasName, ?string $aliasNote = null): int
    {
        $this->auth->enforce('CREATE', 'criminal_aliases');

        $officer = $this->auth->currentOfficer();
        if ($officer === null) {
            throw new RuntimeException('No authenticated officer.');
        }

        $sql = 'INSERT INTO criminal_aliases (criminal_id, alias_name, alias_note)
                VALUES (:criminal_id, :alias_name, :alias_note)';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'criminal_id' => $criminalId,
            'alias_name' => $aliasName,
            'alias_note' => $aliasNote,
        ]);

        $id = (int)$this->db->lastInsertId();

        $this->auditLogger->logActivity((int)$officer['id'], 'CREATE', 'criminal_aliases', $id, 'Added criminal alias');

        return $id;
    }

    public function unlinkCriminalFromSuspect(int $criminalId): void
    {
        // Keep criminal profile but remove direct suspect association.
        $this->auth->enforce('UPDATE', 'criminals');

        $officer = $this->auth->currentOfficer();
        if ($officer === null) {
            throw new RuntimeException('No authenticated officer.');
        }

        $stmt = $this->db->prepare('UPDATE criminals SET suspect_id = NULL, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $criminalId]);

        if ($stmt->rowCount() === 0) {
            throw new RuntimeException('Criminal not found.');
        }

        $this->auditLogger->logActivity((int)$officer['id'], 'UPDATE', 'criminals', $criminalId, 'Unlinked criminal from suspect');
    }

    public function unlinkCriminalFromCrime(int $criminalId, int $crimeReportId): void
    {
        // Remove criminal-history case link and clear primary crime link when matched.
        $this->auth->enforce('UPDATE', 'criminal_history');

        $officer = $this->auth->currentOfficer();
        if ($officer === null) {
            throw new RuntimeException('No authenticated officer.');
        }

        $this->db->beginTransaction();

        try {
            $historyStmt = $this->db->prepare('DELETE FROM criminal_history WHERE criminal_id = :criminal_id AND crime_report_id = :crime_report_id');
            $historyStmt->execute([
                'criminal_id' => $criminalId,
                'crime_report_id' => $crimeReportId,
            ]);

            $primaryStmt = $this->db->prepare('UPDATE criminals
                                               SET crime_report_id = CASE WHEN crime_report_id = :crime_report_id THEN NULL ELSE crime_report_id END,
                                                   updated_at = NOW()
                                               WHERE id = :criminal_id');
            $primaryStmt->execute([
                'criminal_id' => $criminalId,
                'crime_report_id' => $crimeReportId,
            ]);

            $this->db->commit();

            $this->auditLogger->logActivity((int)$officer['id'], 'UPDATE', 'criminal_history', $criminalId, 'Unlinked criminal from crime report');
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function addCriminalHistory(
        int $criminalId,
        string $offenseTitle,
        ?int $crimeReportId = null,
        ?string $convictionDate = null,
        ?string $sentenceDetails = null,
        ?string $jurisdiction = null,
        ?string $notes = null
    ): int {
        // History rows tied to a case follow the same CLOSED/REFERRED guard.
        $this->auth->enforce('CREATE', 'criminal_history');

        $officer = $this->auth->currentOfficer();
        if ($officer === null) {
            throw new RuntimeException('No authenticated officer.');
        }

        if ($crimeReportId !== null) {
            $statusStmt = $this->db->prepare('SELECT investigation_status FROM crime_reports WHERE id = :id');
            $statusStmt->execute(['id' => $crimeReportId]);
            $status = $statusStmt->fetchColumn();
            if ($status === false) {
                throw new RuntimeException('Crime report not found.');
            }

            $status = strtoupper((string)$status);
            if (!in_array($status, ['CLOSED', 'REFERRED'], true)) {
                throw new RuntimeException('Criminals can only be linked to closed or referred cases.');
            }
        }

        $sql = 'INSERT INTO criminal_history (
                    criminal_id, crime_report_id, offense_title, conviction_date,
                    sentence_details, jurisdiction, notes, created_by_officer_id
                ) VALUES (
                    :criminal_id, :crime_report_id, :offense_title, :conviction_date,
                    :sentence_details, :jurisdiction, :notes, :created_by_officer_id
                )';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'criminal_id' => $criminalId,
            'crime_report_id' => $crimeReportId,
            'offense_title' => $offenseTitle,
            'conviction_date' => $convictionDate,
            'sentence_details' => $sentenceDetails,
            'jurisdiction' => $jurisdiction,
            'notes' => $notes,
            'created_by_officer_id' => (int)$officer['id'],
        ]);

        $id = (int)$this->db->lastInsertId();

        $this->auditLogger->logActivity((int)$officer['id'], 'CREATE', 'criminal_history', $id, 'Added criminal history record');

        return $id;
    }

    public function confirmCriminalForCrime(array $data): int
    {
        // Transaction wrapper: create/reuse criminal profile, then attach offense history.
        $this->auth->enforce('CREATE', 'criminals');

        $officer = $this->auth->currentOfficer();
        if ($officer === null) {
            throw new RuntimeException('No authenticated officer.');
        }

        $crimeReportId = isset($data['crime_report_id']) ? (int)$data['crime_report_id'] : 0;
        if ($crimeReportId <= 0) {
            throw new RuntimeException('Crime report is required for confirming a criminal.');
        }

        $suspectId = isset($data['suspect_id']) ? (int)$data['suspect_id'] : 0;
        if ($suspectId <= 0) {
            throw new RuntimeException('Suspect is required for confirming a criminal.');
        }

        $suspectStmt = $this->db->prepare('SELECT id FROM suspects WHERE id = :id');
        $suspectStmt->execute(['id' => $suspectId]);
        if ($suspectStmt->fetchColumn() === false) {
            throw new RuntimeException('Suspect not found.');
        }

        $this->db->beginTransaction();

        try {
            $existingStmt = $this->db->prepare('SELECT id FROM criminals WHERE suspect_id = :suspect_id');
            $existingStmt->execute(['suspect_id' => $suspectId]);
            $criminalId = $existingStmt->fetchColumn();

            if ($criminalId === false) {
                $criminalId = $this->createCriminalFromSuspect(
                    $suspectId,
                    $crimeReportId,
                    (string)$data['criminal_code'],
                    (string)$data['profile_summary'],
                    (string)$data['risk_level'],
                    (string)$data['current_status']
                );
            } else {
                $criminalId = (int)$criminalId;
            }

            $this->addCriminalHistory(
                $criminalId,
                (string)$data['offense_title'],
                $crimeReportId,
                $data['conviction_date'] ?? null,
                $data['sentence_details'] ?? null,
                $data['jurisdiction'] ?? null,
                $data['notes'] ?? null
            );

            $this->db->commit();

            return $criminalId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteCrimeReport(int $crimeReportId): void
    {
        // Physical delete may fail when FK RESTRICT constraints still have dependents.
        $this->auth->enforce('DELETE', 'crime_reports');

        $stmt = $this->db->prepare('DELETE FROM crime_reports WHERE id = :id');
        $stmt->execute(['id' => $crimeReportId]);

        if ($stmt->rowCount() === 0) {
            throw new RuntimeException('Crime report not found.');
        }

        $officer = $this->auth->currentOfficer();
        if ($officer !== null) {
            $this->auditLogger->logActivity((int)$officer['id'], 'DELETE', 'crime_reports', $crimeReportId, 'Crime report deleted');
        }
    }

    public function deleteSuspect(int $suspectId): void
    {
        $this->auth->enforce('DELETE', 'suspects');

        $stmt = $this->db->prepare('DELETE FROM suspects WHERE id = :id');
        $stmt->execute(['id' => $suspectId]);

        if ($stmt->rowCount() === 0) {
            throw new RuntimeException('Suspect not found.');
        }

        $officer = $this->auth->currentOfficer();
        if ($officer !== null) {
            $this->auditLogger->logActivity((int)$officer['id'], 'DELETE', 'suspects', $suspectId, 'Suspect deleted');
        }
    }

    public function deleteCriminal(int $criminalId): void
    {
        $this->auth->enforce('DELETE', 'criminals');

        $stmt = $this->db->prepare('DELETE FROM criminals WHERE id = :id');
        $stmt->execute(['id' => $criminalId]);

        if ($stmt->rowCount() === 0) {
            throw new RuntimeException('Criminal not found.');
        }

        $officer = $this->auth->currentOfficer();
        if ($officer !== null) {
            $this->auditLogger->logActivity((int)$officer['id'], 'DELETE', 'criminals', $criminalId, 'Criminal deleted');
        }
    }

    public function getCriminalProfile(int $criminalId): ?array
    {
        $this->auth->enforce('READ', 'criminals');

        $sql = 'SELECT c.*, s.first_name, s.last_name, s.national_id
                FROM criminals c
                LEFT JOIN suspects s ON s.id = c.suspect_id
                WHERE c.id = :id
                LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $criminalId]);
        $criminal = $stmt->fetch();

        if ($criminal === false) {
            return null;
        }

        $aliasStmt = $this->db->prepare('SELECT alias_name, alias_note, created_at FROM criminal_aliases WHERE criminal_id = :criminal_id ORDER BY id DESC');
        $aliasStmt->execute(['criminal_id' => $criminalId]);
        $criminal['aliases'] = $aliasStmt->fetchAll();

        $historyStmt = $this->db->prepare('SELECT * FROM criminal_history WHERE criminal_id = :criminal_id ORDER BY id DESC');
        $historyStmt->execute(['criminal_id' => $criminalId]);
        $criminal['history'] = $historyStmt->fetchAll();

        return $criminal;
    }
}
