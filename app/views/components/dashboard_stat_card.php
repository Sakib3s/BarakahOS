<?php

declare(strict_types=1);

$variant = (string) ($variant ?? 'default');
$cardClass = trim((string) ($class ?? ''));
$bodyClass = trim((string) ($bodyClass ?? ''));
$kicker = $kicker ?? null;
$title = $title ?? null;
$value = $value ?? null;
$valueClass = (string) ($valueClass ?? 'h4 mb-2');
$subtitle = $subtitle ?? null;
$content = $content ?? null;
$headerBadge = $headerBadge ?? null;
$badges = is_array($badges ?? null) ? $badges : [];
$variantClass = $variant === 'soft' ? 'dashboard-card-soft' : 'dashboard-card';
?>
<div class="<?= e(trim('card border-0 shadow-sm h-100 ' . $variantClass . ' ' . $cardClass)); ?>">
    <div class="<?= e(trim('card-body p-4 ' . $bodyClass)); ?>">
        <?php if ($kicker !== null || $title !== null || $headerBadge !== null): ?>
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <?php if ($kicker !== null): ?>
                        <p class="dashboard-kicker mb-1"><?= e((string) $kicker); ?></p>
                    <?php endif; ?>
                    <?php if ($title !== null): ?>
                        <h2 class="<?= e($value === null ? 'h5 mb-0' : $valueClass); ?>"><?= e((string) $title); ?></h2>
                    <?php endif; ?>
                </div>
                <?php if (is_array($headerBadge)): ?>
                    <span class="<?= e(trim('badge rounded-pill ' . ((string) ($headerBadge['class'] ?? 'text-bg-dark')))); ?>">
                        <?= e((string) ($headerBadge['label'] ?? '')); ?>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($value !== null): ?>
            <div class="<?= e($valueClass); ?>"><?= e((string) $value); ?></div>
        <?php endif; ?>

        <?php if ($subtitle !== null): ?>
            <p class="text-secondary mb-0"><?= e((string) $subtitle); ?></p>
        <?php endif; ?>

        <?php if ($badges !== []): ?>
            <div class="d-flex flex-wrap gap-2<?= $subtitle !== null ? ' mt-3' : ''; ?>">
                <?php foreach ($badges as $badge): ?>
                    <?php if (isset($badge['status'])): ?>
                        <?php component('status_badge', [
                            'status' => (string) $badge['status'],
                            'label' => (string) ($badge['label'] ?? status_label((string) $badge['status'])),
                        ]); ?>
                    <?php else: ?>
                        <span class="<?= e(trim('badge rounded-pill ' . ((string) ($badge['class'] ?? 'text-bg-secondary')))); ?>">
                            <?= e((string) ($badge['label'] ?? '')); ?>
                        </span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (is_string($content) && $content !== ''): ?>
            <?= $content; ?>
        <?php endif; ?>
    </div>
</div>
