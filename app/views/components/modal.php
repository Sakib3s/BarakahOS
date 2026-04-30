<?php

declare(strict_types=1);

$id = (string) ($id ?? 'app-modal');
$title = (string) ($title ?? 'Modal');
$subtitle = $subtitle ?? null;
$body = (string) ($body ?? '');
$footer = (string) ($footer ?? '');
$size = (string) ($size ?? '');
$centered = (bool) ($centered ?? true);
$formAction = $formAction ?? null;
$formMethod = (string) ($formMethod ?? 'post');
$formId = $formId ?? null;
$hidden = is_array($hidden ?? null) ? $hidden : [];
$includeCsrf = (bool) ($includeCsrf ?? true);
$dialogClass = trim((string) ($dialogClass ?? ''));
$labelId = $id . 'Label';
$sizeClass = $size !== '' ? ' modal-' . $size : '';
?>
<div class="modal fade" id="<?= e($id); ?>" tabindex="-1" aria-labelledby="<?= e($labelId); ?>" aria-hidden="true">
    <div class="<?= e(trim('modal-dialog' . $sizeClass . ($centered ? ' modal-dialog-centered' : '') . ' ' . $dialogClass)); ?>">
        <div class="modal-content">
            <?php if ($formAction !== null): ?>
                <form action="<?= e((string) $formAction); ?>" method="<?= e($formMethod); ?>"<?= $formId !== null ? ' id="' . e((string) $formId) . '"' : ''; ?>>
                    <?php if ($includeCsrf): ?>
                        <?= \App\Helpers\Csrf::field(); ?>
                    <?php endif; ?>
                    <?php foreach ($hidden as $name => $value): ?>
                        <?php
                        $hiddenValue = is_array($value) ? (string) ($value['value'] ?? '') : (string) $value;
                        $hiddenId = is_array($value) && isset($value['id']) ? ' id="' . e((string) $value['id']) . '"' : '';
                        ?>
                        <input type="hidden" name="<?= e((string) $name); ?>" value="<?= e($hiddenValue); ?>"<?= $hiddenId; ?>>
                    <?php endforeach; ?>
            <?php endif; ?>

            <div class="modal-header">
                <div>
                    <?php if ($subtitle !== null): ?>
                        <p class="dashboard-kicker mb-1"><?= e((string) $subtitle); ?></p>
                    <?php endif; ?>
                    <h2 class="modal-title h5 mb-0" id="<?= e($labelId); ?>"><?= e($title); ?></h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <?= $body; ?>
            </div>

            <div class="modal-footer">
                <?= $footer; ?>
            </div>

            <?php if ($formAction !== null): ?>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
