-- Demo data for ICICIDS (Integrated Crime Investigation and Criminal Identification Database System)
-- Import this after init.sql

USE ICICIDS;
SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

-- Officers (passwords)
-- GRADE_1: Admin@123
-- GRADE_2: Admin2@123
-- GRADE_3: Viewer3@123
INSERT INTO officers (
    badge_number, first_name, last_name, email, phone, rank, password_hash, is_active
) VALUES
    ('G1-0001', 'System', 'Admin', 'admin@icicids.local', '+880100000001', 'GRADE_1', '$2y$10$GADAA5S00HNXu.ZcuN.o7O0FM6xrmA4DdpxACgQAfVdXfGj33WxZe', 1),
    ('G2-0001', 'Case', 'Manager', 'admin2@icicids.local', '+880100000002', 'GRADE_2', '$2y$10$RNlk5.319wzYrpqi8V45TuI/PX0sOdgQAUv6sAFZPETehiiqJX3wC', 1),
    ('G3-0001', 'Data', 'Viewer', 'viewer3@icicids.local', '+880100000003', 'GRADE_3', '$2y$10$W2RNxsCr3snFswNTd44r0.kuPtFOnTEcwWriokzbTHFh2s1CCasTG', 1)
ON DUPLICATE KEY UPDATE
    first_name = VALUES(first_name),
    last_name = VALUES(last_name),
    phone = VALUES(phone),
    rank = VALUES(rank),
    password_hash = VALUES(password_hash),
    is_active = VALUES(is_active);

-- Crime reports
INSERT INTO crime_reports (
    case_number, crime_type, location_text, latitude, longitude,
    crime_datetime, description, investigation_status, reported_by_officer_id, assigned_officer_id
)
SELECT 'CRIME-2026/04', 'ROBBERY', 'Downtown Sector A', 6.52440000, 3.37920000,
       '2026-04-20 19:40:00', 'Armed robbery at commercial district entrance.', 'UNDER_INVESTIGATION',
       g1.id, g2.id
FROM officers g1
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE g1.email = 'admin@icicids.local'
ON DUPLICATE KEY UPDATE
    investigation_status = VALUES(investigation_status),
    assigned_officer_id = VALUES(assigned_officer_id),
    description = VALUES(description);

INSERT INTO crime_reports (
    case_number, crime_type, location_text, latitude, longitude,
    crime_datetime, description, investigation_status, reported_by_officer_id, assigned_officer_id
)
SELECT 'CRIME-2026/04', 'CYBERCRIME', 'Tech Hub District', 6.46820000, 3.58520000,
       '2026-04-18 10:10:00', 'Coordinated phishing and wallet-drain incident.', 'OPEN',
       g1.id, g2.id
FROM officers g1
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE g1.email = 'admin@icicids.local'
ON DUPLICATE KEY UPDATE
    investigation_status = VALUES(investigation_status),
    assigned_officer_id = VALUES(assigned_officer_id),
    description = VALUES(description);

INSERT INTO crime_reports (
    case_number, crime_type, location_text, latitude, longitude,
    crime_datetime, description, investigation_status, reported_by_officer_id, assigned_officer_id
)
SELECT 'CRIME-2026/04', 'ASSAULT', 'Riverside Block C', 6.43000000, 3.43000000,
       '2026-04-16 22:15:00', 'Late-night assault reported by neighborhood patrol.', 'CLOSED',
       g1.id, g2.id
FROM officers g1
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE g1.email = 'admin@icicids.local'
ON DUPLICATE KEY UPDATE
    investigation_status = VALUES(investigation_status),
    assigned_officer_id = VALUES(assigned_officer_id),
    description = VALUES(description);

INSERT INTO crime_reports (
    case_number, crime_type, location_text, latitude, longitude,
    crime_datetime, description, investigation_status, reported_by_officer_id, assigned_officer_id
)
SELECT 'CRIME-2026/04', 'THEFT', 'Old Market Lane', 6.50010000, 3.42090000,
       '2026-04-14 08:30:00', 'Reported pickpocketing incident with no suspect identified.', 'OPEN',
       g1.id, g2.id
FROM officers g1
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE g1.email = 'admin@icicids.local'
ON DUPLICATE KEY UPDATE
    investigation_status = VALUES(investigation_status),
    assigned_officer_id = VALUES(assigned_officer_id),
    description = VALUES(description);

INSERT INTO crime_reports (
    case_number, crime_type, location_text, latitude, longitude,
    crime_datetime, description, investigation_status, reported_by_officer_id, assigned_officer_id
)
SELECT 'CRIME-2026/04', 'BURGLARY', 'Harbor View Estate', 6.47120000, 3.51230000,
       '2026-04-12 02:10:00', 'Forced entry reported; evidence recovered on site.', 'OPEN',
       g1.id, g2.id
FROM officers g1
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE g1.email = 'admin@icicids.local'
ON DUPLICATE KEY UPDATE
    investigation_status = VALUES(investigation_status),
    assigned_officer_id = VALUES(assigned_officer_id),
    description = VALUES(description);

INSERT INTO crime_reports (
    case_number, crime_type, location_text, latitude, longitude,
    crime_datetime, description, investigation_status, reported_by_officer_id, assigned_officer_id
)
SELECT 'CRIME-2026/04', 'KIDNAPPING', 'East Ring Road', 6.45510000, 3.45170000,
       '2026-04-10 21:05:00', 'Suspect identified; investigation ongoing.', 'UNDER_INVESTIGATION',
       g1.id, g2.id
