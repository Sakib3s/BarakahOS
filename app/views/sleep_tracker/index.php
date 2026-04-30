<?php

declare(strict_types=1);

$sleepData = $sleepLog ?? [
    'sleep_started_at' => null,
    'woke_up_at' => null,
    'duration_minutes' => null,
    'note' => null,
];
$sleepStartedValue = $sleepData['sleep_started_at'] !== null
    ? date('Y-m-d\TH:i', strtotime((string) $sleepData['sleep_started_at']))
    : $defaultSleepStartedAt;
$wokeUpValue = $sleepData['woke_up_at'] !== null
    ? date('Y-m-d\TH:i', strtotime((string) $sleepData['woke_up_at']))
    : $defaultWokeUpAt;
?>
<section class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <p class="dashboard-kicker mb-1">Sleep Tracker</p>
        <h1 class="h3 mb-1"><?= $sleepLog === null ? 'Add sleep session' : 'Edit sleep session'; ?></h1>
        <p class="text-secondary mb-0"><?= e($todayLabel); ?> in <?= e($timezoneLabel); ?>. Sleep is grouped by wake-up date.</p>
    </div>
    <span class="badge rounded-pill text-bg-dark px-3 py-2"><?= e($todayDate); ?></span>
</section>

<section class="row g-4 mb-4">
    <div class="col-xl-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="mb-3">
                    <p class="dashboard-kicker mb-1">Sleep Session</p>
                    <h2 class="h5 mb-0">Sleep to wake-up time</h2>
                </div>

                <form action="<?= base_url('sleep-tracker/save'); ?>" method="post" class="row g-3">
                    <?= \App\Helpers\Csrf::field(); ?>

                    <div class="col-12">
                        <label for="sleep_started_at" class="form-label">Sleep Time</label>
                        <input
                            type="datetime-local"
                            id="sleep_started_at"
                            name="sleep_started_at"
                            class="<?= e(field_class('sleep_started_at', 'form-control')); ?>"
                            value="<?= e((string) old('sleep_started_at', $sleepStartedValue)); ?>"
                            required
                        >
                        <?php component('form_error', ['field' => 'sleep_started_at']); ?>
                    </div>

                    <div class="col-12">
                        <label for="woke_up_at" class="form-label">Wake-up Time</label>
                        <input
                            type="datetime-local"
                            id="woke_up_at"
                            name="woke_up_at"
                            class="<?= e(field_class('woke_up_at', 'form-control')); ?>"
                            value="<?= e((string) old('woke_up_at', $wokeUpValue)); ?>"
                            required
                        >
                        <?php component('form_error', ['field' => 'woke_up_at']); ?>
                    </div>

                    <div class="col-12">
                        <label for="note" class="form-label">Sleep Note</label>
                        <textarea
                            id="note"
                            name="note"
                            rows="4"
                            class="<?= e(field_class('note', 'form-control')); ?>"
                            placeholder="Optional note about sleep quality, interruptions, or routine."
                        ><?= e((string) old('note', $sleepData['note'] ?? '')); ?></textarea>
                        <?php component('form_error', ['field' => 'note']); ?>
                    </div>

                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-dark"><?= $sleepLog === null ? 'Save Sleep Session' : 'Update Sleep Session'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100 dashboard-card">
                    <div class="card-body p-4">
                        <p class="dashboard-kicker mb-1">Today Sleep</p>
                        <h2 class="display-6 fw-bold mb-1">
                            <?= $sleepLog !== null ? e(format_duration((int) $sleepLog['duration_minutes'])) : '0m'; ?>
                        </h2>
                        <p class="text-secondary mb-0">Based on today’s wake-up date.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100 dashboard-card-soft">
                    <div class="card-body p-4">
                        <p class="dashboard-kicker mb-1">Weekly Average</p>
                        <h2 class="display-6 fw-bold mb-1"><?= e(format_duration((int) $weekSummary['average_minutes'])); ?></h2>
                        <p class="text-secondary mb-0"><?= e((string) $weekSummary['days_logged']); ?> days logged this week.</p>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <p class="dashboard-kicker mb-1">This Week</p>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge rounded-pill text-bg-dark">Total <?= e(format_duration((int) $weekSummary['total_minutes'])); ?></span>
                            <span class="badge rounded-pill text-bg-success">Longest <?= e(format_duration((int) $weekSummary['longest_minutes'])); ?></span>
                            <span class="badge rounded-pill text-bg-warning">Shortest <?= e(format_duration((int) $weekSummary['shortest_minutes'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div>
                <p class="dashboard-kicker mb-1">Sleep History</p>
                <h2 class="h5 mb-0">Recent sleep sessions</h2>
            </div>
        </div>

        <?php if ($recentLogs === []): ?>
            <div class="border rounded-4 p-5 bg-light-subtle text-center">
                <p class="mb-1 fw-semibold">No sleep sessions logged yet</p>
                <p class="text-secondary mb-0">Use the form above to record your sleep and wake-up time.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Wake Date</th>
                            <th>Sleep Time</th>
                            <th>Wake-up Time</th>
                            <th>Duration</th>
                            <th>Note</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentLogs as $log): ?>
                            <tr>
                                <td class="fw-semibold"><?= e((string) $log['sleep_date']); ?></td>
                                <td><?= e(date('d M, h:i A', strtotime((string) $log['sleep_started_at']))); ?></td>
                                <td><?= e(date('d M, h:i A', strtotime((string) $log['woke_up_at']))); ?></td>
                                <td><?= e(format_duration((int) $log['duration_minutes'])); ?></td>
                                <td class="text-secondary"><?= $log['note'] !== null ? nl2br(e((string) $log['note'])) : '<span class="text-muted">n/a</span>'; ?></td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="<?= base_url('sleep-tracker?date=' . (string) $log['sleep_date']); ?>" class="btn btn-sm btn-outline-dark">Edit</a>
                                        <form action="<?= base_url('sleep-tracker/delete'); ?>" method="post" class="d-inline">
                                            <?= \App\Helpers\Csrf::field(); ?>
                                            <input type="hidden" name="id" value="<?= e((string) $log['id']); ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
