<?php

declare(strict_types=1);

$reviewData = $review ?? [
    'day_rating' => null,
    'what_went_well' => null,
    'what_failed' => null,
    'top_lesson' => null,
    'tomorrow_priority' => null,
    'sleep_note' => null,
];
?>
<section class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <p class="dashboard-kicker mb-1">Daily Review</p>
        <h1 class="h3 mb-1"><?= $review === null ? 'Create today\'s review' : 'Edit today\'s review'; ?></h1>
        <p class="text-secondary mb-0"><?= e($todayLabel); ?> in <?= e($timezoneLabel); ?>. One review is stored per day.</p>
    </div>
    <span class="badge rounded-pill text-bg-dark px-3 py-2"><?= e($todayDate); ?></span>
</section>

<section class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 dashboard-card">
            <div class="card-body p-4">
                <p class="dashboard-kicker mb-1">Focus Today</p>
                <h2 class="h4 mb-1"><?= e(format_duration((int) $dailySummary['focus']['total_focus_minutes'])); ?></h2>
                <p class="text-secondary mb-0"><?= e((string) $dailySummary['focus']['total_sessions']); ?> sessions.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 dashboard-card">
            <div class="card-body p-4">
                <p class="dashboard-kicker mb-1">Distractions Today</p>
                <h2 class="h4 mb-1"><?= e(format_duration((int) $dailySummary['distraction']['total_duration_minutes'])); ?></h2>
                <p class="text-secondary mb-0"><?= e((string) $dailySummary['distraction']['total_count']); ?> events.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 dashboard-card-soft">
            <div class="card-body p-4">
                <p class="dashboard-kicker mb-1">Sleep Today</p>
                <h2 class="h4 mb-1">
                    <?= $dailySummary['sleep'] !== null ? e(format_duration((int) $dailySummary['sleep']['duration_minutes'])) : '0m'; ?>
                </h2>
                <p class="text-secondary mb-0"><?= $dailySummary['sleep'] !== null ? 'Logged by wake-up date.' : 'No sleep log.'; ?></p>
            </div>
        </div>
    </div>
</section>

<section class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="<?= base_url('daily-review/save'); ?>" method="post" class="row g-3">
            <?= \App\Helpers\Csrf::field(); ?>
            <input type="hidden" name="review_date" value="<?= e($todayDate); ?>">

            <div class="col-md-4">
                <label for="day_rating" class="form-label">Day Rating</label>
                <input
                    type="number"
                    min="1"
                    max="10"
                    id="day_rating"
                    name="day_rating"
                    class="<?= e(field_class('day_rating', 'form-control')); ?>"
                    value="<?= e((string) old('day_rating', (string) ($reviewData['day_rating'] ?? ''))); ?>"
                    required
                >
                <?php component('form_error', ['field' => 'day_rating']); ?>
            </div>

            <div class="col-12">
                <label for="what_went_well" class="form-label">What Went Well</label>
                <textarea
                    id="what_went_well"
                    name="what_went_well"
                    rows="4"
                    class="<?= e(field_class('what_went_well', 'form-control')); ?>"
                    required
                ><?= e((string) old('what_went_well', $reviewData['what_went_well'] ?? '')); ?></textarea>
                <?php component('form_error', ['field' => 'what_went_well']); ?>
            </div>

            <div class="col-12">
                <label for="what_failed" class="form-label">What Failed</label>
                <textarea
                    id="what_failed"
                    name="what_failed"
                    rows="4"
                    class="<?= e(field_class('what_failed', 'form-control')); ?>"
                    required
                ><?= e((string) old('what_failed', $reviewData['what_failed'] ?? '')); ?></textarea>
                <?php component('form_error', ['field' => 'what_failed']); ?>
            </div>

            <div class="col-md-6">
                <label for="top_lesson" class="form-label">Top Lesson</label>
                <textarea
                    id="top_lesson"
                    name="top_lesson"
                    rows="3"
                    class="<?= e(field_class('top_lesson', 'form-control')); ?>"
                    required
                ><?= e((string) old('top_lesson', $reviewData['top_lesson'] ?? '')); ?></textarea>
                <?php component('form_error', ['field' => 'top_lesson']); ?>
            </div>

            <div class="col-md-6">
                <label for="tomorrow_priority" class="form-label">Tomorrow Priority</label>
                <textarea
                    id="tomorrow_priority"
                    name="tomorrow_priority"
                    rows="3"
                    class="<?= e(field_class('tomorrow_priority', 'form-control')); ?>"
                    required
                ><?= e((string) old('tomorrow_priority', $reviewData['tomorrow_priority'] ?? '')); ?></textarea>
                <?php component('form_error', ['field' => 'tomorrow_priority']); ?>
            </div>

            <div class="col-12">
                <label for="sleep_note" class="form-label">Sleep Note</label>
                <textarea
                    id="sleep_note"
                    name="sleep_note"
                    rows="3"
                    class="<?= e(field_class('sleep_note', 'form-control')); ?>"
                ><?= e((string) old('sleep_note', $reviewData['sleep_note'] ?? '')); ?></textarea>
                <?php component('form_error', ['field' => 'sleep_note']); ?>
            </div>

            <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-dark"><?= $review === null ? 'Save Daily Review' : 'Update Daily Review'; ?></button>
            </div>
        </form>
    </div>
</section>
