<?php
// Suspects page: suspect lifecycle operations and case/evidence relationship views.
$canReadSuspects = $canReadSuspects ?? false;
$canCreateSuspect = $canCreateSuspect ?? false;
$canUpdateSuspect = $canUpdateSuspect ?? false;
$canLinkSuspect = $canLinkSuspect ?? false;
$canDeleteSuspect = $canDeleteSuspect ?? false;
$suspects = $suspects ?? [];
$suspectDetail = $suspectDetail ?? null;
$suspectCrimes = $suspectCrimes ?? [];
$suspectEvidence = $suspectEvidence ?? [];
$suspectFilters = $suspectFilters ?? ['query' => '', 'status' => ''];
?>
<?php if ($canCreateSuspect || $canLinkSuspect || $canDeleteSuspect || $canUpdateSuspect): ?>
    <section class="card action-panel">
        <h2>Suspect Actions</h2>
        <p class="help">Select an action to open its form.</p>
        <div class="action-buttons" data-action-group="suspect-actions" data-default-action="suspect-create">
            <?php if ($canCreateSuspect): ?><button type="button" data-action-target="suspect-create">Create Suspect</button><?php endif; ?>
            <?php if ($canLinkSuspect): ?><button type="button" data-action-target="suspect-link">Link Suspect</button><?php endif; ?>
            <?php if ($canUpdateSuspect): ?><button type="button" data-action-target="suspect-update">Update Status</button><?php endif; ?>
            <?php if ($canDeleteSuspect): ?><button type="button" data-action-target="suspect-delete">Delete Suspect</button><?php endif; ?>
            <?php if ($canLinkSuspect): ?><button type="button" data-action-target="suspect-unlink">Unlink Suspect</button><?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<div class="action-forms" data-action-forms="suspect-actions">
    <?php if ($canCreateSuspect): ?>
        <section class="card action-form" data-action-form="suspect-create">
            <h2>Create Suspect (Linked to Crime)</h2>
            <form method="post">
                <input type="hidden" name="action" value="create_suspect">
                <label>Crime Report ID</label>
                <input type="number" name="crime_report_id" required placeholder="Crime report ID">
                <label>Relation Type</label>
                <select name="relation_type">
                    <option>PRIMARY</option>
                    <option>SECONDARY</option>
                    <option>WITNESS_LINKED</option>
                    <option>ASSOCIATE</option>
                </select>
                <label>Relation Notes</label>
                <input name="relation_notes" placeholder="Relation notes (optional)">
                <label>First Name</label>
                <input name="first_name" required placeholder="First name">
                <label>Last Name</label>
                <input name="last_name" required placeholder="Last name">
                <label>Date of Birth</label>
                <input type="date" name="date_of_birth" placeholder="Date of birth">
                <label>Gender</label>
                <select name="gender">
                    <option>UNKNOWN</option>
                    <option>MALE</option>
                    <option>FEMALE</option>
                    <option>OTHER</option>
                </select>
                <label>National ID</label>
                <input name="national_id" placeholder="NID-ICI-0001" pattern="NID-ICI-[0-9]{4}" title="Use NID-ICI-0001 format" data-upper="true">
                <label>Phone</label>
                <input type="tel" name="phone" placeholder="+8801XXXXXXXXX" pattern="\+880[0-9]+" title="Use +880 followed by digits">
                <label>Address</label>
                <input name="address_line" placeholder="Address">
                <label>Suspect Status</label>
                <select name="suspect_status">
                    <option>PERSON_OF_INTEREST</option>
                    <option>WANTED</option>
                    <option>ARRESTED</option>
                    <option>CLEARED</option>
                </select>
                <label>Reason for Suspicion</label>
                <textarea name="reason_for_suspicion" rows="3" required placeholder="Reason for suspicion"></textarea>
                <button type="submit">Create</button>
            </form>
        </section>
    <?php endif; ?>

    <?php if ($canLinkSuspect): ?>
        <section class="card action-form" data-action-form="suspect-link">
            <h2>Link Existing Suspect to Crime</h2>
            <form method="post">
                <input type="hidden" name="action" value="link_suspect">
                <label>Suspect ID</label>
                <input type="number" name="suspect_id" required placeholder="Suspect ID">
                <label>Crime Report ID</label>
                <input type="number" name="crime_report_id" required placeholder="Crime report ID">
                <label>Relation Type</label>
                <select name="relation_type">
                    <option>PRIMARY</option>
                    <option>SECONDARY</option>
                    <option>WITNESS_LINKED</option>
                    <option>ASSOCIATE</option>
                </select>
                <label>Relation Notes</label>
                <input name="relation_notes" placeholder="Relation notes (optional)">
                <button type="submit">Link</button>
            </form>
        </section>
    <?php endif; ?>

    <?php if ($canDeleteSuspect): ?>
        <section class="card action-form" data-action-form="suspect-delete">
            <h2>Delete Suspect</h2>
            <form method="post">
                <input type="hidden" name="action" value="delete_suspect">
                <label>Suspect ID</label>
                <input type="number" name="suspect_id" required placeholder="Suspect ID">
                <button type="submit">Delete</button>
            </form>
        </section>
    <?php endif; ?>

    <?php if ($canUpdateSuspect): ?>
        <section class="card action-form" data-action-form="suspect-update">
            <h2>Update Suspect Status</h2>
            <form method="post">
                <input type="hidden" name="action" value="update_suspect_status">
                <label>Suspect ID</label>
                <input type="number" name="suspect_id" required placeholder="Suspect ID to update">
                <label>New Status</label>
                <select name="suspect_status">
                    <option>PERSON_OF_INTEREST</option>
                    <option>WANTED</option>
                    <option>ARRESTED</option>
                    <option>CLEARED</option>
                </select>
                <button type="submit">Update Status</button>
            </form>
        </section>
    <?php endif; ?>

    <?php if ($canLinkSuspect): ?>
        <section class="card action-form" data-action-form="suspect-unlink">
            <h2>Unlink Suspect From Crime</h2>
            <form method="post">
                <input type="hidden" name="action" value="unlink_suspect">
                <label>Suspect ID</label>
                <input type="number" name="suspect_id" required placeholder="Suspect ID to unlink">
                <label>Crime Report ID</label>
                <input type="number" name="crime_report_id" required placeholder="Crime report ID to unlink from">
                <button type="submit">Unlink</button>
            </form>
        </section>
    <?php endif; ?>
