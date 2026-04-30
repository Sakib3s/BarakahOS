<?php

declare(strict_types=1);

$totals = $summary['totals'];
$running = $runningSession !== null;
?>
<section class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <p class="dashboard-kicker mb-1">Focus Session</p>
        <h1 class="h3 mb-1">Focus Mode Tracker</h1>
        <p class="text-secondary mb-0">Start one session at a time, track duration live, and review today’s focus output by category.</p>
    </div>
    <span class="badge text-bg-dark rounded-pill px-3 py-2"><?= e($todayLabel); ?></span>
</section>

<section class="row g-4 mb-4">
    <div class="col-6 col-xl-3">
        <?php component('dashboard_stat_card', [
            'kicker' => 'Total Sessions',
            'value' => (string) $totals['total_sessions'],
            'valueClass' => 'display-6 fw-bold mb-0',
        ]); ?>
    </div>
    <div class="col-6 col-xl-3">
        <?php component('dashboard_stat_card', [
            'kicker' => 'Total Focus Time',
            'value' => (string) $totals['total_focus_minutes'] . 'm',
            'valueClass' => 'display-6 fw-bold mb-0',
        ]); ?>
    </div>
    <div class="col-6 col-xl-3">
        <?php component('dashboard_stat_card', [
            'kicker' => 'Average Length',
            'value' => (string) $totals['average_session_length'] . 'm',
            'valueClass' => 'display-6 fw-bold mb-0',
            'variant' => 'soft',
        ]); ?>
    </div>
    <div class="col-6 col-xl-3">
        <?php component('dashboard_stat_card', [
            'kicker' => 'Longest Session',
            'value' => (string) $totals['longest_session'] . 'm',
            'valueClass' => 'display-6 fw-bold mb-0',
            'variant' => 'soft',
        ]); ?>
    </div>
</section>

<section class="row g-4">
    <div class="col-xl-7">
        <?php if ($running): ?>
            <div class="card border-0 shadow-sm mb-4 dashboard-card-soft">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div>
                            <p class="dashboard-kicker mb-1">Running Session</p>
                            <h2 class="h4 mb-2"><?= e(ucwords(str_replace('_', ' ', (string) $runningSession['category_name']))); ?></h2>
                            <div class="d-flex flex-wrap gap-3 text-secondary small mb-2">
                                <span>Started: <?= e((string) $runningSession['start_time']); ?></span>
                            </div>
                            <h3
                                class="display-6 fw-bold mb-2 focus-session-timer"
                                data-start-time="<?= e((string) $runningSession['start_time']); ?>"
                            >
                                00:00:00
                            </h3>
                            <?php if (!empty($runningSession['note'])): ?>
                                <p class="text-secondary mb-0"><?= e((string) $runningSession['note']); ?></p>
                            <?php endif; ?>
                        </div>

                        <form action="<?= base_url('focus-sessions/end'); ?>" method="post">
                            <?= \App\Helpers\Csrf::field(); ?>
                            <input type="hidden" name="session_id" value="<?= e((string) $runningSession['id']); ?>">
                            <button type="submit" class="btn btn-danger">End Session</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-lg-5">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                    <div>
                        <p class="dashboard-kicker mb-1">Start Session</p>
                        <h2 class="h5 mb-0">New focus block</h2>
                    </div>
                    <?php if ($running): ?>
                        <span class="badge rounded-pill text-bg-warning">Session already running</span>
                    <?php endif; ?>
                </div>

                <?php if ($latestCompletedChecklist === null): ?>
                    <?php component('alert_messages', [
                        'alerts' => [[
                            'type' => 'warning',
                            'message' => 'No completed pre-work checklist found. You can still start a session, but it will not be linked to checklist readiness.',
                        ]],
                        'dismissible' => false,
                    ]); ?>
                <?php endif; ?>

                <form action="<?= base_url('focus-sessions/start'); ?>" method="post" class="row g-4" id="focus-session-form">
                    <?= \App\Helpers\Csrf::field(); ?>

                    <div class="col-12">
                        <label for="focus_category_id" class="form-label">Focus Category</label>
                        <select id="focus_category_id" name="focus_category_id" class="<?= e(field_class('focus_category_id', 'form-select')); ?>">
                            <option value="">Select a category</option>
                            <?php foreach ($categoryOptions as $category): ?>
                                <option value="<?= e((string) $category['id']); ?>" <?= (string) old('focus_category_id') === (string) $category['id'] ? 'selected' : ''; ?>>
                                    <?= e(ucwords(str_replace('_', ' ', (string) $category['name']))); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php component('form_error', ['field' => 'focus_category_id']); ?>
                    </div>

                    <div class="col-12">
                        <div class="form-check border rounded-3 p-3 bg-light-subtle">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="link_latest_checklist"
                                name="link_latest_checklist"
                                value="1"
                                <?= ($latestCompletedChecklist !== null && old('link_latest_checklist', '1') === '1') ? 'checked' : ''; ?>
                                <?= $latestCompletedChecklist === null ? 'disabled' : ''; ?>
                            >
                            <label class="form-check-label" for="link_latest_checklist">
                                Link latest completed pre-work checklist
                            </label>
                            <?php if ($latestCompletedChecklist !== null): ?>
                                <div class="small text-secondary mt-2">
                                    <?= e((string) $latestCompletedChecklist['completed_at']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="note" class="form-label">Session Note</label>
                        <textarea id="note" name="note" rows="4" class="<?= e(field_class('note', 'form-control')); ?>" placeholder="Optional session note"><?= e((string) old('note')); ?></textarea>
                        <?php component('form_error', ['field' => 'note']); ?>
                    </div>

                    <div class="col-12 d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-dark" <?= $running ? 'disabled' : ''; ?>>Start Focus Session</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card border-0 shadow-sm mb-4 dashboard-card-soft">
            <div class="card-body p-4">
                <p class="dashboard-kicker mb-1">By Category</p>
                <div class="vstack gap-2">
                    <?php foreach ($summary['by_category'] as $category): ?>
                        <div class="d-flex justify-content-between align-items-center border rounded-3 px-3 py-2 bg-white">
                            <div>
                                <span class="fw-semibold"><?= e(ucwords(str_replace('_', ' ', $category['category_name']))); ?></span>
                                <div class="text-secondary small"><?= e((string) $category['total_sessions']); ?> sessions</div>
                            </div>
                            <span class="badge rounded-pill text-bg-dark"><?= e((string) $category['total_minutes']); ?>m</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <p class="dashboard-kicker mb-1">Today's Sessions</p>

                <?php if ($todaySessions === []): ?>
                    <p class="text-secondary mb-0">No focus sessions recorded for today yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Minutes</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($todaySessions as $session): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?= e(ucwords(str_replace('_', ' ', (string) $session['category_name']))); ?></div>
                                        </td>
                                        <td><?= e((string) $session['start_time']); ?></td>
                                        <td><?= $session['end_time'] !== null ? e((string) $session['end_time']) : '<span class="text-warning">Running</span>'; ?></td>
                                        <td><?= $session['duration_minutes'] !== null ? e((string) $session['duration_minutes']) : '-'; ?></td>
                                        <td class="text-end">
                                            <form action="<?= base_url('focus-sessions/delete'); ?>" method="post" class="d-inline">
                                                <?= \App\Helpers\Csrf::field(); ?>
                                                <input type="hidden" name="session_id" value="<?= e((string) $session['id']); ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
