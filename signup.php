<!-- Signup page: public officer application form -->
<div class="card login-box">
    <?php if ($bannerExists): ?>
        <img src="<?= h((string)$bannerPath) ?>" alt="Project Banner" class="hero-banner">
    <?php endif; ?>
    <?php if ($logoExists): ?>
        <img src="<?= h((string)$logoPath) ?>" alt="Project Logo" class="logo">
    <?php endif; ?>
    <h1>Officer Signup</h1>
    <p class="help">Submit your application for Grade 1 approval.</p>
    <form method="post">
        <input type="hidden" name="action" value="signup">
        <label>First Name</label>
        <input type="text" name="first_name" required placeholder="First name">
        <label>Last Name</label>
        <input type="text" name="last_name" required placeholder="Last name">
        <label>Email</label>
        <input type="email" name="email" required placeholder="you@example.com">
        <label>Badge Number</label>
        <input type="text" name="badge_number" required placeholder="Badge number" data-upper="true">
        <label>Phone (+880...)</label>
        <input type="tel" name="phone" placeholder="+8801XXXXXXXXX" pattern="\+880[0-9]+" title="Use +880 followed by digits">
        <label>Requested Rank</label>
        <select name="requested_rank">
            <option value="GRADE_1">Grade 1</option>
            <option value="GRADE_2">Grade 2</option>
            <option value="GRADE_3" selected>Grade 3</option>
        </select>
        <label>Password</label>
        <input type="password" name="password" required placeholder="Minimum 8 characters">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" required placeholder="Re-enter password">
        <button type="submit">Submit Application</button>
    </form>
    <p class="help">Already approved? <a href="index.php">Return to login</a>.</p>
</div>