FROM officers g1
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE g1.email = 'admin@icicids.local'
ON DUPLICATE KEY UPDATE
    investigation_status = VALUES(investigation_status),
    assigned_officer_id = VALUES(assigned_officer_id),
    description = VALUES(description);

INSERT INTO crime_reports (
    case_number, crime_type, location_text, latitude, longitude,
    crime_datetime, description, investigation_status, reported_by_officer_id, assigned_officer_id
)
SELECT 'CRIME-2025/11', 'ROBBERY', 'Lagos Island Precinct', 6.45600000, 3.40300000,
       '2025-11-08 18:20:00', 'Closed robbery case with confirmed conviction.', 'CLOSED',
       g1.id, g1.id
FROM officers g1
WHERE g1.email = 'admin@icicids.local'
ON DUPLICATE KEY UPDATE
    investigation_status = VALUES(investigation_status),
    assigned_officer_id = VALUES(assigned_officer_id),
    description = VALUES(description);

INSERT INTO crime_reports (
    case_number, crime_type, location_text, latitude, longitude,
    crime_datetime, description, investigation_status, reported_by_officer_id, assigned_officer_id
)
SELECT 'CRIME-2025/09', 'CYBERCRIME', 'Federal High Court Annex', 6.46660000, 3.37220000,
       '2025-09-15 14:40:00', 'Closed cybercrime case referred for sentencing.', 'REFERRED',
       g1.id, g1.id
FROM officers g1
WHERE g1.email = 'admin@icicids.local'
ON DUPLICATE KEY UPDATE
    investigation_status = VALUES(investigation_status),
    assigned_officer_id = VALUES(assigned_officer_id),
    description = VALUES(description);

-- Suspects
INSERT INTO suspects (
    first_name, last_name, crime_report_id, date_of_birth, gender, national_id,
    address_line, phone, reason_for_suspicion, suspect_status, created_by_officer_id
)
SELECT 'John', 'Doe', cr.id, '1990-05-15', 'MALE', 'NID-ICI-0001',
    'Unknown', '+880100000004', 'Witnesses place suspect at robbery scene.', 'WANTED', g2.id
FROM crime_reports cr
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE cr.crime_datetime = '2026-04-20 19:40:00'
ON DUPLICATE KEY UPDATE
    crime_report_id = VALUES(crime_report_id),
    reason_for_suspicion = VALUES(reason_for_suspicion),
    suspect_status = VALUES(suspect_status),
    created_by_officer_id = VALUES(created_by_officer_id);

INSERT INTO suspects (
    first_name, last_name, crime_report_id, date_of_birth, gender, national_id,
    address_line, phone, reason_for_suspicion, suspect_status, created_by_officer_id
)
SELECT 'Amaka', 'Nwosu', cr.id, '1988-11-22', 'FEMALE', 'NID-ICI-0002',
    '15 Unity Street', '+880100000005', 'Linked to cybercrime wallet infrastructure.', 'PERSON_OF_INTEREST', g2.id
FROM crime_reports cr
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE cr.crime_datetime = '2026-04-18 10:10:00'
ON DUPLICATE KEY UPDATE
    crime_report_id = VALUES(crime_report_id),
    reason_for_suspicion = VALUES(reason_for_suspicion),
    suspect_status = VALUES(suspect_status),
    created_by_officer_id = VALUES(created_by_officer_id);

INSERT INTO suspects (
    first_name, last_name, crime_report_id, date_of_birth, gender, national_id,
    address_line, phone, reason_for_suspicion, suspect_status, created_by_officer_id
)
SELECT 'Peter', 'Ibrahim', cr.id, '1995-03-04', 'MALE', 'NID-ICI-0003',
    '7 Market Road', '+880100000006', 'Present during assault and identified by CCTV.', 'ARRESTED', g2.id
FROM crime_reports cr
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE cr.crime_datetime = '2026-04-16 22:15:00'
ON DUPLICATE KEY UPDATE
    crime_report_id = VALUES(crime_report_id),
    reason_for_suspicion = VALUES(reason_for_suspicion),
    suspect_status = VALUES(suspect_status),
    created_by_officer_id = VALUES(created_by_officer_id);

INSERT INTO suspects (
    first_name, last_name, crime_report_id, date_of_birth, gender, national_id,
    address_line, phone, reason_for_suspicion, suspect_status, created_by_officer_id
)
SELECT 'Lillian', 'Okoro', cr.id, '1998-02-19', 'FEMALE', 'NID-ICI-0004',
    '23 Bay View', '+880100000007', 'Identified near kidnapping scene; vehicle matched witness report.', 'PERSON_OF_INTEREST', g2.id
FROM crime_reports cr
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE cr.crime_datetime = '2026-04-10 21:05:00'
ON DUPLICATE KEY UPDATE
    crime_report_id = VALUES(crime_report_id),
    reason_for_suspicion = VALUES(reason_for_suspicion),
    suspect_status = VALUES(suspect_status),
    created_by_officer_id = VALUES(created_by_officer_id);

-- Link suspects to reports
INSERT INTO crime_report_suspects (crime_report_id, suspect_id, relation_type, notes, linked_by_officer_id)
SELECT cr.id, s.id, 'PRIMARY', 'Primary suspect for armed robbery.', g2.id
FROM crime_reports cr
JOIN suspects s ON s.national_id = 'NID-ICI-0001'
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE cr.crime_datetime = '2026-04-20 19:40:00'
ON DUPLICATE KEY UPDATE
    relation_type = VALUES(relation_type),
    notes = VALUES(notes),
    linked_by_officer_id = VALUES(linked_by_officer_id);

