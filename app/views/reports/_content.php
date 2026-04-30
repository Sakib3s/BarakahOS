<?php

declare(strict_types=1);

$tasks = $report['tasks'];
$fixedTasks = $report['fixed_tasks'];
$focus = $report['focus'];
$distraction = $report['distraction'];
$prayer = $report['prayer'];
$sleep = $report['sleep'];
$review = $report['review'];
?>
<section class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <p class="dashboard-kicker mb-1"><?= e(ucfirst($period)); ?> Report</p>
        <h1 class="h3 mb-1">Productivity Report</h1>
        <p class="text-secondary mb-0"><?= e($rangeLabel); ?>. Filter any report page with a custom date range.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= base_url('reports/daily'); ?>" class="btn <?= $period === 'daily' ? 'btn-dark' : 'btn-outline-dark'; ?>">Daily</a>
        <a href="<?= base_url('reports/weekly'); ?>" class="btn <?= $period === 'weekly' ? 'btn-dark' : 'btn-outline-dark'; ?>">Weekly</a>
        <a href="<?= base_url('reports/monthly'); ?>" class="btn <?= $period === 'monthly' ? 'btn-dark' : 'btn-outline-dark'; ?>">Monthly</a>
    </div>
</section>

<section class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form action="<?= base_url('reports/' . $period); ?>" method="get" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" id="start_date" name="start_date" value="<?= e($startDate); ?>" class="form-control">
            </div>
            <div class="col-md-4">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" id="end_date" name="end_date" value="<?= e($endDate); ?>" class="form-control">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-dark">Apply Range</button>
                <a href="<?= base_url('reports/' . $period); ?>" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</section>

<section class="row g-4 mb-4">
    <div class="col-md-6 col-xxl-3">
        <div class="card border-0 shadow-sm h-100 dashboard-card">
            <div class="card-body p-4">
                <p class="dashboard-kicker mb-1">Task Completion</p>
                <h2 class="h4 mb-2"><?= e((string) $tasks['summary']['done_count']); ?>/<?= e((string) $tasks['summary']['total_count']); ?> done</h2>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge rounded-pill text-bg-secondary">Pending <?= e((string) $tasks['summary']['pending_count']); ?></span>
                    <span class="badge rounded-pill text-bg-success">Done <?= e((string) $tasks['summary']['done_count']); ?></span>
                    <span class="badge rounded-pill text-bg-warning">Partial <?= e((string) $tasks['summary']['partial_count']); ?></span>
                    <span class="badge rounded-pill text-bg-danger">Missed <?= e((string) $tasks['summary']['missed_count']); ?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xxl-3">
        <div class="card border-0 shadow-sm h-100 dashboard-card-soft">
            <div class="card-body p-4">
                <p class="dashboard-kicker mb-1">Fixed Tasks</p>
                <h2 class="h4 mb-2"><?= e((string) $fixedTasks['summary']['total_count']); ?> logged</h2>
                <div class="d-flex flex-wrap gap-2">
                    <?php component('status_badge', ['status' => 'done', 'label' => 'Done ' . (string) $fixedTasks['summary']['done_count']]); ?>
                    <?php component('status_badge', ['status' => 'partial', 'label' => 'Partial ' . (string) $fixedTasks['summary']['partial_count']]); ?>
                    <?php component('status_badge', ['status' => 'skipped_with_note', 'label' => 'Skipped ' . (string) $fixedTasks['summary']['skipped_with_note_count']]); ?>
                    <?php component('status_badge', ['status' => 'missed', 'label' => 'Missed ' . (string) $fixedTasks['summary']['missed_count']]); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xxl-3">
        <div class="card border-0 shadow-sm h-100 dashboard-card">
            <div class="card-body p-4">
                <p class="dashboard-kicker mb-1">Focus Sessions</p>
                <h2 class="h4 mb-2"><?= e((string) $focus['summary']['session_count']); ?> sessions</h2>
                <p class="text-secondary mb-0">
                    <?= e(format_duration($focus['summary']['total_minutes'])); ?> total,
                    avg <?= e(format_duration($focus['summary']['average_minutes'])); ?>,
                    longest <?= e(format_duration($focus['summary']['longest_minutes'])); ?>.
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xxl-3">
        <div class="card border-0 shadow-sm h-100 dashboard-card">
            <div class="card-body p-4">
                <p class="dashboard-kicker mb-1">Distractions</p>
                <h2 class="h4 mb-2"><?= e((string) $distraction['summary']['total_count']); ?> events</h2>
                <p class="text-secondary small mb-2"><?= e(format_duration((int) $distraction['summary']['total_duration_minutes'])); ?> wasted.</p>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge rounded-pill <?= distraction_badge_class('mobile_used'); ?>">Mobile <?= e((string) $distraction['summary']['mobile_used_count']); ?></span>
                    <span class="badge rounded-pill <?= distraction_badge_class('social_media_used'); ?>">Social <?= e((string) $distraction['summary']['social_media_used_count']); ?></span>
                    <span class="badge rounded-pill <?= distraction_badge_class('waste_time'); ?>">Waste <?= e((string) $distraction['summary']['waste_time_count']); ?></span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="row g-4 mb-4">
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100 dashboard-card">
            <div class="card-body p-4">
                <p class="dashboard-kicker mb-1">Prayer Summary</p>
                <h2 class="h4 mb-2"><?= e((string) $prayer['summary']['logged_count']); ?>/<?= e((string) $prayer['summary']['expected_count']); ?> logged</h2>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge rounded-pill text-bg-success">On Time <?= e((string) $prayer['summary']['on_time_count']); ?></span>
                    <span class="badge rounded-pill text-bg-warning">Delayed <?= e((string) $prayer['summary']['delayed_count']); ?></span>
                    <span class="badge rounded-pill text-bg-danger">Missed <?= e((string) $prayer['summary']['missed_count']); ?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100 dashboard-card-soft">
            <div class="card-body p-4">
                <p class="dashboard-kicker mb-1">Sleep Summary</p>
                <h2 class="h4 mb-2"><?= e(format_duration((int) $sleep['summary']['average_minutes'])); ?> avg</h2>
                <div class="d-flex flex-wrap gap-2 mb-2">
                    <span class="badge rounded-pill text-bg-dark"><?= e((string) $sleep['summary']['days_logged']); ?> days</span>
                    <span class="badge rounded-pill text-bg-success">Longest <?= e(format_duration((int) $sleep['summary']['longest_minutes'])); ?></span>
                    <span class="badge rounded-pill text-bg-warning">Shortest <?= e(format_duration((int) $sleep['summary']['shortest_minutes'])); ?></span>
                </div>
                <p class="text-secondary mb-0"><?= e(format_duration((int) $sleep['summary']['total_minutes'])); ?> total sleep.</p>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100 dashboard-card">
            <div class="card-body p-4">
                <p class="dashboard-kicker mb-1">Daily Review Summary</p>
                <h2 class="h4 mb-2"><?= e((string) $review['summary']['days_logged']); ?> days logged</h2>
                <div class="d-flex flex-wrap gap-2 mb-2">
                    <span class="badge rounded-pill text-bg-dark">Avg <?= e(number_format((float) $review['summary']['average_day_rating'], 1)); ?>/10</span>
                    <span class="badge rounded-pill text-bg-success">High <?= e((string) $review['summary']['max_day_rating']); ?></span>
                    <span class="badge rounded-pill text-bg-warning">Low <?= e((string) $review['summary']['min_day_rating']); ?></span>
                </div>
                <p class="text-secondary mb-0">One review per day with lessons and tomorrow priority.</p>
            </div>
        </div>
    </div>
