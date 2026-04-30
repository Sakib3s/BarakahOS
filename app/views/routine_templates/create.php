<?php

declare(strict_types=1);

$formAction = base_url('routine-templates/create');
$submitLabel = 'Create Template';
?>
<section class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <p class="dashboard-kicker mb-1">Routine Templates</p>
        <h1 class="h3 mb-1">Create Template</h1>
        <p class="text-secondary mb-0">Add a recurring routine block with its category, time range, active days, and ordering.</p>
    </div>
</section>

<?php require APP_PATH . '/views/routine_templates/form.php'; ?>
