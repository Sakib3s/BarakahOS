<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helpers\Flash;

class AuthMiddleware
{
    public function handle(): void
    {
        if (is_authenticated()) {
            return;
        }

        Flash::set('message', 'Please log in to access the dashboard.', 'warning');
        redirect_to('/login');
    }
}
