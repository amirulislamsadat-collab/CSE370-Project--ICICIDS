<?php
declare(strict_types=1);

require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/AuditLogger.php';

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
        $this->auth->enforce('CREATE', 'crime_reports');

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
        $stmt->bindValue(':case_number', $data['case_number']);
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

    public function createSuspect(array $data): int
    {
        $this->auth->enforce('CREATE', 'suspects');

        $officer = $this->auth->currentOfficer();
        if ($officer === null) {
            throw new RuntimeException('No authenticated officer.');
        }

        $sql = 'INSERT INTO suspects (
                    first_name, last_name, date_of_birth, gender, national_id,
                    address_line, phone, reason_for_suspicion, suspect_status, created_by_officer_id
                ) VALUES (
                    :first_name, :last_name, :date_of_birth, :gender, :national_id,
                    :address_line, :phone, :reason_for_suspicion, :suspect_status, :created_by_officer_id
                )';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => strtoupper(trim($data['gender'] ?? 'UNKNOWN')),
            'national_id' => $data['national_id'] ?? null,
            'address_line' => $data['address_line'] ?? null,
            'phone' => $data['phone'] ?? null,
            'reason_for_suspicion' => $data['reason_for_suspicion'],
            'suspect_status' => strtoupper(trim($data['suspect_status'] ?? 'PERSON_OF_INTEREST')),
            'created_by_officer_id' => (int)$officer['id'],
        ]);

        $id = (int)$this->db->lastInsertId();

        $this->auditLogger->logActivity((int)$officer['id'], 'CREATE', 'suspects', $id, 'Suspect profile created');

        return $id;
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

    public function linkSuspectToCrime(int $crimeReportId, int $suspectId, string $relationType = 'PRIMARY', ?string $notes = null): int
    {
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

    public function createCriminalFromSuspect(int $suspectId, string $criminalCode, string $profileSummary, string $riskLevel, string $currentStatus): int
    {
        $this->auth->enforce('CREATE', 'criminals');

        $officer = $this->auth->currentOfficer();
        if ($officer === null) {
            throw new RuntimeException('No authenticated officer.');
        }

        $sql = 'INSERT INTO criminals (
                    suspect_id, criminal_code, profile_summary, risk_level, current_status, added_by_officer_id
                ) VALUES (
                    :suspect_id, :criminal_code, :profile_summary, :risk_level, :current_status, :added_by_officer_id
                )';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'suspect_id' => $suspectId,
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

    public function addCriminalHistory(
        int $criminalId,
        string $offenseTitle,
        ?int $crimeReportId = null,
        ?string $convictionDate = null,
        ?string $sentenceDetails = null,
        ?string $jurisdiction = null,
        ?string $notes = null
    ): int {
        $this->auth->enforce('CREATE', 'criminal_history');

        $officer = $this->auth->currentOfficer();
        if ($officer === null) {
            throw new RuntimeException('No authenticated officer.');
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