</section>

<section class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <p class="dashboard-kicker mb-1">Sleep Detail</p>
        <?php if ($sleep['details'] === []): ?>
            <div class="border rounded-4 p-4 bg-light-subtle text-center text-secondary">No sleep sessions logged in this range.</div>
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sleep['details'] as $row): ?>
                            <tr>
                                <td class="fw-semibold"><?= e((string) $row['sleep_date']); ?></td>
                                <td><?= e(date('d M, h:i A', strtotime((string) $row['sleep_started_at']))); ?></td>
                                <td><?= e(date('d M, h:i A', strtotime((string) $row['woke_up_at']))); ?></td>
                                <td><?= e(format_duration((int) $row['duration_minutes'])); ?></td>
                                <td class="text-secondary"><?= $row['note'] !== null ? nl2br(e((string) $row['note'])) : '<span class="text-muted">n/a</span>'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <p class="dashboard-kicker mb-1">Fixed Task Detail</p>
                <h2 class="h5 mb-0">Status breakdown with skipped_with_note explanations</h2>
            </div>
        </div>
        <?php component('data_table', [
            'headers' => ['Date', 'Task', 'Status', 'Time', 'Skipped With Note', 'General Note'],
            'rows' => $fixedTasks['details'],
            'emptyMessage' => 'No fixed task logs in this range.',
            'rowRenderer' => static function (array $row): void {
                ?>
                <tr>
                    <td class="fw-semibold"><?= e((string) $row['log_date']); ?></td>
                    <td>
                        <?= e((string) $row['title']); ?>
                        <div class="small text-secondary"><?= e(ucwords(str_replace('_', ' ', (string) $row['source_type']))); ?></div>
                    </td>
                    <td><?php component('status_badge', ['status' => (string) $row['status']]); ?></td>
                    <td>
                        <?= $row['planned_start_time'] !== null ? e(substr((string) $row['planned_start_time'], 0, 5)) : 'n/a'; ?>
                        -
                        <?= $row['planned_end_time'] !== null ? e(substr((string) $row['planned_end_time'], 0, 5)) : 'n/a'; ?>
                    </td>
                    <td class="text-primary"><?= $row['skip_note'] !== null ? nl2br(e((string) $row['skip_note'])) : '<span class="text-muted">n/a</span>'; ?></td>
                    <td class="text-secondary"><?= $row['general_note'] !== null ? nl2br(e((string) $row['general_note'])) : '<span class="text-muted">n/a</span>'; ?></td>
                </tr>
                <?php
            },
        ]); ?>
    </div>
