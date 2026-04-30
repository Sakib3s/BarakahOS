<?php

declare(strict_types=1);
?>
<div class="row justify-content-center py-lg-5">
    <div class="col-md-9 col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-lg-5">
                <p class="text-secondary small fw-semibold mb-2">Authentication</p>
                <h1 class="h3 mb-3">Create Account</h1>
                <p class="text-secondary mb-4">Start with a single-user account and keep the structure ready for expansion.</p>

                <form action="<?= base_url('register'); ?>" method="post" class="row g-3" novalidate>
                    <?= \App\Helpers\Csrf::field(); ?>

                    <div class="col-12">
                        <label for="display_name" class="form-label">Display Name</label>
                        <input
                            type="text"
                            id="display_name"
                            name="display_name"
                            class="form-control<?= error('display_name') !== null ? ' is-invalid' : ''; ?>"
                            value="<?= e((string) old('display_name')); ?>"
                            autocomplete="name"
                        >
                        <?php if (error('display_name') !== null): ?>
                            <div class="invalid-feedback"><?= e((string) error('display_name')); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-12">
                        <label for="email" class="form-label">Email Address</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control<?= error('email') !== null ? ' is-invalid' : ''; ?>"
                            value="<?= e((string) old('email')); ?>"
                            autocomplete="email"
                        >
                        <?php if (error('email') !== null): ?>
                            <div class="invalid-feedback"><?= e((string) error('email')); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label for="password" class="form-label">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control<?= error('password') !== null ? ' is-invalid' : ''; ?>"
                            autocomplete="new-password"
                        >
                        <?php if (error('password') !== null): ?>
                            <div class="invalid-feedback"><?= e((string) error('password')); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            class="form-control<?= error('password_confirmation') !== null ? ' is-invalid' : ''; ?>"
                            autocomplete="new-password"
                        >
                        <?php if (error('password_confirmation') !== null): ?>
                            <div class="invalid-feedback"><?= e((string) error('password_confirmation')); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-12 d-grid">
                        <button type="submit" class="btn btn-dark btn-lg">Register</button>
                    </div>
                </form>

                <p class="text-secondary mt-4 mb-0">
                    Already have an account?
                    <a href="<?= base_url('login'); ?>" class="link-dark fw-semibold">Login here</a>
                </p>
            </div>
        </div>
    </div>
</div>
