<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\HttpException;
use App\Models\DailyChecklistTask;
use DateTimeImmutable;
use DateTimeZone;

class DailyChecklistController extends BaseController
{
    public function index(): void
    {
        $userId = $this->userId();
        $today = $this->today();
        $model = new DailyChecklistTask();
        $filters = $this->filtersFromQuery();

        $this->render('daily_checklist/index', [
            'pageTitle' => 'Daily Checklist',
            'todayLabel' => $today->format('l, d F Y'),
            'todayDate' => $today->format('Y-m-d'),
            'filters' => $filters,
            'tasks' => $model->getTodayForUser($userId, $today->format('Y-m-d'), $filters),
            'summary' => $model->summaryForUser($userId, $today->format('Y-m-d'), $filters),
            'statusOptions' => DailyChecklistTask::STATUSES,
            'priorityOptions' => DailyChecklistTask::PRIORITIES,
            'categoryOptions' => $model->categoryOptionsForUser($userId),
        ]);
    }

    public function create(): void
    {
        $today = $this->today();
        $model = new DailyChecklistTask();

        $this->render('daily_checklist/create', [
            'pageTitle' => 'Add Daily Checklist Task',
            'todayLabel' => $today->format('l, d F Y'),
            'todayDate' => $today->format('Y-m-d'),
            'priorityOptions' => DailyChecklistTask::PRIORITIES,
            'statusOptions' => DailyChecklistTask::STATUSES,
            'categoryOptions' => $model->categoryOptionsForUser($this->userId()),
            'task' => null,
        ]);
    }

    public function store(): void
    {
        Csrf::ensureValid($_POST['_token'] ?? null);

        $payload = $this->sanitizePayload($_POST);
        with_old_input($_POST);

        $errors = $this->validate($payload);

        if ($errors !== []) {
            with_errors($errors);
            Flash::set('message', 'Please fix the highlighted fields.', 'danger');
            $this->redirect('/daily-checklist/create');
        }

        $model = new DailyChecklistTask();
        $model->create($this->userId(), $payload);

        clear_old_input();
        clear_errors();

        $this->redirect('/daily-checklist', 'Daily checklist task added successfully.');
    }

    public function edit(): void
    {
        $model = new DailyChecklistTask();
        $task = $model->findForUser($this->taskIdFromQuery(), $this->userId());

        if ($task === null) {
            throw new HttpException('Checklist task not found.', 404);
        }

        $this->render('daily_checklist/edit', [
            'pageTitle' => 'Edit Daily Checklist Task',
            'todayLabel' => $this->today()->format('l, d F Y'),
            'todayDate' => $this->today()->format('Y-m-d'),
            'priorityOptions' => DailyChecklistTask::PRIORITIES,
            'statusOptions' => DailyChecklistTask::STATUSES,
            'categoryOptions' => $model->categoryOptionsForUser($this->userId()),
            'task' => $task,
        ]);
    }

    public function update(): void
    {
        Csrf::ensureValid($_POST['_token'] ?? null);

        $taskId = $this->taskIdFromPost();
        $model = new DailyChecklistTask();
        $existingTask = $model->findForUser($taskId, $this->userId());

        if ($existingTask === null) {
            throw new HttpException('Checklist task not found.', 404);
        }

        $payload = $this->sanitizePayload($_POST, true, (string) $existingTask['task_date']);
        with_old_input($_POST);

        $errors = $this->validate($payload, true);

        if ($errors !== []) {
            with_errors($errors);
            Flash::set('message', 'Please fix the highlighted fields.', 'danger');
            $this->redirect('/daily-checklist/edit?id=' . $taskId);
        }

        $model->update($taskId, $this->userId(), $payload);

        clear_old_input();
        clear_errors();

        $this->redirect('/daily-checklist', 'Daily checklist task updated successfully.');
    }

