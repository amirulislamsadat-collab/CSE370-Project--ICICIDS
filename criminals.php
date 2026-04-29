<?php
// Criminals page: confirmation flow for closed/referred cases and history linkage.
$canCreateCriminal = $canCreateCriminal ?? false;
$canLinkCriminal = $canLinkCriminal ?? false;
$canDeleteCriminal = $canDeleteCriminal ?? false;
$criminals = $criminals ?? [];
$criminalDetail = $criminalDetail ?? null;
$criminalCrimes = $criminalCrimes ?? [];
?>

<div class="grid-2">
    <?php if ($canCreateCriminal): ?>
        <section class="card">
            <h2>Confirm Criminal (Closed/Referred Case)</h2>
            <form method="post">
                <input type="hidden" name="action" value="create_criminal">
                <input type="number" name="suspect_id" required placeholder="Suspect ID">
                <input type="number" name="crime_report_id" required placeholder="Closed crime report ID">
                <input name="criminal_code" required placeholder="09" pattern="[0-9]+" inputmode="numeric" title="Numbers only, e.g., 09">
                <input name="profile_summary" required placeholder="Profile summary">
                <select name="risk_level">
                    <option>LOW</option>
                    <option>MEDIUM</option>
                    <option>HIGH</option>
                    <option>CRITICAL</option>
                </select>
                <select name="current_status">
                    <option>INCARCERATED</option>
                    <option>AT_LARGE</option>
                    <option>PAROLE</option>
                    <option>DECEASED</option>
                </select>
                <input name="offense_title" required placeholder="Offense title">
                <input type="date" name="conviction_date" placeholder="Conviction date">
                <input name="jurisdiction" placeholder="Jurisdiction">
                <input name="sentence_details" placeholder="Sentence details">
                <textarea name="notes" rows="3" placeholder="Notes (optional)"></textarea>
                <button type="submit">Confirm</button>
            </form>
        </section>
    <?php endif; ?>

    <?php if ($canLinkCriminal): ?>
        <section class="card">
            <h2>Link Existing Criminal to Closed Case</h2>
            <form method="post">
                <input type="hidden" name="action" value="link_criminal">
                <input type="number" name="criminal_id" required placeholder="Criminal ID">
                <input type="number" name="crime_report_id" required placeholder="Closed crime report ID">
                <input name="offense_title" required placeholder="Offense title">
                <input type="date" name="conviction_date" placeholder="Conviction date">
                <input name="jurisdiction" placeholder="Jurisdiction">
                <input name="sentence_details" placeholder="Sentence details">
                <textarea name="notes" rows="3" placeholder="Notes (optional)"></textarea>
                <button type="submit">Link</button>
            </form>
        </section>
    <?php endif; ?>

    <?php if ($canDeleteCriminal): ?>
        <section class="card">
            <h2>Delete Criminal</h2>
            <form method="post">
                <input type="hidden" name="action" value="delete_criminal">
                <input type="number" name="criminal_id" required placeholder="Criminal ID">
                <button type="submit">Delete</button>
            </form>
        </section>
    <?php endif; ?>

    <?php if ($canLinkCriminal): ?>
        <section class="card">
            <h2>Unlink Criminal From Suspect</h2>
            <form method="post">
                <input type="hidden" name="action" value="unlink_criminal_suspect">
                <input type="number" name="criminal_id" required placeholder="Criminal ID to unlink">
                <button type="submit">Unlink Suspect</button>
            </form>
        </section>

        <section class="card">
            <h2>Unlink Criminal From Case</h2>
            <form method="post">
                <input type="hidden" name="action" value="unlink_criminal_case">
                <input type="number" name="criminal_id" required placeholder="Criminal ID to unlink">
                <input type="number" name="crime_report_id" required placeholder="Crime report ID to unlink from">
                <button type="submit">Unlink Case</button>
            </form>
        </section>
    <?php endif; ?>
</div>

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
                    <thead><tr><th>Crime</th><th>Case</th><th>Status</th><th>Offense</th><th>Conviction Date</th><th>Jurisdiction</th></tr></thead>
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
                            <td><?= h((string)($row['case_number'] ?? '')) ?></td>
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
    <h2>Criminals</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Code</th><th>Name</th><th>Risk</th><th>Status</th><th>Created</th></tr></thead>
            <tbody>
            <?php foreach ($criminals as $row): ?>
                <tr>
                    <td><a href="index.php?view=criminals&criminal_id=<?= h((string)$row['id']) ?>"><?= h((string)$row['id']) ?></a></td>
                    <td><?= h((string)$row['criminal_code']) ?></td>
                    <td><?= h(trim((string)($row['first_name'] ?? '') . ' ' . (string)($row['last_name'] ?? '')) ?: 'Unlinked suspect') ?></td>
                    <td><?= h((string)$row['risk_level']) ?></td>
                    <td><?= h((string)$row['current_status']) ?></td>
                    <td><?= h((string)$row['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