</section>

<section class="row g-4 mb-4">
    <div class="col-xl-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <p class="dashboard-kicker mb-1">Focus Session Detail</p>
                <?php if ($focus['details'] === []): ?>
                    <div class="border rounded-4 p-4 bg-light-subtle text-center text-secondary">No focus sessions in this range.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($focus['details'] as $row): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= e((string) $row['session_date']); ?></td>
                                        <td><?= e((string) $row['category_name']); ?></td>
                                        <td><?= e(format_duration((int) ($row['duration_minutes'] ?? 0))); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if ($focus['by_category'] !== []): ?>
                    <hr class="my-4">
                    <p class="dashboard-kicker mb-2">Grouped By Category</p>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($focus['by_category'] as $row): ?>
                            <span class="badge rounded-pill text-bg-dark">
                                <?= e((string) $row['category_name']); ?> <?= e(format_duration((int) $row['total_minutes'])); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <p class="dashboard-kicker mb-1">Distraction Detail</p>
                <?php if ($distraction['details'] === []): ?>
                    <div class="border rounded-4 p-4 bg-light-subtle text-center text-secondary">No distractions logged in this range.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Time</th>
                                    <th>Duration</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($distraction['details'] as $row): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= e((string) $row['log_date']); ?></td>
                                        <td><span class="badge rounded-pill <?= distraction_badge_class((string) $row['distraction_type']); ?>"><?= e(distraction_type_label((string) $row['distraction_type'])); ?></span></td>
                                        <td><?= e(date('h:i A', strtotime((string) $row['occurred_at']))); ?></td>
                                        <td><?= e(format_duration((int) $row['duration_minutes'])); ?></td>
                                        <td class="text-secondary"><?= $row['note'] !== null ? nl2br(e((string) $row['note'])) : '<span class="text-muted">n/a</span>'; ?></td>
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

<section class="row g-4 mb-4">
    <div class="col-xl-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <p class="dashboard-kicker mb-1">Prayer Detail</p>
                <?php if ($prayer['details'] === []): ?>
                    <div class="border rounded-4 p-4 bg-light-subtle text-center text-secondary">No prayer logs in this range.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Prayer</th>
                                    <th>Status</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($prayer['details'] as $row): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= e((string) $row['log_date']); ?></td>
                                        <td><?= e((string) $row['prayer_name']); ?></td>
                                        <td><span class="badge rounded-pill <?= status_badge_class((string) $row['status']); ?>"><?= e(prayer_status_label((string) $row['status'])); ?></span></td>
                                        <td class="text-secondary"><?= $row['note'] !== null ? nl2br(e((string) $row['note'])) : '<span class="text-muted">n/a</span>'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <p class="dashboard-kicker mb-1">Task Detail</p>
                <?php if ($tasks['details'] === []): ?>
                    <div class="border rounded-4 p-4 bg-light-subtle text-center text-secondary">No daily task logs in this range.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Task</th>
                                    <th>Status</th>
                                    <th>Category</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tasks['details'] as $row): ?>
                                    <tr>
                                        <td class="fw-semibold"><?= e((string) $row['task_date']); ?></td>
                                        <td><?= e((string) $row['title']); ?></td>
                                        <td><span class="badge rounded-pill <?= status_badge_class((string) $row['status']); ?>"><?= e(ucfirst((string) $row['status'])); ?></span></td>
                                        <td class="text-secondary"><?= e((string) $row['category']); ?></td>
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

<section class="card border-0 shadow-sm mt-4">
    <div class="card-body p-4">
        <p class="dashboard-kicker mb-1">Daily Review Detail</p>
        <?php if ($review['details'] === []): ?>
            <div class="border rounded-4 p-4 bg-light-subtle text-center text-secondary">No daily reviews in this range.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Rating</th>
                            <th>Top Lesson</th>
                            <th>Tomorrow Priority</th>
                            <th>Sleep Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($review['details'] as $row): ?>
                            <tr>
                                <td class="fw-semibold"><?= e((string) $row['review_date']); ?></td>
                                <td><span class="badge rounded-pill text-bg-dark"><?= e((string) $row['day_rating']); ?>/10</span></td>
                                <td><?= e((string) $row['top_lesson']); ?></td>
                                <td><?= e((string) $row['tomorrow_priority']); ?></td>
                                <td class="text-secondary"><?= $row['sleep_note'] !== null ? nl2br(e((string) $row['sleep_note'])) : '<span class="text-muted">n/a</span>'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
