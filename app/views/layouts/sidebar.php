<?php

declare(strict_types=1);
?>
<aside class="col-lg-2 d-none d-lg-block border-end bg-white min-vh-100 px-0">
    <div class="p-3">
        <p class="text-secondary small fw-semibold mb-3">Workspace</p>

        <div class="list-group list-group-flush sidebar-nav">
            <a href="<?= base_url('/'); ?>" class="list-group-item list-group-item-action border-0 rounded-3 mb-2<?= is_current_path('/') ? ' active' : ''; ?>">
                Dashboard
            </a>
            <a
                href="<?= base_url('routine-templates'); ?>"
                class="list-group-item list-group-item-action border-0 rounded-3 mb-2<?= str_starts_with(current_path(), '/routine-templates') ? ' active' : ''; ?>"
            >
                Routine Templates
            </a>
            <a
                href="<?= base_url('daily-checklist'); ?>"
                class="list-group-item list-group-item-action border-0 rounded-3 mb-2<?= str_starts_with(current_path(), '/daily-checklist') ? ' active' : ''; ?>"
            >
                Daily Checklist
            </a>
            <a
                href="<?= base_url('fixed-task-tracking'); ?>"
                class="list-group-item list-group-item-action border-0 rounded-3 mb-2<?= str_starts_with(current_path(), '/fixed-task-tracking') ? ' active' : ''; ?>"
            >
                Fixed Task Tracking
            </a>
            <a
                href="<?= base_url('focus-sessions'); ?>"
                class="list-group-item list-group-item-action border-0 rounded-3 mb-2<?= str_starts_with(current_path(), '/focus-sessions') ? ' active' : ''; ?>"
            >
                Focus Sessions
            </a>
            <a
                href="<?= base_url('distractions'); ?>"
                class="list-group-item list-group-item-action border-0 rounded-3 mb-2<?= str_starts_with(current_path(), '/distractions') ? ' active' : ''; ?>"
            >
                Distraction Tracking
            </a>
            <a
                href="<?= base_url('prayers'); ?>"
                class="list-group-item list-group-item-action border-0 rounded-3 mb-2<?= str_starts_with(current_path(), '/prayers') ? ' active' : ''; ?>"
            >
                Prayer Tracking
            </a>
            <a
                href="<?= base_url('sleep-tracker'); ?>"
                class="list-group-item list-group-item-action border-0 rounded-3 mb-2<?= str_starts_with(current_path(), '/sleep-tracker') ? ' active' : ''; ?>"
            >
                Sleep Tracker
            </a>
            <a
                href="<?= base_url('daily-review'); ?>"
                class="list-group-item list-group-item-action border-0 rounded-3 mb-2<?= str_starts_with(current_path(), '/daily-review') ? ' active' : ''; ?>"
            >
                Daily Review
            </a>
            <a
                href="<?= base_url('weekly-score'); ?>"
                class="list-group-item list-group-item-action border-0 rounded-3 mb-2<?= str_starts_with(current_path(), '/weekly-score') ? ' active' : ''; ?>"
            >
                Weekly Score
            </a>
            <a
                href="<?= base_url('reports/daily'); ?>"
                class="list-group-item list-group-item-action border-0 rounded-3 mb-2<?= str_starts_with(current_path(), '/reports') ? ' active' : ''; ?>"
            >
                Reports
            </a>
        </div>
    </div>
</aside>
