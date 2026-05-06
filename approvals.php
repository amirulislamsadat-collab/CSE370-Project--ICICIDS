<?php
// Approvals page: Grade 1 review of officer signup applications.
$applications = $applications ?? [];
$applicationStatusFilter = $applicationStatusFilter ?? 'PENDING';
?>

<section class="card">
    <h2>Officer Applications</h2>
    <form method="get" class="filter-grid">
        <input type="hidden" name="view" value="approvals">
        <label>Status</label>
        <select name="application_status">
            <option value="PENDING" <?= strtoupper((string)$applicationStatusFilter) === 'PENDING' ? 'selected' : '' ?>>Pending</option>
            <option value="APPROVED" <?= strtoupper((string)$applicationStatusFilter) === 'APPROVED' ? 'selected' : '' ?>>Approved</option>
            <option value="REJECTED" <?= strtoupper((string)$applicationStatusFilter) === 'REJECTED' ? 'selected' : '' ?>>Rejected</option>
            <option value="" <?= $applicationStatusFilter === '' ? 'selected' : '' ?>>All</option>
        </select>
        <button type="submit">Apply Filters</button>
        <a class="btn-secondary" href="index.php?view=approvals">Reset</a>
    </form>
</section>

<section class="card">
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Badge</th>
                <th>Requested Rank</th>
                <th>Status</th>
                <th>Review</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$applications): ?>
                <tr>
                    <td colspan="7" class="help">No applications found for this filter.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($applications as $row): ?>
                    <tr>
                        <td><?= h((string)$row['id']) ?></td>
                        <td><?= h((string)$row['first_name'] . ' ' . (string)$row['last_name']) ?></td>
                        <td><?= h((string)$row['email']) ?></td>
                        <td><?= h((string)$row['badge_number']) ?></td>
                        <td><?= h((string)$row['requested_rank']) ?></td>
                        <td><?= h((string)$row['status']) ?></td>
                        <td>
                            <?php if (strtoupper((string)$row['status']) === 'PENDING'): ?>
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="action" value="approve_officer">
                                    <input type="hidden" name="application_id" value="<?= h((string)$row['id']) ?>">
                                    <label>Final Rank</label>
                                    <select name="final_rank">
                                        <option value="GRADE_1">Grade 1</option>
                                        <option value="GRADE_2">Grade 2</option>
                                        <option value="GRADE_3" selected>Grade 3</option>
                                    </select>
                                    <label>Review Note</label>
                                    <input type="text" name="review_note" placeholder="Optional note">
                                    <button type="submit">Approve</button>
                                </form>
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="action" value="reject_officer">
                                    <input type="hidden" name="application_id" value="<?= h((string)$row['id']) ?>">
                                    <label>Rejection Note</label>
                                    <input type="text" name="review_note" placeholder="Optional note">
                                    <button class="btn-secondary" type="submit">Reject</button>
                                </form>
                            <?php else: ?>
                                <span class="help">Reviewed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
