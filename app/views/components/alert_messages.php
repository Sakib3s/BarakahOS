<?php

declare(strict_types=1);

$alerts = $alerts ?? [];
$dismissible = $dismissible ?? true;

foreach ($alerts as $alert):
    $type = (string) ($alert['type'] ?? 'secondary');
    $message = (string) ($alert['message'] ?? '');

    if ($message === '') {
        continue;
    }

    $classes = 'alert alert-' . $type;

    if ($dismissible) {
        $classes .= ' alert-dismissible fade show';
    }
    ?>
    <div class="<?= e($classes); ?>" role="alert">
        <?= e($message); ?>
        <?php if ($dismissible): ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
