<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Flash;
use App\HttpException;

abstract class BaseController
{
    protected function render(string $view, array $data = []): void
    {
        $viewFile = APP_PATH . '/views/' . $view . '.php';

        if (!is_file($viewFile)) {
            throw new HttpException('The requested view could not be loaded.', 500);
        }

        extract($data, EXTR_SKIP);

        $pageTitle = $data['pageTitle'] ?? config('app.name', 'Focus Ledger');
        $showSidebar = $data['showSidebar'] ?? is_authenticated();

        require APP_PATH . '/views/layouts/app.php';

        clear_old_input();
        clear_errors();
    }

    protected function redirect(string $path, ?string $message = null, string $type = 'success'): never
    {
        if ($message !== null) {
            Flash::set('message', $message, $type);
        }

        redirect_to($path);
    }
}