INSERT INTO crime_report_suspects (crime_report_id, suspect_id, relation_type, notes, linked_by_officer_id)
SELECT cr.id, s.id, 'PRIMARY', 'Person of interest in cybercrime case.', g2.id
FROM crime_reports cr
JOIN suspects s ON s.national_id = 'NID-ICI-0002'
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE cr.crime_datetime = '2026-04-18 10:10:00'
ON DUPLICATE KEY UPDATE
    relation_type = VALUES(relation_type),
    notes = VALUES(notes),
    linked_by_officer_id = VALUES(linked_by_officer_id);

-- Bulk demo dataset (compat mode: numbers-table instead of CTE)

-- Bulk demo crime_reports (1..120)
INSERT INTO crime_reports (
    case_number, crime_type, location_text, latitude, longitude,
    crime_datetime, description, investigation_status, reported_by_officer_id, assigned_officer_id
)
SELECT
    CONCAT('CRIME-', DATE_FORMAT(DATE_ADD('2026-01-01 08:00:00', INTERVAL seq.n DAY), '%Y/%m')),
    CASE MOD(seq.n, 6)
        WHEN 0 THEN 'ROBBERY'
        WHEN 1 THEN 'CYBERCRIME'
        WHEN 2 THEN 'BURGLARY'
        WHEN 3 THEN 'ASSAULT'
        WHEN 4 THEN 'THEFT'
        ELSE 'KIDNAPPING'
    END,
    CONCAT('Demo Sector ', LPAD(seq.n, 3, '0')),
    6.400000 + (seq.n / 1000),
    3.300000 + (seq.n / 1000),
    DATE_ADD('2026-01-01 08:00:00', INTERVAL seq.n DAY),
    CONCAT('Auto-generated demo report #', seq.n, '.'),
    CASE
        WHEN MOD(seq.n, 12) = 0 THEN 'REFERRED'
        WHEN MOD(seq.n, 10) = 0 THEN 'CLOSED'
        WHEN MOD(seq.n, 4) = 0 THEN 'SUSPENDED'
        WHEN MOD(seq.n, 3) = 0 THEN 'UNDER_INVESTIGATION'
        ELSE 'OPEN'
    END,
    g1.id, g2.id
FROM (
    SELECT units.n + tens.n * 10 + hundreds.n * 100 AS n
    FROM (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) units
    CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) tens
    CROSS JOIN (SELECT 0 n UNION ALL SELECT 1) hundreds
    ) seq
JOIN officers g1 ON g1.email = 'admin@icicids.local'
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE seq.n BETWEEN 1 AND 120
  AND NOT EXISTS (
    SELECT 1 FROM crime_reports existing
    WHERE existing.location_text = CONCAT('Demo Sector ', LPAD(seq.n, 3, '0'))
);

-- Suspects for demo crime reports
INSERT INTO suspects (
    first_name, last_name, crime_report_id, date_of_birth, gender, national_id,
    address_line, phone, reason_for_suspicion, suspect_status, created_by_officer_id
)
SELECT
    CONCAT('Demo', seq.n),
    CONCAT('Subject', seq.n),
    cr.id,
    DATE_SUB('1995-01-01', INTERVAL seq.n DAY),
    CASE WHEN MOD(seq.n, 2) = 0 THEN 'MALE' ELSE 'FEMALE' END,
    CONCAT('NID-ICI-', LPAD(1000 + seq.n, 4, '0')),
    CONCAT('Block ', LPAD(seq.n, 3, '0'), ', Demo City'),
    CONCAT('+8801', LPAD(seq.n, 8, '0')),
    CONCAT('Auto-generated suspect for demo case ', LPAD(seq.n, 3, '0')),
    CASE
        WHEN MOD(seq.n, 5) = 0 THEN 'ARRESTED'
        WHEN MOD(seq.n, 4) = 0 THEN 'WANTED'
        WHEN MOD(seq.n, 3) = 0 THEN 'CLEARED'
        ELSE 'PERSON_OF_INTEREST'
    END,
    g2.id
FROM (
    SELECT units.n + tens.n * 10 + hundreds.n * 100 AS n
    FROM (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) units
    CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) tens
    CROSS JOIN (SELECT 0 n UNION ALL SELECT 1) hundreds
    ) seq
JOIN crime_reports cr ON cr.location_text = CONCAT('Demo Sector ', LPAD(seq.n, 3, '0'))
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE seq.n BETWEEN 1 AND 120
ON DUPLICATE KEY UPDATE
    crime_report_id = VALUES(crime_report_id),
    reason_for_suspicion = VALUES(reason_for_suspicion),
    suspect_status = VALUES(suspect_status),
    created_by_officer_id = VALUES(created_by_officer_id);

-- Auto-link suspects to their reports
INSERT INTO crime_report_suspects (crime_report_id, suspect_id, relation_type, notes, linked_by_officer_id)
SELECT cr.id, s.id, 'PRIMARY', 'Auto-linked demo suspect.', g2.id
FROM suspects s
JOIN crime_reports cr ON cr.id = s.crime_report_id
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE s.national_id LIKE 'NID-ICI-1%'
ON DUPLICATE KEY UPDATE
    relation_type = VALUES(relation_type),
    notes = VALUES(notes),
    linked_by_officer_id = VALUES(linked_by_officer_id);

