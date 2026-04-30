<?php

declare(strict_types=1);
?>
<div class="row justify-content-center py-lg-5">
    <div class="col-md-8 col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-lg-5">
                <p class="text-secondary small fw-semibold mb-2">Authentication</p>
                <h1 class="h3 mb-3">Login</h1>
                <p class="text-secondary mb-4">Use your account to access the productivity dashboard.</p>

                <form action="<?= base_url('login'); ?>" method="post" class="row g-3" novalidate>
                    <?= \App\Helpers\Csrf::field(); ?>

                    <div class="col-12">
                        <label for="email" class="form-label">Email Address</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control<?= error('email') !== null || error('auth') !== null ? ' is-invalid' : ''; ?>"
                            value="<?= e((string) old('email')); ?>"
                            autocomplete="email"
                        >
                        <?php if (error('email') !== null): ?>
                            <div class="invalid-feedback"><?= e((string) error('email')); ?></div>
                        <?php elseif (error('auth') !== null): ?>
                            <div class="invalid-feedback"><?= e((string) error('auth')); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-12">
                        <label for="password" class="form-label">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control<?= error('password') !== null || error('auth') !== null ? ' is-invalid' : ''; ?>"
                            autocomplete="current-password"
                        >
                        <?php if (error('password') !== null): ?>
                            <div class="invalid-feedback"><?= e((string) error('password')); ?></div>
                        <?php elseif (error('auth') !== null): ?>
                            <div class="invalid-feedback"><?= e((string) error('auth')); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-12 d-grid">
                        <button type="submit" class="btn btn-dark btn-lg">Login</button>
                    </div>
                </form>

                <p class="text-secondary mt-4 mb-0">
                    Need an account?
                    <a href="<?= base_url('register'); ?>" class="link-dark fw-semibold">Register here</a>
                </p>
            </div>
        </div>
    </div>
</div>
