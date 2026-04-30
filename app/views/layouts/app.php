<?php

declare(strict_types=1);

use App\Helpers\Flash;

$flashes = Flash::all();

require APP_PATH . '/views/layouts/header.php';
require APP_PATH . '/views/layouts/navbar.php';
?>
<div class="container-fluid">
    <div class="row">
        <?php if ($showSidebar): ?>
            <?php require APP_PATH . '/views/layouts/sidebar.php'; ?>
        <?php endif; ?>

        <main class="<?= $showSidebar ? 'col-lg-10 ms-sm-auto px-3 px-md-4 py-3 py-md-4' : 'col-12 px-3 px-md-4 py-3 py-md-4'; ?>">
            <?php component('alert_messages', ['alerts' => $flashes]); ?>

            <?php require $viewFile; ?>
        </main>
    </div>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>