-- Evidence for demo cases
INSERT INTO evidence (
    evidence_code, crime_report_id, evidence_type, title, description, file_path,
    collected_at, collected_by_officer_id, storage_location, chain_status, integrity_hash
)
SELECT
    CONCAT('EVD-1', LPAD(seq.n, 4, '0')),
    cr.id,
    CASE MOD(seq.n, 6)
        WHEN 0 THEN 'DIGITAL_FILE'
        WHEN 1 THEN 'DOCUMENT'
        WHEN 2 THEN 'WEAPON'
        WHEN 3 THEN 'FINGERPRINT'
        WHEN 4 THEN 'BIOLOGICAL'
        ELSE 'VIDEO'
    END,
    CONCAT('Demo Evidence ', LPAD(seq.n, 3, '0')),
    CONCAT('Auto-generated evidence for demo case ', LPAD(seq.n, 3, '0')),
    CONCAT('/evidence/demo/evd-', LPAD(seq.n, 4, '0'), '.dat'),
    DATE_ADD('2026-01-01 10:00:00', INTERVAL seq.n DAY),
    g2.id,
    CONCAT('Storage Bin ', LPAD(seq.n, 3, '0')),
    CASE
        WHEN MOD(seq.n, 5) = 0 THEN 'IN_COURT'
        WHEN MOD(seq.n, 4) = 0 THEN 'IN_STORAGE'
        WHEN MOD(seq.n, 3) = 0 THEN 'IN_LAB'
        ELSE 'COLLECTED'
    END,
    SHA2(CONCAT('EVD-1', LPAD(seq.n, 4, '0')), 256)
FROM (
    SELECT units.n + tens.n * 10 + hundreds.n * 100 AS n
    FROM (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) units
    CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) tens
    CROSS JOIN (SELECT 0 n UNION ALL SELECT 1) hundreds
    ) seq
JOIN crime_reports cr ON cr.location_text = CONCAT('Demo Sector ', LPAD(seq.n, 3, '0'))
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE seq.n BETWEEN 1 AND 120
ON DUPLICATE KEY UPDATE
    crime_report_id = VALUES(crime_report_id),
    description = VALUES(description),
    chain_status = VALUES(chain_status),
    integrity_hash = VALUES(integrity_hash);

-- Auto-link evidence to reports and suspects
INSERT INTO crime_report_evidence (crime_report_id, evidence_id, linked_by_officer_id, relation_note)
SELECT cr.id, e.id, g2.id, 'Auto-linked demo evidence.'
FROM evidence e
JOIN crime_reports cr ON cr.id = e.crime_report_id
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE e.evidence_code LIKE 'EVD-1%'
  AND NOT EXISTS (
      SELECT 1 FROM crime_report_evidence cre
      WHERE cre.crime_report_id = cr.id AND cre.evidence_id = e.id
  );

INSERT INTO suspect_evidence (suspect_id, evidence_id, relevance_reason, linked_by_officer_id)
SELECT s.id, e.id, 'Auto-linked demo evidence to suspect.', g2.id
FROM suspects s
JOIN evidence e ON e.crime_report_id = s.crime_report_id
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE s.national_id LIKE 'NID-ICI-1%'
  AND NOT EXISTS (
      SELECT 1 FROM suspect_evidence se
      WHERE se.suspect_id = s.id AND se.evidence_id = e.id
  );

-- Criminals derived from some demo records
INSERT INTO criminals (suspect_id, crime_report_id, criminal_code, profile_summary, risk_level, current_status, added_by_officer_id)
SELECT
    s.id,
    cr.id,
    CONCAT('CRIM-', LPAD(2000 + seq.n, 4, '0')),
    CONCAT('Demo criminal profile for suspect ', LPAD(seq.n, 3, '0')),
    CASE
        WHEN MOD(seq.n, 4) = 0 THEN 'HIGH'
        WHEN MOD(seq.n, 3) = 0 THEN 'MEDIUM'
        ELSE 'LOW'
    END,
    CASE
        WHEN MOD(seq.n, 5) = 0 THEN 'PAROLE'
        WHEN MOD(seq.n, 3) = 0 THEN 'INCARCERATED'
        ELSE 'AT_LARGE'
    END,
    g1.id
FROM (
    SELECT units.n + tens.n * 10 + hundreds.n * 100 AS n
    FROM (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) units
    CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) tens
    CROSS JOIN (SELECT 0 n UNION ALL SELECT 1) hundreds
    ) seq
JOIN crime_reports cr ON cr.location_text = CONCAT('Demo Sector ', LPAD(seq.n, 3, '0'))
JOIN suspects s ON s.crime_report_id = cr.id
JOIN officers g1 ON g1.email = 'admin@icicids.local'
WHERE seq.n BETWEEN 1 AND 120
  AND cr.investigation_status IN ('CLOSED', 'REFERRED')
  AND MOD(seq.n, 4) = 0
ON DUPLICATE KEY UPDATE
    crime_report_id = VALUES(crime_report_id),
    profile_summary = VALUES(profile_summary),
    risk_level = VALUES(risk_level),
    current_status = VALUES(current_status),
    added_by_officer_id = VALUES(added_by_officer_id);

INSERT INTO crime_report_evidence (crime_report_id, evidence_id, linked_by_officer_id, relation_note)
SELECT cr.id, e.id, g2.id, 'Ledger ties suspects to laundering path.'
FROM crime_reports cr
JOIN evidence e ON e.evidence_code = 'EVD-0002'
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE cr.crime_datetime = '2026-04-18 10:10:00'
ON DUPLICATE KEY UPDATE
    relation_note = VALUES(relation_note),
    linked_by_officer_id = VALUES(linked_by_officer_id);

