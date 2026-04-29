-- Integrated Crime Investigation and Criminal Identification Database System (ICICIDS)
-- STEP 1: init.sql
-- Target: MySQL 8+ / MariaDB 10.5+ (InnoDB, utf8mb4)
-- Profile: ICICIDS (phpMyAdmin import-ready)
-- IMPORTANT:
--   1) Import this file first
--   2) Then import demo_data.sql for sample records

-- phpMyAdmin import-ready settings
CREATE DATABASE IF NOT EXISTS ICICIDS
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE ICICIDS;

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET sql_mode = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
SET foreign_key_checks = 0;

DROP TABLE IF EXISTS feedbacks;
DROP TABLE IF EXISTS schema_requests;
DROP TABLE IF EXISTS login_logs;
DROP TABLE IF EXISTS officer_activity;
DROP TABLE IF EXISTS interviews;
DROP TABLE IF EXISTS investigation_updates;
DROP TABLE IF EXISTS suspect_evidence;
DROP TABLE IF EXISTS crime_report_evidence;
DROP TABLE IF EXISTS evidence;
DROP TABLE IF EXISTS criminal_history;
DROP TABLE IF EXISTS criminal_aliases;
DROP TABLE IF EXISTS criminals;
DROP TABLE IF EXISTS crime_report_suspects;
DROP TABLE IF EXISTS suspects;
DROP TABLE IF EXISTS crime_reports;
DROP TABLE IF EXISTS officers;

SET foreign_key_checks = 1;

