<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\HttpException;
use App\Models\RoutineTemplate;

class RoutineTemplateController extends BaseController
{
    public function index(): void
    {
        $model = new RoutineTemplate();

        $this->render('routine_templates/index', [
            'pageTitle' => 'Routine Templates',
            'templates' => $model->allForUser($this->userId()),
            'categories' => RoutineTemplate::CATEGORIES,
            'weekdays' => RoutineTemplate::WEEKDAYS,
        ]);
    }

    public function create(): void
    {
        $this->render('routine_templates/create', [
            'pageTitle' => 'Create Routine Template',
            'categories' => RoutineTemplate::CATEGORIES,
            'weekdays' => RoutineTemplate::WEEKDAYS,
            'template' => null,
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
            $this->redirect('/routine-templates/create');
        }

        $model = new RoutineTemplate();
        $model->create($this->userId(), $payload);

        clear_old_input();
        clear_errors();

        $this->redirect('/routine-templates', 'Routine template created successfully.');
    }

    public function edit(): void
    {
        $model = new RoutineTemplate();
        $template = $model->findForUser($this->templateIdFromQuery(), $this->userId());

        if ($template === null) {
            throw new HttpException('Routine template not found.', 404);
        }

        $this->render('routine_templates/edit', [
            'pageTitle' => 'Edit Routine Template',
            'categories' => RoutineTemplate::CATEGORIES,
            'weekdays' => RoutineTemplate::WEEKDAYS,
            'template' => $template,
        ]);
    }

    public function update(): void
    {
        Csrf::ensureValid($_POST['_token'] ?? null);

        $templateId = $this->templateIdFromPost();
        $payload = $this->sanitizePayload($_POST);
        with_old_input($_POST);

        $errors = $this->validate($payload);

        $model = new RoutineTemplate();
        $existingTemplate = $model->findForUser($templateId, $this->userId());

        if ($existingTemplate === null) {
            throw new HttpException('Routine template not found.', 404);
        }

        if ($errors !== []) {
            with_errors($errors);
            Flash::set('message', 'Please fix the highlighted fields.', 'danger');
            $this->redirect('/routine-templates/edit?id=' . $templateId);
        }

        $model->update($templateId, $this->userId(), $payload);

        clear_old_input();
        clear_errors();

        $this->redirect('/routine-templates', 'Routine template updated successfully.');
    }

    public function delete(): void
    {
        Csrf::ensureValid($_POST['_token'] ?? null);

        $templateId = $this->templateIdFromPost();
        $model = new RoutineTemplate();
        $existingTemplate = $model->findForUser($templateId, $this->userId());

        if ($existingTemplate === null) {
            throw new HttpException('Routine template not found.', 404);
        }

        $model->delete($templateId, $this->userId());

        $this->redirect('/routine-templates', 'Routine template deleted successfully.', 'info');
    }

    private function sanitizePayload(array $input): array
    {
        $activeDays = array_values(array_filter(
            (array) ($input['active_days'] ?? []),
            static fn (mixed $day): bool => is_string($day) && $day !== ''
        ));
        $isAnyTime = (string) ($input['any_time'] ?? '') === '1';

        return [
            'title' => trim((string) ($input['title'] ?? '')),
            'category' => trim((string) ($input['category'] ?? '')),
            'start_time' => $isAnyTime ? null : trim((string) ($input['start_time'] ?? '')),
            'end_time' => $isAnyTime ? null : trim((string) ($input['end_time'] ?? '')),
            'any_time' => $isAnyTime,
            'is_fixed_task' => (string) ($input['is_fixed_task'] ?? '') === '1',
            'active_days' => $activeDays,
            'sort_order' => trim((string) ($input['sort_order'] ?? '0')),
        ];
    }

    private function validate(array $payload): array
    {
        $errors = [];

        if ($payload['title'] === '') {
            $errors['title'] = 'Title is required.';
        } elseif (mb_strlen($payload['title']) > 150) {
            $errors['title'] = 'Title must be 150 characters or fewer.';
        }

        if (!in_array($payload['category'], RoutineTemplate::CATEGORIES, true)) {
            $errors['category'] = 'Choose a valid category.';
        }

        if (!$payload['any_time']) {
            if (!$this->isValidTime($payload['start_time'])) {
                $errors['start_time'] = 'Start time is required.';
            }

            if (!$this->isValidTime($payload['end_time'])) {
                $errors['end_time'] = 'End time is required.';
            }

            if (
                $this->isValidTime($payload['start_time'])
                && $this->isValidTime($payload['end_time'])
                && strcmp((string) $payload['end_time'], (string) $payload['start_time']) <= 0
            ) {
                $errors['end_time'] = 'End time must be later than start time.';
            }
        }

        if ($payload['active_days'] === []) {
            $errors['active_days'] = 'Select at least one active day.';
        } else {
            foreach ($payload['active_days'] as $day) {
                if (!array_key_exists($day, RoutineTemplate::WEEKDAYS)) {
                    $errors['active_days'] = 'Active days contain an invalid value.';
                    break;
                }
            }
        }

        if ($payload['sort_order'] === '' || filter_var($payload['sort_order'], FILTER_VALIDATE_INT) === false) {
            $errors['sort_order'] = 'Sort order must be a valid number.';
        } elseif ((int) $payload['sort_order'] < 0) {
            $errors['sort_order'] = 'Sort order cannot be negative.';
        }

        return $errors;
    }

    private function isValidTime(?string $value): bool
    {
        if ($value === null) {
            return false;
        }

        return preg_match('/^\d{2}:\d{2}$/', $value) === 1;
    }

    private function templateIdFromQuery(): int
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if ($id === false || $id === null || $id < 1) {
            throw new HttpException('Invalid routine template id.', 404);
        }

        return $id;
    }

    private function templateIdFromPost(): int
    {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if ($id === false || $id === null || $id < 1) {
            throw new HttpException('Invalid routine template id.', 404);
        }

        return $id;
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