INSERT INTO crime_report_evidence (crime_report_id, evidence_id, linked_by_officer_id, relation_note)
SELECT cr.id, e.id, g2.id, 'Weapon matched witness account and medical report.'
FROM crime_reports cr
JOIN evidence e ON e.evidence_code = 'EVD-0003'
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE cr.crime_datetime = '2026-04-16 22:15:00'
ON DUPLICATE KEY UPDATE
    relation_note = VALUES(relation_note),
    linked_by_officer_id = VALUES(linked_by_officer_id);

INSERT INTO crime_report_evidence (crime_report_id, evidence_id, linked_by_officer_id, relation_note)
SELECT cr.id, e.id, g2.id, 'Prints lifted from entry point; suspect unknown.'
FROM crime_reports cr
JOIN evidence e ON e.evidence_code = 'EVD-0004'
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE cr.crime_datetime = '2026-04-12 02:10:00'
ON DUPLICATE KEY UPDATE
    relation_note = VALUES(relation_note),
    linked_by_officer_id = VALUES(linked_by_officer_id);

-- Link evidence to suspects
INSERT INTO suspect_evidence (suspect_id, evidence_id, relevance_reason, linked_by_officer_id)
SELECT s.id, e.id, 'Facial match from CCTV frame extraction.', g2.id
FROM suspects s
JOIN evidence e ON e.evidence_code = 'EVD-0001'
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE s.national_id = 'NID-ICI-0001'
ON DUPLICATE KEY UPDATE
    relevance_reason = VALUES(relevance_reason),
    linked_by_officer_id = VALUES(linked_by_officer_id);

INSERT INTO suspect_evidence (suspect_id, evidence_id, relevance_reason, linked_by_officer_id)
SELECT s.id, e.id, 'Document fingerprints map to suspect workstation.', g2.id
FROM suspects s
JOIN evidence e ON e.evidence_code = 'EVD-0002'
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE s.national_id = 'NID-ICI-0002'
ON DUPLICATE KEY UPDATE
    relevance_reason = VALUES(relevance_reason),
    linked_by_officer_id = VALUES(linked_by_officer_id);

INSERT INTO suspect_evidence (suspect_id, evidence_id, relevance_reason, linked_by_officer_id)
SELECT s.id, e.id, 'Weapon proximity and witness testimony overlap.', g2.id
FROM suspects s
JOIN evidence e ON e.evidence_code = 'EVD-0003'
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE s.national_id = 'NID-ICI-0003'
ON DUPLICATE KEY UPDATE
    relevance_reason = VALUES(relevance_reason),
    linked_by_officer_id = VALUES(linked_by_officer_id);

-- Investigation updates
INSERT INTO investigation_updates (crime_report_id, update_type, progress_percent, note_text, created_by_officer_id)
SELECT cr.id, 'NOTE', 40, 'Initial witnesses interviewed and CCTV evidence ingested.', g2.id
FROM crime_reports cr
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE cr.crime_datetime = '2026-04-20 19:40:00';

INSERT INTO investigation_updates (crime_report_id, update_type, progress_percent, note_text, created_by_officer_id)
SELECT cr.id, 'FORENSIC_RESULT', 60, 'Forensic team validated metadata integrity of seized devices.', g2.id
FROM crime_reports cr
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE cr.crime_datetime = '2026-04-18 10:10:00';

INSERT INTO investigation_updates (crime_report_id, update_type, progress_percent, note_text, created_by_officer_id)
SELECT cr.id, 'STATUS_CHANGE', 100, 'Case concluded and transferred for court filing.', g2.id
FROM crime_reports cr
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE cr.crime_datetime = '2026-04-16 22:15:00';

-- Interviews
INSERT INTO interviews (
    crime_report_id, suspect_id, interviewee_name, interviewee_role,
    interview_datetime, summary, conducted_by_officer_id
)
SELECT cr.id, s.id, 'John Doe', 'SUSPECT', '2026-04-21 09:30:00',
       'Suspect denied involvement; statements conflicted with camera timeline.', g2.id
FROM crime_reports cr
JOIN suspects s ON s.national_id = 'NID-ICI-0001'
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE cr.crime_datetime = '2026-04-20 19:40:00';

INSERT INTO interviews (
    crime_report_id, suspect_id, interviewee_name, interviewee_role,
    interview_datetime, summary, conducted_by_officer_id
)
SELECT cr.id, NULL, 'Ifeoma U.', 'WITNESS', '2026-04-18 15:10:00',
       'Witness provided timeline of suspicious transactions and contact patterns.', g2.id
FROM crime_reports cr
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE cr.crime_datetime = '2026-04-18 10:10:00';

INSERT INTO interviews (
    crime_report_id, suspect_id, interviewee_name, interviewee_role,
    interview_datetime, summary, conducted_by_officer_id
)
SELECT cr.id, s.id, 'Peter Ibrahim', 'SUSPECT', '2026-04-17 11:20:00',
       'Suspect admitted altercation but disputed weapon possession.', g2.id
FROM crime_reports cr
JOIN suspects s ON s.national_id = 'NID-ICI-0003'
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE cr.crime_datetime = '2026-04-16 22:15:00';

-- Schema requests (from Grade 2)
INSERT INTO schema_requests (
    requested_by_officer_id, request_type, object_name, reason, sql_proposal,
    status, reviewed_by_officer_id, review_notes, reviewed_at
)
SELECT g2.id, 'INDEX', 'crime_reports',
       'Optimize dashboard filters by status/date.',
       'ALTER TABLE crime_reports ADD INDEX idx_status_datetime (investigation_status, crime_datetime);',
       'APPROVED', g1.id, 'Approved for rollout in maintenance window.', NOW()
FROM officers g1
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE g1.email = 'admin@icicids.local';

