<?php

declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 | <?= e(config('app.name')); ?></title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-5 text-center">
                        <p class="text-secondary fw-semibold small mb-2">403 Error</p>
                        <h1 class="h3 mb-3">Request blocked</h1>
                        <p class="text-secondary mb-4"><?= e($message); ?></p>
                        <a href="<?= base_url('/'); ?>" class="btn btn-dark">Back to dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
