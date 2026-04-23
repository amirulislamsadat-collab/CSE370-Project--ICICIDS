<header class="header card">
    <div>
        <?php if ($logoExists): ?>
            <img src="<?= h((string)$logoPath) ?>" alt="ICICIDS Logo" class="logo logo-small">
        <?php endif; ?>
        <h1>ICICIDS Dashboard</h1>
        <p class="help">Welcome <?= h((string)($officer['name'] ?? 'Officer')) ?> | Role: <?= h((string)$rank) ?></p>
    </div>
    <form method="post">
        <input type="hidden" name="action" value="logout">
        <button class="btn-secondary" type="submit">Logout</button>
    </form>
</header>

<nav class="tabs">
    <a class="<?= $view === 'dashboard' ? 'active' : '' ?>" href="index.php?view=dashboard">Dashboard</a>
    <?php if ($canReadCrimes): ?><a class="<?= $view === 'crimes' ? 'active' : '' ?>" href="index.php?view=crimes">Crimes</a><?php endif; ?>
    <?php if ($canReadEvidence): ?><a class="<?= $view === 'evidence' ? 'active' : '' ?>" href="index.php?view=evidence">Evidence</a><?php endif; ?>
    <?php if ($canReadFeedbacks): ?><a class="<?= $view === 'feedbacks' ? 'active' : '' ?>" href="index.php?view=feedbacks">Feedbacks</a><?php endif; ?>
    <?php if ($canReadSchema): ?><a class="<?= $view === 'schema' ? 'active' : '' ?>" href="index.php?view=schema">Schema Requests</a><?php endif; ?>
    <?php if ($canReadLogs): ?><a class="<?= $view === 'logs' ? 'active' : '' ?>" href="index.php?view=logs">Audit Logs</a><?php endif; ?>
</nav>
