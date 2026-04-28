<?php
$crimeDetail = $crimeDetail ?? null;
$crimeSuspects = $crimeSuspects ?? [];
$crimeEvidence = $crimeEvidence ?? [];
$crimeCriminals = $crimeCriminals ?? [];
$crimeCriminalsBlocked = $crimeCriminalsBlocked ?? false;
$crimeReports = $crimeReports ?? [];
$canDeleteCrime = $canDeleteCrime ?? false;
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
                    <th>Case</th>
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
                            <td><?= h((string)$row['first_name'] . ' ' . (string)$row['last_name']) ?></td>
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
                            <td><?= h((string)$row['title']) ?></td>
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
                            <td><?= h((string)($row['first_name'] ?? '') . ' ' . (string)($row['last_name'] ?? '')) ?></td>
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

<div class="grid-2">
    <?php if ($canCreateCrime): ?>
        <section class="card">
            <h2>Create Crime Report</h2>
            <form method="post">
                <input type="hidden" name="action" value="create_crime">
                <input name="case_number" required placeholder="CASE-2026-0101">
                <input name="crime_type" required placeholder="ROBBERY">
                <input name="location_text" required placeholder="Location">
                <input type="datetime-local" name="crime_datetime" required>
                <select name="investigation_status">
                    <option>OPEN</option>
                    <option>UNDER_INVESTIGATION</option>
                    <option>SUSPENDED</option>
                    <option>CLOSED</option>
                    <option>REFERRED</option>
                </select>
                <textarea name="description" rows="3" required placeholder="Description"></textarea>
                <button type="submit">Create</button>
            </form>
        </section>
    <?php endif; ?>

    <?php if ($canUpdateCrime): ?>
        <section class="card">
            <h2>Update Crime Status</h2>
            <form method="post">
                <input type="hidden" name="action" value="update_crime">
                <input type="number" name="crime_report_id" required placeholder="Crime report ID">
                <select name="new_status">
                    <option>OPEN</option>
                    <option>UNDER_INVESTIGATION</option>
                    <option>SUSPENDED</option>
                    <option>CLOSED</option>
                    <option>REFERRED</option>
                </select>
                <textarea name="status_note" rows="3" placeholder="Optional note"></textarea>
                <button type="submit">Update</button>
            </form>
        </section>
    <?php endif; ?>

    <?php if ($canDeleteCrime): ?>
        <section class="card">
            <h2>Delete Crime Report</h2>
            <form method="post">
                <input type="hidden" name="action" value="delete_crime">
                <input type="number" name="crime_report_id" required placeholder="Crime report ID">
                <button type="submit">Delete</button>
            </form>
        </section>
    <?php endif; ?>
</div>

<section class="card">
    <h2>Crime Reports</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Case</th><th>Type</th><th>Status</th><th>Location</th><th>Date</th></tr></thead>
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
