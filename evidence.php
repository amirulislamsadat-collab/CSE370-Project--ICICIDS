<?php
// Evidence page: evidence CRUD, chain-status updates, and entity links.
$canCreateEvidence = $canCreateEvidence ?? false;
$canUpdateEvidence = $canUpdateEvidence ?? false;
$canLinkEvidence = $canLinkEvidence ?? false;
$canDeleteEvidence = $canDeleteEvidence ?? false;
$evidenceDetail = $evidenceDetail ?? null;
$evidenceCrimes = $evidenceCrimes ?? [];
$evidenceSuspects = $evidenceSuspects ?? [];
$evidences = $evidences ?? [];
?>

<?php if ($evidenceDetail): ?>
    <section class="card">
        <h2>Evidence Details</h2>
        <div class="table-wrap">
            <table>
                <tbody>
                <tr>
                    <th>ID</th>
                    <td><?= h((string)$evidenceDetail['id']) ?></td>
                    <th>Code</th>
                    <td><?= h((string)$evidenceDetail['evidence_code']) ?></td>
                </tr>
                <tr>
                    <th>Title</th>
                    <td><?= h((string)$evidenceDetail['title']) ?></td>
                    <th>Type</th>
                    <td><?= h((string)$evidenceDetail['evidence_type']) ?></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td><?= h((string)$evidenceDetail['chain_status']) ?></td>
                    <th>Collected At</th>
                    <td><?= h((string)($evidenceDetail['collected_at'] ?? '')) ?></td>
                </tr>
                <tr>
                    <th>Collected By</th>
                    <td><?= h((string)($evidenceDetail['collected_by_first_name'] ?? '') . ' ' . (string)($evidenceDetail['collected_by_last_name'] ?? '')) ?></td>
                    <th>Storage</th>
                    <td><?= h((string)($evidenceDetail['storage_location'] ?? '')) ?></td>
                </tr>
                <tr>
                    <th>File</th>
                    <td colspan="3"><?= h((string)($evidenceDetail['file_path'] ?? '')) ?></td>
                </tr>
                </tbody>
            </table>
        </div>

        <h3>Related Crimes</h3>
        <?php if ($evidenceCrimes): ?>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>ID</th><th>Case</th><th>Type</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                    <?php foreach ($evidenceCrimes as $row): ?>
                        <tr>
                            <td><a href="index.php?view=crimes&crime_id=<?= h((string)$row['id']) ?>"><?= h((string)$row['id']) ?></a></td>
                            <td><?= h((string)$row['case_number']) ?></td>
                            <td><?= h((string)$row['crime_type']) ?></td>
                            <td><?= h((string)$row['investigation_status']) ?></td>
                            <td><?= h((string)$row['crime_datetime']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="help">No crimes linked to this evidence yet.</p>
        <?php endif; ?>

        <h3>Related Suspects</h3>
        <?php if ($evidenceSuspects): ?>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>ID</th><th>Name</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($evidenceSuspects as $row): ?>
                        <tr>
                            <td><a href="index.php?view=suspects&suspect_id=<?= h((string)$row['id']) ?>"><?= h((string)$row['id']) ?></a></td>
                            <td><?= h((string)$row['first_name'] . ' ' . (string)$row['last_name']) ?></td>
                            <td><?= h((string)$row['suspect_status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="help">No suspects linked to this evidence yet.</p>
        <?php endif; ?>
    </section>
<?php endif; ?>

<div class="grid-2">
    <?php if ($canCreateEvidence): ?>
        <section class="card">
            <h2>Add Evidence (Linked to Crime)</h2>
            <form method="post">
                <input type="hidden" name="action" value="create_evidence">
                <input type="number" name="crime_report_id" required placeholder="Crime report ID">
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
                <input name="relation_note" placeholder="Relation note (optional)">
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

    <?php if ($canDeleteEvidence): ?>
        <section class="card">
            <h2>Delete Evidence</h2>
            <form method="post">
                <input type="hidden" name="action" value="delete_evidence">
                <input type="number" name="evidence_id" required placeholder="Evidence ID">
                <button type="submit">Delete</button>
            </form>
        </section>
    <?php endif; ?>
</div>

<?php if ($canLinkEvidence): ?>
    <section class="card">
        <h2>Link Existing Evidence to Crime</h2>
        <form method="post">
            <input type="hidden" name="action" value="link_evidence">
            <input type="number" name="evidence_id" required placeholder="Evidence ID">
            <input type="number" name="crime_report_id" required placeholder="Crime report ID">
            <input name="relation_note" placeholder="Relation note (optional)">
            <button type="submit">Link</button>
        </form>
    </section>

    <section class="card">
        <h2>Unlink Evidence From Crime</h2>
        <form method="post">
            <input type="hidden" name="action" value="unlink_evidence_case">
            <input type="number" name="evidence_id" required placeholder="Evidence ID to unlink">
            <input type="number" name="crime_report_id" required placeholder="Crime report ID to unlink from">
            <button type="submit">Unlink</button>
        </form>
    </section>

    <section class="card">
        <h2>Unlink Evidence From Suspect</h2>
        <form method="post">
            <input type="hidden" name="action" value="unlink_evidence_suspect">
            <input type="number" name="evidence_id" required placeholder="Evidence ID to unlink">
            <input type="number" name="suspect_id" required placeholder="Suspect ID to unlink from">
            <button type="submit">Unlink</button>
        </form>
    </section>
<?php endif; ?>

<section class="card">
    <h2>Evidence List</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Code</th><th>Type</th><th>Title</th><th>Status</th><th>Created</th></tr></thead>
            <tbody>
            <?php foreach ($evidences as $row): ?>
                <tr>
                    <td><a href="index.php?view=evidence&evidence_id=<?= h((string)$row['id']) ?>"><?= h((string)$row['id']) ?></a></td>
                    <td><a href="index.php?view=evidence&evidence_id=<?= h((string)$row['id']) ?>"><?= h((string)$row['evidence_code']) ?></a></td>
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
