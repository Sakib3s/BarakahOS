<?php

declare(strict_types=1);

$weeklyTotals = $weeklySummary['totals'];
$weeklyPrayerRows = $weeklySummary['by_prayer'];
?>
<section class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <p class="dashboard-kicker mb-1">Prayer Tracking</p>
        <h1 class="h3 mb-1">Daily Prayer Check</h1>
        <p class="text-secondary mb-0"><?= e($todayLabel); ?> in <?= e($timezoneLabel); ?>. Mark each prayer quickly and review the current week summary below.</p>
    </div>
    <span class="badge rounded-pill text-bg-dark px-3 py-2"><?= e($todayDate); ?></span>
</section>

<section class="row g-4 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 dashboard-card">
            <div class="card-body p-4">
                <p class="dashboard-kicker mb-1">Logged Today</p>
                <h2 class="display-6 fw-bold mb-0"><?= e((string) $dailySummary['logged_count']); ?>/<?= e((string) $dailySummary['expected_count']); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 dashboard-card">
            <div class="card-body p-4">
                <p class="dashboard-kicker mb-1">On Time</p>
                <h2 class="display-6 fw-bold mb-0"><?= e((string) $dailySummary['on_time_count']); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 dashboard-card">
            <div class="card-body p-4">
                <p class="dashboard-kicker mb-1">Delayed</p>
                <h2 class="display-6 fw-bold mb-0"><?= e((string) $dailySummary['delayed_count']); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 dashboard-card-soft">
            <div class="card-body p-4">
                <p class="dashboard-kicker mb-1">Missed / Left</p>
                <h2 class="display-6 fw-bold mb-0"><?= e((string) ($dailySummary['missed_count'] + $dailySummary['remaining_count'])); ?></h2>
            </div>
        </div>
    </div>
</section>

<section class="row g-4 mb-4">
    <?php foreach ($dailyPrayers as $prayer): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <p class="dashboard-kicker mb-1"><?= e((string) $prayer['code']); ?></p>
                            <h2 class="h4 mb-0"><?= e((string) $prayer['name']); ?></h2>
                        </div>
                        <span class="badge rounded-pill <?= status_badge_class((string) ($prayer['status'] ?? 'pending')); ?>">
                            <?= e(prayer_status_label((string) ($prayer['status'] ?? 'pending'))); ?>
                        </span>
                    </div>

                    <?php if (!empty($prayer['prayed_at'])): ?>
                        <p class="text-secondary small mb-3">
                            Marked at <?= e(date('h:i A', strtotime((string) $prayer['prayed_at']))); ?>
                        </p>
                    <?php else: ?>
                        <p class="text-secondary small mb-3">No status recorded yet for today.</p>
                    <?php endif; ?>

                    <form action="<?= base_url('prayers/update'); ?>" method="post">
                        <?= \App\Helpers\Csrf::field(); ?>
                        <input type="hidden" name="prayer_definition_id" value="<?= e((string) $prayer['id']); ?>">
                        <input type="hidden" name="prayer_date" value="<?= e($todayDate); ?>">

                        <div class="mb-3">
                            <label for="note-<?= e((string) $prayer['id']); ?>" class="form-label">Note</label>
                            <textarea
                                id="note-<?= e((string) $prayer['id']); ?>"
                                name="note"
                                rows="3"
                                class="form-control<?= error('note') !== null ? ' is-invalid' : ''; ?>"
                                placeholder="Optional note"
                            ><?= e((string) old('prayer_definition_id') === (string) $prayer['id'] ? old('note') : ($prayer['note'] ?? '')); ?></textarea>
                            <?php if (error('note') !== null && old('prayer_definition_id') === (string) $prayer['id']): ?>
                                <div class="invalid-feedback"><?= e((string) error('note')); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" name="status" value="on_time" class="btn btn-success btn-sm">On Time</button>
                            <button type="submit" name="status" value="delayed" class="btn btn-warning btn-sm">Delayed</button>
                            <button type="submit" name="status" value="missed" class="btn btn-danger btn-sm">Missed</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</section>

<section class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <p class="dashboard-kicker mb-1">Weekly Report Summary</p>
                <h2 class="h5 mb-0"><?= e($weekLabel); ?></h2>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge rounded-pill text-bg-dark">Logged <?= e((string) $weeklyTotals['logged_count']); ?>/<?= e((string) $weeklyTotals['expected_count']); ?></span>
                <span class="badge rounded-pill text-bg-success">On Time <?= e((string) $weeklyTotals['on_time_count']); ?></span>
                <span class="badge rounded-pill text-bg-warning">Delayed <?= e((string) $weeklyTotals['delayed_count']); ?></span>
                <span class="badge rounded-pill text-bg-danger">Missed <?= e((string) $weeklyTotals['missed_count']); ?></span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Prayer</th>
                        <th scope="col">On Time</th>
                        <th scope="col">Delayed</th>
                        <th scope="col">Missed</th>
                        <th scope="col">Logged</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($weeklyPrayerRows as $row): ?>
                        <tr>
                            <td class="fw-semibold"><?= e((string) $row['name']); ?></td>
                            <td><span class="badge rounded-pill text-bg-success"><?= e((string) $row['on_time_count']); ?></span></td>
                            <td><span class="badge rounded-pill text-bg-warning"><?= e((string) $row['delayed_count']); ?></span></td>
                            <td><span class="badge rounded-pill text-bg-danger"><?= e((string) $row['missed_count']); ?></span></td>
                            <td><?= e((string) $row['logged_count']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
