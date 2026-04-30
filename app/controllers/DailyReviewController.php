<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\HttpException;
use App\Models\DailyReview;
use App\Models\DistractionLog;
use App\Models\FocusSession;
use App\Models\SleepLog;
use DateTimeImmutable;
use DateTimeZone;

class DailyReviewController extends BaseController
{
    public function index(): void
    {
        $today = $this->today();
        $todayDate = $today->format('Y-m-d');
        $model = new DailyReview();
        $userId = $this->userId();

        $this->render('daily_review/index', [
            'pageTitle' => 'Daily Review',
            'todayLabel' => $today->format('l, d F Y'),
            'todayDate' => $todayDate,
            'timezoneLabel' => (string) config('app.timezone', 'Asia/Dhaka'),
            'review' => $model->findForUserAndDate($userId, $todayDate),
            'dailySummary' => [
                'focus' => (new FocusSession())->todaySummaryForUser($userId, $todayDate)['totals'],
                'distraction' => (new DistractionLog())->dashboardSummary($userId, $todayDate),
                'sleep' => (new SleepLog())->findForUserAndDate($userId, $todayDate),
            ],
        ]);
    }

    public function save(): void
    {
        Csrf::ensureValid($_POST['_token'] ?? null);

        with_old_input($_POST);

        $date = trim((string) ($_POST['review_date'] ?? ''));
        $payload = $this->sanitize($_POST);
        $errors = $this->validate($date, $payload);

        if ($errors !== []) {
            with_errors($errors);
            Flash::set('message', 'Please fix the highlighted fields.', 'danger');
            $this->redirect('/daily-review');
        }

        $model = new DailyReview();
        $model->upsertForUserAndDate($this->userId(), $date, $payload);

        clear_old_input();
        clear_errors();

        $this->redirect('/daily-review', 'Daily review saved.');
    }

    private function sanitize(array $input): array
    {
        return [
            'day_rating' => $this->ratingValue($input['day_rating'] ?? null),
            'what_went_well' => trim((string) ($input['what_went_well'] ?? '')),
            'what_failed' => trim((string) ($input['what_failed'] ?? '')),
            'top_lesson' => trim((string) ($input['top_lesson'] ?? '')),
            'tomorrow_priority' => trim((string) ($input['tomorrow_priority'] ?? '')),
            'sleep_note' => $this->nullableString($input['sleep_note'] ?? null),
        ];
    }

    private function validate(string $date, array $payload): array
    {
        $errors = [];

        if (!$this->isValidDate($date)) {
            $errors['review_date'] = 'Invalid review date.';
        }

        if ($payload['day_rating'] < 1 || $payload['day_rating'] > 10) {
            $errors['day_rating'] = 'Day rating must be between 1 and 10.';
        }

        if ($payload['what_went_well'] === '') {
            $errors['what_went_well'] = 'This field is required.';
        } elseif (mb_strlen($payload['what_went_well']) > 2000) {
            $errors['what_went_well'] = 'Use 2000 characters or fewer.';
        }

        if ($payload['what_failed'] === '') {
            $errors['what_failed'] = 'This field is required.';
        } elseif (mb_strlen($payload['what_failed']) > 2000) {
            $errors['what_failed'] = 'Use 2000 characters or fewer.';
        }

        if ($payload['top_lesson'] === '') {
            $errors['top_lesson'] = 'Top lesson is required.';
        } elseif (mb_strlen($payload['top_lesson']) > 1000) {
            $errors['top_lesson'] = 'Use 1000 characters or fewer.';
        }

        if ($payload['tomorrow_priority'] === '') {
            $errors['tomorrow_priority'] = 'Tomorrow priority is required.';
        } elseif (mb_strlen($payload['tomorrow_priority']) > 1000) {
            $errors['tomorrow_priority'] = 'Use 1000 characters or fewer.';
        }

        if ($payload['sleep_note'] !== null && mb_strlen($payload['sleep_note']) > 1000) {
            $errors['sleep_note'] = 'Use 1000 characters or fewer.';
        }

        return $errors;
    }

    private function ratingValue(mixed $value): int
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            return 0;
        }

        return (int) $value;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function isValidDate(string $value): bool
    {
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $value);

        return $date instanceof DateTimeImmutable && $date->format('Y-m-d') === $value;
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
}
