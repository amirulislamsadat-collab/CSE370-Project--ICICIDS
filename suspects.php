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
?>

<div class="grid-2">
    <?php if ($canCreateSuspect): ?>
        <section class="card">
            <h2>Create Suspect (Linked to Crime)</h2>
            <form method="post">
                <input type="hidden" name="action" value="create_suspect">
                <input type="number" name="crime_report_id" required placeholder="Crime report ID">
                <select name="relation_type">
                    <option>PRIMARY</option>
                    <option>SECONDARY</option>
                    <option>WITNESS_LINKED</option>
                    <option>ASSOCIATE</option>
                </select>
                <input name="relation_notes" placeholder="Relation notes (optional)">
                <input name="first_name" required placeholder="First name">
                <input name="last_name" required placeholder="Last name">
                <input type="date" name="date_of_birth" placeholder="Date of birth">
                <select name="gender">
                    <option>UNKNOWN</option>
                    <option>MALE</option>
                    <option>FEMALE</option>
                    <option>OTHER</option>
                </select>
                <input name="national_id" placeholder="NID-ICI-0001" pattern="NID-ICI-[0-9]{4}" title="Use NID-ICI-0001 format" data-upper="true">
                <input type="tel" name="phone" placeholder="+8801XXXXXXXXX" pattern="\+880[0-9]+" title="Use +880 followed by digits">
                <input name="address_line" placeholder="Address">
                <select name="suspect_status">
                    <option>PERSON_OF_INTEREST</option>
                    <option>WANTED</option>
                    <option>ARRESTED</option>
                    <option>CLEARED</option>
                </select>
                <textarea name="reason_for_suspicion" rows="3" required placeholder="Reason for suspicion"></textarea>
                <button type="submit">Create</button>
            </form>
        </section>
    <?php endif; ?>

    <?php if ($canLinkSuspect): ?>
        <section class="card">
            <h2>Link Existing Suspect to Crime</h2>
            <form method="post">
                <input type="hidden" name="action" value="link_suspect">
                <input type="number" name="suspect_id" required placeholder="Suspect ID">
                <input type="number" name="crime_report_id" required placeholder="Crime report ID">
                <select name="relation_type">
                    <option>PRIMARY</option>
                    <option>SECONDARY</option>
                    <option>WITNESS_LINKED</option>
                    <option>ASSOCIATE</option>
                </select>
                <input name="relation_notes" placeholder="Relation notes (optional)">
                <button type="submit">Link</button>
            </form>
        </section>
    <?php endif; ?>

    <?php if ($canDeleteSuspect): ?>
        <section class="card">
            <h2>Delete Suspect</h2>
            <form method="post">
                <input type="hidden" name="action" value="delete_suspect">
                <input type="number" name="suspect_id" required placeholder="Suspect ID">
                <button type="submit">Delete</button>
            </form>
        </section>
    <?php endif; ?>

    <?php if ($canUpdateSuspect): ?>
        <section class="card">
            <h2>Update Suspect Status</h2>
            <form method="post">
                <input type="hidden" name="action" value="update_suspect_status">
                <input type="number" name="suspect_id" required placeholder="Suspect ID to update">
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
        <section class="card">
            <h2>Unlink Suspect From Crime</h2>
            <form method="post">
                <input type="hidden" name="action" value="unlink_suspect">
                <input type="number" name="suspect_id" required placeholder="Suspect ID to unlink">
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
                    <thead><tr><th>ID</th><th>Case</th><th>Type</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                    <?php foreach ($suspectCrimes as $row): ?>
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
                            <td><?= h((string)$row['title']) ?></td>
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
    <h2>Suspects</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Name</th><th>National ID</th><th>Status</th><th>Created</th></tr></thead>
            <tbody>
            <?php foreach ($suspects as $row): ?>
                <tr>
                    <td><a href="index.php?view=suspects&suspect_id=<?= h((string)$row['id']) ?>"><?= h((string)$row['id']) ?></a></td>
                    <td><?= h((string)$row['first_name'] . ' ' . (string)$row['last_name']) ?></td>
                    <td><?= h((string)($row['national_id'] ?? '')) ?></td>
                    <td><?= h((string)$row['suspect_status']) ?></td>
                    <td><?= h((string)$row['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
