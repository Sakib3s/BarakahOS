<?php

declare(strict_types=1);

$routine = $summary['routine'];
$prayer = $summary['prayer'];
$tasks = $summary['tasks'];
$fixedTasks = $summary['fixedTasks'];
$focus = $summary['focus'];
$checklist = $summary['checklist'];
$distraction = $summary['distraction'];
$sleep = $summary['sleep'];
$review = $summary['review'];
$checklistProgress = $checklist['total_items'] > 0
    ? (string) (int) round(($checklist['checked_count'] / $checklist['total_items']) * 100)
    : '0';
$checklistProgressBar = capture(static function () use ($checklistProgress): void {
    ?>
    <div class="progress dashboard-progress" role="progressbar" aria-label="Checklist progress">
        <div class="progress-bar bg-success" style="width: <?= e($checklistProgress); ?>%;"></div>
    </div>
    <?php
});
?>
<section class="dashboard-hero dashboard-hero--<?= e((string) $heroTheme); ?> card border-0 shadow-sm overflow-hidden mb-4">
    <div class="card-body p-4 p-lg-5">
        <div class="row g-4">
            <div class="col-lg-7 dashboard-hero-content">
                <h1 class="dashboard-hero-title fw-bold mb-2"><?= e((string) $heroTitle); ?></h1>
                <p class="dashboard-hero-message mb-0"><?= e((string) $heroMessage); ?></p>
            </div>
            <div class="col-lg-5">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="dashboard-mini-card h-100">
                            <p class="dashboard-kicker mb-1">Today</p>
                            <h2 class="h5 mb-0"><?= e($todayLabel); ?></h2>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="dashboard-mini-card h-100">
                            <p class="dashboard-kicker mb-1">Timezone</p>
                            <h2 class="h5 mb-0"><?= e($timezoneLabel); ?></h2>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="dashboard-mini-card h-100">
                            <p class="dashboard-kicker mb-1">Focus Sessions</p>
                            <h2 class="h4 mb-0"><?= e((string) $focus['session_count']); ?></h2>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="dashboard-mini-card h-100">
                            <p class="dashboard-kicker mb-1">Focus Minutes</p>
                            <h2 class="h4 mb-0"><?= e((string) $focus['total_minutes']); ?>m</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="mobile-quick-actions d-lg-none mb-4">
    <a href="<?= base_url('distractions'); ?>" class="btn btn-outline-dark btn-sm">Distraction</a>
    <a href="<?= base_url('sleep-tracker'); ?>" class="btn btn-outline-dark btn-sm">Sleep</a>
    <a href="<?= base_url('focus-sessions'); ?>" class="btn btn-dark btn-sm">Start Focus</a>
</section>

<section class="row g-4 mb-4" id="summary-grid">
    <div class="col-md-6 col-xxl-3">
        <?php component('dashboard_stat_card', [
            'kicker' => 'Today\'s Routine',
            'title' => (string) $routine['logged_count'] . '/' . (string) $routine['active_count'] . ' logged',
            'headerBadge' => ['label' => (string) $routine['remaining_count'] . ' left', 'class' => 'text-bg-dark'],
            'badges' => [
                ['label' => 'Done ' . (string) $routine['done_count'], 'class' => 'text-bg-success'],
                ['label' => 'Partial ' . (string) $routine['partial_count'], 'class' => 'text-bg-warning'],
                ['label' => 'Skipped ' . (string) $routine['skipped_count'], 'class' => 'text-bg-secondary'],
            ],
        ]); ?>
    </div>

    <div class="col-md-6 col-xxl-3">
        <?php component('dashboard_stat_card', [
            'kicker' => 'Prayer Tracking',
            'title' => (string) $prayer['logged_count'] . '/' . (string) $prayer['expected_count'] . ' logged',
            'headerBadge' => ['label' => (string) $prayer['remaining_count'] . ' left', 'class' => 'text-bg-dark'],
            'badges' => [
                ['label' => 'On Time ' . (string) $prayer['on_time_count'], 'class' => 'text-bg-success'],
                ['label' => 'Delayed ' . (string) $prayer['delayed_count'], 'class' => 'text-bg-warning'],
                ['label' => 'Missed ' . (string) $prayer['missed_count'], 'class' => 'text-bg-danger'],
            ],
        ]); ?>
    </div>

    <div class="col-md-6 col-xxl-3">
        <?php component('dashboard_stat_card', [
            'kicker' => 'Task Completion',
            'title' => (string) $tasks['completed_today_count'] . ' completed today',
            'headerBadge' => ['label' => (string) $tasks['total_count'] . ' total', 'class' => 'text-bg-dark'],
            'badges' => [
                ['label' => 'Pending ' . (string) $tasks['pending_count'], 'class' => 'text-bg-secondary'],
                ['label' => 'In Progress ' . (string) $tasks['in_progress_count'], 'class' => 'text-bg-warning'],
                ['label' => 'Completed ' . (string) $tasks['completed_count'], 'class' => 'text-bg-success'],
            ],
        ]); ?>
    </div>

    <div class="col-md-6 col-xxl-3">
        <?php component('dashboard_stat_card', [
            'kicker' => 'Pre-Work Checklist',
            'title' => (string) $checklist['checked_count'] . '/' . (string) $checklist['total_items'] . ' completed',
            'headerBadge' => ['label' => (string) $checklist['remaining_count'] . ' left', 'class' => 'text-bg-dark'],
            'content' => $checklistProgressBar,
        ]); ?>
    </div>