INSERT INTO schema_requests (
        requested_by_officer_id, request_type, object_name, reason, sql_proposal,
        status, reviewed_by_officer_id, review_notes, reviewed_at
)
SELECT g2.id, 'ALTER', 'evidence',
             'Add a chain-of-custody status column for faster review.',
             'ALTER TABLE evidence ADD COLUMN custody_review_note VARCHAR(255) NULL AFTER chain_status;',
             'PENDING', NULL, NULL, NULL
FROM officers g2
WHERE g2.email = 'admin2@icicids.local'
    AND NOT EXISTS (
            SELECT 1 FROM schema_requests sr
            WHERE sr.object_name = 'evidence'
                AND sr.request_type = 'ALTER'
                AND sr.reason = 'Add a chain-of-custody status column for faster review.'
    );

INSERT INTO schema_requests (
        requested_by_officer_id, request_type, object_name, reason, sql_proposal,
        status, reviewed_by_officer_id, review_notes, reviewed_at
)
SELECT g1.id, 'CREATE', 'audit_export_archive',
             'Provide an archive table for exported audit snapshots.',
             'CREATE TABLE audit_export_archive (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, export_name VARCHAR(150) NOT NULL, exported_by_officer_id BIGINT UNSIGNED NOT NULL, exported_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, notes TEXT NULL);',
             'APPROVED', g1.id, 'Requested by Grade 1 for admin reporting.', NOW()
FROM officers g1
WHERE g1.email = 'admin@icicids.local'
    AND NOT EXISTS (
            SELECT 1 FROM schema_requests sr
            WHERE sr.object_name = 'audit_export_archive'
                AND sr.request_type = 'CREATE'
                AND sr.reason = 'Provide an archive table for exported audit snapshots.'
    );

-- Feedbacks (Grade 2 and Grade 3)
INSERT INTO feedbacks (submitted_by_officer_id, module_name, category, message, status, resolved_by_officer_id, resolved_at)
SELECT g2.id, 'INVESTIGATION_PROGRESS', 'FEATURE', 'Need export to CSV for timeline reports.', 'IN_REVIEW', NULL, NULL
FROM officers g2 WHERE g2.email = 'admin2@icicids.local';

INSERT INTO feedbacks (submitted_by_officer_id, module_name, category, message, status, resolved_by_officer_id, resolved_at)
SELECT g3.id, 'AUTH_SECURITY', 'USABILITY', 'Viewer dashboard should hide write-only controls.', 'NEW', NULL, NULL
FROM officers g3 WHERE g3.email = 'viewer3@icicids.local';

-- Login logs
INSERT INTO login_logs (
    officer_id, email_attempted, login_status, login_time, ip_address,
    ip_location, user_agent, device_fingerprint, failure_reason
)
SELECT g1.id, g1.email, 'SUCCESS', NOW() - INTERVAL 2 DAY, '127.0.0.1',
       'Local Development', 'Mozilla/5.0 Demo Browser', SHA2(CONCAT(g1.email, 'ok1'), 256), NULL
FROM officers g1 WHERE g1.email = 'admin@icicids.local';

INSERT INTO login_logs (
    officer_id, email_attempted, login_status, login_time, ip_address,
    ip_location, user_agent, device_fingerprint, failure_reason
)
SELECT g2.id, g2.email, 'SUCCESS', NOW() - INTERVAL 1 DAY, '127.0.0.1',
       'Local Development', 'Mozilla/5.0 Demo Browser', SHA2(CONCAT(g2.email, 'ok2'), 256), NULL
FROM officers g2 WHERE g2.email = 'admin2@icicids.local';

INSERT INTO login_logs (
    officer_id, email_attempted, login_status, login_time, ip_address,
    ip_location, user_agent, device_fingerprint, failure_reason
)
SELECT NULL, 'unknown@icicids.local', 'FAILED', NOW() - INTERVAL 12 HOUR, '127.0.0.1',
       'Local Development', 'Mozilla/5.0 Demo Browser', SHA2('unknown@icicids.local', 256), 'Invalid credentials';

-- Officer activity samples
INSERT INTO officer_activity (
    officer_id, activity_type, target_table, target_record_id, action_details,
    ip_address, ip_location, user_agent, created_at
)
SELECT g1.id, 'CREATE', 'crime_reports', cr.id, 'Created demo robbery case.',
       '127.0.0.1', 'Local Development', 'Seeder/1.0', NOW() - INTERVAL 3 DAY
FROM officers g1
JOIN crime_reports cr ON cr.crime_datetime = '2026-04-20 19:40:00'
WHERE g1.email = 'admin@icicids.local';

INSERT INTO officer_activity (
    officer_id, activity_type, target_table, target_record_id, action_details,
    ip_address, ip_location, user_agent, created_at
)
SELECT g2.id, 'SUSPECT_UPDATE', 'suspects', s.id, 'Updated suspect status during investigation.',
       '127.0.0.1', 'Local Development', 'Seeder/1.0', NOW() - INTERVAL 2 DAY
FROM officers g2
JOIN suspects s ON s.national_id = 'NID-ICI-0001'
WHERE g2.email = 'admin2@icicids.local';

INSERT INTO officer_activity (
    officer_id, activity_type, target_table, target_record_id, action_details,
    ip_address, ip_location, user_agent, created_at
)
SELECT g2.id, 'EVIDENCE_UPLOAD', 'evidence', e.id, 'Uploaded CCTV extract and checksum.',
       '127.0.0.1', 'Local Development', 'Seeder/1.0', NOW() - INTERVAL 1 DAY
