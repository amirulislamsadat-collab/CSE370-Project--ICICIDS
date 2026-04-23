<div class="grid-2">
    <?php if ($canCreateEvidence): ?>
        <section class="card">
            <h2>Add Evidence</h2>
            <form method="post">
                <input type="hidden" name="action" value="create_evidence">
                <select name="evidence_type">
                    <option>FINGERPRINT</option>
                    <option>DOCUMENT</option>
                    <option>DIGITAL_FILE</option>
                    <option>WEAPON</option>
                    <option>BIOLOGICAL</option>
                    <option>VIDEO</option>
                    <option>AUDIO</option>
                    <option>OTHER</option>
                </select>
                <input name="title" required placeholder="Evidence title">
                <input name="file_path" placeholder="/evidence/file.ext">
                <input name="storage_location" placeholder="Storage location">
                <select name="chain_status">
                    <option>COLLECTED</option>
                    <option>IN_LAB</option>
                    <option>IN_STORAGE</option>
                    <option>IN_COURT</option>
                    <option>RELEASED</option>
                    <option>DISPOSED</option>
                </select>
                <textarea name="evidence_description" rows="3" placeholder="Description"></textarea>
                <button type="submit">Create</button>
            </form>
        </section>
    <?php endif; ?>

    <?php if ($canUpdateEvidence): ?>
        <section class="card">
            <h2>Update Evidence Status</h2>
            <form method="post">
                <input type="hidden" name="action" value="update_evidence">
                <input type="number" name="evidence_id" required placeholder="Evidence ID">
                <select name="new_chain_status">
                    <option>COLLECTED</option>
                    <option>IN_LAB</option>
                    <option>IN_STORAGE</option>
                    <option>IN_COURT</option>
                    <option>RELEASED</option>
                    <option>DISPOSED</option>
                </select>
                <button type="submit">Update</button>
            </form>
        </section>
    <?php endif; ?>
</div>

<section class="card">
    <h2>Evidence List</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Code</th><th>Type</th><th>Title</th><th>Status</th><th>Created</th></tr></thead>
            <tbody>
            <?php foreach ($evidences as $row): ?>
                <tr>
                    <td><?= h((string)$row['id']) ?></td>
                    <td><?= h((string)$row['evidence_code']) ?></td>
                    <td><?= h((string)$row['evidence_type']) ?></td>
                    <td><?= h((string)$row['title']) ?></td>
                    <td><?= h((string)$row['chain_status']) ?></td>
                    <td><?= h((string)$row['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
