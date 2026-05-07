<?php
declare(strict_types=1);

// ICICIDS request router and page controller.
// Responsibilities:
// - Bootstrap core services.
// - Dispatch POST actions to domain managers.
// - Build permission-aware view models for page templates.

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/AuditLogger.php';
require_once __DIR__ . '/CrimeAndSuspectManager.php';
require_once __DIR__ . '/EvidenceAndInvestigation.php';
require_once __DIR__ . '/OfficerApplicationManager.php';

$db = Database::getInstance()->getConnection();
$auth = new Auth($db);
$audit = new AuditLogger($db, $auth);
$crimeManager = new CrimeAndSuspectManager($db, $auth, $audit);
$evidenceManager = new EvidenceAndInvestigation($db, $auth, $audit);
$officerApplications = new OfficerApplicationManager($db, $auth, $audit);

$view = isset($_GET['view']) ? (string)$_GET['view'] : 'dashboard';
$validViews = ['dashboard', 'crimes', 'suspects', 'criminals', 'evidence', 'feedbacks', 'schema', 'logs', 'signup', 'approvals'];
if (!in_array($view, $validViews, true)) {
    $view = 'dashboard';
}

function h(string $v): string
{
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

function flashSet(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flashGet(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Action dispatcher for all mutating operations.
    $action = (string)($_POST['action'] ?? '');

    try {
        if ($action === 'login') {
            $email = trim((string)($_POST['email'] ?? ''));
            $password = (string)($_POST['password'] ?? '');
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Browser';
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $deviceFingerprint = hash('sha256', $userAgent . '|' . $ipAddress);

            if ($auth->login($email, $password, $userAgent, $ipAddress, 'Web Login', $deviceFingerprint)) {
                flashSet('success', 'Login successful.');
            } else {
                flashSet('error', 'Invalid login details.');
            }

            header('Location: index.php');
            exit;
        }

        if ($action === 'logout') {
            $auth->logout($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1', $_SERVER['HTTP_USER_AGENT'] ?? 'Browser', 'Web Logout');
            flashSet('success', 'Logged out.');
            header('Location: index.php');
            exit;
        }

        if ($action === 'signup') {
            $officerApplications->submitApplication([
                'first_name' => trim((string)($_POST['first_name'] ?? '')),
                'last_name' => trim((string)($_POST['last_name'] ?? '')),
                'email' => trim((string)($_POST['email'] ?? '')),
                'badge_number' => trim((string)($_POST['badge_number'] ?? '')),
                'phone' => trim((string)($_POST['phone'] ?? '')),
                'requested_rank' => trim((string)($_POST['requested_rank'] ?? Auth::ROLE_GRADE_3)),
                'password' => (string)($_POST['password'] ?? ''),
                'confirm_password' => (string)($_POST['confirm_password'] ?? ''),
            ]);

            flashSet('success', 'Signup request submitted. A Grade 1 officer will review it soon.');
            header('Location: index.php?view=signup');
            exit;
        }

        $auth->requireAuthentication();

        if ($action === 'create_crime') {
            // Normalize datetime-local browser input before service validation.
            $rawDate = trim((string)($_POST['crime_datetime'] ?? ''));
            $crimeDate = str_replace('T', ' ', $rawDate);
            if (strlen($crimeDate) === 16) {
                $crimeDate .= ':00';
            }

            $crimeManager->createCrimeReport([
                'crime_type' => trim((string)($_POST['crime_type'] ?? '')),
                'location_text' => trim((string)($_POST['location_text'] ?? '')),
                'crime_datetime' => $crimeDate,
                'description' => trim((string)($_POST['description'] ?? '')),
                'investigation_status' => trim((string)($_POST['investigation_status'] ?? 'OPEN')),
            ]);

            flashSet('success', 'Crime report created.');
            header('Location: index.php?view=crimes');
            exit;
        }

        if ($action === 'update_crime') {
            $crimeManager->updateCrimeReportStatus(
                (int)($_POST['crime_report_id'] ?? 0),
                trim((string)($_POST['new_status'] ?? 'OPEN')),
                trim((string)($_POST['status_note'] ?? ''))
            );

            flashSet('success', 'Crime report status updated.');
            header('Location: index.php?view=crimes');
            exit;
        }

        if ($action === 'delete_crime') {
            $crimeManager->deleteCrimeReport((int)($_POST['crime_report_id'] ?? 0));

            flashSet('success', 'Crime report deleted.');
            header('Location: index.php?view=crimes');
            exit;
        }

        if ($action === 'create_evidence') {
            $code = 'EVD-' . date('Ymd-His');
            $evidenceManager->createEvidence([
                'crime_report_id' => (int)($_POST['crime_report_id'] ?? 0),
                'evidence_code' => $code,
                'evidence_type' => trim((string)($_POST['evidence_type'] ?? 'OTHER')),
                'title' => trim((string)($_POST['title'] ?? '')),
                'description' => trim((string)($_POST['evidence_description'] ?? '')),
                'file_path' => trim((string)($_POST['file_path'] ?? '')),
                'collected_at' => date('Y-m-d H:i:s'),
                'storage_location' => trim((string)($_POST['storage_location'] ?? '')),
                'chain_status' => trim((string)($_POST['chain_status'] ?? 'COLLECTED')),
                'integrity_hash' => hash('sha256', $code . microtime(true)),
                'relation_note' => trim((string)($_POST['relation_note'] ?? '')),
            ]);

            flashSet('success', 'Evidence added.');
            header('Location: index.php?view=evidence');
            exit;
        }

        if ($action === 'update_evidence') {
            $evidenceManager->updateEvidenceChainStatus(
                (int)($_POST['evidence_id'] ?? 0),
                trim((string)($_POST['new_chain_status'] ?? 'COLLECTED'))
            );

            flashSet('success', 'Evidence status updated.');
            header('Location: index.php?view=evidence');
            exit;
        }

        if ($action === 'delete_evidence') {
            $evidenceManager->deleteEvidence((int)($_POST['evidence_id'] ?? 0));

            flashSet('success', 'Evidence deleted.');
            header('Location: index.php?view=evidence');
            exit;
        }

        if ($action === 'link_evidence') {
            $evidenceManager->linkEvidenceToCrime(
                (int)($_POST['crime_report_id'] ?? 0),
                (int)($_POST['evidence_id'] ?? 0),
                trim((string)($_POST['relation_note'] ?? ''))
            );

            flashSet('success', 'Evidence linked to crime report.');
            header('Location: index.php?view=evidence');
            exit;
        }

        if ($action === 'unlink_evidence_case') {
            $evidenceManager->unlinkEvidenceFromCrime(
                (int)($_POST['evidence_id'] ?? 0),
                (int)($_POST['crime_report_id'] ?? 0)
            );

            flashSet('success', 'Evidence unlinked from crime report.');
            header('Location: index.php?view=evidence');
            exit;
        }

        if ($action === 'unlink_evidence_suspect') {
            $evidenceManager->unlinkEvidenceFromSuspect(
                (int)($_POST['suspect_id'] ?? 0),
                (int)($_POST['evidence_id'] ?? 0)
            );

            flashSet('success', 'Evidence unlinked from suspect.');
            header('Location: index.php?view=evidence');
            exit;
        }

        if ($action === 'create_suspect') {
            $crimeManager->createSuspect([
                'crime_report_id' => (int)($_POST['crime_report_id'] ?? 0),
                'relation_type' => trim((string)($_POST['relation_type'] ?? 'PRIMARY')),
                'relation_notes' => trim((string)($_POST['relation_notes'] ?? '')),
                'first_name' => trim((string)($_POST['first_name'] ?? '')),
                'last_name' => trim((string)($_POST['last_name'] ?? '')),
                'date_of_birth' => trim((string)($_POST['date_of_birth'] ?? '')),
                'gender' => trim((string)($_POST['gender'] ?? 'UNKNOWN')),
                'national_id' => trim((string)($_POST['national_id'] ?? '')),
                'address_line' => trim((string)($_POST['address_line'] ?? '')),
                'phone' => trim((string)($_POST['phone'] ?? '')),
                'reason_for_suspicion' => trim((string)($_POST['reason_for_suspicion'] ?? '')),
                'suspect_status' => trim((string)($_POST['suspect_status'] ?? 'PERSON_OF_INTEREST')),
            ]);

            flashSet('success', 'Suspect created and linked to crime report.');
            header('Location: index.php?view=suspects');
            exit;
        }

        if ($action === 'delete_suspect') {
            $crimeManager->deleteSuspect((int)($_POST['suspect_id'] ?? 0));

            flashSet('success', 'Suspect deleted.');
            header('Location: index.php?view=suspects');
            exit;
        }

        if ($action === 'link_suspect') {
            $crimeManager->linkSuspectToCrime(
                (int)($_POST['crime_report_id'] ?? 0),
                (int)($_POST['suspect_id'] ?? 0),
                trim((string)($_POST['relation_type'] ?? 'PRIMARY')),
                trim((string)($_POST['relation_notes'] ?? ''))
            );

            flashSet('success', 'Suspect linked to crime report.');
            header('Location: index.php?view=suspects');
            exit;
        }

        if ($action === 'update_suspect_status') {
            $crimeManager->updateSuspectStatus(
                (int)($_POST['suspect_id'] ?? 0),
                trim((string)($_POST['suspect_status'] ?? 'PERSON_OF_INTEREST'))
            );

            flashSet('success', 'Suspect status updated.');
            header('Location: index.php?view=suspects');
            exit;
        }

        if ($action === 'unlink_suspect') {
            $crimeManager->unlinkSuspectFromCrime(
                (int)($_POST['suspect_id'] ?? 0),
                (int)($_POST['crime_report_id'] ?? 0)
            );

            flashSet('success', 'Suspect unlinked from crime report.');
            header('Location: index.php?view=suspects');
            exit;
        }

        if ($action === 'create_criminal') {
            $crimeManager->confirmCriminalForCrime([
                'suspect_id' => (int)($_POST['suspect_id'] ?? 0),
                'crime_report_id' => (int)($_POST['crime_report_id'] ?? 0),
                'criminal_code' => trim((string)($_POST['criminal_code'] ?? '')),
                'profile_summary' => trim((string)($_POST['profile_summary'] ?? '')),
                'risk_level' => trim((string)($_POST['risk_level'] ?? 'MEDIUM')),
                'current_status' => trim((string)($_POST['current_status'] ?? 'INCARCERATED')),
                'offense_title' => trim((string)($_POST['offense_title'] ?? '')),
                'conviction_date' => trim((string)($_POST['conviction_date'] ?? '')),
                'sentence_details' => trim((string)($_POST['sentence_details'] ?? '')),
                'jurisdiction' => trim((string)($_POST['jurisdiction'] ?? '')),
                'notes' => trim((string)($_POST['notes'] ?? '')),
            ]);

            flashSet('success', 'Criminal confirmed and linked to closed case.');
            header('Location: index.php?view=criminals');
            exit;
        }

        if ($action === 'delete_criminal') {
            $crimeManager->deleteCriminal((int)($_POST['criminal_id'] ?? 0));

            flashSet('success', 'Criminal deleted.');
            header('Location: index.php?view=criminals');
            exit;
        }

        if ($action === 'link_criminal') {
            $crimeManager->addCriminalHistory(
                (int)($_POST['criminal_id'] ?? 0),
                trim((string)($_POST['offense_title'] ?? '')),
                (int)($_POST['crime_report_id'] ?? 0),
                trim((string)($_POST['conviction_date'] ?? '')),
                trim((string)($_POST['sentence_details'] ?? '')),
                trim((string)($_POST['jurisdiction'] ?? '')),
                trim((string)($_POST['notes'] ?? ''))
            );

            flashSet('success', 'Criminal linked to closed case.');
            header('Location: index.php?view=criminals');
            exit;
        }

        if ($action === 'unlink_criminal_suspect') {
            $crimeManager->unlinkCriminalFromSuspect((int)($_POST['criminal_id'] ?? 0));

            flashSet('success', 'Criminal unlinked from suspect.');
            header('Location: index.php?view=criminals');
            exit;
        }

        if ($action === 'unlink_criminal_case') {
            $crimeManager->unlinkCriminalFromCrime(
                (int)($_POST['criminal_id'] ?? 0),
                (int)($_POST['crime_report_id'] ?? 0)
            );

            flashSet('success', 'Criminal unlinked from case.');
            header('Location: index.php?view=criminals');
            exit;
        }

        if ($action === 'submit_feedback') {
            $audit->submitFeedback(
                trim((string)($_POST['module_name'] ?? 'OTHER')),
                trim((string)($_POST['category'] ?? 'OTHER')),
                trim((string)($_POST['message'] ?? ''))
            );

            flashSet('success', 'Feedback submitted.');
            header('Location: index.php?view=feedbacks');
            exit;
        }

        if ($action === 'submit_schema') {
            $audit->submitSchemaRequest(
                trim((string)($_POST['request_type'] ?? 'OTHER')),
                trim((string)($_POST['object_name'] ?? '')),
                trim((string)($_POST['reason'] ?? '')),
                trim((string)($_POST['sql_proposal'] ?? ''))
            );

            flashSet('success', 'Schema request submitted.');
            header('Location: index.php?view=schema');
            exit;
        }

        if ($action === 'approve_officer') {
            $officerApplications->approveApplication(
                (int)($_POST['application_id'] ?? 0),
                trim((string)($_POST['final_rank'] ?? Auth::ROLE_GRADE_3)),
                trim((string)($_POST['review_note'] ?? ''))
            );

            flashSet('success', 'Officer application approved.');
            header('Location: index.php?view=approvals');
            exit;
        }

        if ($action === 'reject_officer') {
            $officerApplications->rejectApplication(
                (int)($_POST['application_id'] ?? 0),
                trim((string)($_POST['review_note'] ?? ''))
            );

            flashSet('success', 'Officer application rejected.');
            header('Location: index.php?view=approvals');
            exit;
        }

        flashSet('error', 'Unknown action.');
        header('Location: index.php');
        exit;
    } catch (Throwable $e) {
        // Translate known FK integrity errors into user-friendly messages.
        $message = $e->getMessage();

        if ($action === 'delete_crime' && $e instanceof PDOException) {
            $sqlState = $e->getCode();
            $driverCode = $e->errorInfo[1] ?? null;
            if ($sqlState === '23000' || $driverCode === 1451) {
                $message = 'Cannot delete this crime report because related data exists. Delete related suspects, evidence, criminal history, and links first, then try again.';
            }
        }

        flashSet('error', $message);
        header('Location: index.php?view=' . urlencode($view));
        exit;
    }
}

$isAuthed = $auth->isAuthenticated();
$officer = $auth->currentOfficer();
$rank = $officer['rank'] ?? '';
$flash = flashGet();
$pageTitle = 'ICICIDS';

$selectedCrimeId = isset($_GET['crime_id']) ? max(0, (int)$_GET['crime_id']) : 0;
$selectedEvidenceId = isset($_GET['evidence_id']) ? max(0, (int)$_GET['evidence_id']) : 0;
$selectedSuspectId = isset($_GET['suspect_id']) ? max(0, (int)$_GET['suspect_id']) : 0;
$selectedCriminalId = isset($_GET['criminal_id']) ? max(0, (int)$_GET['criminal_id']) : 0;

$crimeFilters = [
    'query' => trim((string)($_GET['crime_q'] ?? '')),
    'status' => trim((string)($_GET['crime_status'] ?? '')),
    'type' => trim((string)($_GET['crime_type'] ?? '')),
    'from' => trim((string)($_GET['crime_from'] ?? '')),
    'to' => trim((string)($_GET['crime_to'] ?? '')),
];
$suspectFilters = [
    'query' => trim((string)($_GET['suspect_q'] ?? '')),
    'status' => trim((string)($_GET['suspect_status'] ?? '')),
];
$criminalFilters = [
    'query' => trim((string)($_GET['criminal_q'] ?? '')),
    'status' => trim((string)($_GET['criminal_status'] ?? '')),
    'risk' => trim((string)($_GET['criminal_risk'] ?? '')),
];
$evidenceFilters = [
    'query' => trim((string)($_GET['evidence_q'] ?? '')),
    'status' => trim((string)($_GET['evidence_status'] ?? '')),
    'type' => trim((string)($_GET['evidence_type'] ?? '')),
];
$applicationStatusFilter = trim((string)($_GET['application_status'] ?? 'PENDING'));

function resolveImagePath(array $candidates): ?string
{
    foreach ($candidates as $path) {
        $fullPath = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
        if (file_exists($fullPath)) {
            return $path;
        }
    }

    return null;
}

$canReadCrimes = false;
$canReadSuspects = false;
$canReadCriminals = false;
$canReadEvidence = false;
$canReadFeedbacks = false;
$canReadSchema = false;
$canReadLogs = false;
$canCreateCrime = false;
$canUpdateCrime = false;
$canDeleteCrime = false;
$canCreateSuspect = false;
$canUpdateSuspect = false;
$canLinkSuspect = false;
$canDeleteSuspect = false;
$canCreateCriminal = false;
$canLinkCriminal = false;
$canDeleteCriminal = false;
$canCreateEvidence = false;
$canUpdateEvidence = false;
$canDeleteEvidence = false;
$canLinkEvidence = false;
$canUnlinkEvidence = false;
$canUnlinkSuspect = false;
$canUnlinkCriminal = false;
$canSubmitFeedback = false;
$canSubmitSchema = false;
$canReviewOfficers = false;

$stats = ['officers' => 0, 'crime_reports' => 0, 'suspects' => 0, 'evidence' => 0, 'criminals' => 0];
$crimeReports = [];
$crimeDetail = null;
$crimeSuspects = [];
$crimeEvidence = [];
$crimeCriminals = [];
$crimeCriminalsBlocked = false;
$suspects = [];
$suspectDetail = null;
$suspectCrimes = [];
$suspectEvidence = [];
$criminals = [];
$criminalDetail = null;
$criminalCrimes = [];
$evidences = [];
$evidenceDetail = null;
$evidenceCrimes = [];
$evidenceSuspects = [];
$feedbacks = [];
$schemas = [];
$activityLogs = [];
$loginLogs = [];
$applications = [];

if ($isAuthed) {
    // Permission matrix for tabs, actions, and protected datasets.
    $canReadCrimes = $auth->can('READ', 'crime_reports');
    $canReadSuspects = $auth->can('READ', 'suspects');
    $canReadCriminals = $auth->can('READ', 'criminals');
    $canReadEvidence = $auth->can('READ', 'evidence');
    $canReadFeedbacks = $auth->can('READ', 'feedbacks');
    $canReadSchema = $auth->can('READ', 'schema_requests');
    $canReadLogs = $auth->can('READ', 'officer_activity');

    $canCreateCrime = $auth->can('CREATE', 'crime_reports');
    $canUpdateCrime = $auth->can('UPDATE', 'crime_reports');
    $canDeleteCrime = $auth->can('DELETE', 'crime_reports');
    $canCreateSuspect = $auth->can('CREATE', 'suspects');
    $canUpdateSuspect = $auth->can('UPDATE', 'suspects');
    $canLinkSuspect = $auth->can('CREATE', 'crime_report_suspects');
    $canDeleteSuspect = $auth->can('DELETE', 'suspects');
    $canCreateCriminal = $auth->can('CREATE', 'criminals');
    $canLinkCriminal = $auth->can('CREATE', 'criminal_history');
    $canDeleteCriminal = $auth->can('DELETE', 'criminals');
    $canCreateEvidence = $auth->can('CREATE', 'evidence');
    $canUpdateEvidence = $auth->can('UPDATE', 'evidence');
    $canDeleteEvidence = $auth->can('DELETE', 'evidence');
    $canLinkEvidence = $auth->can('CREATE', 'crime_report_evidence');
    $canUnlinkEvidence = in_array($rank, [Auth::ROLE_GRADE_1, Auth::ROLE_GRADE_2], true);
    $canUnlinkSuspect = in_array($rank, [Auth::ROLE_GRADE_1, Auth::ROLE_GRADE_2], true);
    $canUnlinkCriminal = in_array($rank, [Auth::ROLE_GRADE_1, Auth::ROLE_GRADE_2], true);
    $canSubmitFeedback = $auth->can('CREATE', 'feedbacks');
    $canSubmitSchema = $auth->can('CREATE', 'schema_requests');
    $canReviewOfficers = $rank === Auth::ROLE_GRADE_1;

    $viewAllowed = (
        ($view === 'dashboard')
        || ($view === 'crimes' && $canReadCrimes)
        || ($view === 'suspects' && $canReadSuspects)
        || ($view === 'criminals' && $canReadCriminals)
        || ($view === 'evidence' && $canReadEvidence)
        || ($view === 'feedbacks' && $canReadFeedbacks)
        || ($view === 'schema' && $canReadSchema)
        || ($view === 'logs' && $canReadLogs)
        || ($view === 'approvals' && $canReviewOfficers)
    );

    if (!$viewAllowed) {
        $view = 'dashboard';
    }

    if ($canReadCrimes) {
        // Dashboard counters and recent case list.
        foreach (['officers', 'crime_reports', 'suspects', 'evidence', 'criminals'] as $table) {
            $stmt = $db->query('SELECT COUNT(*) AS total FROM ' . $table);
            $stats[$table] = (int)($stmt->fetch()['total'] ?? 0);
        }

        $crimeReports = $crimeManager->listCrimeReports(null, null, 10);
    }

    if ($view === 'crimes' && $canReadCrimes) {
        $crimeReports = $crimeManager->listCrimeReportsFiltered($crimeFilters, 200);
    }

    if ($view === 'crimes' && $canReadCrimes && $selectedCrimeId > 0) {
        $crimeDetail = $crimeManager->getCrimeReportById($selectedCrimeId);

        if ($crimeDetail !== null) {
            $stmt = $db->prepare('SELECT s.id, s.first_name, s.last_name, s.suspect_status, crs.relation_type
                                  FROM crime_report_suspects crs
                                  JOIN suspects s ON s.id = crs.suspect_id
                                  WHERE crs.crime_report_id = :crime_report_id
                                  ORDER BY crs.id DESC');
            $stmt->execute(['crime_report_id' => $selectedCrimeId]);
            $crimeSuspects = $stmt->fetchAll();

            $stmt = $db->prepare('SELECT e.id, e.evidence_code, e.title, e.evidence_type, e.chain_status
                                  FROM crime_report_evidence cre
                                  JOIN evidence e ON e.id = cre.evidence_id
                                  WHERE cre.crime_report_id = :crime_report_id
                                  ORDER BY cre.id DESC');
            $stmt->execute(['crime_report_id' => $selectedCrimeId]);
            $crimeEvidence = $stmt->fetchAll();

            $status = strtoupper((string)($crimeDetail['investigation_status'] ?? ''));
            if (in_array($status, ['CLOSED', 'REFERRED'], true)) {
                $stmt = $db->prepare('SELECT c.id, c.criminal_code, c.risk_level, c.current_status,
                                             s.first_name, s.last_name
                                      FROM criminal_history ch
                                      JOIN criminals c ON c.id = ch.criminal_id
                                      LEFT JOIN suspects s ON s.id = c.suspect_id
                                      WHERE ch.crime_report_id = :crime_report_id
                                      ORDER BY ch.id DESC');
                $stmt->execute(['crime_report_id' => $selectedCrimeId]);
                $crimeCriminals = $stmt->fetchAll();
            } else {
                $crimeCriminalsBlocked = true;
            }
        }
    }

    if ($view === 'suspects' && $canReadSuspects) {
        $sql = 'SELECT id, first_name, last_name, national_id, suspect_status, created_at FROM suspects WHERE 1=1';
        $params = [];

        if ($suspectFilters['query'] !== '') {
            $sql .= ' AND (first_name LIKE :q OR last_name LIKE :q OR national_id LIKE :q)';
            $params['q'] = '%' . $suspectFilters['query'] . '%';
        }

        if ($suspectFilters['status'] !== '') {
            $sql .= ' AND suspect_status = :status';
            $params['status'] = strtoupper($suspectFilters['status']);
        }

        $sql .= ' ORDER BY id DESC LIMIT :limit';

        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', 200, PDO::PARAM_INT);
        $stmt->execute();
        $suspects = $stmt->fetchAll();

        if ($selectedSuspectId > 0) {
            $stmt = $db->prepare('SELECT * FROM suspects WHERE id = :id');
            $stmt->execute(['id' => $selectedSuspectId]);
            $suspectDetail = $stmt->fetch() ?: null;

            if ($suspectDetail !== null) {
                $stmt = $db->prepare('SELECT cr.id, cr.case_number, cr.crime_type, cr.investigation_status, cr.crime_datetime
                                      FROM crime_report_suspects crs
                                      JOIN crime_reports cr ON cr.id = crs.crime_report_id
                                      WHERE crs.suspect_id = :suspect_id
                                      ORDER BY cr.crime_datetime DESC');
                $stmt->execute(['suspect_id' => $selectedSuspectId]);
                $suspectCrimes = $stmt->fetchAll();

                $stmt = $db->prepare('SELECT e.id, e.evidence_code, e.title, e.evidence_type, e.chain_status
                                      FROM suspect_evidence se
                                      JOIN evidence e ON e.id = se.evidence_id
                                      WHERE se.suspect_id = :suspect_id
                                      ORDER BY e.id DESC');
                $stmt->execute(['suspect_id' => $selectedSuspectId]);
                $suspectEvidence = $stmt->fetchAll();
            }
        }
    }

    if ($view === 'criminals' && $canReadCriminals) {
        $sql = 'SELECT c.id, c.criminal_code, c.risk_level, c.current_status, c.confirmed_at AS created_at,
                       s.first_name, s.last_name
                FROM criminals c
                LEFT JOIN suspects s ON s.id = c.suspect_id
                WHERE 1=1';
        $params = [];

        if ($criminalFilters['query'] !== '') {
            $sql .= ' AND (c.criminal_code LIKE :q OR s.first_name LIKE :q OR s.last_name LIKE :q)';
            $params['q'] = '%' . $criminalFilters['query'] . '%';
        }

        if ($criminalFilters['status'] !== '') {
            $sql .= ' AND c.current_status = :status';
            $params['status'] = strtoupper($criminalFilters['status']);
        }

        if ($criminalFilters['risk'] !== '') {
            $sql .= ' AND c.risk_level = :risk';
            $params['risk'] = strtoupper($criminalFilters['risk']);
        }

        $sql .= ' ORDER BY c.id DESC LIMIT :limit';

        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', 200, PDO::PARAM_INT);
        $stmt->execute();
        $criminals = $stmt->fetchAll();

        if ($selectedCriminalId > 0) {
            $stmt = $db->prepare('SELECT c.*, s.id AS suspect_id, s.first_name, s.last_name, s.national_id
                                  FROM criminals c
                                  LEFT JOIN suspects s ON s.id = c.suspect_id
                                  WHERE c.id = :id');
            $stmt->execute(['id' => $selectedCriminalId]);
            $criminalDetail = $stmt->fetch() ?: null;

            if ($criminalDetail !== null) {
                $stmt = $db->prepare('SELECT ch.id, ch.offense_title, ch.conviction_date, ch.jurisdiction, ch.notes,
                                             cr.id AS crime_id, cr.case_number, cr.investigation_status
                                      FROM criminal_history ch
                                      LEFT JOIN crime_reports cr ON cr.id = ch.crime_report_id
                                      WHERE ch.criminal_id = :criminal_id
                                      ORDER BY ch.id DESC');
                $stmt->execute(['criminal_id' => $selectedCriminalId]);
                $criminalCrimes = $stmt->fetchAll();
            }
        }
    }

    if ($view === 'evidence') {
        $sql = 'SELECT id, evidence_code, evidence_type, title, chain_status, created_at FROM evidence WHERE 1=1';
        $params = [];

        if ($evidenceFilters['query'] !== '') {
            $sql .= ' AND (evidence_code LIKE :q OR title LIKE :q)';
            $params['q'] = '%' . $evidenceFilters['query'] . '%';
        }

        if ($evidenceFilters['status'] !== '') {
            $sql .= ' AND chain_status = :status';
            $params['status'] = strtoupper($evidenceFilters['status']);
        }

        if ($evidenceFilters['type'] !== '') {
            $sql .= ' AND evidence_type = :type';
            $params['type'] = strtoupper($evidenceFilters['type']);
        }

        $sql .= ' ORDER BY id DESC LIMIT :limit';

        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':limit', 200, PDO::PARAM_INT);
        $stmt->execute();
        $evidences = $stmt->fetchAll();

        if ($selectedEvidenceId > 0) {
            $stmt = $db->prepare('SELECT e.*, o.first_name AS collected_by_first_name, o.last_name AS collected_by_last_name
                                  FROM evidence e
                                  LEFT JOIN officers o ON o.id = e.collected_by_officer_id
                                  WHERE e.id = :id');
            $stmt->execute(['id' => $selectedEvidenceId]);
            $evidenceDetail = $stmt->fetch() ?: null;

            if ($evidenceDetail !== null) {
                $stmt = $db->prepare('SELECT cr.id, cr.case_number, cr.crime_type, cr.investigation_status, cr.crime_datetime
                                      FROM crime_report_evidence cre
                                      JOIN crime_reports cr ON cr.id = cre.crime_report_id
                                      WHERE cre.evidence_id = :evidence_id
                                      ORDER BY cr.crime_datetime DESC');
                $stmt->execute(['evidence_id' => $selectedEvidenceId]);
                $evidenceCrimes = $stmt->fetchAll();

                $stmt = $db->prepare('SELECT s.id, s.first_name, s.last_name, s.suspect_status
                                      FROM suspect_evidence se
                                      JOIN suspects s ON s.id = se.suspect_id
                                      WHERE se.evidence_id = :evidence_id
                                      ORDER BY s.last_name, s.first_name');
                $stmt->execute(['evidence_id' => $selectedEvidenceId]);
                $evidenceSuspects = $stmt->fetchAll();
            }
        }
    }

    if ($view === 'feedbacks') {
        $stmt = $db->query('SELECT f.id, f.module_name, f.category, f.message, f.status, f.created_at,
                                   o.first_name, o.last_name
                            FROM feedbacks f
                            JOIN officers o ON o.id = f.submitted_by_officer_id
                            ORDER BY f.id DESC LIMIT 100');
        $feedbacks = $stmt->fetchAll();
    }

    if ($view === 'schema') {
        $stmt = $db->query('SELECT sr.id, sr.request_type, sr.object_name, sr.reason, sr.sql_proposal, sr.status, sr.created_at,
                                   o.first_name, o.last_name
                            FROM schema_requests sr
                            JOIN officers o ON o.id = sr.requested_by_officer_id
                            ORDER BY sr.id DESC LIMIT 100');
        $schemas = $stmt->fetchAll();
    }

    if ($view === 'approvals' && $canReviewOfficers) {
        $applications = $officerApplications->listApplications($applicationStatusFilter);
    }

    if ($view === 'logs' && $canReadLogs) {
        $activityLogs = $audit->listOfficerActivity(50);
        $loginLogs = $audit->listLoginLogs(50);
    }
}

$logoPath = resolveImagePath([
    'assets/images/logo.png',
    'assets/images/logo.png.png',
]);
$bannerPath = resolveImagePath([
    'assets/images/banner.jpg',
    'assets/images/banner.png',
    'assets/images/banner.jpg.png',
]);
$logoExists = $logoPath !== null;
$bannerExists = $bannerPath !== null;

require __DIR__ . '/views/partials/top.php';

if (!$isAuthed) {
    if ($view === 'signup') {
        require __DIR__ . '/views/pages/signup.php';
    } else {
        require __DIR__ . '/views/pages/login.php';
    }
} else {
    require __DIR__ . '/views/partials/nav.php';

    if ($view === 'dashboard') {
        require __DIR__ . '/views/pages/dashboard.php';
    } elseif ($view === 'crimes' && $canReadCrimes) {
        require __DIR__ . '/views/pages/crimes.php';
    } elseif ($view === 'suspects' && $canReadSuspects) {
        require __DIR__ . '/views/pages/suspects.php';
    } elseif ($view === 'criminals' && $canReadCriminals) {
        require __DIR__ . '/views/pages/criminals.php';
    } elseif ($view === 'evidence' && $canReadEvidence) {
        require __DIR__ . '/views/pages/evidence.php';
    } elseif ($view === 'feedbacks' && $canReadFeedbacks) {
        require __DIR__ . '/views/pages/feedbacks.php';
    } elseif ($view === 'schema' && $canReadSchema) {
        require __DIR__ . '/views/pages/schema.php';
    } elseif ($view === 'logs' && $canReadLogs) {
        require __DIR__ . '/views/pages/logs.php';
    } elseif ($view === 'approvals' && $canReviewOfficers) {
        require __DIR__ . '/views/pages/approvals.php';
    }
}

require __DIR__ . '/views/partials/bottom.php';
