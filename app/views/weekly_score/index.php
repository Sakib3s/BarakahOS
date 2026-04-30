<?php

declare(strict_types=1);
?>
<section class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <p class="dashboard-kicker mb-1">Weekly Discipline Score</p>
        <h1 class="h3 mb-1"><?= e($score['label']); ?></h1>
        <p class="text-secondary mb-0"><?= e($rangeLabel); ?>. Sleep, prayer, focus, distractions, and task completion in one score.</p>
    </div>
    <span class="badge rounded-pill text-bg-dark px-3 py-2"><?= e((string) $score['overall']); ?>/100</span>
</section>

<section class="card border-0 shadow-sm mb-4 dashboard-card-soft">
    <div class="card-body p-4 p-lg-5">
        <div class="row g-4 align-items-center">
            <div class="col-md-4">
                <p class="dashboard-kicker mb-1">Overall</p>
                <h2 class="display-3 fw-bold mb-0"><?= e((string) $score['overall']); ?></h2>
            </div>
            <div class="col-md-8">
                <div class="progress dashboard-progress" role="progressbar" aria-label="Weekly discipline score">
                    <div class="progress-bar bg-success" style="width: <?= e((string) $score['overall']); ?>%;"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="row g-4">
    <?php foreach ($score['factors'] as $factor): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100 dashboard-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <p class="dashboard-kicker mb-1"><?= e((string) $factor['label']); ?></p>
                            <h2 class="h5 mb-0"><?= e((string) $factor['score']); ?>/100</h2>
                        </div>
                        <span class="badge rounded-pill text-bg-dark"><?= e((string) $factor['score']); ?></span>
                    </div>
                    <p class="text-secondary mb-3"><?= e((string) $factor['detail']); ?></p>
                    <div class="progress dashboard-progress" role="progressbar" aria-label="<?= e((string) $factor['label']); ?> score">
                        <div class="progress-bar bg-success" style="width: <?= e((string) $factor['score']); ?>%;"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</section>
