<?php

declare(strict_types=1);

$field = (string) ($field ?? '');
$message = $field === '' ? null : error($field);

if ($message === null) {
    return;
}

$tag = (string) ($tag ?? 'div');
$class = (string) ($class ?? 'invalid-feedback');
?>
<?= '<' . $tag; ?> class="<?= e($class); ?>"><?= e((string) $message); ?><?= '</' . $tag . '>'; ?>
