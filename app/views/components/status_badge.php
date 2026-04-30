<?php

declare(strict_types=1);

$status = (string) ($status ?? 'pending');
$label = (string) ($label ?? status_label($status));
$pill = (bool) ($pill ?? true);
$class = trim((string) ($class ?? ''));
$badgeClass = trim('badge ' . ($pill ? 'rounded-pill ' : '') . status_badge_class($status) . ' ' . $class);
?>
<span class="<?= e($badgeClass); ?>"><?= e($label); ?></span>
