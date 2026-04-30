<?php

declare(strict_types=1);
?>
<section class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <p class="dashboard-kicker mb-1">Routine Templates</p>
        <h1 class="h3 mb-1">Recurring Routine Blocks</h1>
        <p class="text-secondary mb-0">Define recurring blocks for prayer, planning, trading, coding, and the rest of your day.</p>
    </div>
    <a href="<?= base_url('routine-templates/create'); ?>" class="btn btn-dark">Create Template</a>
</section>

<section class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <?php if ($templates === []): ?>
            <div class="border rounded-4 p-5 bg-light-subtle text-center">
                <p class="mb-1 fw-semibold">No routine templates yet</p>
                <p class="text-secondary mb-3">Create your first recurring routine block to start structuring the day.</p>
                <a href="<?= base_url('routine-templates/create'); ?>" class="btn btn-dark">Create Template</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Time Block</th>
                            <th>Active Days</th>
                            <th>Fixed Task</th>
                            <th>Order</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($templates as $template): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($template['title']); ?></td>
                                <td>
                                    <span class="badge rounded-pill text-bg-light border text-dark">
                                        <?= e(ucwords(str_replace('_', ' ', $template['category']))); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($template['start_time'] === null || $template['end_time'] === null): ?>
                                        <span class="badge rounded-pill text-bg-light border text-dark">Any Time</span>
                                    <?php else: ?>
                                        <?= e(substr((string) $template['start_time'], 0, 5)); ?> - <?= e(substr((string) $template['end_time'], 0, 5)); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php foreach ($template['active_days'] as $day): ?>
                                            <span class="badge rounded-pill text-bg-secondary"><?= e($weekdays[$day] ?? $day); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge rounded-pill <?= $template['is_fixed_task'] ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                                        <?= $template['is_fixed_task'] ? 'Yes' : 'No'; ?>
                                    </span>
                                </td>
                                <td><?= e((string) $template['sort_order']); ?></td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <a
                                            href="<?= base_url('routine-templates/edit?id=' . $template['id']); ?>"
                                            class="btn btn-sm btn-outline-dark"
                                        >
                                            Edit
                                        </a>
                                        <form action="<?= base_url('routine-templates/delete'); ?>" method="post" class="d-inline">
                                            <?= \App\Helpers\Csrf::field(); ?>
                                            <input type="hidden" name="id" value="<?= e((string) $template['id']); ?>">
                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Delete this routine template?');"
                                            >
                                                Delete
                                            </button>
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
