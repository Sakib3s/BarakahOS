<?php

declare(strict_types=1);

$formAction = base_url('daily-checklist/update');
$submitLabel = 'Update Task';
?>
<section class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <p class="dashboard-kicker mb-1">Daily Checklist</p>
        <h1 class="h3 mb-1">Edit Task</h1>
        <p class="text-secondary mb-0">Update task details, durations, note, and current status for this checklist item.</p>
    </div>
    <a href="<?= base_url('daily-checklist'); ?>" class="btn btn-outline-secondary">Back To Today List</a>
</section>

<?php require APP_PATH . '/views/daily_checklist/form.php'; ?>
