<!-- Feedback page: submission form and feedback listing table. -->
<?php if ($canSubmitFeedback): ?>
    <section class="card">
        <h2>Submit Feedback</h2>
        <form method="post">
            <input type="hidden" name="action" value="submit_feedback">
            <label>Module</label>
            <select name="module_name">
                <option>CRIME_REPORTING</option>
                <option>SUSPECT_TRACKING</option>
                <option>CRIMINAL_DB</option>
                <option>EVIDENCE_MANAGEMENT</option>
                <option>INVESTIGATION_PROGRESS</option>
                <option>AUTH_SECURITY</option>
                <option>OTHER</option>
            </select>
            <label>Category</label>
            <select name="category">
                <option>BUG</option>
                <option>FEATURE</option>
                <option>DATA_QUALITY</option>
                <option>USABILITY</option>
                <option>SECURITY</option>
                <option>OTHER</option>
            </select>
            <label>Message</label>
            <textarea name="message" rows="4" required placeholder="Write your feedback"></textarea>
            <button type="submit">Submit</button>
        </form>
    </section>
<?php endif; ?>

<section class="card">
    <h2>Feedback List</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Officer</th><th>Module</th><th>Category</th><th>Status</th><th>Message</th></tr></thead>
            <tbody>
            <?php foreach ($feedbacks as $row): ?>
                <tr>
                    <td><?= h((string)$row['id']) ?></td>
                    <td><?= h((string)$row['first_name'] . ' ' . (string)$row['last_name']) ?></td>
                    <td><?= h((string)$row['module_name']) ?></td>
                    <td><?= h((string)$row['category']) ?></td>
                    <td><?= h((string)$row['status']) ?></td>
                    <td><?= h((string)$row['message']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
