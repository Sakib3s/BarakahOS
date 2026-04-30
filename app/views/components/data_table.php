<?php

declare(strict_types=1);

$headers = is_array($headers ?? null) ? $headers : [];
$rows = is_array($rows ?? null) ? $rows : [];
$rowRenderer = $rowRenderer ?? null;
$emptyMessage = (string) ($emptyMessage ?? 'No records found.');
$tableClass = (string) ($tableClass ?? 'table align-middle mb-0');
$wrapperClass = (string) ($wrapperClass ?? 'table-responsive');
$emptyClass = (string) ($emptyClass ?? 'border rounded-4 p-4 bg-light-subtle text-center text-secondary');

if ($rows === []): ?>
    <div class="<?= e($emptyClass); ?>"><?= e($emptyMessage); ?></div>
<?php elseif (is_callable($rowRenderer)): ?>
    <div class="<?= e($wrapperClass); ?>">
        <table class="<?= e($tableClass); ?>">
            <?php if ($headers !== []): ?>
                <thead>
                    <tr>
                        <?php foreach ($headers as $header): ?>
                            <th><?= e((string) $header); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
            <?php endif; ?>
            <tbody>
                <?php foreach ($rows as $index => $row): ?>
                    <?php $rowRenderer($row, $index); ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