CREATE TABLE officers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    badge_number VARCHAR(30) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    phone VARCHAR(30) NULL,
    rank ENUM('GRADE_1', 'GRADE_2', 'GRADE_3') NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_officers_rank (rank),
    INDEX idx_officers_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE crime_reports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    case_number VARCHAR(40) NOT NULL UNIQUE,
    crime_type VARCHAR(120) NOT NULL,
    location_text VARCHAR(255) NOT NULL,
    latitude DECIMAL(10,8) NULL,
    longitude DECIMAL(11,8) NULL,
    crime_datetime DATETIME NOT NULL,
    description TEXT NOT NULL,
    investigation_status ENUM('OPEN', 'UNDER_INVESTIGATION', 'SUSPENDED', 'CLOSED', 'REFERRED') NOT NULL DEFAULT 'OPEN',
    reported_by_officer_id BIGINT UNSIGNED NOT NULL,
    assigned_officer_id BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_crime_reports_reported_by
        FOREIGN KEY (reported_by_officer_id) REFERENCES officers(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_crime_reports_assigned_to
        FOREIGN KEY (assigned_officer_id) REFERENCES officers(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_crime_reports_type (crime_type),
    INDEX idx_crime_reports_status (investigation_status),
    INDEX idx_crime_reports_datetime (crime_datetime),
    INDEX idx_crime_reports_reported_by (reported_by_officer_id),
    INDEX idx_crime_reports_assigned (assigned_officer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE suspects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    crime_report_id BIGINT UNSIGNED NULL,
    date_of_birth DATE NULL,
    gender ENUM('MALE', 'FEMALE', 'OTHER', 'UNKNOWN') NOT NULL DEFAULT 'UNKNOWN',
    national_id VARCHAR(80) NULL,
    address_line VARCHAR(255) NULL,
    phone VARCHAR(30) NULL,
    reason_for_suspicion TEXT NOT NULL,
    suspect_status ENUM('PERSON_OF_INTEREST', 'WANTED', 'ARRESTED', 'CLEARED') NOT NULL DEFAULT 'PERSON_OF_INTEREST',
    created_by_officer_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_suspects_national_id UNIQUE (national_id),
    CONSTRAINT fk_suspects_primary_crime
        FOREIGN KEY (crime_report_id) REFERENCES crime_reports(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_suspects_created_by
        FOREIGN KEY (created_by_officer_id) REFERENCES officers(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_suspects_name (last_name, first_name),
    INDEX idx_suspects_status (suspect_status),
    INDEX idx_suspects_crime_report (crime_report_id),
    INDEX idx_suspects_created_by (created_by_officer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE crime_report_suspects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    crime_report_id BIGINT UNSIGNED NOT NULL,
    suspect_id BIGINT UNSIGNED NOT NULL,
    relation_type ENUM('PRIMARY', 'SECONDARY', 'WITNESS_LINKED', 'ASSOCIATE') NOT NULL DEFAULT 'PRIMARY',
    notes TEXT NULL,
    linked_by_officer_id BIGINT UNSIGNED NOT NULL,
    linked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_crime_report_suspects_report
        FOREIGN KEY (crime_report_id) REFERENCES crime_reports(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_crime_report_suspects_suspect
        FOREIGN KEY (suspect_id) REFERENCES suspects(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_crime_report_suspects_linked_by
        FOREIGN KEY (linked_by_officer_id) REFERENCES officers(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT uq_crime_report_suspect UNIQUE (crime_report_id, suspect_id),
    INDEX idx_crime_report_suspects_suspect (suspect_id),
    INDEX idx_crime_report_suspects_linked_by (linked_by_officer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE criminals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    suspect_id BIGINT UNSIGNED NULL,
    crime_report_id BIGINT UNSIGNED NULL,
    criminal_code VARCHAR(40) NOT NULL UNIQUE,
    profile_summary TEXT NOT NULL,
    risk_level ENUM('LOW', 'MEDIUM', 'HIGH', 'CRITICAL') NOT NULL DEFAULT 'MEDIUM',
    current_status ENUM('INCARCERATED', 'AT_LARGE', 'PAROLE', 'DECEASED') NOT NULL,
    added_by_officer_id BIGINT UNSIGNED NOT NULL,
    confirmed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_criminals_suspect UNIQUE (suspect_id),
    CONSTRAINT fk_criminals_suspect
        FOREIGN KEY (suspect_id) REFERENCES suspects(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_criminals_primary_crime
        FOREIGN KEY (crime_report_id) REFERENCES crime_reports(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_criminals_added_by
        FOREIGN KEY (added_by_officer_id) REFERENCES officers(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_criminals_status (current_status),
    INDEX idx_criminals_risk (risk_level),
    INDEX idx_criminals_crime_report (crime_report_id),
    INDEX idx_criminals_added_by (added_by_officer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE criminal_aliases (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    criminal_id BIGINT UNSIGNED NOT NULL,
    alias_name VARCHAR(120) NOT NULL,
    alias_note VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_criminal_aliases_criminal
        FOREIGN KEY (criminal_id) REFERENCES criminals(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT uq_criminal_alias UNIQUE (criminal_id, alias_name),
    INDEX idx_criminal_alias_name (alias_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE criminal_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    criminal_id BIGINT UNSIGNED NOT NULL,
    crime_report_id BIGINT UNSIGNED NULL,
    offense_title VARCHAR(150) NOT NULL,
    conviction_date DATE NULL,
    sentence_details TEXT NULL,
    jurisdiction VARCHAR(120) NULL,
    notes TEXT NULL,
    created_by_officer_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_criminal_history_criminal
        FOREIGN KEY (criminal_id) REFERENCES criminals(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_criminal_history_crime_report
        FOREIGN KEY (crime_report_id) REFERENCES crime_reports(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_criminal_history_created_by
        FOREIGN KEY (created_by_officer_id) REFERENCES officers(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_criminal_history_criminal (criminal_id),
    INDEX idx_criminal_history_conviction_date (conviction_date),
    INDEX idx_criminal_history_report (crime_report_id),
    INDEX idx_criminal_history_created_by (created_by_officer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE evidence (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    evidence_code VARCHAR(50) NOT NULL UNIQUE,
    crime_report_id BIGINT UNSIGNED NULL,
    evidence_type ENUM('FINGERPRINT', 'DOCUMENT', 'DIGITAL_FILE', 'WEAPON', 'BIOLOGICAL', 'VIDEO', 'AUDIO', 'OTHER') NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NULL,
    file_path VARCHAR(255) NULL,
    collected_at DATETIME NULL,
    collected_by_officer_id BIGINT UNSIGNED NULL,
    storage_location VARCHAR(150) NULL,
    chain_status ENUM('COLLECTED', 'IN_LAB', 'IN_STORAGE', 'IN_COURT', 'RELEASED', 'DISPOSED') NOT NULL DEFAULT 'COLLECTED',
    integrity_hash CHAR(64) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_evidence_primary_crime
        FOREIGN KEY (crime_report_id) REFERENCES crime_reports(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_evidence_collected_by
        FOREIGN KEY (collected_by_officer_id) REFERENCES officers(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_evidence_type (evidence_type),
    INDEX idx_evidence_chain_status (chain_status),
    INDEX idx_evidence_crime_report (crime_report_id),
    INDEX idx_evidence_collected_by (collected_by_officer_id),
    INDEX idx_evidence_collected_at (collected_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE crime_report_evidence (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    crime_report_id BIGINT UNSIGNED NOT NULL,
    evidence_id BIGINT UNSIGNED NOT NULL,
    linked_by_officer_id BIGINT UNSIGNED NOT NULL,
    linked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    relation_note VARCHAR(255) NULL,
    CONSTRAINT fk_crime_report_evidence_report
        FOREIGN KEY (crime_report_id) REFERENCES crime_reports(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_crime_report_evidence_evidence
        FOREIGN KEY (evidence_id) REFERENCES evidence(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_crime_report_evidence_linked_by
        FOREIGN KEY (linked_by_officer_id) REFERENCES officers(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT uq_crime_report_evidence UNIQUE (crime_report_id, evidence_id),
    INDEX idx_crime_report_evidence_evidence (evidence_id),
    INDEX idx_crime_report_evidence_linked_by (linked_by_officer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE suspect_evidence (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    suspect_id BIGINT UNSIGNED NOT NULL,
    evidence_id BIGINT UNSIGNED NOT NULL,
    relevance_reason TEXT NOT NULL,
    linked_by_officer_id BIGINT UNSIGNED NOT NULL,
    linked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_suspect_evidence_suspect
        FOREIGN KEY (suspect_id) REFERENCES suspects(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_suspect_evidence_evidence
        FOREIGN KEY (evidence_id) REFERENCES evidence(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_suspect_evidence_linked_by
        FOREIGN KEY (linked_by_officer_id) REFERENCES officers(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT uq_suspect_evidence UNIQUE (suspect_id, evidence_id),
    INDEX idx_suspect_evidence_evidence (evidence_id),
    INDEX idx_suspect_evidence_linked_by (linked_by_officer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE investigation_updates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    crime_report_id BIGINT UNSIGNED NOT NULL,
    update_type ENUM('NOTE', 'STATUS_CHANGE', 'ACTION', 'LEAD', 'FORENSIC_RESULT') NOT NULL DEFAULT 'NOTE',
    progress_percent TINYINT UNSIGNED NULL,
    note_text TEXT NOT NULL,
    created_by_officer_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_investigation_updates_report
        FOREIGN KEY (crime_report_id) REFERENCES crime_reports(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_investigation_updates_created_by
        FOREIGN KEY (created_by_officer_id) REFERENCES officers(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_investigation_progress
        CHECK (progress_percent IS NULL OR progress_percent <= 100),
    INDEX idx_investigation_updates_report (crime_report_id),
    INDEX idx_investigation_updates_type (update_type),
    INDEX idx_investigation_updates_created_by (created_by_officer_id),
    INDEX idx_investigation_updates_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE interviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    crime_report_id BIGINT UNSIGNED NOT NULL,
    suspect_id BIGINT UNSIGNED NULL,
    interviewee_name VARCHAR(150) NOT NULL,
    interviewee_role ENUM('SUSPECT', 'WITNESS', 'VICTIM', 'OFFICER', 'EXPERT', 'OTHER') NOT NULL,
    interview_datetime DATETIME NOT NULL,
    summary TEXT NOT NULL,
    conducted_by_officer_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_interviews_report
        FOREIGN KEY (crime_report_id) REFERENCES crime_reports(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_interviews_suspect
        FOREIGN KEY (suspect_id) REFERENCES suspects(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT fk_interviews_conducted_by
        FOREIGN KEY (conducted_by_officer_id) REFERENCES officers(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_interviews_report (crime_report_id),
    INDEX idx_interviews_suspect (suspect_id),
    INDEX idx_interviews_role (interviewee_role),
    INDEX idx_interviews_datetime (interview_datetime),
    INDEX idx_interviews_conducted_by (conducted_by_officer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE officer_activity (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    officer_id BIGINT UNSIGNED NOT NULL,
    activity_type ENUM(
        'CREATE', 'READ', 'UPDATE', 'DELETE',
        'LOGIN', 'LOGOUT',
        'EVIDENCE_UPLOAD', 'SUSPECT_UPDATE', 'REPORT_UPDATE',
        'SCHEMA_REQUEST_SUBMIT', 'SCHEMA_CHANGE_EXECUTED',
        'OTHER'
    ) NOT NULL,
    target_table VARCHAR(80) NULL,
    target_record_id BIGINT UNSIGNED NULL,
    action_details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    ip_location VARCHAR(150) NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_officer_activity_officer
        FOREIGN KEY (officer_id) REFERENCES officers(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    INDEX idx_officer_activity_officer (officer_id),
    INDEX idx_officer_activity_type (activity_type),
    INDEX idx_officer_activity_target (target_table, target_record_id),
    INDEX idx_officer_activity_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE login_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    officer_id BIGINT UNSIGNED NULL,
    email_attempted VARCHAR(191) NULL,
    login_status ENUM('SUCCESS', 'FAILED') NOT NULL,
    login_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) NOT NULL,
    ip_location VARCHAR(150) NULL,
    user_agent VARCHAR(255) NOT NULL,
    device_fingerprint VARCHAR(191) NULL,
    failure_reason VARCHAR(255) NULL,
    CONSTRAINT fk_login_logs_officer
        FOREIGN KEY (officer_id) REFERENCES officers(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_login_logs_officer (officer_id),
    INDEX idx_login_logs_status (login_status),
    INDEX idx_login_logs_time (login_time),
    INDEX idx_login_logs_ip (ip_address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE schema_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    requested_by_officer_id BIGINT UNSIGNED NOT NULL,
    request_type ENUM('CREATE', 'ALTER', 'DROP', 'INDEX', 'OTHER') NOT NULL,
    object_name VARCHAR(150) NOT NULL,
    reason TEXT NOT NULL,
    sql_proposal TEXT NOT NULL,
    status ENUM('PENDING', 'APPROVED', 'REJECTED', 'IMPLEMENTED') NOT NULL DEFAULT 'PENDING',
    reviewed_by_officer_id BIGINT UNSIGNED NULL,
    review_notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reviewed_at DATETIME NULL,
    implemented_at DATETIME NULL,
    CONSTRAINT fk_schema_requests_requested_by
        FOREIGN KEY (requested_by_officer_id) REFERENCES officers(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_schema_requests_reviewed_by
        FOREIGN KEY (reviewed_by_officer_id) REFERENCES officers(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_schema_requests_requested_by (requested_by_officer_id),
    INDEX idx_schema_requests_status (status),
    INDEX idx_schema_requests_type (request_type),
    INDEX idx_schema_requests_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE feedbacks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submitted_by_officer_id BIGINT UNSIGNED NOT NULL,
    module_name ENUM(
        'CRIME_REPORTING',
        'SUSPECT_TRACKING',
        'CRIMINAL_DB',
        'EVIDENCE_MANAGEMENT',
        'INVESTIGATION_PROGRESS',
        'AUTH_SECURITY',
        'OTHER'
    ) NOT NULL,
    category ENUM('BUG', 'FEATURE', 'DATA_QUALITY', 'USABILITY', 'SECURITY', 'OTHER') NOT NULL DEFAULT 'OTHER',
    message TEXT NOT NULL,
    status ENUM('NEW', 'IN_REVIEW', 'RESOLVED', 'DISMISSED') NOT NULL DEFAULT 'NEW',
    resolved_by_officer_id BIGINT UNSIGNED NULL,
    resolved_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_feedbacks_submitted_by
        FOREIGN KEY (submitted_by_officer_id) REFERENCES officers(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_feedbacks_resolved_by
        FOREIGN KEY (resolved_by_officer_id) REFERENCES officers(id)
        ON UPDATE CASCADE ON DELETE SET NULL,
    INDEX idx_feedbacks_submitted_by (submitted_by_officer_id),
    INDEX idx_feedbacks_module (module_name),
    INDEX idx_feedbacks_status (status),
    INDEX idx_feedbacks_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional seed for one initial Super Admin account (replace hash before production use).
-- INSERT INTO officers (badge_number, first_name, last_name, email, rank, password_hash)
-- VALUES ('G1-0001', 'System', 'Admin', 'admin@example.com', 'GRADE_1', '$2y$10$replace_with_real_hash');

-- Demo credentials are seeded in demo_data.sql:
--   GRADE_1 -> admin@icicids.local / Admin@123
--   GRADE_2 -> admin2@icicids.local / Admin2@123
--   GRADE_3 -> viewer3@icicids.local / Viewer3@123