    public function updateStatus(): void
    {
        Csrf::ensureValid($_POST['_token'] ?? null);

        $taskId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $status = trim((string) ($_POST['status'] ?? ''));

        if ($taskId === false || $taskId === null || $taskId < 1) {
            throw new HttpException('Invalid checklist task id.', 404);
        }

        if (!in_array($status, DailyChecklistTask::STATUSES, true)) {
            throw new HttpException('Invalid checklist task status.', 422);
        }

        $model = new DailyChecklistTask();
        $task = $model->findForUser($taskId, $this->userId());

        if ($task === null) {
            throw new HttpException('Checklist task not found.', 404);
        }

        $model->updateStatus($taskId, $this->userId(), $status);

        $redirectPath = '/daily-checklist';
        $query = $this->filtersFromPost();

        if ($query !== '') {
            $redirectPath .= '?' . $query;
        }

        $this->redirect($redirectPath, 'Checklist task updated.', 'info');
    }

    private function sanitizePayload(array $input, bool $includeStatus = false, ?string $taskDate = null): array
    {
        $payload = [
            'task_date' => $this->today()->format('Y-m-d'),
            'title' => trim((string) ($input['title'] ?? '')),
            'category' => trim((string) ($input['category'] ?? '')),
            'priority' => trim((string) ($input['priority'] ?? 'medium')),
            'status' => 'pending',
            'estimated_duration_minutes' => $this->nullablePositiveInt($input['estimated_duration_minutes'] ?? null),
            'actual_duration_minutes' => $this->nullablePositiveInt($input['actual_duration_minutes'] ?? null),
            'note' => $this->nullableString($input['note'] ?? null),
        ];

        if ($taskDate !== null) {
            $payload['task_date'] = $taskDate;
        }

        if ($includeStatus) {
            $payload['status'] = trim((string) ($input['status'] ?? 'pending'));
        }

        return $payload;
    }

    private function validate(array $payload, bool $includeStatus = false): array
    {
        $errors = [];

        if ($payload['title'] === '') {
            $errors['title'] = 'Title is required.';
        } elseif (mb_strlen($payload['title']) > 255) {
            $errors['title'] = 'Title must be 255 characters or fewer.';
        }

        if ($payload['category'] === '') {
            $errors['category'] = 'Category is required.';
        } elseif (mb_strlen($payload['category']) > 100) {
            $errors['category'] = 'Category must be 100 characters or fewer.';
        }

        if (!in_array($payload['priority'], DailyChecklistTask::PRIORITIES, true)) {
            $errors['priority'] = 'Choose a valid priority.';
        }

        if ($includeStatus && !in_array($payload['status'], DailyChecklistTask::STATUSES, true)) {
            $errors['status'] = 'Choose a valid status.';
        }

        if (
            $payload['estimated_duration_minutes'] !== null
            && $payload['estimated_duration_minutes'] < 1
        ) {
            $errors['estimated_duration_minutes'] = 'Estimated duration must be greater than zero.';
        }

        if (
            $payload['actual_duration_minutes'] !== null
            && $payload['actual_duration_minutes'] < 1
        ) {
            $errors['actual_duration_minutes'] = 'Actual duration must be greater than zero.';
        }

        if ($payload['note'] !== null && mb_strlen($payload['note']) > 500) {
            $errors['note'] = 'Note must be 500 characters or fewer.';
        }

        return $errors;
    }

    private function filtersFromQuery(): array
    {
        $status = trim((string) ($_GET['status'] ?? ''));
        $category = trim((string) ($_GET['category'] ?? ''));

        if (!in_array($status, DailyChecklistTask::STATUSES, true)) {
            $status = '';
        }

        return [
            'status' => $status,
            'category' => $category,
        ];
    }

    private function filtersFromPost(): string
    {
        $status = trim((string) ($_POST['filter_status'] ?? ''));
        $category = trim((string) ($_POST['filter_category'] ?? ''));
        $query = [];

        if (in_array($status, DailyChecklistTask::STATUSES, true)) {
            $query['status'] = $status;
        }

        if ($category !== '') {
            $query['category'] = $category;
        }

        return http_build_query($query);
    }

    private function nullablePositiveInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            return -1;
        }

        return (int) $value;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function taskIdFromQuery(): int
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if ($id === false || $id === null || $id < 1) {
            throw new HttpException('Invalid checklist task id.', 404);
        }

        return $id;
    }

    private function taskIdFromPost(): int
    {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if ($id === false || $id === null || $id < 1) {
            throw new HttpException('Invalid checklist task id.', 404);
        }

        return $id;
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
