<?php if ($canSubmitSchema): ?>
    <section class="card">
        <h2>Submit Schema Request</h2>
        <form method="post">
            <input type="hidden" name="action" value="submit_schema">
            <select name="request_type">
                <option>CREATE</option>
                <option>ALTER</option>
                <option>DROP</option>
                <option>INDEX</option>
                <option>OTHER</option>
            </select>
            <input name="object_name" required placeholder="Target object">
            <textarea name="reason" rows="3" required placeholder="Reason"></textarea>
            <textarea name="sql_proposal" rows="4" required placeholder="SQL proposal"></textarea>
            <button type="submit">Submit</button>
        </form>
    </section>
<?php endif; ?>

<section class="card">
    <h2>Schema Requests</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Officer</th><th>Type</th><th>Object</th><th>Status</th><th>Reason</th></tr></thead>
            <tbody>
            <?php foreach ($schemas as $row): ?>
                <tr>
                    <td><?= h((string)$row['id']) ?></td>
                    <td><?= h((string)$row['first_name'] . ' ' . (string)$row['last_name']) ?></td>
                    <td><?= h((string)$row['request_type']) ?></td>
                    <td><?= h((string)$row['object_name']) ?></td>
                    <td><?= h((string)$row['status']) ?></td>
                    <td><?= h((string)$row['reason']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
