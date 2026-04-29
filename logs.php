<!-- Logs page: officer activity and authentication audit trails. -->
<section class="card">
    <h2>Officer Activity Logs</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Officer</th><th>Type</th><th>Target</th><th>Details</th><th>Time</th></tr></thead>
            <tbody>
            <?php foreach ($activityLogs as $row): ?>
                <tr>
                    <td><?= h((string)$row['id']) ?></td>
                    <td><?= h((string)$row['officer_id']) ?></td>
                    <td><?= h((string)$row['activity_type']) ?></td>
                    <td><?= h((string)($row['target_table'] ?? '') . ' #' . (string)($row['target_record_id'] ?? '')) ?></td>
                    <td><?= h((string)($row['action_details'] ?? '')) ?></td>
                    <td><?= h((string)$row['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="card">
    <h2>Login Logs</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Officer</th><th>Email</th><th>Status</th><th>IP</th><th>Time</th></tr></thead>
            <tbody>
            <?php foreach ($loginLogs as $row): ?>
                <tr>
                    <td><?= h((string)$row['id']) ?></td>
                    <td><?= h((string)($row['officer_id'] ?? '')) ?></td>
                    <td><?= h((string)($row['email_attempted'] ?? '')) ?></td>
                    <td><?= h((string)$row['login_status']) ?></td>
                    <td><?= h((string)$row['ip_address']) ?></td>
                    <td><?= h((string)$row['login_time']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
