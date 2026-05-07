<?php
// Evidence page: evidence CRUD, chain-status updates, and entity links.
$canCreateEvidence = $canCreateEvidence ?? false;
$canUpdateEvidence = $canUpdateEvidence ?? false;
$canLinkEvidence = $canLinkEvidence ?? false;
$canDeleteEvidence = $canDeleteEvidence ?? false;
$canUnlinkEvidence = $canUnlinkEvidence ?? false;
$evidenceDetail = $evidenceDetail ?? null;
$evidenceCrimes = $evidenceCrimes ?? [];
$evidenceSuspects = $evidenceSuspects ?? [];
$evidences = $evidences ?? [];
$evidenceFilters = $evidenceFilters ?? ['query' => '', 'status' => '', 'type' => ''];
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
                    <thead><tr><th>ID</th><th>Crime Name</th><th>Type</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                    <?php foreach ($evidenceCrimes as $row): ?>
                        <tr>
                            <td><a href="index.php?view=crimes&crime_id=<?= h((string)$row['id']) ?>"><?= h((string)$row['id']) ?></a></td>
                            <td><a href="index.php?view=crimes&crime_id=<?= h((string)$row['id']) ?>"><?= h((string)$row['case_number']) ?></a></td>
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
                            <td><a href="index.php?view=suspects&suspect_id=<?= h((string)$row['id']) ?>"><?= h((string)$row['first_name'] . ' ' . (string)$row['last_name']) ?></a></td>
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

<?php if ($canCreateEvidence || $canUpdateEvidence || $canDeleteEvidence || $canLinkEvidence): ?>
    <section class="card action-panel">
        <h2>Evidence Actions</h2>
        <p class="help">Select an action to open its form.</p>
        <div class="action-buttons" data-action-group="evidence-actions" data-default-action="evidence-create">
            <?php if ($canCreateEvidence): ?><button type="button" data-action-target="evidence-create">Add Evidence</button><?php endif; ?>
            <?php if ($canUpdateEvidence): ?><button type="button" data-action-target="evidence-update">Update Status</button><?php endif; ?>
            <?php if ($canDeleteEvidence): ?><button type="button" data-action-target="evidence-delete">Delete Evidence</button><?php endif; ?>
            <?php if ($canLinkEvidence): ?><button type="button" data-action-target="evidence-link">Link Evidence</button><?php endif; ?>
            <?php if ($canUnlinkEvidence): ?><button type="button" data-action-target="evidence-unlink-case">Unlink from Crime</button><?php endif; ?>
            <?php if ($canUnlinkEvidence): ?><button type="button" data-action-target="evidence-unlink-suspect">Unlink from Suspect</button><?php endif; ?>
        </div>
    </section>
    <div class="action-forms" data-action-forms="evidence-actions">
    <?php if ($canCreateEvidence): ?>
        <section class="card action-form" data-action-form="evidence-create" hidden>
            <h2>Add Evidence (Linked to Crime)</h2>
            <form method="post">
                <input type="hidden" name="action" value="create_evidence">
                <label>Crime Report ID</label>
                <input type="number" name="crime_report_id" required placeholder="Crime report ID">
                <label>Evidence Type</label>
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
                <label>Evidence Title</label>
                <input name="title" required placeholder="Evidence title">
                <label>File Path</label>
                <input name="file_path" placeholder="/evidence/file.ext">
                <label>Storage Location</label>
                <input name="storage_location" placeholder="Storage location">
                <label>Chain Status</label>
                <select name="chain_status">
                    <option>COLLECTED</option>
                    <option>IN_LAB</option>
                    <option>IN_STORAGE</option>
                    <option>IN_COURT</option>
                    <option>RELEASED</option>
                    <option>DISPOSED</option>
                </select>
                <label>Relation Note</label>
                <input name="relation_note" placeholder="Relation note (optional)">
                <label>Description</label>
                <textarea name="evidence_description" rows="3" placeholder="Description"></textarea>
                <button type="submit">Create</button>
            </form>
        </section>
    <?php endif; ?>

    <?php if ($canUpdateEvidence): ?>
        <section class="card action-form" data-action-form="evidence-update" hidden>
            <h2>Update Evidence Status</h2>
            <form method="post">
                <input type="hidden" name="action" value="update_evidence">
                <label>Evidence ID</label>
                <input type="number" name="evidence_id" required placeholder="Evidence ID">
                <label>New Chain Status</label>
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
        <section class="card action-form" data-action-form="evidence-delete" hidden>
            <h2>Delete Evidence</h2>
            <form method="post">
                <input type="hidden" name="action" value="delete_evidence">
                <label>Evidence ID</label>
                <input type="number" name="evidence_id" required placeholder="Evidence ID">
                <button type="submit">Delete</button>
            </form>
        </section>
    <?php endif; ?>
    <?php if ($canLinkEvidence): ?>
        <section class="card action-form" data-action-form="evidence-link" hidden>
            <h2>Link Existing Evidence to Crime</h2>
            <form method="post">
                <input type="hidden" name="action" value="link_evidence">
                <label>Evidence ID</label>
                <input type="number" name="evidence_id" required placeholder="Evidence ID">
                <label>Crime Report ID</label>
                <input type="number" name="crime_report_id" required placeholder="Crime report ID">
                <label>Relation Note</label>
                <input name="relation_note" placeholder="Relation note (optional)">
                <button type="submit">Link</button>
            </form>
        </section>

    <?php if ($canUnlinkEvidence): ?>
        <section class="card action-form" data-action-form="evidence-unlink-case" hidden>
            <h2>Unlink Evidence From Crime</h2>
            <form method="post">
                <input type="hidden" name="action" value="unlink_evidence_case">
                <label>Evidence ID</label>
                <input type="number" name="evidence_id" required placeholder="Evidence ID to unlink">
                <label>Crime Report ID</label>
                <input type="number" name="crime_report_id" required placeholder="Crime report ID to unlink from">
                <button type="submit">Unlink</button>
            </form>
        </section>

        <section class="card action-form" data-action-form="evidence-unlink-suspect" hidden>
            <h2>Unlink Evidence From Suspect</h2>
            <form method="post">
                <input type="hidden" name="action" value="unlink_evidence_suspect">
                <label>Evidence ID</label>
                <input type="number" name="evidence_id" required placeholder="Evidence ID to unlink">
                <label>Suspect ID</label>
                <input type="number" name="suspect_id" required placeholder="Suspect ID to unlink from">
                <button type="submit">Unlink</button>
            </form>
        </section>
    <?php endif; ?>
    <?php endif; ?>
    </div>