</div>

<?php if ($suspectDetail): ?>
    <section class="card">
        <h2>Suspect Details</h2>
        <div class="table-wrap">
            <table>
                <tbody>
                <tr>
                    <th>ID</th>
                    <td><?= h((string)$suspectDetail['id']) ?></td>
                    <th>Status</th>
                    <td><?= h((string)$suspectDetail['suspect_status']) ?></td>
                </tr>
                <tr>
                    <th>Name</th>
                    <td><?= h((string)$suspectDetail['first_name'] . ' ' . (string)$suspectDetail['last_name']) ?></td>
                    <th>Gender</th>
                    <td><?= h((string)$suspectDetail['gender']) ?></td>
                </tr>
                <tr>
                    <th>Date of Birth</th>
                    <td><?= h((string)($suspectDetail['date_of_birth'] ?? '')) ?></td>
                    <th>National ID</th>
                    <td><?= h((string)($suspectDetail['national_id'] ?? '')) ?></td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td><?= h((string)($suspectDetail['phone'] ?? '')) ?></td>
                    <th>Address</th>
                    <td><?= h((string)($suspectDetail['address_line'] ?? '')) ?></td>
                </tr>
                <tr>
                    <th>Reason</th>
                    <td colspan="3"><?= h((string)$suspectDetail['reason_for_suspicion']) ?></td>
                </tr>
                </tbody>
            </table>
        </div>

        <h3>Related Crimes</h3>
        <?php if ($suspectCrimes): ?>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>ID</th><th>Crime Name</th><th>Type</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                    <?php foreach ($suspectCrimes as $row): ?>
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
            <p class="help">No crime links recorded for this suspect.</p>
        <?php endif; ?>

        <h3>Related Evidence</h3>
        <?php if ($suspectEvidence): ?>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>ID</th><th>Code</th><th>Title</th><th>Type</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($suspectEvidence as $row): ?>
                        <tr>
                            <td><a href="index.php?view=evidence&evidence_id=<?= h((string)$row['id']) ?>"><?= h((string)$row['id']) ?></a></td>
                            <td><?= h((string)$row['evidence_code']) ?></td>
                            <td><a href="index.php?view=evidence&evidence_id=<?= h((string)$row['id']) ?>"><?= h((string)$row['title']) ?></a></td>
                            <td><?= h((string)$row['evidence_type']) ?></td>
                            <td><?= h((string)$row['chain_status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="help">No evidence linked to this suspect.</p>
        <?php endif; ?>
    </section>
<?php endif; ?>

<section class="card">
    <h2>Filter Suspects</h2>
    <form method="get" class="filter-grid">
        <input type="hidden" name="view" value="suspects">
        <label>Search (Name or NID)</label>
        <input type="text" name="suspect_q" value="<?= h((string)$suspectFilters['query']) ?>" placeholder="Search name or NID">
        <label>Status</label>
        <select name="suspect_status">
            <option value="" <?= $suspectFilters['status'] === '' ? 'selected' : '' ?>>All statuses</option>
            <option value="PERSON_OF_INTEREST" <?= $suspectFilters['status'] === 'PERSON_OF_INTEREST' ? 'selected' : '' ?>>PERSON_OF_INTEREST</option>
            <option value="WANTED" <?= $suspectFilters['status'] === 'WANTED' ? 'selected' : '' ?>>WANTED</option>
            <option value="ARRESTED" <?= $suspectFilters['status'] === 'ARRESTED' ? 'selected' : '' ?>>ARRESTED</option>
            <option value="CLEARED" <?= $suspectFilters['status'] === 'CLEARED' ? 'selected' : '' ?>>CLEARED</option>
        </select>
        <button type="submit">Apply Filters</button>
        <a class="btn-secondary" href="index.php?view=suspects">Reset</a>
    </form>
</section>

<section class="card">
    <h2>Suspects</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Name</th><th>National ID</th><th>Status</th><th>Created</th></tr></thead>
            <tbody>
            <?php foreach ($suspects as $row): ?>
                <tr>
                    <td><a href="index.php?view=suspects&suspect_id=<?= h((string)$row['id']) ?>"><?= h((string)$row['id']) ?></a></td>
                    <td><a href="index.php?view=suspects&suspect_id=<?= h((string)$row['id']) ?>"><?= h((string)$row['first_name'] . ' ' . (string)$row['last_name']) ?></a></td>
                    <td><?= h((string)($row['national_id'] ?? '')) ?></td>
                    <td><?= h((string)$row['suspect_status']) ?></td>
                    <td><?= h((string)$row['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
