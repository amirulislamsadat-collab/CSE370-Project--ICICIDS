-- Demo data for ICICIDS
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
    ('G1-0001', 'System', 'Admin', 'admin@icicids.local', '+234000000001', 'GRADE_1', '$2y$10$GADAA5S00HNXu.ZcuN.o7O0FM6xrmA4DdpxACgQAfVdXfGj33WxZe', 1),
    ('G2-0001', 'Case', 'Manager', 'admin2@icicids.local', '+234000000002', 'GRADE_2', '$2y$10$RNlk5.319wzYrpqi8V45TuI/PX0sOdgQAUv6sAFZPETehiiqJX3wC', 1),
    ('G3-0001', 'Data', 'Viewer', 'viewer3@icicids.local', '+234000000003', 'GRADE_3', '$2y$10$W2RNxsCr3snFswNTd44r0.kuPtFOnTEcwWriokzbTHFh2s1CCasTG', 1)
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
SELECT 'CASE-2026-0001', 'ROBBERY', 'Downtown Sector A', 6.52440000, 3.37920000,
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
SELECT 'CASE-2026-0002', 'CYBERCRIME', 'Tech Hub District', 6.46820000, 3.58520000,
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
SELECT 'CASE-2026-0003', 'ASSAULT', 'Riverside Block C', 6.43000000, 3.43000000,
       '2026-04-16 22:15:00', 'Late-night assault reported by neighborhood patrol.', 'CLOSED',
       g1.id, g2.id
FROM officers g1
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE g1.email = 'admin@icicids.local'
ON DUPLICATE KEY UPDATE
    investigation_status = VALUES(investigation_status),
    assigned_officer_id = VALUES(assigned_officer_id),
    description = VALUES(description);

-- Suspects
INSERT INTO suspects (
    first_name, last_name, date_of_birth, gender, national_id,
    address_line, phone, reason_for_suspicion, suspect_status, created_by_officer_id
)
SELECT 'John', 'Doe', '1990-05-15', 'MALE', 'NID-ICI-0001',
       'Unknown', '08000000001', 'Witnesses place suspect at robbery scene.', 'WANTED', g2.id
FROM officers g2 WHERE g2.email = 'admin2@icicids.local'
ON DUPLICATE KEY UPDATE
    reason_for_suspicion = VALUES(reason_for_suspicion),
    suspect_status = VALUES(suspect_status),
    created_by_officer_id = VALUES(created_by_officer_id);

INSERT INTO suspects (
    first_name, last_name, date_of_birth, gender, national_id,
    address_line, phone, reason_for_suspicion, suspect_status, created_by_officer_id
)
SELECT 'Amaka', 'Nwosu', '1988-11-22', 'FEMALE', 'NID-ICI-0002',
       '15 Unity Street', '08000000002', 'Linked to cybercrime wallet infrastructure.', 'PERSON_OF_INTEREST', g2.id
FROM officers g2 WHERE g2.email = 'admin2@icicids.local'
ON DUPLICATE KEY UPDATE
    reason_for_suspicion = VALUES(reason_for_suspicion),
    suspect_status = VALUES(suspect_status),
    created_by_officer_id = VALUES(created_by_officer_id);

INSERT INTO suspects (
    first_name, last_name, date_of_birth, gender, national_id,
    address_line, phone, reason_for_suspicion, suspect_status, created_by_officer_id
)
SELECT 'Peter', 'Ibrahim', '1995-03-04', 'MALE', 'NID-ICI-0003',
       '7 Market Road', '08000000003', 'Present during assault and identified by CCTV.', 'ARRESTED', g2.id
FROM officers g2 WHERE g2.email = 'admin2@icicids.local'
ON DUPLICATE KEY UPDATE
    reason_for_suspicion = VALUES(reason_for_suspicion),
    suspect_status = VALUES(suspect_status),
    created_by_officer_id = VALUES(created_by_officer_id);

-- Link suspects to reports
INSERT INTO crime_report_suspects (crime_report_id, suspect_id, relation_type, notes, linked_by_officer_id)
SELECT cr.id, s.id, 'PRIMARY', 'Primary suspect for armed robbery.', g2.id
FROM crime_reports cr
JOIN suspects s ON s.national_id = 'NID-ICI-0001'
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE cr.case_number = 'CASE-2026-0001'
ON DUPLICATE KEY UPDATE
    relation_type = VALUES(relation_type),
    notes = VALUES(notes),
    linked_by_officer_id = VALUES(linked_by_officer_id);

INSERT INTO crime_report_suspects (crime_report_id, suspect_id, relation_type, notes, linked_by_officer_id)
SELECT cr.id, s.id, 'PRIMARY', 'Person of interest in cybercrime case.', g2.id
FROM crime_reports cr
JOIN suspects s ON s.national_id = 'NID-ICI-0002'
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE cr.case_number = 'CASE-2026-0002'
ON DUPLICATE KEY UPDATE
    relation_type = VALUES(relation_type),
    notes = VALUES(notes),
    linked_by_officer_id = VALUES(linked_by_officer_id);

