<?php

declare(strict_types=1);

$formAction = base_url('daily-checklist/create');
$submitLabel = 'Add Task';
?>
<section class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <p class="dashboard-kicker mb-1">Daily Checklist</p>
        <h1 class="h3 mb-1">Add Task For Today</h1>
        <p class="text-secondary mb-0">Create a task for <?= e($todayLabel); ?> with category, priority, durations, and an optional note.</p>
    </div>
    <a href="<?= base_url('daily-checklist'); ?>" class="btn btn-outline-secondary">Back To Today List</a>
</section>

<?php require APP_PATH . '/views/daily_checklist/form.php'; ?>
