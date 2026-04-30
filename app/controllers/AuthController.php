<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Models\User;

class AuthController extends BaseController
{
    public function showRegister(): void
    {
        $this->render('auth/register', [
            'pageTitle' => 'Register',
            'showSidebar' => false,
        ]);
    }

    public function register(): void
    {
        Csrf::ensureValid($_POST['_token'] ?? null);

        $displayName = trim((string) ($_POST['display_name'] ?? ''));
        $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');

        with_old_input($_POST);

        $errors = [];
        $userModel = new User();

        if ($displayName === '') {
            $errors['display_name'] = 'Display name is required.';
        } elseif (mb_strlen($displayName) > 120) {
            $errors['display_name'] = 'Display name must be 120 characters or fewer.';
        }

        if ($email === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        } elseif ($userModel->emailExists($email)) {
            $errors['email'] = 'That email address is already registered.';
        }

        if ($password === '') {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }

        if ($passwordConfirmation === '') {
            $errors['password_confirmation'] = 'Please confirm the password.';
        } elseif (!hash_equals($password, $passwordConfirmation)) {
            $errors['password_confirmation'] = 'Password confirmation does not match.';
        }

        if ($errors !== []) {
            with_errors($errors);
            Flash::set('message', 'Please fix the highlighted fields.', 'danger');
            $this->redirect('/register', null, 'danger');
        }

        $userId = $userModel->create([
            'display_name' => $displayName,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'timezone' => 'Asia/Dhaka',
        ]);

        clear_old_input();
        clear_errors();
        login_user($userId);

        $this->redirect('/', 'Your account has been created.');
    }

    public function showLogin(): void
    {
        $this->render('auth/login', [
            'pageTitle' => 'Login',
            'showSidebar' => false,
        ]);
    }

    public function login(): void
    {
        Csrf::ensureValid($_POST['_token'] ?? null);

        $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');

        with_old_input($_POST);

        $errors = [];

        if ($email === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }

        if ($password === '') {
            $errors['password'] = 'Password is required.';
        }

        if ($errors !== []) {
            with_errors($errors);
            Flash::set('message', 'Please fix the highlighted fields.', 'danger');
            $this->redirect('/login', null, 'danger');
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (
            $user === null
            || !(bool) $user['is_active']
            || !password_verify($password, (string) $user['password_hash'])
        ) {
            with_errors([
                'auth' => 'Invalid email or password.',
            ]);
            Flash::set('message', 'Invalid email or password.', 'danger');
            $this->redirect('/login', null, 'danger');
        }

        clear_old_input();
        clear_errors();
        login_user((int) $user['id']);

        $this->redirect('/', 'Welcome back.');
    }

    public function logout(): void
    {
        Csrf::ensureValid($_POST['_token'] ?? null);

        logout_user();

        $this->redirect('/login', 'You have been logged out.', 'info');
    }
}
