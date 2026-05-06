<?php
// Crimes page: case details, linked entities, and create/update/delete workflows.
$crimeDetail = $crimeDetail ?? null;
$crimeSuspects = $crimeSuspects ?? [];
$crimeEvidence = $crimeEvidence ?? [];
$crimeCriminals = $crimeCriminals ?? [];
$crimeCriminalsBlocked = $crimeCriminalsBlocked ?? false;
$crimeReports = $crimeReports ?? [];
$crimeFilters = $crimeFilters ?? ['query' => '', 'status' => '', 'type' => '', 'from' => '', 'to' => ''];
$canDeleteCrime = $canDeleteCrime ?? false;
$canLinkSuspect = $canLinkSuspect ?? false;
$canLinkEvidence = $canLinkEvidence ?? false;
$canLinkCriminal = $canLinkCriminal ?? false;
?>

<?php if ($crimeDetail): ?>
    <section class="card">
        <h2>Crime Details</h2>
        <div class="table-wrap">
            <table>
                <tbody>
                <tr>
                    <th>ID</th>
                    <td><?= h((string)$crimeDetail['id']) ?></td>
                    <th>Crime Name</th>
                    <td><?= h((string)$crimeDetail['case_number']) ?></td>
                </tr>
                <tr>
                    <th>Type</th>
                    <td><?= h((string)$crimeDetail['crime_type']) ?></td>
                    <th>Status</th>
                    <td><?= h((string)$crimeDetail['investigation_status']) ?></td>
                </tr>
                <tr>
                    <th>Location</th>
                    <td><?= h((string)$crimeDetail['location_text']) ?></td>
                    <th>Date</th>
                    <td><?= h((string)$crimeDetail['crime_datetime']) ?></td>
                </tr>
                <tr>
                    <th>Reported By</th>
                    <td><?= h((string)$crimeDetail['reported_by_first_name'] . ' ' . (string)$crimeDetail['reported_by_last_name']) ?></td>
                    <th>Assigned To</th>
                    <td><?= h((string)($crimeDetail['assigned_first_name'] ?? '') . ' ' . (string)($crimeDetail['assigned_last_name'] ?? '')) ?></td>
                </tr>
                </tbody>
            </table>
        </div>

        <h3>Related Suspects</h3>
        <?php if ($crimeSuspects): ?>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>ID</th><th>Name</th><th>Status</th><th>Relation</th></tr></thead>
                    <tbody>
                    <?php foreach ($crimeSuspects as $row): ?>
                        <tr>
                            <td><a href="index.php?view=suspects&suspect_id=<?= h((string)$row['id']) ?>"><?= h((string)$row['id']) ?></a></td>
                            <td><a href="index.php?view=suspects&suspect_id=<?= h((string)$row['id']) ?>"><?= h((string)$row['first_name'] . ' ' . (string)$row['last_name']) ?></a></td>
                            <td><?= h((string)$row['suspect_status']) ?></td>
                            <td><?= h((string)$row['relation_type']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="help">No suspects linked to this case yet.</p>
        <?php endif; ?>

        <h3>Related Evidence</h3>
        <?php if ($crimeEvidence): ?>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>ID</th><th>Code</th><th>Title</th><th>Type</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($crimeEvidence as $row): ?>
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
            <p class="help">No evidence linked to this case yet.</p>
        <?php endif; ?>

        <h3>Related Criminals</h3>
        <?php if ($crimeCriminals): ?>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>ID</th><th>Code</th><th>Name</th><th>Risk</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($crimeCriminals as $row): ?>
                        <tr>
                            <td><a href="index.php?view=criminals&criminal_id=<?= h((string)$row['id']) ?>"><?= h((string)$row['id']) ?></a></td>
                            <td><?= h((string)$row['criminal_code']) ?></td>
                            <td><a href="index.php?view=criminals&criminal_id=<?= h((string)$row['id']) ?>"><?= h((string)($row['first_name'] ?? '') . ' ' . (string)($row['last_name'] ?? '')) ?></a></td>
                            <td><?= h((string)$row['risk_level']) ?></td>
                            <td><?= h((string)$row['current_status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($crimeCriminalsBlocked): ?>
            <p class="help">Criminals can be linked only when a case is CLOSED or REFERRED.</p>
        <?php else: ?>
            <p class="help">No criminals linked to this case.</p>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?php if ($canCreateCrime || $canUpdateCrime || $canDeleteCrime || $canLinkSuspect || $canLinkEvidence || $canLinkCriminal): ?>
    <section class="card action-panel">
        <h2>Crime Actions</h2>
        <p class="help">Select an action to open its form.</p>
        <div class="action-buttons" data-action-group="crime-actions" data-default-action="crime-create">
            <?php if ($canCreateCrime): ?><button type="button" data-action-target="crime-create">Create Crime</button><?php endif; ?>
            <?php if ($canUpdateCrime): ?><button type="button" data-action-target="crime-update">Update Status</button><?php endif; ?>
            <?php if ($canDeleteCrime): ?><button type="button" data-action-target="crime-delete">Delete Crime</button><?php endif; ?>
            <?php if ($canLinkSuspect): ?><button type="button" data-action-target="crime-unlink-suspect">Unlink Suspect</button><?php endif; ?>
            <?php if ($canLinkEvidence): ?><button type="button" data-action-target="crime-unlink-evidence">Unlink Evidence</button><?php endif; ?>
            <?php if ($canLinkCriminal): ?><button type="button" data-action-target="crime-unlink-criminal">Unlink Criminal</button><?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<div class="action-forms" data-action-forms="crime-actions">
    <?php if ($canCreateCrime): ?>
        <section class="card action-form" data-action-form="crime-create">
            <h2>Create Crime Report</h2>
            <form method="post">
                <input type="hidden" name="action" value="create_crime">
                <label>Crime Name (auto)</label>
                <input type="text" value="CRIME-YYYY/MM" disabled>
                <label>Crime Type</label>
                <input name="crime_type" required placeholder="ROBBERY">
                <label>Location</label>
                <input name="location_text" required placeholder="Location">
                <label>Crime Date and Time</label>
                <input type="datetime-local" name="crime_datetime" required>
                <label>Investigation Status</label>
                <select name="investigation_status">
                    <option>OPEN</option>
                    <option>UNDER_INVESTIGATION</option>
                    <option>SUSPENDED</option>
                    <option>CLOSED</option>
                    <option>REFERRED</option>
                </select>
                <label>Description</label>
                <textarea name="description" rows="3" required placeholder="Description"></textarea>
                <button type="submit">Create</button>
            </form>
        </section>
    <?php endif; ?>

    <?php if ($canUpdateCrime): ?>
        <section class="card action-form" data-action-form="crime-update">
            <h2>Update Crime Status</h2>
            <form method="post">
                <input type="hidden" name="action" value="update_crime">
                <label>Crime Report ID</label>
                <input type="number" name="crime_report_id" required placeholder="Crime report ID">
                <label>New Status</label>
                <select name="new_status">
                    <option>OPEN</option>
                    <option>UNDER_INVESTIGATION</option>
                    <option>SUSPENDED</option>
                    <option>CLOSED</option>
                    <option>REFERRED</option>
                </select>
                <label>Status Note</label>
                <textarea name="status_note" rows="3" placeholder="Optional note"></textarea>
                <button type="submit">Update</button>
            </form>
        </section>
    <?php endif; ?>

    <?php if ($canDeleteCrime): ?>
        <section class="card action-form" data-action-form="crime-delete">
            <h2>Delete Crime Report</h2>
            <form method="post">
                <input type="hidden" name="action" value="delete_crime">
                <label>Crime Report ID</label>
                <input type="number" name="crime_report_id" required placeholder="Crime report ID">
                <button type="submit">Delete</button>
            </form>
        </section>
    <?php endif; ?>

    <?php if ($canLinkSuspect): ?>
        <section class="card action-form" data-action-form="crime-unlink-suspect">
            <h2>Unlink Suspect From This Crime</h2>
            <form method="post">
                <input type="hidden" name="action" value="unlink_suspect">
                <label>Suspect ID</label>
                <input type="number" name="suspect_id" required placeholder="Suspect ID to unlink">
                <label>Crime Report ID</label>
                <input type="number" name="crime_report_id" required placeholder="Crime report ID">
                <button type="submit">Unlink Suspect</button>
            </form>
        </section>
    <?php endif; ?>

    <?php if ($canLinkEvidence): ?>
        <section class="card action-form" data-action-form="crime-unlink-evidence">
            <h2>Unlink Evidence From This Crime</h2>
            <form method="post">
                <input type="hidden" name="action" value="unlink_evidence_case">
                <label>Evidence ID</label>
                <input type="number" name="evidence_id" required placeholder="Evidence ID to unlink">
                <label>Crime Report ID</label>
                <input type="number" name="crime_report_id" required placeholder="Crime report ID">
                <button type="submit">Unlink Evidence</button>
            </form>
        </section>
    <?php endif; ?>

    <?php if ($canLinkCriminal): ?>
        <section class="card action-form" data-action-form="crime-unlink-criminal">
            <h2>Unlink Criminal From This Crime</h2>
            <form method="post">
                <input type="hidden" name="action" value="unlink_criminal_case">
                <label>Criminal ID</label>
                <input type="number" name="criminal_id" required placeholder="Criminal ID to unlink">
                <label>Crime Report ID</label>
                <input type="number" name="crime_report_id" required placeholder="Crime report ID">
                <button type="submit">Unlink Criminal</button>
            </form>
        </section>
    <?php endif; ?>
</div>

<section class="card">
    <h2>Filter Crime Reports</h2>
    <form method="get" class="filter-grid">
        <input type="hidden" name="view" value="crimes">
        <label>Search (Name, Type, Location)</label>
        <input type="text" name="crime_q" value="<?= h((string)$crimeFilters['query']) ?>" placeholder="Search crime name, type, or location">
        <label>Status</label>
        <select name="crime_status">
            <option value="" <?= $crimeFilters['status'] === '' ? 'selected' : '' ?>>All statuses</option>
            <option value="OPEN" <?= $crimeFilters['status'] === 'OPEN' ? 'selected' : '' ?>>OPEN</option>
            <option value="UNDER_INVESTIGATION" <?= $crimeFilters['status'] === 'UNDER_INVESTIGATION' ? 'selected' : '' ?>>UNDER_INVESTIGATION</option>
            <option value="SUSPENDED" <?= $crimeFilters['status'] === 'SUSPENDED' ? 'selected' : '' ?>>SUSPENDED</option>
            <option value="CLOSED" <?= $crimeFilters['status'] === 'CLOSED' ? 'selected' : '' ?>>CLOSED</option>
            <option value="REFERRED" <?= $crimeFilters['status'] === 'REFERRED' ? 'selected' : '' ?>>REFERRED</option>
        </select>
        <label>Crime Type</label>
        <input type="text" name="crime_type" value="<?= h((string)$crimeFilters['type']) ?>" placeholder="E.g., ROBBERY">
        <label>Date From</label>
        <input type="date" name="crime_from" value="<?= h((string)$crimeFilters['from']) ?>">
        <label>Date To</label>
        <input type="date" name="crime_to" value="<?= h((string)$crimeFilters['to']) ?>">
        <button type="submit">Apply Filters</button>
        <a class="btn-secondary" href="index.php?view=crimes">Reset</a>
    </form>
</section>

<section class="card">
    <h2>Crime Reports</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Crime Name</th><th>Type</th><th>Status</th><th>Location</th><th>Date</th></tr></thead>
            <tbody>
            <?php foreach ($crimeReports as $row): ?>
                <tr>
                    <td><a href="index.php?view=crimes&crime_id=<?= h((string)$row['id']) ?>"><?= h((string)$row['id']) ?></a></td>
                    <td><a href="index.php?view=crimes&crime_id=<?= h((string)$row['id']) ?>"><?= h((string)$row['case_number']) ?></a></td>
                    <td><?= h((string)$row['crime_type']) ?></td>
                    <td><?= h((string)$row['investigation_status']) ?></td>
                    <td><?= h((string)$row['location_text']) ?></td>
                    <td><?= h((string)$row['crime_datetime']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
