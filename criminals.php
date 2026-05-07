<?php
// Criminals page: confirmation flow for closed/referred cases and history linkage.
$canCreateCriminal = $canCreateCriminal ?? false;
$canLinkCriminal = $canLinkCriminal ?? false;
$canDeleteCriminal = $canDeleteCriminal ?? false;
$canUnlinkCriminal = $canUnlinkCriminal ?? false;
$criminals = $criminals ?? [];
$criminalDetail = $criminalDetail ?? null;
$criminalCrimes = $criminalCrimes ?? [];
$criminalFilters = $criminalFilters ?? ['query' => '', 'status' => '', 'risk' => ''];
?>
<?php if ($canCreateCriminal || $canLinkCriminal || $canDeleteCriminal): ?>
    <section class="card action-panel">
        <h2>Criminal Actions</h2>
        <p class="help">Select an action to open its form.</p>
        <div class="action-buttons" data-action-group="criminal-actions" data-default-action="criminal-create">
            <?php if ($canCreateCriminal): ?><button type="button" data-action-target="criminal-create">Confirm Criminal</button><?php endif; ?>
            <?php if ($canLinkCriminal): ?><button type="button" data-action-target="criminal-link">Link Criminal</button><?php endif; ?>
            <?php if ($canDeleteCriminal): ?><button type="button" data-action-target="criminal-delete">Delete Criminal</button><?php endif; ?>
            <?php if ($canUnlinkCriminal): ?><button type="button" data-action-target="criminal-unlink-suspect">Unlink Suspect</button><?php endif; ?>
            <?php if ($canUnlinkCriminal): ?><button type="button" data-action-target="criminal-unlink-case">Unlink Case</button><?php endif; ?>
        </div>
    </section>
    <div class="action-forms" data-action-forms="criminal-actions">
    <?php if ($canCreateCriminal): ?>
        <section class="card action-form" data-action-form="criminal-create" hidden>
            <h2>Confirm Criminal (Closed/Referred Case)</h2>
            <form method="post">
                <input type="hidden" name="action" value="create_criminal">
                <label>Suspect ID</label>
                <input type="number" name="suspect_id" required placeholder="Suspect ID">
                <label>Crime Report ID (Closed)</label>
                <input type="number" name="crime_report_id" required placeholder="Closed crime report ID">
                <label>Criminal Code (Numbers Only)</label>
                <input name="criminal_code" required placeholder="09" pattern="[0-9]+" inputmode="numeric" title="Numbers only, e.g., 09">
                <label>Profile Summary</label>
                <input name="profile_summary" required placeholder="Profile summary">
                <label>Risk Level</label>
                <select name="risk_level">
                    <option>LOW</option>
                    <option>MEDIUM</option>
                    <option>HIGH</option>
                    <option>CRITICAL</option>
                </select>
                <label>Current Status</label>
                <select name="current_status">
                    <option>INCARCERATED</option>
                    <option>AT_LARGE</option>
                    <option>PAROLE</option>
                    <option>DECEASED</option>
                </select>
                <label>Offense Title</label>
                <input name="offense_title" required placeholder="Offense title">
                <label>Conviction Date</label>
                <input type="date" name="conviction_date" placeholder="Conviction date">
                <label>Jurisdiction</label>
                <input name="jurisdiction" placeholder="Jurisdiction">
                <label>Sentence Details</label>
                <input name="sentence_details" placeholder="Sentence details">
                <label>Notes</label>
                <textarea name="notes" rows="3" placeholder="Notes (optional)"></textarea>
                <button type="submit">Confirm</button>
            </form>
        </section>
    <?php endif; ?>

    <?php if ($canLinkCriminal): ?>
        <section class="card action-form" data-action-form="criminal-link" hidden>
            <h2>Link Existing Criminal to Closed Case</h2>
            <form method="post">
                <input type="hidden" name="action" value="link_criminal">
                <label>Criminal ID</label>
                <input type="number" name="criminal_id" required placeholder="Criminal ID">
                <label>Crime Report ID (Closed)</label>
                <input type="number" name="crime_report_id" required placeholder="Closed crime report ID">
                <label>Offense Title</label>
                <input name="offense_title" required placeholder="Offense title">
                <label>Conviction Date</label>
                <input type="date" name="conviction_date" placeholder="Conviction date">
                <label>Jurisdiction</label>
                <input name="jurisdiction" placeholder="Jurisdiction">
                <label>Sentence Details</label>
                <input name="sentence_details" placeholder="Sentence details">
                <label>Notes</label>
                <textarea name="notes" rows="3" placeholder="Notes (optional)"></textarea>
                <button type="submit">Link</button>
            </form>
        </section>
    <?php endif; ?>

    <?php if ($canDeleteCriminal): ?>
        <section class="card action-form" data-action-form="criminal-delete" hidden>
            <h2>Delete Criminal</h2>
            <form method="post">
                <input type="hidden" name="action" value="delete_criminal">
                <label>Criminal ID</label>
                <input type="number" name="criminal_id" required placeholder="Criminal ID">
                <button type="submit">Delete</button>
            </form>
        </section>
    <?php endif; ?>

    <?php if ($canUnlinkCriminal): ?>
        <section class="card action-form" data-action-form="criminal-unlink-suspect" hidden>
            <h2>Unlink Criminal From Suspect</h2>
            <form method="post">
                <input type="hidden" name="action" value="unlink_criminal_suspect">
                <label>Criminal ID</label>
                <input type="number" name="criminal_id" required placeholder="Criminal ID to unlink">
                <button type="submit">Unlink Suspect</button>
            </form>
        </section>

        <section class="card action-form" data-action-form="criminal-unlink-case" hidden>
            <h2>Unlink Criminal From Case</h2>
            <form method="post">
                <input type="hidden" name="action" value="unlink_criminal_case">
                <label>Criminal ID</label>
                <input type="number" name="criminal_id" required placeholder="Criminal ID to unlink">
                <label>Crime Report ID</label>
                <input type="number" name="crime_report_id" required placeholder="Crime report ID to unlink from">
                <button type="submit">Unlink Case</button>
            </form>
        </section>
    <?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($criminalDetail): ?>
    <section class="card">
        <h2>Criminal Details</h2>
        <div class="table-wrap">
            <table>
                <tbody>
                <tr>
                    <th>ID</th>
                    <td><?= h((string)$criminalDetail['id']) ?></td>
                    <th>Code</th>
                    <td><?= h((string)$criminalDetail['criminal_code']) ?></td>
                </tr>
                <tr>
                    <th>Risk</th>
                    <td><?= h((string)$criminalDetail['risk_level']) ?></td>
                    <th>Status</th>
                    <td><?= h((string)$criminalDetail['current_status']) ?></td>
                </tr>
                <tr>
                    <th>Suspect</th>
                    <td>
                        <?php if (!empty($criminalDetail['suspect_id'])): ?>
                            <a href="index.php?view=suspects&suspect_id=<?= h((string)$criminalDetail['suspect_id']) ?>">
                                <?= h((string)$criminalDetail['first_name'] . ' ' . (string)$criminalDetail['last_name']) ?>
                            </a>
                        <?php else: ?>
                            <?= h('Unlinked suspect') ?>
                        <?php endif; ?>
                    </td>
                    <th>National ID</th>
                    <td><?= h((string)($criminalDetail['national_id'] ?? '')) ?></td>
                </tr>
                <tr>
                    <th>Confirmed At</th>
                    <td><?= h((string)$criminalDetail['confirmed_at']) ?></td>
                    <th>Profile</th>
                    <td><?= h((string)$criminalDetail['profile_summary']) ?></td>
                </tr>
                </tbody>
            </table>
        </div>

        <h3>Related Crimes</h3>
        <?php if ($criminalCrimes): ?>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Crime</th><th>Crime Name</th><th>Status</th><th>Offense</th><th>Conviction Date</th><th>Jurisdiction</th></tr></thead>
                    <tbody>
                    <?php foreach ($criminalCrimes as $row): ?>
                        <tr>
                            <td>
                                <?php if (!empty($row['crime_id'])): ?>
                                    <a href="index.php?view=crimes&crime_id=<?= h((string)$row['crime_id']) ?>"><?= h((string)$row['crime_id']) ?></a>
                                <?php else: ?>
                                    <?= h('') ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($row['crime_id'])): ?>
                                    <a href="index.php?view=crimes&crime_id=<?= h((string)$row['crime_id']) ?>"><?= h((string)($row['case_number'] ?? '')) ?></a>
                                <?php else: ?>
                                    <?= h((string)($row['case_number'] ?? '')) ?>
                                <?php endif; ?>
                            </td>
                            <td><?= h((string)($row['investigation_status'] ?? '')) ?></td>
                            <td><?= h((string)$row['offense_title']) ?></td>
                            <td><?= h((string)($row['conviction_date'] ?? '')) ?></td>
                            <td><?= h((string)($row['jurisdiction'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="help">No crimes recorded for this criminal.</p>
        <?php endif; ?>
    </section>
<?php endif; ?>

<section class="card">
    <h2>Filter Criminals</h2>
    <form method="get" class="filter-grid">
        <input type="hidden" name="view" value="criminals">
        <div class="filter-field">
            <label>ID</label>
            <input type="text" name="criminal_q" value="<?= h((string)$criminalFilters['query']) ?>" placeholder="10">
        </div>
        <div class="filter-field">
            <label>Status</label>
            <select name="criminal_status">
            <option value="" <?= $criminalFilters['status'] === '' ? 'selected' : '' ?>>All statuses</option>
            <option value="INCARCERATED" <?= $criminalFilters['status'] === 'INCARCERATED' ? 'selected' : '' ?>>INCARCERATED</option>
            <option value="AT_LARGE" <?= $criminalFilters['status'] === 'AT_LARGE' ? 'selected' : '' ?>>AT_LARGE</option>
            <option value="PAROLE" <?= $criminalFilters['status'] === 'PAROLE' ? 'selected' : '' ?>>PAROLE</option>
            <option value="DECEASED" <?= $criminalFilters['status'] === 'DECEASED' ? 'selected' : '' ?>>DECEASED</option>
            </select>
        </div>
        <div class="filter-field">
            <label>Risk Level</label>
            <select name="criminal_risk">
            <option value="" <?= $criminalFilters['risk'] === '' ? 'selected' : '' ?>>All risk levels</option>
            <option value="LOW" <?= $criminalFilters['risk'] === 'LOW' ? 'selected' : '' ?>>LOW</option>
            <option value="MEDIUM" <?= $criminalFilters['risk'] === 'MEDIUM' ? 'selected' : '' ?>>MEDIUM</option>
            <option value="HIGH" <?= $criminalFilters['risk'] === 'HIGH' ? 'selected' : '' ?>>HIGH</option>
            <option value="CRITICAL" <?= $criminalFilters['risk'] === 'CRITICAL' ? 'selected' : '' ?>>CRITICAL</option>
            </select>
        </div>
        <div class="filter-actions">
            <button type="submit">Apply Filters</button>
            <a class="btn-secondary" href="index.php?view=criminals">Reset</a>
        </div>
    </form>
</section>

<section class="card">
    <h2>Criminals</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Code</th><th>Name</th><th>Risk</th><th>Status</th><th>Created</th></tr></thead>
            <tbody>
            <?php foreach ($criminals as $row): ?>
                <tr>
                    <td><a href="index.php?view=criminals&criminal_id=<?= h((string)$row['id']) ?>"><?= h((string)$row['id']) ?></a></td>
                    <td><?= h((string)$row['criminal_code']) ?></td>
                    <td><a href="index.php?view=criminals&criminal_id=<?= h((string)$row['id']) ?>"><?= h(trim((string)($row['first_name'] ?? '') . ' ' . (string)($row['last_name'] ?? '')) ?: 'Unlinked suspect') ?></a></td>
                    <td><?= h((string)$row['risk_level']) ?></td>
                    <td><?= h((string)$row['current_status']) ?></td>
                    <td><?= h((string)$row['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
