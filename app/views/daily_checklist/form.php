<?php

declare(strict_types=1);

$taskData = $task ?? [];
?>
<section class="card border-0 shadow-sm">
    <div class="card-body p-4 p-lg-5">
        <form action="<?= e($formAction); ?>" method="post" class="row g-4">
            <?= \App\Helpers\Csrf::field(); ?>

            <?php if (!empty($taskData['id'])): ?>
                <input type="hidden" name="id" value="<?= e((string) $taskData['id']); ?>">
            <?php endif; ?>

            <div class="col-12">
                <label for="title" class="form-label">Task Title</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    class="form-control<?= error('title') !== null ? ' is-invalid' : ''; ?>"
                    value="<?= e((string) old('title', $taskData['title'] ?? '')); ?>"
                    placeholder="Send client update"
                >
                <?php if (error('title') !== null): ?>
                    <div class="invalid-feedback"><?= e((string) error('title')); ?></div>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <label for="category" class="form-label">Category</label>
                <input
                    list="category-options"
                    type="text"
                    id="category"
                    name="category"
                    class="form-control<?= error('category') !== null ? ' is-invalid' : ''; ?>"
                    value="<?= e((string) old('category', $taskData['category'] ?? '')); ?>"
                    placeholder="Planning"
                >
                <datalist id="category-options">
                    <?php foreach ($categoryOptions as $categoryOption): ?>
                        <option value="<?= e($categoryOption); ?>"></option>
                    <?php endforeach; ?>
                </datalist>
                <?php if (error('category') !== null): ?>
                    <div class="invalid-feedback"><?= e((string) error('category')); ?></div>
                <?php endif; ?>
            </div>

            <div class="col-md-3">
                <label for="priority" class="form-label">Priority</label>
                <select id="priority" name="priority" class="form-select<?= error('priority') !== null ? ' is-invalid' : ''; ?>">
                    <?php foreach ($priorityOptions as $priority): ?>
                        <option value="<?= e($priority); ?>" <?= old('priority', $taskData['priority'] ?? 'medium') === $priority ? 'selected' : ''; ?>>
                            <?= e(ucfirst($priority)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (error('priority') !== null): ?>
                    <div class="invalid-feedback"><?= e((string) error('priority')); ?></div>
                <?php endif; ?>
            </div>

            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select<?= error('status') !== null ? ' is-invalid' : ''; ?>">
                    <?php foreach ($statusOptions as $status): ?>
                        <option value="<?= e($status); ?>" <?= old('status', $taskData['status'] ?? 'pending') === $status ? 'selected' : ''; ?>>
                            <?= e(ucfirst($status)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (error('status') !== null): ?>
                    <div class="invalid-feedback"><?= e((string) error('status')); ?></div>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <label for="estimated_duration_minutes" class="form-label">Estimated Duration (minutes)</label>
                <input
                    type="number"
                    min="1"
                    id="estimated_duration_minutes"
                    name="estimated_duration_minutes"
                    class="form-control<?= error('estimated_duration_minutes') !== null ? ' is-invalid' : ''; ?>"
                    value="<?= e((string) old('estimated_duration_minutes', $taskData['estimated_duration_minutes'] ?? '')); ?>"
                    placeholder="30"
                >
                <?php if (error('estimated_duration_minutes') !== null): ?>
                    <div class="invalid-feedback"><?= e((string) error('estimated_duration_minutes')); ?></div>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <label for="actual_duration_minutes" class="form-label">Actual Duration (minutes)</label>
                <input
                    type="number"
                    min="1"
                    id="actual_duration_minutes"
                    name="actual_duration_minutes"
                    class="form-control<?= error('actual_duration_minutes') !== null ? ' is-invalid' : ''; ?>"
                    value="<?= e((string) old('actual_duration_minutes', $taskData['actual_duration_minutes'] ?? '')); ?>"
                    placeholder="25"
                >
                <?php if (error('actual_duration_minutes') !== null): ?>
                    <div class="invalid-feedback"><?= e((string) error('actual_duration_minutes')); ?></div>
                <?php endif; ?>
            </div>

            <div class="col-12">
                <label for="note" class="form-label">Note</label>
                <textarea
                    id="note"
                    name="note"
                    rows="4"
                    class="form-control<?= error('note') !== null ? ' is-invalid' : ''; ?>"
                    placeholder="Optional context for this checklist item"
                ><?= e((string) old('note', $taskData['note'] ?? '')); ?></textarea>
                <?php if (error('note') !== null): ?>
                    <div class="invalid-feedback"><?= e((string) error('note')); ?></div>
                <?php endif; ?>
            </div>

            <div class="col-12">
                <div class="small text-secondary">
                    Task Date: <?= e((string) ($taskData['task_date'] ?? $todayDate)); ?>
                </div>
            </div>

            <div class="col-12 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-dark"><?= e($submitLabel); ?></button>
                <a href="<?= base_url('daily-checklist'); ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</section>
