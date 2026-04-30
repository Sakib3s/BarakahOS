<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\HttpException;
use App\Models\FixedTaskTracker;
use DateTimeImmutable;
use DateTimeZone;

class FixedTaskTrackingController extends BaseController
{
    public function index(): void
    {
        $today = $this->today();
        $weekday = strtolower($today->format('l'));
        $model = new FixedTaskTracker();
        $tasks = $model->getTodayPlannedTasks($this->userId(), $today->format('Y-m-d'), $weekday);

        $this->render('fixed_tasks/index', [
            'pageTitle' => 'Fixed Task Tracking',
            'todayLabel' => $today->format('l, d F Y'),
            'todayDate' => $today->format('Y-m-d'),
            'tasks' => $tasks,
            'summary' => $model->summarize($tasks),
            'statusOptions' => FixedTaskTracker::STATUSES,
        ]);
    }

    public function update(): void
    {
        Csrf::ensureValid($_POST['_token'] ?? null);

        $sourceType = trim((string) ($_POST['source_type'] ?? ''));
        $sourceId = filter_input(INPUT_POST, 'source_id', FILTER_VALIDATE_INT);
        $status = trim((string) ($_POST['status'] ?? ''));
        $generalNote = $this->nullableString($_POST['general_note'] ?? null);
        $skipNote = $this->nullableString($_POST['skip_note'] ?? null);

        if ($sourceId === false || $sourceId === null || $sourceId < 1) {
            throw new HttpException('Invalid fixed task source id.', 404);
        }

        if (!in_array($sourceType, ['routine_template', 'fixed_task'], true)) {
            throw new HttpException('Invalid fixed task source type.', 422);
        }

        if (!in_array($status, FixedTaskTracker::STATUSES, true)) {
            throw new HttpException('Invalid fixed task status.', 422);
        }

        $errors = [];

        if ($status === 'skipped_with_note' && $skipNote === null) {
            $errors[] = 'Skip note is required when marking a fixed task as skipped with note.';
        }

        if ($status !== 'skipped_with_note') {
            $skipNote = null;
        }

        if ($skipNote !== null && mb_strlen($skipNote) > 1000) {
            $errors[] = 'Skip note must be 1000 characters or fewer.';
        }

        if ($generalNote !== null && mb_strlen($generalNote) > 1000) {
            $errors[] = 'General note must be 1000 characters or fewer.';
        }

        if ($errors !== []) {
            Flash::set('message', implode(' ', $errors), 'danger');
            $this->redirect('/fixed-task-tracking');
        }

        $today = $this->today();
        $model = new FixedTaskTracker();
        $task = $model->findPlannedTaskForToday(
            $this->userId(),
            $today->format('Y-m-d'),
            strtolower($today->format('l')),
            $sourceType,
            $sourceId
        );

        if ($task === null) {
            throw new HttpException('Fixed task not found for today.', 404);
        }

        $model->saveLog(
            $this->userId(),
            $task,
            $today->format('Y-m-d'),
            $status,
            $skipNote,
            $generalNote
        );

        $this->redirect('/fixed-task-tracking', 'Fixed task status updated.', 'info');
    }

    private function today(): DateTimeImmutable
    {
        return new DateTimeImmutable(
            'now',
            new DateTimeZone((string) config('app.timezone', 'Asia/Dhaka'))
        );
    }

    private function userId(): int
    {
        $userId = auth_user_id();

        if ($userId === null) {
            throw new HttpException('Authentication is required.', 403);
        }

        return $userId;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