</section>

<section class="row g-4 mb-4">
    <div class="col-xl-5">
        <div class="card border-0 shadow-sm h-100 dashboard-card-soft">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <p class="dashboard-kicker mb-1">Fixed Task Status Summary</p>
                        <h2 class="h5 mb-0"><?= e((string) $fixedTasks['expected_count']); ?> active fixed tasks</h2>
                    </div>
                    <span class="badge text-bg-secondary rounded-pill">Pending <?= e((string) $fixedTasks['pending_count']); ?></span>
                </div>
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <?php component('status_badge', ['status' => 'done', 'label' => 'Done ' . (string) $fixedTasks['done_count']]); ?>
                    <?php component('status_badge', ['status' => 'partial', 'label' => 'Partial ' . (string) $fixedTasks['partial_count']]); ?>
                    <?php component('status_badge', ['status' => 'skipped_with_note', 'label' => 'Skipped w/ Note ' . (string) $fixedTasks['skipped_with_note_count']]); ?>
                    <?php component('status_badge', ['status' => 'missed', 'label' => 'Missed ' . (string) $fixedTasks['missed_count']]); ?>
                </div>

                <div class="vstack gap-3" id="fixed-tasks">
                    <?php if ($fixedTaskStatuses === []): ?>
                        <div class="border rounded-4 p-4 bg-white text-center text-secondary">
                            No fixed tasks configured yet.
                        </div>
                    <?php else: ?>
                        <?php foreach ($fixedTaskStatuses as $fixedTask): ?>
                            <div class="dashboard-list-item">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <h3 class="h6 mb-1"><?= e($fixedTask['title']); ?></h3>
                                        <p class="text-secondary small mb-0">
                                            <?= $fixedTask['planned_start_time'] !== null ? e(substr((string) $fixedTask['planned_start_time'], 0, 5)) : 'n/a'; ?>
                                            -
                                            <?= $fixedTask['planned_end_time'] !== null ? e(substr((string) $fixedTask['planned_end_time'], 0, 5)) : 'n/a'; ?>
                                        </p>
                                        <?php if (!empty($fixedTask['skip_note'])): ?>
                                            <p class="small text-primary mb-0 mt-2"><?= e((string) $fixedTask['skip_note']); ?></p>
                                        <?php elseif (!empty($fixedTask['general_note'])): ?>
                                            <p class="small text-secondary mb-0 mt-2"><?= e((string) $fixedTask['general_note']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <?php component('status_badge', ['status' => (string) $fixedTask['status']]); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100 dashboard-card">
                    <div class="card-body p-4">
                        <p class="dashboard-kicker mb-1">Focus Sessions Today</p>
                        <h2 class="display-6 fw-bold mb-1"><?= e((string) $focus['session_count']); ?></h2>
                        <p class="text-secondary mb-0"><?= e(format_duration($focus['total_minutes'])); ?> total focus time.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100 dashboard-card-soft">
                    <div class="card-body p-4">
                        <p class="dashboard-kicker mb-1">Sleep Today</p>
                        <h2 class="display-6 fw-bold mb-1"><?= e(format_duration((int) $sleep['duration_minutes'])); ?></h2>
                        <p class="text-secondary mb-0">
                            <?= $sleep['has_log'] ? 'Tracked by wake-up date.' : 'No sleep session logged yet.'; ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100 dashboard-card">
                    <div class="card-body p-4">
                        <p class="dashboard-kicker mb-1">Distractions Today</p>
                        <h2 class="display-6 fw-bold mb-1"><?= e((string) $distraction['total_count']); ?></h2>
                        <p class="text-secondary small mb-2"><?= e(format_duration((int) $distraction['total_duration_minutes'])); ?> wasted.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge rounded-pill <?= distraction_badge_class('mobile_used'); ?>">
                                Mobile <?= e((string) $distraction['mobile_used_count']); ?>
                            </span>
                            <span class="badge rounded-pill <?= distraction_badge_class('social_media_used'); ?>">
                                Social Media <?= e((string) $distraction['social_media_used_count']); ?>
                            </span>
                            <span class="badge rounded-pill <?= distraction_badge_class('waste_time'); ?>">
                                Waste <?= e((string) $distraction['waste_time_count']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12" id="quick-add">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <p class="dashboard-kicker mb-1">Daily Review</p>
                                        <h2 class="h5 mb-0"><?= $review['has_review'] ? 'Today\'s reflection saved' : 'No review yet today'; ?></h2>
                                    </div>
                                    <a href="<?= base_url('daily-review'); ?>" class="btn btn-outline-dark btn-sm"><?= $review['has_review'] ? 'Edit Review' : 'Add Review'; ?></a>
                                </div>
                                <?php if ($review['has_review']): ?>
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <span class="badge rounded-pill text-bg-dark">Rating <?= e((string) $review['day_rating']); ?>/10</span>
                                    </div>
                                    <p class="text-secondary mb-2">
                                        <strong>Top lesson:</strong> <?= e((string) $review['top_lesson']); ?>
                                    </p>
                                    <p class="text-secondary mb-0">
                                        <strong>Tomorrow priority:</strong> <?= e((string) $review['tomorrow_priority']); ?>
                                    </p>
                                <?php else: ?>
                                    <p class="text-secondary mb-0">Capture the day rating, lessons, and tomorrow’s main priority before ending the day.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <p class="dashboard-kicker mb-1">Quick Add</p>
                                        <h2 class="h5 mb-0">Create a task</h2>
                                    </div>
                                </div>

                                <form action="<?= base_url('tasks'); ?>" method="post" class="row g-3">
                                    <?= \App\Helpers\Csrf::field(); ?>

                                    <div class="col-md-9">
                                        <label for="title" class="form-label">Task Title</label>
                                        <input
                                            type="text"
                                            id="title"
                                            name="title"
                                            class="form-control<?= error('title') !== null ? ' is-invalid' : ''; ?>"
                                            placeholder="Review weekly goals"
                                            value="<?= e((string) old('title')); ?>"
                                        >
                                        <?php if (error('title') !== null): ?>
                                            <div class="invalid-feedback"><?= e((string) error('title')); ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-dark w-100">+</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="row g-4" id="recent-tasks">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <p class="dashboard-kicker mb-1">All Tasks</p>
                        <h2 class="h5 mb-0">Latest task activity</h2>
                    </div>
                    <span class="text-secondary small"><?= e($todayDate); ?></span>
                </div>

                <?php if ($recentTasks === []): ?>
                    <div class="border rounded-4 p-4 bg-light-subtle text-center">
                        <p class="mb-1 fw-semibold">No tasks available</p>
                        <p class="text-secondary mb-0">Create a task to start populating the dashboard.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Due Date</th>
                                    <th>Created</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentTasks as $task): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= e($task['title']); ?></td>
                                        <td>
                                            <span class="badge rounded-pill <?= status_badge_class((string) $task['status']); ?>">
                                                <?= e(ucwords(str_replace('_', ' ', (string) $task['status']))); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($task['due_date'] !== null): ?>
                                                <?= e((string) $task['due_date']); ?>
                                            <?php else: ?>
                                                <span class="text-secondary">Not set</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= e((string) $task['created_at']); ?></td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-2">
                                                <?php if ((string) $task['status'] === 'completed'): ?>
                                                    <span class="badge rounded-pill text-bg-success align-self-center">Done</span>
                                                <?php else: ?>
                                                    <form action="<?= base_url('tasks/complete'); ?>" method="post" class="d-inline">
                                                        <?= \App\Helpers\Csrf::field(); ?>
                                                        <input type="hidden" name="id" value="<?= e((string) $task['id']); ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-success">Mark Done</button>
                                                    </form>
                                                <?php endif; ?>
                                                <form action="<?= base_url('tasks/delete'); ?>" method="post" class="d-inline">
                                                    <?= \App\Helpers\Csrf::field(); ?>
                                                    <input type="hidden" name="id" value="<?= e((string) $task['id']); ?>">
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
        </div>
    </div>
</section>
