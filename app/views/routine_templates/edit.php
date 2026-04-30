<?php

declare(strict_types=1);

$formAction = base_url('routine-templates/update');
$submitLabel = 'Update Template';
?>
<section class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <p class="dashboard-kicker mb-1">Routine Templates</p>
        <h1 class="h3 mb-1">Edit Template</h1>
        <p class="text-secondary mb-0">Update the recurring block details, active days, fixed-task flag, or ordering.</p>
    </div>

    <?php if ($template !== null): ?>
        <form action="<?= base_url('routine-templates/delete'); ?>" method="post" class="mb-0">
            <?= \App\Helpers\Csrf::field(); ?>
            <input type="hidden" name="id" value="<?= e((string) $template['id']); ?>">
            <button
                type="submit"
                class="btn btn-outline-danger"
                onclick="return confirm('Delete this routine template?');"
            >
                Delete Template
            </button>
        </form>
    <?php endif; ?>
</section>

<?php require APP_PATH . '/views/routine_templates/form.php'; ?>