FROM officers g2
JOIN evidence e ON e.evidence_code = 'EVD-0001'
WHERE g2.email = 'admin2@icicids.local';

-- Bulk demo dataset (120 additional crimes, suspects, evidence, and criminals)
-- Bulk demo crime_reports (1..120)
INSERT INTO crime_reports (
    case_number, crime_type, location_text, latitude, longitude,
    crime_datetime, description, investigation_status, reported_by_officer_id, assigned_officer_id
)
SELECT
    CONCAT('CRIME-', DATE_FORMAT(DATE_ADD('2026-01-01 08:00:00', INTERVAL seq.n DAY), '%Y/%m')),
    CASE MOD(seq.n, 6)
        WHEN 0 THEN 'ROBBERY'
        WHEN 1 THEN 'CYBERCRIME'
        WHEN 2 THEN 'BURGLARY'
        WHEN 3 THEN 'ASSAULT'
        WHEN 4 THEN 'THEFT'
        ELSE 'KIDNAPPING'
    END,
    CONCAT('Demo Sector ', LPAD(seq.n, 3, '0')),
    6.400000 + (seq.n / 1000),
    3.300000 + (seq.n / 1000),
    DATE_ADD('2026-01-01 08:00:00', INTERVAL seq.n DAY),
    CONCAT('Auto-generated demo report #', seq.n, '.'),
    CASE
        WHEN MOD(seq.n, 12) = 0 THEN 'REFERRED'
        WHEN MOD(seq.n, 10) = 0 THEN 'CLOSED'
        WHEN MOD(seq.n, 4) = 0 THEN 'SUSPENDED'
        WHEN MOD(seq.n, 3) = 0 THEN 'UNDER_INVESTIGATION'
        ELSE 'OPEN'
    END,
    g1.id, g2.id
FROM (
    SELECT units.n + tens.n * 10 + hundreds.n * 100 AS n
    FROM (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) units
    CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) tens
    CROSS JOIN (SELECT 0 n UNION ALL SELECT 1) hundreds
    ) seq
JOIN officers g1 ON g1.email = 'admin@icicids.local'
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE seq.n BETWEEN 1 AND 120
  AND NOT EXISTS (
    SELECT 1 FROM crime_reports existing
    WHERE existing.location_text = CONCAT('Demo Sector ', LPAD(seq.n, 3, '0'))
);

-- Suspects for demo crime reports
INSERT INTO suspects (
    first_name, last_name, crime_report_id, date_of_birth, gender, national_id,
    address_line, phone, reason_for_suspicion, suspect_status, created_by_officer_id
)
SELECT
    CONCAT('Demo', seq.n),
    CONCAT('Subject', seq.n),
    cr.id,
    DATE_SUB('1995-01-01', INTERVAL seq.n DAY),
    CASE WHEN MOD(seq.n, 2) = 0 THEN 'MALE' ELSE 'FEMALE' END,
    CONCAT('NID-ICI-', LPAD(1000 + seq.n, 4, '0')),
    CONCAT('Block ', LPAD(seq.n, 3, '0'), ', Demo City'),
    CONCAT('+8801', LPAD(seq.n, 8, '0')),
    CONCAT('Auto-generated suspect for demo case ', LPAD(seq.n, 3, '0')),
    CASE
        WHEN MOD(seq.n, 5) = 0 THEN 'ARRESTED'
        WHEN MOD(seq.n, 4) = 0 THEN 'WANTED'
        WHEN MOD(seq.n, 3) = 0 THEN 'CLEARED'
        ELSE 'PERSON_OF_INTEREST'
    END,
    g2.id
FROM (
    SELECT units.n + tens.n * 10 + hundreds.n * 100 AS n
    FROM (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) units
    CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) tens
    CROSS JOIN (SELECT 0 n UNION ALL SELECT 1) hundreds
    ) seq
JOIN crime_reports cr ON cr.location_text = CONCAT('Demo Sector ', LPAD(seq.n, 3, '0'))
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE seq.n BETWEEN 1 AND 120
ON DUPLICATE KEY UPDATE
    crime_report_id = VALUES(crime_report_id),
    reason_for_suspicion = VALUES(reason_for_suspicion),
    suspect_status = VALUES(suspect_status),
    created_by_officer_id = VALUES(created_by_officer_id);

-- Auto-link suspects to their reports
INSERT INTO crime_report_suspects (crime_report_id, suspect_id, relation_type, notes, linked_by_officer_id)
SELECT cr.id, s.id, 'PRIMARY', 'Auto-linked demo suspect.', g2.id
FROM suspects s
JOIN crime_reports cr ON cr.id = s.crime_report_id
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE s.national_id LIKE 'NID-ICI-1%'
ON DUPLICATE KEY UPDATE
    relation_type = VALUES(relation_type),
    notes = VALUES(notes),
    linked_by_officer_id = VALUES(linked_by_officer_id);

