<?php

declare(strict_types=1);

$skipModalBody = capture(static function (): void {
    ?>
    <p class="text-secondary mb-3" id="skip-modal-task-title"></p>

    <div class="mb-3">
        <label for="skip_note" class="form-label">Alternative Work Note</label>
        <textarea
            id="skip_note"
            name="skip_note"
            rows="4"
            maxlength="1000"
            required
            class="form-control"
            placeholder="Explain what useful work was done during this planned time."
        ></textarea>
        <div class="invalid-feedback">Alternative work note is required for skipped_with_note.</div>
    </div>

    <p class="small text-secondary mb-0">Use this only when the planned task was skipped but another useful task replaced it.</p>
    <?php
});

$skipModalFooter = capture(static function (): void {
    ?>
    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
    <button type="submit" class="btn btn-primary">Save Skipped With Note</button>
    <?php
});
?>
<section class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <p class="dashboard-kicker mb-1">Fixed Task Tracking</p>
        <h1 class="h3 mb-1">Today's Fixed Tasks</h1>
        <p class="text-secondary mb-0"><?= e($todayLabel); ?>. Update planned fixed routine tasks inline and capture alternative work when skipped.</p>
    </div>
</section>

<section class="row g-4 mb-4">
    <div class="col-6 col-xl-3">
        <?php component('dashboard_stat_card', [
            'kicker' => 'Planned',
            'value' => (string) $summary['total_count'],
            'valueClass' => 'display-6 fw-bold mb-0',
        ]); ?>
    </div>
    <div class="col-6 col-xl-3">
        <?php component('dashboard_stat_card', [
            'kicker' => 'Done / Partial',
            'value' => (string) ($summary['done_count'] + $summary['partial_count']),
            'valueClass' => 'display-6 fw-bold mb-0',
        ]); ?>
    </div>
    <div class="col-6 col-xl-3">
        <?php component('dashboard_stat_card', [
            'kicker' => 'Skipped With Note',
            'value' => (string) $summary['skipped_with_note_count'],
            'valueClass' => 'display-6 fw-bold mb-0',
            'variant' => 'soft',
        ]); ?>
    </div>
    <div class="col-6 col-xl-3">
        <?php component('dashboard_stat_card', [
            'kicker' => 'Missed / Pending',
            'value' => (string) ($summary['missed_count'] + $summary['pending_count']),
            'valueClass' => 'display-6 fw-bold mb-0',
        ]); ?>
    </div>
</section>

<section class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <?php if ($tasks === []): ?>
            <div class="border rounded-4 p-5 bg-light-subtle text-center">
                <p class="mb-1 fw-semibold">No fixed tasks planned for today</p>
                <p class="text-secondary mb-0">Mark routine templates as fixed tasks or add standalone fixed tasks to track them here.</p>
            </div>
        <?php else: ?>
            <div class="vstack gap-3">
                <?php foreach ($tasks as $task): ?>
                    <?php $generalNoteInputId = 'general-note-' . $task['source_type'] . '-' . $task['source_id']; ?>
                    <div class="dashboard-list-item">
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex flex-column flex-xl-row justify-content-between gap-3">
                                <div class="flex-grow-1">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                        <h2 class="h5 mb-0"><?= e($task['title']); ?></h2>
                                        <?php component('status_badge', ['status' => (string) $task['status']]); ?>
                                        <span class="badge rounded-pill text-bg-light border text-dark">
                                            <?= e(ucwords(str_replace('_', ' ', (string) $task['category']))); ?>
                                        </span>
                                        <span class="badge rounded-pill text-bg-secondary"><?= e($task['source_label']); ?></span>
                                    </div>

                                    <div class="d-flex flex-wrap gap-3 text-secondary small mb-2">
                                        <span>
                                            Planned:
                                            <?= $task['planned_start_time'] !== null ? e(substr((string) $task['planned_start_time'], 0, 5)) : 'n/a'; ?>
                                            -
                                            <?= $task['planned_end_time'] !== null ? e(substr((string) $task['planned_end_time'], 0, 5)) : 'n/a'; ?>
                                        </span>
                                        <span>Date: <?= e($todayDate); ?></span>
                                    </div>

                                    <?php if (!empty($task['skip_note'])): ?>
                                        <p class="mb-1 text-primary small">
                                            <strong>Alternative work:</strong> <?= e((string) $task['skip_note']); ?>
                                        </p>
                                    <?php endif; ?>

                                    <?php if (!empty($task['general_note'])): ?>
                                        <p class="mb-0 text-secondary small">
                                            <strong>General note:</strong> <?= e((string) $task['general_note']); ?>
                                        </p>
                                    <?php elseif (!empty($task['description'])): ?>
                                        <p class="mb-0 text-secondary small">
                                            <strong>Description:</strong> <?= e((string) $task['description']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <form action="<?= base_url('fixed-task-tracking/update'); ?>" method="post" class="fixed-task-inline-form">
                                    <?= \App\Helpers\Csrf::field(); ?>
                                    <input type="hidden" name="source_type" value="<?= e((string) $task['source_type']); ?>">
                                    <input type="hidden" name="source_id" value="<?= e((string) $task['source_id']); ?>">

                                    <div class="mb-3">
                                        <label for="<?= e($generalNoteInputId); ?>" class="form-label small fw-semibold">General Note</label>
                                        <textarea
                                            id="<?= e($generalNoteInputId); ?>"
                                            name="general_note"
                                            rows="2"
                                            maxlength="1000"
                                            class="form-control form-control-sm"
                                            placeholder="Optional note for this fixed task"
                                        ><?= e((string) ($task['general_note'] ?? '')); ?></textarea>
                                    </div>

                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="submit" name="status" value="done" class="btn btn-sm btn-outline-success">Done</button>
                                        <button type="submit" name="status" value="partial" class="btn btn-sm btn-outline-warning">Partial</button>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary fixed-task-skip-button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#skipNoteModal"
                                            data-source-type="<?= e((string) $task['source_type']); ?>"
                                            data-source-id="<?= e((string) $task['source_id']); ?>"
                                            data-title="<?= e($task['title']); ?>"
                                            data-skip-note="<?= e((string) ($task['skip_note'] ?? '')); ?>"
                                            data-general-note-input-id="<?= e($generalNoteInputId); ?>"
                                        >
                                            Skipped With Note
                                        </button>
                                        <button type="submit" name="status" value="missed" class="btn btn-sm btn-outline-danger">Missed</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php component('modal', [
    'id' => 'skipNoteModal',
    'title' => 'Explain the alternative work',
    'subtitle' => 'Skipped With Note',
    'formAction' => base_url('fixed-task-tracking/update'),
    'formId' => 'skip-note-form',
    'hidden' => [
        'source_type' => ['value' => '', 'id' => 'skip-modal-source-type'],
        'source_id' => ['value' => '', 'id' => 'skip-modal-source-id'],
        'status' => 'skipped_with_note',
        'general_note' => ['value' => '', 'id' => 'skip-modal-general-note'],
    ],
    'body' => $skipModalBody,
    'footer' => $skipModalFooter,
]); ?>
