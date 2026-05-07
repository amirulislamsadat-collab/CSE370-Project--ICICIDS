-- Compatible bulk demo data (numbers-based, avoids CTEs)
-- Import this after init.sql and after officers are present

USE ICICIDS;
SET NAMES utf8mb4;
SET foreign_key_checks = 0;

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

-- Criminal history for generated criminals
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

-- End of compatible bulk demo data
