<?php

declare(strict_types=1);

$workspaceLinks = [
    ['path' => '/', 'label' => 'Dashboard', 'active' => is_current_path('/')],
    ['path' => 'routine-templates', 'label' => 'Routine Templates', 'active' => str_starts_with(current_path(), '/routine-templates')],
    ['path' => 'daily-checklist', 'label' => 'Daily Checklist', 'active' => str_starts_with(current_path(), '/daily-checklist')],
    ['path' => 'fixed-task-tracking', 'label' => 'Fixed Task Tracking', 'active' => str_starts_with(current_path(), '/fixed-task-tracking')],
    ['path' => 'focus-sessions', 'label' => 'Focus Sessions', 'active' => str_starts_with(current_path(), '/focus-sessions')],
    ['path' => 'distractions', 'label' => 'Distraction Tracking', 'active' => str_starts_with(current_path(), '/distractions')],
    ['path' => 'prayers', 'label' => 'Prayer Tracking', 'active' => str_starts_with(current_path(), '/prayers')],
    ['path' => 'sleep-tracker', 'label' => 'Sleep Tracker', 'active' => str_starts_with(current_path(), '/sleep-tracker')],
    ['path' => 'daily-review', 'label' => 'Daily Review', 'active' => str_starts_with(current_path(), '/daily-review')],
    ['path' => 'weekly-score', 'label' => 'Weekly Score', 'active' => str_starts_with(current_path(), '/weekly-score')],
    ['path' => 'reports/daily', 'label' => 'Reports', 'active' => str_starts_with(current_path(), '/reports')],
];
?>
<nav class="navbar navbar-expand-lg border-bottom bg-white sticky-top shadow-sm">
    <div class="container-fluid px-3 px-lg-4">
        <a class="navbar-brand fw-bold" href="<?= base_url(is_authenticated() ? '/' : 'login'); ?>">
            <img
                src="<?= asset_url('img/logo.png'); ?>"
                alt="<?= e((string) config('app.name')); ?>"
                class="navbar-logo"
            >
        </a>

        <div class="d-flex align-items-center gap-2 gap-lg-3">
            <span class="navbar-quote text-secondary small d-none d-xl-inline">Astaghfirullah - "Seek forgiveness of your Lord... He will send rain, increase wealth and children..."</span>
            <?php if (is_authenticated()): ?>
                <span class="small text-secondary d-none d-md-inline">Welcome back, <?= e((string) (current_user()['display_name'] ?? 'User')); ?>!</span>
                <form action="<?= base_url('logout'); ?>" method="post" class="mb-0 d-none d-sm-block">
                    <?= \App\Helpers\Csrf::field(); ?>
                    <button type="submit" class="btn btn-outline-dark btn-sm">Logout</button>
                </form>
                <button
                    class="navbar-toggler d-lg-none"
                    type="button"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#mobile-workspace-menu"
                    aria-controls="mobile-workspace-menu"
                    aria-label="Open navigation"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>
            <?php else: ?>
                <a href="<?= base_url('login'); ?>" class="btn btn-outline-dark btn-sm">Login</a>
                <a href="<?= base_url('register'); ?>" class="btn btn-dark btn-sm">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<?php if (is_authenticated()): ?>
    <div class="offcanvas offcanvas-start mobile-workspace-menu" tabindex="-1" id="mobile-workspace-menu" aria-labelledby="mobile-workspace-menu-title">
        <div class="offcanvas-header border-bottom">
            <div>
                <p class="text-secondary small fw-semibold mb-1">Workspace</p>
                <h2 class="h6 mb-0" id="mobile-workspace-menu-title"><?= e((string) config('app.name')); ?></h2>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close navigation"></button>
        </div>
        <div class="offcanvas-body">
            <nav class="list-group list-group-flush sidebar-nav">
                <?php foreach ($workspaceLinks as $link): ?>
                    <a
                        href="<?= base_url($link['path']); ?>"
                        class="list-group-item list-group-item-action border-0 rounded-3 mb-2<?= $link['active'] ? ' active' : ''; ?>"
                    >
                        <?= e($link['label']); ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <form action="<?= base_url('logout'); ?>" method="post" class="mt-4">
                <?= \App\Helpers\Csrf::field(); ?>
                <button type="submit" class="btn btn-outline-dark w-100">Logout</button>
            </form>
        </div>
    </div>
<?php endif; ?>
