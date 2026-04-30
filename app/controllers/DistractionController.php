<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\HttpException;
use App\Models\DistractionLog;
use DateTimeImmutable;
use DateTimeZone;

class DistractionController extends BaseController
{
    public function index(): void
    {
        $userId = $this->userId();
        $now = $this->now();
        $today = $now->format('Y-m-d');
        $weekStart = $now->modify('monday this week');
        $weekEnd = $weekStart->modify('+6 days');
        $monthStart = $now->modify('first day of this month');
        $monthEnd = $now->modify('last day of this month');
        $model = new DistractionLog();
        $editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);

        $this->render('distractions/index', [
            'pageTitle' => 'Distraction Tracking',
            'todayLabel' => $now->format('l, d F Y'),
            'todayDate' => $today,
            'timezoneLabel' => (string) config('app.timezone', 'Asia/Dhaka'),
            'typeOptions' => DistractionLog::TYPES,
            'todayCounts' => $model->todayCounts($userId, $today),
            'weekCounts' => $model->weeklyCounts($userId, $weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')),
            'monthCounts' => $model->monthlyCounts($userId, $monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')),
            'todayLogs' => $model->todayLogsForUser($userId, $today),
            'editLog' => ($editId !== false && $editId !== null && $editId > 0) ? $model->findForUser($editId, $userId) : null,
            'weekLabel' => $weekStart->format('d M') . ' - ' . $weekEnd->format('d M Y'),
            'monthLabel' => $monthStart->format('F Y'),
        ]);
    }

    public function store(): void
    {
        Csrf::ensureValid($_POST['_token'] ?? null);

        with_old_input($_POST);
        $payload = $this->sanitize($_POST);
        $errors = $this->validate($payload);

        if ($errors !== []) {
            with_errors($errors);
            Flash::set('message', 'Please fix the highlighted fields.', 'danger');
            $this->redirect('/distractions');
        }

        $model = new DistractionLog();
        $model->create($this->userId(), $payload);

        clear_old_input();
        clear_errors();

        $this->redirect('/distractions', 'Distraction event logged.', 'success');
    }

    public function update(): void
    {
        Csrf::ensureValid($_POST['_token'] ?? null);

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if ($id === false || $id === null || $id < 1) {
            throw new HttpException('Invalid distraction log id.', 404);
        }

        $model = new DistractionLog();

        if ($model->findForUser($id, $this->userId()) === null) {
            throw new HttpException('Distraction log not found.', 404);
        }

        with_old_input($_POST);
        $payload = $this->sanitize($_POST);
        $errors = $this->validate($payload);

        if ($errors !== []) {
            with_errors($errors);
            Flash::set('message', 'Please fix the highlighted fields.', 'danger');
            $this->redirect('/distractions?edit=' . (string) $id);
        }

        $model->update($id, $this->userId(), $payload);

        clear_old_input();
        clear_errors();

        $this->redirect('/distractions', 'Distraction event updated.');
    }

    public function delete(): void
    {
        Csrf::ensureValid($_POST['_token'] ?? null);

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if ($id === false || $id === null || $id < 1) {
            throw new HttpException('Invalid distraction log id.', 404);
        }

        (new DistractionLog())->deleteForUser($id, $this->userId());

        $this->redirect('/distractions', 'Distraction event deleted.');
    }

    private function sanitize(array $input): array
    {
        $now = $this->now();
        $durationHours = $this->integerInput($input['duration_hours'] ?? 0);
        $durationMinutes = $this->integerInput($input['duration_minutes'] ?? 0);

        return [
            'distraction_type' => trim((string) ($input['distraction_type'] ?? '')),
            'occurred_at' => $now->format('Y-m-d H:i:s'),
            'log_date' => $now->format('Y-m-d'),
            'duration_hours' => $durationHours,
            'duration_minutes_part' => $durationMinutes,
            'duration_minutes' => ($durationHours * 60) + $durationMinutes,
            'note' => $this->nullableString($input['note'] ?? null),
            'focus_session_id' => null,
        ];
    }

    private function validate(array $payload): array
    {
        $errors = [];

        if (!in_array($payload['distraction_type'], DistractionLog::TYPES, true)) {
            $errors['distraction_type'] = 'Choose a valid distraction type.';
        }

        if ($payload['duration_hours'] < 0 || $payload['duration_hours'] > 24) {
            $errors['duration_hours'] = 'Hours must be between 0 and 24.';
        }

        if ($payload['duration_minutes_part'] < 0 || $payload['duration_minutes_part'] > 59) {
            $errors['duration_minutes'] = 'Minutes must be between 0 and 59.';
        }

        if ($payload['duration_minutes'] < 1 || $payload['duration_minutes'] > 1440) {
            $errors['duration_minutes'] = 'Enter a waste time duration from 1 minute to 24 hours.';
        }

        if ($payload['note'] !== null && mb_strlen($payload['note']) > 1000) {
            $errors['note'] = 'Note must be 1000 characters or fewer.';
        }

        return $errors;
    }

    private function integerInput(mixed $value): int
    {
        $value = trim((string) $value);

        if ($value === '') {
            return 0;
        }

        if (!ctype_digit($value)) {
            return -1;
        }

        return (int) $value;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function now(): DateTimeImmutable
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
}
