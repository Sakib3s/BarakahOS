<?php

declare(strict_types=1);

$reportSections = [
    [
        'title' => 'Today',
        'subtitle' => $todayLabel,
        'counts' => $todayCounts,
    ],
    [
        'title' => 'This Week',
        'subtitle' => $weekLabel,
        'counts' => $weekCounts,
    ],
    [
        'title' => 'This Month',
        'subtitle' => $monthLabel,
        'counts' => $monthCounts,
    ],
];
$typeCountKeys = [
    'mobile_used' => 'mobile_used_count',
    'social_media_used' => 'social_media_used_count',
    'waste_time' => 'waste_time_count',
];
$editingDistraction = $editLog !== null;
$editDurationHours = $editingDistraction ? intdiv((int) $editLog['duration_minutes'], 60) : 0;
$editDurationMinutes = $editingDistraction ? ((int) $editLog['duration_minutes'] % 60) : 5;
?>
<section class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <p class="dashboard-kicker mb-1">Distraction Tracking</p>
        <h1 class="h3 mb-1">Daily Discipline Failures</h1>
        <p class="text-secondary mb-0">Log mobile use, social media, and wasted time with duration for daily review.</p>
    </div>
    <span class="badge rounded-pill text-bg-dark px-3 py-2"><?= e($timezoneLabel); ?></span>
</section>

<section class="row g-4 mb-4">
    <div class="col-xl-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="mb-3">
                    <p class="dashboard-kicker mb-1">Add Distraction Log</p>
                    <h2 class="h5 mb-0"><?= $editingDistraction ? 'Edit what happened' : 'Record what happened'; ?></h2>
                </div>

                <form action="<?= base_url($editingDistraction ? 'distractions/update' : 'distractions'); ?>" method="post" class="row g-3">
                    <?= \App\Helpers\Csrf::field(); ?>
                    <?php if ($editingDistraction): ?>
                        <input type="hidden" name="id" value="<?= e((string) $editLog['id']); ?>">
                    <?php endif; ?>

                    <div class="col-12">
                        <label for="distraction_type" class="form-label">Type</label>
                        <select
                            id="distraction_type"
                            name="distraction_type"
                            class="form-select<?= error('distraction_type') !== null ? ' is-invalid' : ''; ?>"
                            required
                        >
                            <option value="">Select a type</option>
                            <?php foreach ($typeOptions as $typeOption): ?>
                                <option value="<?= e($typeOption); ?>" <?= old('distraction_type', $editLog['distraction_type'] ?? '') === $typeOption ? 'selected' : ''; ?>>
                                    <?= e(distraction_type_label($typeOption)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (error('distraction_type') !== null): ?>
                            <div class="invalid-feedback"><?= e((string) error('distraction_type')); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label for="duration_hours" class="form-label">Hours</label>
                        <input
                            type="number"
                            id="duration_hours"
                            name="duration_hours"
                            class="form-control<?= error('duration_hours') !== null ? ' is-invalid' : ''; ?>"
                            value="<?= e((string) old('duration_hours', (string) $editDurationHours)); ?>"
                            min="0"
                            max="24"
                            required
                        >
                        <?php if (error('duration_hours') !== null): ?>
                            <div class="invalid-feedback"><?= e((string) error('duration_hours')); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label for="duration_minutes" class="form-label">Minutes</label>
                        <input
                            type="number"
                            id="duration_minutes"
                            name="duration_minutes"
                            class="form-control<?= error('duration_minutes') !== null ? ' is-invalid' : ''; ?>"
                            value="<?= e((string) old('duration_minutes', (string) $editDurationMinutes)); ?>"
                            min="0"
                            max="59"
                            required
                        >
                        <?php if (error('duration_minutes') !== null): ?>
                            <div class="invalid-feedback"><?= e((string) error('duration_minutes')); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-12">
                        <label for="note" class="form-label">Note</label>
                        <textarea
                            id="note"
                            name="note"
                            rows="4"
                            class="form-control<?= error('note') !== null ? ' is-invalid' : ''; ?>"
                            placeholder="Optional context about what happened."
                        ><?= e((string) old('note', $editLog['note'] ?? '')); ?></textarea>
                        <?php if (error('note') !== null): ?>
                            <div class="invalid-feedback"><?= e((string) error('note')); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-12 d-flex justify-content-end">
                        <?php if ($editingDistraction): ?>
                            <a href="<?= base_url('distractions'); ?>" class="btn btn-outline-secondary me-2">Cancel</a>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-dark"><?= $editingDistraction ? 'Update Distraction Log' : 'Save Distraction Log'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-7">
        <div class="row g-4">
            <?php foreach ($reportSections as $section): ?>
                <div class="col-md-6 col-xxl-4">
                    <div class="card border-0 shadow-sm h-100 dashboard-card">
                        <div class="card-body p-4">
                            <p class="dashboard-kicker mb-1"><?= e($section['title']); ?></p>
                            <h2 class="h4 mb-1"><?= e((string) $section['counts']['total_count']); ?> events</h2>
                            <p class="text-secondary small mb-3">
                                <?= e($section['subtitle']); ?>.
                                <?= e(format_duration((int) $section['counts']['total_duration_minutes'])); ?> wasted.
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($typeCountKeys as $type => $key): ?>
                                    <span class="badge rounded-pill <?= distraction_badge_class($type); ?>">
                                        <?= e(distraction_type_label($type)); ?> <?= e((string) $section['counts'][$key]); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
            <div>
                <p class="dashboard-kicker mb-1">Today’s Logs</p>
                <h2 class="h5 mb-0"><?= e($todayLabel); ?></h2>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($typeCountKeys as $type => $key): ?>
                    <span class="badge rounded-pill <?= distraction_badge_class($type); ?>">
                        <?= e(distraction_type_label($type)); ?> <?= e((string) $todayCounts[$key]); ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($todayLogs === []): ?>
            <div class="border rounded-4 p-5 bg-light-subtle text-center">
                <p class="mb-1 fw-semibold">No distraction events logged today</p>
                <p class="text-secondary mb-0">Use the form above when you want to capture discipline failures for reporting.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Time</th>
                            <th scope="col">Type</th>
                            <th scope="col">Duration</th>
                            <th scope="col">Note</th>
                            <th scope="col" class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($todayLogs as $log): ?>
                            <tr>
                                <td class="fw-semibold"><?= e(date('h:i A', strtotime((string) $log['occurred_at']))); ?></td>
                                <td>
                                    <span class="badge rounded-pill <?= distraction_badge_class((string) $log['distraction_type']); ?>">
                                        <?= e(distraction_type_label((string) $log['distraction_type'])); ?>
                                    </span>
                                </td>
                                <td><?= e(format_duration((int) $log['duration_minutes'])); ?></td>
                                <td class="text-secondary">
                                    <?= $log['note'] !== null && $log['note'] !== '' ? nl2br(e((string) $log['note'])) : '<span class="text-muted">No note</span>'; ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="<?= base_url('distractions?edit=' . (string) $log['id']); ?>" class="btn btn-sm btn-outline-dark">Edit</a>
                                        <form action="<?= base_url('distractions/delete'); ?>" method="post" class="d-inline">
                                            <?= \App\Helpers\Csrf::field(); ?>
                                            <input type="hidden" name="id" value="<?= e((string) $log['id']); ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