INSERT INTO crime_report_suspects (crime_report_id, suspect_id, relation_type, notes, linked_by_officer_id)
SELECT cr.id, s.id, 'PRIMARY', 'Assault case suspect currently arrested.', g2.id
FROM crime_reports cr
JOIN suspects s ON s.national_id = 'NID-ICI-0003'
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE cr.case_number = 'CASE-2026-0003'
ON DUPLICATE KEY UPDATE
    relation_type = VALUES(relation_type),
    notes = VALUES(notes),
    linked_by_officer_id = VALUES(linked_by_officer_id);

-- Criminals (confirmed)
INSERT INTO criminals (suspect_id, criminal_code, profile_summary, risk_level, current_status, added_by_officer_id)
SELECT s.id, 'CRIM-0001', 'Repeat violent offender tied to armed robbery ring.', 'HIGH', 'AT_LARGE', g1.id
FROM suspects s
JOIN officers g1 ON g1.email = 'admin@icicids.local'
WHERE s.national_id = 'NID-ICI-0001'
ON DUPLICATE KEY UPDATE
    profile_summary = VALUES(profile_summary),
    risk_level = VALUES(risk_level),
    current_status = VALUES(current_status),
    added_by_officer_id = VALUES(added_by_officer_id);

INSERT INTO criminals (suspect_id, criminal_code, profile_summary, risk_level, current_status, added_by_officer_id)
SELECT s.id, 'CRIM-0002', 'Digital fraud operator with cross-border activity markers.', 'MEDIUM', 'INCARCERATED', g1.id
FROM suspects s
JOIN officers g1 ON g1.email = 'admin@icicids.local'
WHERE s.national_id = 'NID-ICI-0002'
ON DUPLICATE KEY UPDATE
    profile_summary = VALUES(profile_summary),
    risk_level = VALUES(risk_level),
    current_status = VALUES(current_status),
    added_by_officer_id = VALUES(added_by_officer_id);

-- Aliases
INSERT INTO criminal_aliases (criminal_id, alias_name, alias_note)
SELECT c.id, 'Ghost Walker', 'Used in robbery communications.'
FROM criminals c WHERE c.criminal_code = 'CRIM-0001'
ON DUPLICATE KEY UPDATE
    alias_note = VALUES(alias_note);

INSERT INTO criminal_aliases (criminal_id, alias_name, alias_note)
SELECT c.id, 'Packet Queen', 'Online pseudonym in fraud channels.'
FROM criminals c WHERE c.criminal_code = 'CRIM-0002'
ON DUPLICATE KEY UPDATE
    alias_note = VALUES(alias_note);

-- Criminal history
INSERT INTO criminal_history (
    criminal_id, crime_report_id, offense_title, conviction_date,
    sentence_details, jurisdiction, notes, created_by_officer_id
)
SELECT c.id, cr.id, 'Armed Robbery', '2025-12-12',
       'Pending sentencing hearing.', 'Lagos State', 'Case reopened due to new witness account.', g1.id
FROM criminals c
JOIN crime_reports cr ON cr.case_number = 'CASE-2026-0001'
JOIN officers g1 ON g1.email = 'admin@icicids.local'
WHERE c.criminal_code = 'CRIM-0001';

INSERT INTO criminal_history (
    criminal_id, crime_report_id, offense_title, conviction_date,
    sentence_details, jurisdiction, notes, created_by_officer_id
)
SELECT c.id, cr.id, 'Financial Cyber Fraud', '2026-01-30',
       '3-year sentence, cybercrime unit custody.', 'Federal High Court', 'Digital wallet tracing confirmed chain.', g1.id
FROM criminals c
JOIN crime_reports cr ON cr.case_number = 'CASE-2026-0002'
JOIN officers g1 ON g1.email = 'admin@icicids.local'
WHERE c.criminal_code = 'CRIM-0002';

-- Evidence
INSERT INTO evidence (
    evidence_code, evidence_type, title, description, file_path,
    collected_at, collected_by_officer_id, storage_location, chain_status, integrity_hash
)
SELECT 'EVD-0001', 'DIGITAL_FILE', 'Store CCTV Archive', 'Raw CCTV footage around robbery window.', '/evidence/cctv/evd-0001.mp4',
       '2026-04-20 20:10:00', g2.id, 'Digital Vault', 'IN_STORAGE', SHA2('EVD-0001', 256)
FROM officers g2 WHERE g2.email = 'admin2@icicids.local'
ON DUPLICATE KEY UPDATE
    description = VALUES(description),
    chain_status = VALUES(chain_status),
    integrity_hash = VALUES(integrity_hash);

