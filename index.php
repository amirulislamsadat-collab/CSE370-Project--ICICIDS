<?php
declare(strict_types=1);

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/AuditLogger.php';
require_once __DIR__ . '/CrimeAndSuspectManager.php';
require_once __DIR__ . '/EvidenceAndInvestigation.php';

$db = Database::getInstance()->getConnection();
$auth = new Auth($db);
$audit = new AuditLogger($db, $auth);
$crimeManager = new CrimeAndSuspectManager($db, $auth, $audit);
$evidenceManager = new EvidenceAndInvestigation($db, $auth, $audit);

$view = isset($_GET['view']) ? (string)$_GET['view'] : 'dashboard';
$validViews = ['dashboard', 'crimes', 'evidence', 'feedbacks', 'schema', 'logs'];
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

        $auth->requireAuthentication();

        if ($action === 'create_crime') {
            $rawDate = trim((string)($_POST['crime_datetime'] ?? ''));
            $crimeDate = str_replace('T', ' ', $rawDate);
            if (strlen($crimeDate) === 16) {
                $crimeDate .= ':00';
            }

            $crimeManager->createCrimeReport([
                'case_number' => trim((string)($_POST['case_number'] ?? '')),
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

        if ($action === 'create_evidence') {
            $code = 'EVD-' . date('Ymd-His');
            $evidenceManager->createEvidence([
                'evidence_code' => $code,
                'evidence_type' => trim((string)($_POST['evidence_type'] ?? 'OTHER')),
                'title' => trim((string)($_POST['title'] ?? '')),
                'description' => trim((string)($_POST['evidence_description'] ?? '')),
                'file_path' => trim((string)($_POST['file_path'] ?? '')),
                'collected_at' => date('Y-m-d H:i:s'),
                'storage_location' => trim((string)($_POST['storage_location'] ?? '')),
                'chain_status' => trim((string)($_POST['chain_status'] ?? 'COLLECTED')),
                'integrity_hash' => hash('sha256', $code . microtime(true)),
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

        flashSet('error', 'Unknown action.');
        header('Location: index.php');
        exit;
    } catch (Throwable $e) {
        flashSet('error', $e->getMessage());
        header('Location: index.php?view=' . urlencode($view));
        exit;
    }
}

$isAuthed = $auth->isAuthenticated();
$officer = $auth->currentOfficer();
$rank = $officer['rank'] ?? '';
$flash = flashGet();
$pageTitle = 'ICICIDS';

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
$canReadEvidence = false;
$canReadFeedbacks = false;
$canReadSchema = false;
$canReadLogs = false;
$canCreateCrime = false;
$canUpdateCrime = false;
$canCreateEvidence = false;
$canUpdateEvidence = false;
$canSubmitFeedback = false;
$canSubmitSchema = false;

$stats = ['officers' => 0, 'crime_reports' => 0, 'suspects' => 0, 'evidence' => 0, 'criminals' => 0];
$crimeReports = [];
$evidences = [];
$feedbacks = [];
$schemas = [];
$activityLogs = [];
$loginLogs = [];

if ($isAuthed) {
    $canReadCrimes = $auth->can('READ', 'crime_reports');
    $canReadEvidence = $auth->can('READ', 'evidence');
    $canReadFeedbacks = $auth->can('READ', 'feedbacks');
    $canReadSchema = $auth->can('READ', 'schema_requests');
    $canReadLogs = $auth->can('READ', 'officer_activity');

    $canCreateCrime = $auth->can('CREATE', 'crime_reports');
    $canUpdateCrime = $auth->can('UPDATE', 'crime_reports');
    $canCreateEvidence = $auth->can('CREATE', 'evidence');
    $canUpdateEvidence = $auth->can('UPDATE', 'evidence');
    $canSubmitFeedback = $auth->can('CREATE', 'feedbacks');
    $canSubmitSchema = $auth->can('CREATE', 'schema_requests');

    $viewAllowed = (
        ($view === 'dashboard')
        || ($view === 'crimes' && $canReadCrimes)
        || ($view === 'evidence' && $canReadEvidence)
        || ($view === 'feedbacks' && $canReadFeedbacks)
        || ($view === 'schema' && $canReadSchema)
        || ($view === 'logs' && $canReadLogs)
    );

    if (!$viewAllowed) {
        $view = 'dashboard';
    }

    if ($canReadCrimes) {
        foreach (['officers', 'crime_reports', 'suspects', 'evidence', 'criminals'] as $table) {
            $stmt = $db->query('SELECT COUNT(*) AS total FROM ' . $table);
            $stats[$table] = (int)($stmt->fetch()['total'] ?? 0);
        }

        $crimeReports = $crimeManager->listCrimeReports(null, null, 100);
    }

    if ($view === 'evidence') {
        $stmt = $db->query('SELECT id, evidence_code, evidence_type, title, chain_status, created_at FROM evidence ORDER BY id DESC LIMIT 100');
        $evidences = $stmt->fetchAll();
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
        $stmt = $db->query('SELECT sr.id, sr.request_type, sr.object_name, sr.reason, sr.status, sr.created_at,
                                   o.first_name, o.last_name
                            FROM schema_requests sr
                            JOIN officers o ON o.id = sr.requested_by_officer_id
                            ORDER BY sr.id DESC LIMIT 100');
        $schemas = $stmt->fetchAll();
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
    require __DIR__ . '/views/pages/login.php';
} else {
    require __DIR__ . '/views/partials/nav.php';

    if ($view === 'dashboard') {
        require __DIR__ . '/views/pages/dashboard.php';
    } elseif ($view === 'crimes' && $canReadCrimes) {
        require __DIR__ . '/views/pages/crimes.php';
    } elseif ($view === 'evidence' && $canReadEvidence) {
        require __DIR__ . '/views/pages/evidence.php';
    } elseif ($view === 'feedbacks' && $canReadFeedbacks) {
        require __DIR__ . '/views/pages/feedbacks.php';
    } elseif ($view === 'schema' && $canReadSchema) {
        require __DIR__ . '/views/pages/schema.php';
    } elseif ($view === 'logs' && $canReadLogs) {
        require __DIR__ . '/views/pages/logs.php';
    }
}

require __DIR__ . '/views/partials/bottom.php';
