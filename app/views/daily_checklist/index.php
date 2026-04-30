<?php

declare(strict_types=1);
?>
<section class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <p class="dashboard-kicker mb-1">Daily Checklist</p>
        <h1 class="h3 mb-1">Today's Task List</h1>
        <p class="text-secondary mb-0"><?= e($todayLabel); ?>. Filter by status or category and update task progress directly from the list.</p>
    </div>
    <a href="<?= base_url('daily-checklist/create'); ?>" class="btn btn-dark">Add Daily Task</a>
</section>

<section class="row g-4 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 dashboard-card">
            <div class="card-body p-4">
                <p class="dashboard-kicker mb-1">Total</p>
                <h2 class="display-6 fw-bold mb-0"><?= e((string) $summary['total_count']); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 dashboard-card">
            <div class="card-body p-4">
                <p class="dashboard-kicker mb-1">Pending</p>
                <h2 class="display-6 fw-bold mb-0"><?= e((string) $summary['pending_count']); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 dashboard-card">
            <div class="card-body p-4">
                <p class="dashboard-kicker mb-1">Done</p>
                <h2 class="display-6 fw-bold mb-0"><?= e((string) $summary['done_count']); ?></h2>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 dashboard-card-soft">
            <div class="card-body p-4">
                <p class="dashboard-kicker mb-1">Partial / Missed</p>
                <h2 class="display-6 fw-bold mb-0"><?= e((string) ($summary['partial_count'] + $summary['missed_count'])); ?></h2>
            </div>
        </div>
    </div>
</section>

<section class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <form action="<?= base_url('daily-checklist'); ?>" method="get" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="status" class="form-label">Filter By Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">All statuses</option>
                    <?php foreach ($statusOptions as $statusOption): ?>
                        <option value="<?= e($statusOption); ?>" <?= $filters['status'] === $statusOption ? 'selected' : ''; ?>>
                            <?= e(ucfirst($statusOption)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="category" class="form-label">Filter By Category</label>
                <select id="category" name="category" class="form-select">
                    <option value="">All categories</option>
                    <?php foreach ($categoryOptions as $categoryOption): ?>
                        <option value="<?= e($categoryOption); ?>" <?= $filters['category'] === $categoryOption ? 'selected' : ''; ?>>
                            <?= e($categoryOption); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-dark">Apply Filters</button>
                <a href="<?= base_url('daily-checklist'); ?>" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</section>

<section class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <?php if ($tasks === []): ?>
            <div class="border rounded-4 p-5 bg-light-subtle text-center">
                <p class="mb-1 fw-semibold">No tasks found for today</p>
                <p class="text-secondary mb-3">Add a task or widen the filters to see more checklist items.</p>
                <a href="<?= base_url('daily-checklist/create'); ?>" class="btn btn-dark">Add Daily Task</a>
            </div>
        <?php else: ?>
            <div class="vstack gap-3">
                <?php foreach ($tasks as $task): ?>
                    <div class="dashboard-list-item">
                        <div class="d-flex flex-column flex-xl-row justify-content-between gap-3">
                            <div class="flex-grow-1">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                    <h2 class="h5 mb-0"><?= e($task['title']); ?></h2>
                                    <span class="badge rounded-pill <?= status_badge_class((string) $task['status']); ?>">
                                        <?= e(ucfirst((string) $task['status'])); ?>
                                    </span>
                                    <span class="badge rounded-pill text-bg-light border text-dark">
                                        <?= e($task['category']); ?>
                                    </span>
                                    <span class="badge rounded-pill text-bg-dark">
                                        <?= e(ucfirst((string) $task['priority'])); ?> Priority
                                    </span>
                                </div>

                                <div class="d-flex flex-wrap gap-3 text-secondary small mb-2">
                                    <span>Estimated: <?= $task['estimated_duration_minutes'] !== null ? e((string) $task['estimated_duration_minutes']) . 'm' : 'n/a'; ?></span>
                                    <span>Actual: <?= $task['actual_duration_minutes'] !== null ? e((string) $task['actual_duration_minutes']) . 'm' : 'n/a'; ?></span>
                                    <span>Date: <?= e((string) $task['task_date']); ?></span>
                                </div>

                                <?php if (!empty($task['note'])): ?>
                                    <p class="mb-0 text-secondary"><?= nl2br(e((string) $task['note'])); ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex flex-wrap gap-2 align-content-start">
                                <a
                                    href="<?= base_url('daily-checklist/edit?id=' . $task['id']); ?>"
                                    class="btn btn-sm btn-outline-secondary"
                                >
                                    Edit
                                </a>
                                <?php foreach ($statusOptions as $statusOption): ?>
                                    <form action="<?= base_url('daily-checklist/status'); ?>" method="post">
                                        <?= \App\Helpers\Csrf::field(); ?>
                                        <input type="hidden" name="id" value="<?= e((string) $task['id']); ?>">
                                        <input type="hidden" name="status" value="<?= e($statusOption); ?>">
                                        <input type="hidden" name="filter_status" value="<?= e($filters['status']); ?>">
                                        <input type="hidden" name="filter_category" value="<?= e($filters['category']); ?>">
                                        <button
                                            type="submit"
                                            class="btn btn-sm <?= $task['status'] === $statusOption ? 'btn-dark' : 'btn-outline-dark'; ?>"
                                        >
                                            <?= e(ucfirst($statusOption)); ?>
                                        </button>
                                    </form>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