<?php endif; ?>

<section class="card">
    <h2>Filter Evidence</h2>
    <form method="get" class="filter-grid">
        <input type="hidden" name="view" value="evidence">
        <div class="filter-field">
            <label>Search (Code or Title)</label>
            <input type="text" name="evidence_q" value="<?= h((string)$evidenceFilters['query']) ?>" placeholder="Search code or title">
        </div>
        <div class="filter-field">
            <label>Type</label>
            <select name="evidence_type">
            <option value="" <?= $evidenceFilters['type'] === '' ? 'selected' : '' ?>>All types</option>
            <option value="FINGERPRINT" <?= $evidenceFilters['type'] === 'FINGERPRINT' ? 'selected' : '' ?>>FINGERPRINT</option>
            <option value="DOCUMENT" <?= $evidenceFilters['type'] === 'DOCUMENT' ? 'selected' : '' ?>>DOCUMENT</option>
            <option value="DIGITAL_FILE" <?= $evidenceFilters['type'] === 'DIGITAL_FILE' ? 'selected' : '' ?>>DIGITAL_FILE</option>
            <option value="WEAPON" <?= $evidenceFilters['type'] === 'WEAPON' ? 'selected' : '' ?>>WEAPON</option>
            <option value="BIOLOGICAL" <?= $evidenceFilters['type'] === 'BIOLOGICAL' ? 'selected' : '' ?>>BIOLOGICAL</option>
            <option value="VIDEO" <?= $evidenceFilters['type'] === 'VIDEO' ? 'selected' : '' ?>>VIDEO</option>
            <option value="AUDIO" <?= $evidenceFilters['type'] === 'AUDIO' ? 'selected' : '' ?>>AUDIO</option>
            <option value="OTHER" <?= $evidenceFilters['type'] === 'OTHER' ? 'selected' : '' ?>>OTHER</option>
            </select>
        </div>
        <div class="filter-field">
            <label>Status</label>
            <select name="evidence_status">
            <option value="" <?= $evidenceFilters['status'] === '' ? 'selected' : '' ?>>All statuses</option>
            <option value="COLLECTED" <?= $evidenceFilters['status'] === 'COLLECTED' ? 'selected' : '' ?>>COLLECTED</option>
            <option value="IN_LAB" <?= $evidenceFilters['status'] === 'IN_LAB' ? 'selected' : '' ?>>IN_LAB</option>
            <option value="IN_STORAGE" <?= $evidenceFilters['status'] === 'IN_STORAGE' ? 'selected' : '' ?>>IN_STORAGE</option>
            <option value="IN_COURT" <?= $evidenceFilters['status'] === 'IN_COURT' ? 'selected' : '' ?>>IN_COURT</option>
            <option value="RELEASED" <?= $evidenceFilters['status'] === 'RELEASED' ? 'selected' : '' ?>>RELEASED</option>
            <option value="DISPOSED" <?= $evidenceFilters['status'] === 'DISPOSED' ? 'selected' : '' ?>>DISPOSED</option>
            </select>
        </div>
        <div class="filter-actions">
            <button type="submit">Apply Filters</button>
            <a class="btn-secondary" href="index.php?view=evidence">Reset</a>
        </div>
    </form>
</section>

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
