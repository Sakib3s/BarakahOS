<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\HttpException;
use App\Models\Dashboard;
use App\Models\Task;
use DateTimeImmutable;
use DateTimeZone;

class HomeController extends BaseController
{
    public function index(): void
    {
        $userId = auth_user_id();
        $timezone = (string) config('app.timezone', 'Asia/Dhaka');
        $today = new DateTimeImmutable('now', new DateTimeZone($timezone));
        $dashboardModel = new Dashboard();
        $heroContent = $this->heroContentForHour((int) $today->format('G'));

        $this->render('home/index', [
            'pageTitle' => 'Dashboard',
            'todayDate' => $today->format('Y-m-d'),
            'todayLabel' => $today->format('l, d F Y'),
            'timezoneLabel' => $timezone,
            'heroTheme' => $heroContent['theme'],
            'heroTitle' => $heroContent['title'],
            'heroMessage' => $heroContent['message'],
            'summary' => $dashboardModel->getSummary($userId, $today->format('Y-m-d')),
            'fixedTaskStatuses' => $dashboardModel->getFixedTaskStatuses($userId, $today->format('Y-m-d')),
            'recentTasks' => $dashboardModel->getRecentTasks($userId, null),
        ]);
    }

    public function store(): void
    {
        Csrf::ensureValid($_POST['_token'] ?? null);

        $title = trim((string) ($_POST['title'] ?? ''));
        with_old_input($_POST);

        if ($title === '') {
            with_errors([
                'title' => 'Task title is required.',
            ]);
            Flash::set('message', 'Task title is required.', 'danger');
            $this->redirect('/');
        }

        $taskModel = new Task();
        $taskModel->create(auth_user_id(), $title);

        clear_old_input();
        clear_errors();
        $this->redirect('/', 'Task added successfully.');
    }

    public function complete(): void
    {
        Csrf::ensureValid($_POST['_token'] ?? null);

        $taskId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if ($taskId === false || $taskId === null || $taskId < 1) {
            throw new HttpException('Invalid task id.', 404);
        }

        $taskModel = new Task();
        $task = $taskModel->findForUser($taskId, auth_user_id());

        if ($task === null) {
            throw new HttpException('Task not found.', 404);
        }

        if ((string) $task['status'] === 'completed') {
            $this->redirect('/', 'Task is already completed.', 'info');
        }

        $taskModel->markCompleted($taskId, auth_user_id());

        $this->redirect('/', 'Task marked as done.');
    }

    public function deleteTask(): void
    {
        Csrf::ensureValid($_POST['_token'] ?? null);

        $taskId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if ($taskId === false || $taskId === null || $taskId < 1) {
            throw new HttpException('Invalid task id.', 404);
        }

        $taskModel = new Task();
        $task = $taskModel->findForUser($taskId, auth_user_id());

        if ($task === null) {
            throw new HttpException('Task not found.', 404);
        }

        $taskModel->deleteForUser($taskId, auth_user_id());

        $this->redirect('/', 'Task deleted.');
    }

    private function heroContentForHour(int $hour): array
    {
        if ($hour >= 5 && $hour < 11) {
            return [
                'theme' => 'morning',
                'title' => '🌅 Work with focus. Allah sees your effort.',
                'message' => 'Bismillah — A new day, a new chance to do better.',
            ];
        }

        if ($hour >= 11 && $hour < 18) {
            return [
                'theme' => 'work',
                'title' => '☀️ Work time',
                'message' => 'Stay focused. Execute your plan. Allah sees your effort.',
            ];
        }

        return [
            'theme' => 'night',
            'title' => '🌙 Night work+review',
            'message' => 'Review your day. Seek forgiveness. Plan better for tomorrow.',
        ];
    }
}
