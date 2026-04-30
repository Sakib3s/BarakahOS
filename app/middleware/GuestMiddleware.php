<?php

declare(strict_types=1);

namespace App\Middleware;

class GuestMiddleware
{
    public function handle(): void
    {
        if (!is_authenticated()) {
            return;
        }

        redirect_to('/');
    }
}
