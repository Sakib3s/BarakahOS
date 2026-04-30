<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\HttpException;
use App\Models\SleepLog;
use DateTimeImmutable;
use DateTimeZone;

class SleepTrackerController extends BaseController
{
    public function index(): void
    {
        $now = $this->now();
        $today = $this->dateQueryValue($_GET['date'] ?? null) ?? $now->format('Y-m-d');
        $weekStart = $now->modify('monday this week')->format('Y-m-d');
        $weekEnd = $now->modify('monday this week')->modify('+6 days')->format('Y-m-d');
        $model = new SleepLog();
        $sleepLog = $model->findForUserAndDate($this->userId(), $today);

        $this->render('sleep_tracker/index', [
            'pageTitle' => 'Sleep Tracker',
            'todayLabel' => (new DateTimeImmutable($today))->format('l, d F Y'),
            'todayDate' => $today,
            'timezoneLabel' => (string) config('app.timezone', 'Asia/Dhaka'),
            'sleepLog' => $sleepLog,
            'recentLogs' => $model->recentForUser($this->userId(), 14),
            'weekSummary' => $model->summaryForRange($this->userId(), $weekStart, $weekEnd),
            'defaultSleepStartedAt' => $now->modify('-7 hours')->format('Y-m-d\TH:i'),
            'defaultWokeUpAt' => $now->format('Y-m-d\TH:i'),
        ]);
    }

    public function save(): void
    {
        Csrf::ensureValid($_POST['_token'] ?? null);

        with_old_input($_POST);
        $payload = $this->sanitize($_POST);
        $errors = $this->validate($payload);

        if ($errors !== []) {
            with_errors($errors);
            Flash::set('message', 'Please fix the highlighted fields.', 'danger');
            $this->redirect('/sleep-tracker');
        }

        $model = new SleepLog();
        $model->upsertForUserAndDate($this->userId(), $payload['sleep_date'], $payload);

        clear_old_input();
        clear_errors();

        $this->redirect('/sleep-tracker', 'Sleep session saved.');
    }

    public function delete(): void
    {
        Csrf::ensureValid($_POST['_token'] ?? null);

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if ($id === false || $id === null || $id < 1) {
            throw new HttpException('Invalid sleep log id.', 404);
        }

        (new SleepLog())->deleteForUser($id, $this->userId());

        $this->redirect('/sleep-tracker', 'Sleep session deleted.');
    }

    private function sanitize(array $input): array
    {
        $sleepStartedAt = $this->dateTimeValue($input['sleep_started_at'] ?? null);
        $wokeUpAt = $this->dateTimeValue($input['woke_up_at'] ?? null);
        $durationMinutes = 0;

        if ($sleepStartedAt !== null && $wokeUpAt !== null) {
            $durationMinutes = max(0, (int) ceil(($wokeUpAt->getTimestamp() - $sleepStartedAt->getTimestamp()) / 60));
        }

        return [
            'sleep_date' => $wokeUpAt?->format('Y-m-d'),
            'sleep_started_at' => $sleepStartedAt?->format('Y-m-d H:i:s'),
            'woke_up_at' => $wokeUpAt?->format('Y-m-d H:i:s'),
            'duration_minutes' => $durationMinutes,
            'note' => $this->nullableString($input['note'] ?? null),
        ];
    }

    private function validate(array $payload): array
    {
        $errors = [];

        if ($payload['sleep_started_at'] === null) {
            $errors['sleep_started_at'] = 'Sleep time is required.';
        }

        if ($payload['woke_up_at'] === null) {
            $errors['woke_up_at'] = 'Wake-up time is required.';
        }

        if ($payload['sleep_started_at'] !== null && $payload['woke_up_at'] !== null) {
            if (strtotime((string) $payload['woke_up_at']) <= strtotime((string) $payload['sleep_started_at'])) {
                $errors['woke_up_at'] = 'Wake-up time must be after sleep time.';
            }

            if (strtotime((string) $payload['woke_up_at']) > $this->now()->getTimestamp()) {
                $errors['woke_up_at'] = 'Wake-up time cannot be in the future.';
            }
        }

        if ($payload['duration_minutes'] < 1 || $payload['duration_minutes'] > 1440) {
            $errors['woke_up_at'] = 'Sleep duration must be between 1 minute and 24 hours.';
        }

        if ($payload['note'] !== null && mb_strlen($payload['note']) > 1000) {
            $errors['note'] = 'Note must be 1000 characters or fewer.';
        }

        return $errors;
    }

    private function dateTimeValue(mixed $value): ?DateTimeImmutable
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $timezone = new DateTimeZone((string) config('app.timezone', 'Asia/Dhaka'));

        foreach (['Y-m-d\TH:i', 'Y-m-d\TH:i:s', 'Y-m-d H:i:s'] as $format) {
            $dateTime = DateTimeImmutable::createFromFormat($format, $value, $timezone);

            if ($dateTime instanceof DateTimeImmutable) {
                return $dateTime;
            }
        }

        return null;
    }

    private function dateQueryValue(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', $value);

        return $date instanceof DateTimeImmutable && $date->format('Y-m-d') === $value ? $value : null;
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