INSERT INTO evidence (
    evidence_code, evidence_type, title, description, file_path,
    collected_at, collected_by_officer_id, storage_location, chain_status, integrity_hash
)
SELECT 'EVD-0002', 'DOCUMENT', 'Transaction Ledger Printout', 'Printed ledger from seized office terminal.', '/evidence/docs/evd-0002.pdf',
       '2026-04-18 13:00:00', g2.id, 'Records Room B', 'IN_LAB', SHA2('EVD-0002', 256)
FROM officers g2 WHERE g2.email = 'admin2@icicids.local'
ON DUPLICATE KEY UPDATE
    description = VALUES(description),
    chain_status = VALUES(chain_status),
    integrity_hash = VALUES(integrity_hash);

INSERT INTO evidence (
    evidence_code, evidence_type, title, description, file_path,
    collected_at, collected_by_officer_id, storage_location, chain_status, integrity_hash
)
SELECT 'EVD-0003', 'WEAPON', 'Recovered Knife', 'Knife recovered near assault location.', '/evidence/weapons/evd-0003.jpg',
       '2026-04-16 23:00:00', g2.id, 'Locker 12', 'IN_COURT', SHA2('EVD-0003', 256)
FROM officers g2 WHERE g2.email = 'admin2@icicids.local'
ON DUPLICATE KEY UPDATE
    description = VALUES(description),
    chain_status = VALUES(chain_status),
    integrity_hash = VALUES(integrity_hash);

-- Link evidence to reports
INSERT INTO crime_report_evidence (crime_report_id, evidence_id, linked_by_officer_id, relation_note)
SELECT cr.id, e.id, g2.id, 'Video confirms suspect route and timeline.'
FROM crime_reports cr
JOIN evidence e ON e.evidence_code = 'EVD-0001'
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE cr.case_number = 'CASE-2026-0001'
ON DUPLICATE KEY UPDATE
    relation_note = VALUES(relation_note),
    linked_by_officer_id = VALUES(linked_by_officer_id);

INSERT INTO crime_report_evidence (crime_report_id, evidence_id, linked_by_officer_id, relation_note)
SELECT cr.id, e.id, g2.id, 'Ledger ties suspects to laundering path.'
FROM crime_reports cr
JOIN evidence e ON e.evidence_code = 'EVD-0002'
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE cr.case_number = 'CASE-2026-0002'
ON DUPLICATE KEY UPDATE
    relation_note = VALUES(relation_note),
    linked_by_officer_id = VALUES(linked_by_officer_id);

INSERT INTO crime_report_evidence (crime_report_id, evidence_id, linked_by_officer_id, relation_note)
SELECT cr.id, e.id, g2.id, 'Weapon matched witness account and medical report.'
FROM crime_reports cr
JOIN evidence e ON e.evidence_code = 'EVD-0003'
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE cr.case_number = 'CASE-2026-0003'
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
WHERE cr.case_number = 'CASE-2026-0001';

INSERT INTO investigation_updates (crime_report_id, update_type, progress_percent, note_text, created_by_officer_id)
SELECT cr.id, 'FORENSIC_RESULT', 60, 'Forensic team validated metadata integrity of seized devices.', g2.id
FROM crime_reports cr
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE cr.case_number = 'CASE-2026-0002';

INSERT INTO investigation_updates (crime_report_id, update_type, progress_percent, note_text, created_by_officer_id)
SELECT cr.id, 'STATUS_CHANGE', 100, 'Case concluded and transferred for court filing.', g2.id
FROM crime_reports cr
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE cr.case_number = 'CASE-2026-0003';

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
WHERE cr.case_number = 'CASE-2026-0001';

INSERT INTO interviews (
    crime_report_id, suspect_id, interviewee_name, interviewee_role,
    interview_datetime, summary, conducted_by_officer_id
)
SELECT cr.id, NULL, 'Ifeoma U.', 'WITNESS', '2026-04-18 15:10:00',
       'Witness provided timeline of suspicious transactions and contact patterns.', g2.id
FROM crime_reports cr
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE cr.case_number = 'CASE-2026-0002';

INSERT INTO interviews (
    crime_report_id, suspect_id, interviewee_name, interviewee_role,
    interview_datetime, summary, conducted_by_officer_id
)
SELECT cr.id, s.id, 'Peter Ibrahim', 'SUSPECT', '2026-04-17 11:20:00',
       'Suspect admitted altercation but disputed weapon possession.', g2.id
FROM crime_reports cr
JOIN suspects s ON s.national_id = 'NID-ICI-0003'
JOIN officers g2 ON g2.email = 'admin2@icicids.local'
WHERE cr.case_number = 'CASE-2026-0003';

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
JOIN crime_reports cr ON cr.case_number = 'CASE-2026-0001'
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

SET foreign_key_checks = 1;

-- Quick checks
-- SELECT rank, COUNT(*) FROM officers GROUP BY rank;
-- SELECT investigation_status, COUNT(*) FROM crime_reports GROUP BY investigation_status;
-- SELECT evidence_type, COUNT(*) FROM evidence GROUP BY evidence_type;
