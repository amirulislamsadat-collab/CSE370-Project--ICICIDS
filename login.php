<!-- Login page: officer authentication form with project branding assets. -->
<div class="card login-box">
    <?php if ($bannerExists): ?>
        <img src="<?= h((string)$bannerPath) ?>" alt="Project Banner" class="hero-banner">
    <?php endif; ?>
    <?php if ($logoExists): ?>
        <img src="<?= h((string)$logoPath) ?>" alt="Project Logo" class="logo">
    <?php endif; ?>
    <h1>ICICIDS Login</h1>
    <p class="help">Crime Reporting and Management System</p>
    <form method="post">
        <input type="hidden" name="action" value="login">
        <label>Email</label>
        <input type="email" name="email" required placeholder="admin@icicids.local">
        <label>Password</label>
        <input type="password" name="password" required>
        <button type="submit">Login</button>
    </form>
</div>
