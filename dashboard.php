<?php if ($bannerExists): ?>
    <section class="card">
        <img src="<?= h((string)$bannerPath) ?>" alt="ICICIDS Banner" class="hero-banner">
    </section>
<?php endif; ?>

<section class="stats">
    <div class="stat"><span>Officers</span><strong><?= h((string)$stats['officers']) ?></strong></div>
    <div class="stat"><span>Reports</span><strong><?= h((string)$stats['crime_reports']) ?></strong></div>
    <div class="stat"><span>Suspects</span><strong><?= h((string)$stats['suspects']) ?></strong></div>
    <div class="stat"><span>Evidence</span><strong><?= h((string)$stats['evidence']) ?></strong></div>
    <div class="stat"><span>Criminals</span><strong><?= h((string)$stats['criminals']) ?></strong></div>
</section>

<section class="card">
    <h2>Recent Crime Reports</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>ID</th><th>Case</th><th>Type</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
            <?php foreach ($crimeReports as $row): ?>
                <tr>
                    <td><?= h((string)$row['id']) ?></td>
                    <td><?= h((string)$row['case_number']) ?></td>
                    <td><?= h((string)$row['crime_type']) ?></td>
                    <td><?= h((string)$row['investigation_status']) ?></td>
                    <td><?= h((string)$row['crime_datetime']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
