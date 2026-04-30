<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\HttpException;
use App\Models\PrayerLog;
use DateTimeImmutable;
use DateTimeZone;

class PrayerController extends BaseController
{
    public function index(): void
    {
        $userId = $this->userId();
        $now = $this->now();
        $today = $now->format('Y-m-d');
        $weekStart = $now->modify('monday this week');
        $weekEnd = $weekStart->modify('+6 days');
        $model = new PrayerLog();

        $model->ensureDefaultDefinitions();

        $this->render('prayers/index', [
            'pageTitle' => 'Prayer Tracking',
            'todayLabel' => $now->format('l, d F Y'),
            'todayDate' => $today,
            'timezoneLabel' => (string) config('app.timezone', 'Asia/Dhaka'),
            'dailyPrayers' => $model->dailyChecklistForUser($userId, $today),
            'dailySummary' => $model->dailySummaryForUser($userId, $today),
            'weeklySummary' => $model->weeklySummaryForUser($userId, $weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')),
            'statusOptions' => PrayerLog::STATUSES,
            'weekLabel' => $weekStart->format('d M') . ' - ' . $weekEnd->format('d M Y'),
        ]);
    }

    public function update(): void
    {
        Csrf::ensureValid($_POST['_token'] ?? null);

        $model = new PrayerLog();
        $model->ensureDefaultDefinitions();

        $definitionId = filter_input(INPUT_POST, 'prayer_definition_id', FILTER_VALIDATE_INT);
        $date = trim((string) ($_POST['prayer_date'] ?? ''));
        $status = trim((string) ($_POST['status'] ?? ''));
        $note = $this->nullableString($_POST['note'] ?? null);

        with_old_input($_POST);

        $errors = [];

        if ($definitionId === false || $definitionId === null || $definitionId < 1 || !$model->definitionExists($definitionId)) {
            $errors['prayer_definition_id'] = 'Invalid prayer selection.';
        }

        if (!$this->isValidDate($date)) {
            $errors['prayer_date'] = 'Invalid prayer date.';
        }

        if (!in_array($status, PrayerLog::STATUSES, true)) {
            $errors['status'] = 'Choose a valid prayer status.';
        }

        if ($note !== null && mb_strlen($note) > 500) {
            $errors['note'] = 'Note must be 500 characters or fewer.';
        }

        if ($errors !== []) {
            with_errors($errors);
            Flash::set('message', 'Unable to update prayer status.', 'danger');
            $this->redirect('/prayers');
        }

        $prayedAt = $status === 'missed' ? null : $this->now()->format('Y-m-d H:i:s');

        $model->upsertForUser(
            $this->userId(),
            (int) $definitionId,
            $date,
            $status,
            $note,
            $prayedAt
        );

        clear_old_input();
        clear_errors();

        $this->redirect('/prayers', 'Prayer status updated.', 'success');
    }

    private function isValidDate(string $value): bool
    {
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $value);

        return $date instanceof DateTimeImmutable && $date->format('Y-m-d') === $value;
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
