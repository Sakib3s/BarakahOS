<?php

declare(strict_types=1);

$templateData = $template ?? [];
$selectedDays = old('active_days', $templateData['active_days'] ?? []);
$selectedDays = is_array($selectedDays) ? $selectedDays : [];
$isFixedTask = old('is_fixed_task', isset($templateData['is_fixed_task']) && $templateData['is_fixed_task'] ? '1' : '0') === '1';
$isAnyTime = old('any_time', (!isset($templateData['start_time'], $templateData['end_time']) || ($templateData['start_time'] === null && $templateData['end_time'] === null)) ? '1' : '0') === '1';
?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-4 p-lg-5">
        <form action="<?= e($formAction); ?>" method="post" class="row g-4">
            <?= \App\Helpers\Csrf::field(); ?>

            <?php if (!empty($templateData['id'])): ?>
                <input type="hidden" name="id" value="<?= e((string) $templateData['id']); ?>">
            <?php endif; ?>

            <div class="col-12">
                <label for="title" class="form-label">Title</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    class="<?= e(field_class('title', 'form-control')); ?>"
                    value="<?= e((string) old('title', $templateData['title'] ?? '')); ?>"
                    placeholder="Morning planning"
                >
                <?php component('form_error', ['field' => 'title']); ?>
            </div>

            <div class="col-md-6">
                <label for="category" class="form-label">Category</label>
                <select id="category" name="category" class="<?= e(field_class('category', 'form-select')); ?>">
                    <option value="">Select category</option>
                    <?php foreach ($categories as $category): ?>
                        <?php $selectedCategory = old('category', $templateData['category'] ?? ''); ?>
                        <option value="<?= e($category); ?>" <?= $selectedCategory === $category ? 'selected' : ''; ?>>
                            <?= e(ucwords(str_replace('_', ' ', $category))); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php component('form_error', ['field' => 'category']); ?>
            </div>

            <div class="col-md-6 d-flex align-items-end">
                <div class="form-check form-switch">
                    <input type="hidden" name="any_time" value="0">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="any_time"
                        name="any_time"
                        value="1"
                        <?= $isAnyTime ? 'checked' : ''; ?>
                    >
                    <label class="form-check-label" for="any_time">Any Time</label>
                </div>
            </div>

            <div class="col-md-3">
                <label for="start_time" class="form-label">Start Time</label>
                <input
                    type="time"
                    id="start_time"
                    name="start_time"
                    class="<?= e(field_class('start_time', 'form-control')); ?>"
                    value="<?= e((string) old('start_time', $templateData['start_time'] ?? '')); ?>"
                    <?= $isAnyTime ? 'disabled' : ''; ?>
                >
                <?php component('form_error', ['field' => 'start_time']); ?>
            </div>

            <div class="col-md-3">
                <label for="end_time" class="form-label">End Time</label>
                <input
                    type="time"
                    id="end_time"
                    name="end_time"
                    class="<?= e(field_class('end_time', 'form-control')); ?>"
                    value="<?= e((string) old('end_time', $templateData['end_time'] ?? '')); ?>"
                    <?= $isAnyTime ? 'disabled' : ''; ?>
                >
                <?php component('form_error', ['field' => 'end_time']); ?>
            </div>

            <div class="col-md-4">
                <label for="sort_order" class="form-label">Sort Order</label>
                <input
                    type="number"
                    min="0"
                    id="sort_order"
                    name="sort_order"
                    class="<?= e(field_class('sort_order', 'form-control')); ?>"
                    value="<?= e((string) old('sort_order', $templateData['sort_order'] ?? '0')); ?>"
                >
                <?php component('form_error', ['field' => 'sort_order']); ?>
            </div>

            <div class="col-md-8 d-flex align-items-end">
                <div class="form-check form-switch">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="is_fixed_task"
                        name="is_fixed_task"
                        value="1"
                        <?= $isFixedTask ? 'checked' : ''; ?>
                    >
                    <label class="form-check-label" for="is_fixed_task">Treat this routine block as a fixed task</label>
                </div>
            </div>

            <div class="col-12">
                <label class="form-label d-block mb-3">Active Days</label>
                <div class="row g-3">
                    <?php foreach ($weekdays as $dayKey => $dayLabel): ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="form-check border rounded-3 px-3 py-2 bg-light h-100">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="day-<?= e($dayKey); ?>"
                                    name="active_days[]"
                                    value="<?= e($dayKey); ?>"
                                    <?= in_array($dayKey, $selectedDays, true) ? 'checked' : ''; ?>
                                >
                                <label class="form-check-label" for="day-<?= e($dayKey); ?>">
                                    <?= e($dayLabel); ?>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php component('form_error', ['field' => 'active_days', 'class' => 'text-danger small mt-2']); ?>
            </div>

            <div class="col-12 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-dark"><?= e($submitLabel); ?></button>
                <a href="<?= base_url('routine-templates'); ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const anyTimeCheckbox = document.getElementById('any_time');
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');

    if (!anyTimeCheckbox || !startTimeInput || !endTimeInput) {
        return;
    }

    const syncTimeInputs = function () {
        const disabled = anyTimeCheckbox.checked;
        startTimeInput.disabled = disabled;
        endTimeInput.disabled = disabled;
    };

    anyTimeCheckbox.addEventListener('change', syncTimeInputs);
    syncTimeInputs();
});
</script>