-- Evidence for demo cases
INSERT INTO evidence (
    evidence_code, crime_report_id, evidence_type, title, description, file_path,
    collected_at, collected_by_officer_id, storage_location, chain_status, integrity_hash
)
SELECT
    CONCAT('EVD-1', LPAD(seq.n, 4, '0')),
    cr.id,
    CASE MOD(seq.n, 6)
        WHEN 0 THEN 'DIGITAL_FILE'
        WHEN 1 THEN 'DOCUMENT'
        WHEN 2 THEN 'WEAPON'
        WHEN 3 THEN 'FINGERPRINT'
        WHEN 4 THEN 'BIOLOGICAL'
        ELSE 'VIDEO'
    END,
    CONCAT('Demo Evidence ', LPAD(seq.n, 3, '0')),
    CONCAT('Auto-generated evidence for demo case ', LPAD(seq.n, 3, '0')),
    CONCAT('/evidence/demo/evd-', LPAD(seq.n, 4, '0'), '.dat'),
    DATE_ADD('2026-01-01 10:00:00', INTERVAL seq.n DAY),
    g2.id,
    CONCAT('Storage Bin ', LPAD(seq.n, 3, '0')),
    CASE
        WHEN MOD(seq.n, 5) = 0 THEN 'IN_COURT'
        WHEN MOD(seq.n, 4) = 0 THEN 'IN_STORAGE'
        WHEN MOD(seq.n, 3) = 0 THEN 'IN_LAB'
        ELSE 'COLLECTED'
    END,
    SHA2(CONCAT('EVD-1', LPAD(seq.n, 4, '0')), 256)
FROM (
    SELECT units.n + tens.n * 10 + hundreds.n * 100 AS n
    FROM (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) units
    CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) tens
    CROSS JOIN (SELECT 0 n UNION ALL SELECT 1) hundreds
    ) seq
JOIN crime_reports cr ON cr.location_text = CONCAT('Demo Sector ', LPAD(seq.n, 3, '0'))
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE seq.n BETWEEN 1 AND 120
ON DUPLICATE KEY UPDATE
    crime_report_id = VALUES(crime_report_id),
    description = VALUES(description),
    chain_status = VALUES(chain_status),
    integrity_hash = VALUES(integrity_hash);

-- Auto-link evidence to reports and suspects
INSERT INTO crime_report_evidence (crime_report_id, evidence_id, linked_by_officer_id, relation_note)
SELECT cr.id, e.id, g2.id, 'Auto-linked demo evidence.'
FROM evidence e
JOIN crime_reports cr ON cr.id = e.crime_report_id
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE e.evidence_code LIKE 'EVD-1%'
  AND NOT EXISTS (
      SELECT 1 FROM crime_report_evidence cre
      WHERE cre.crime_report_id = cr.id AND cre.evidence_id = e.id
  );

INSERT INTO suspect_evidence (suspect_id, evidence_id, relevance_reason, linked_by_officer_id)
SELECT s.id, e.id, 'Auto-linked demo evidence to suspect.', g2.id
FROM suspects s
JOIN evidence e ON e.crime_report_id = s.crime_report_id
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE s.national_id LIKE 'NID-ICI-1%'
  AND NOT EXISTS (
      SELECT 1 FROM suspect_evidence se
      WHERE se.suspect_id = s.id AND se.evidence_id = e.id
  );

-- Criminals derived from some demo records
INSERT INTO criminals (suspect_id, crime_report_id, criminal_code, profile_summary, risk_level, current_status, added_by_officer_id)
SELECT
    s.id,
    cr.id,
    CONCAT('CRIM-', LPAD(2000 + seq.n, 4, '0')),
    CONCAT('Demo criminal profile for suspect ', LPAD(seq.n, 3, '0')),
    CASE
        WHEN MOD(seq.n, 4) = 0 THEN 'HIGH'
        WHEN MOD(seq.n, 3) = 0 THEN 'MEDIUM'
        ELSE 'LOW'
    END,
    CASE
        WHEN MOD(seq.n, 5) = 0 THEN 'PAROLE'
        WHEN MOD(seq.n, 3) = 0 THEN 'INCARCERATED'
        ELSE 'AT_LARGE'
    END,
    g1.id
FROM (
    SELECT units.n + tens.n * 10 + hundreds.n * 100 AS n
    FROM (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) units
    CROSS JOIN (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) tens
    CROSS JOIN (SELECT 0 n UNION ALL SELECT 1) hundreds
    ) seq
JOIN crime_reports cr ON cr.location_text = CONCAT('Demo Sector ', LPAD(seq.n, 3, '0'))
JOIN suspects s ON s.crime_report_id = cr.id
JOIN officers g1 ON g1.email = 'admin@icicids.local'
WHERE seq.n BETWEEN 1 AND 120
  AND cr.investigation_status IN ('CLOSED', 'REFERRED')
  AND MOD(seq.n, 4) = 0
ON DUPLICATE KEY UPDATE
    crime_report_id = VALUES(crime_report_id),
    profile_summary = VALUES(profile_summary),
    risk_level = VALUES(risk_level),
    current_status = VALUES(current_status),
    added_by_officer_id = VALUES(added_by_officer_id);

INSERT INTO criminal_history (
    criminal_id, crime_report_id, offense_title, conviction_date,
    sentence_details, jurisdiction, notes, created_by_officer_id
)
SELECT c.id, cr.id,
       CONCAT('Demo Offense ', LPAD(c.id, 3, '0')),
       DATE(cr.crime_datetime),
       'Demo sentencing details.',
       'Demo Court',
       'Auto-generated criminal history entry.',
       g1.id
FROM criminals c
JOIN crime_reports cr ON cr.id = c.crime_report_id
JOIN officers g1 ON g1.email = 'admin@icicids.local'
WHERE c.criminal_code LIKE 'CRIM-2%'
  AND NOT EXISTS (
      SELECT 1 FROM criminal_history ch
      WHERE ch.criminal_id = c.id AND ch.crime_report_id = cr.id
  );

SET foreign_key_checks = 1;

-- Quick checks
-- SELECT rank, COUNT(*) FROM officers GROUP BY rank;
-- SELECT investigation_status, COUNT(*) FROM crime_reports GROUP BY investigation_status;
-- SELECT evidence_type, COUNT(*) FROM evidence GROUP BY evidence_type;
