<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\HttpException;
use App\Models\FocusSession;
use DateTimeImmutable;
use DateTimeZone;

class FocusSessionController extends BaseController
{
    public function index(): void
    {
        $userId = $this->userId();
        $focusSessionModel = new FocusSession();

        $focusSessionModel->ensureDefaultCategories($userId);
        $today = $this->today();

        $this->render('focus_sessions/create', [
            'pageTitle' => 'Focus Session Tracker',
            'todayLabel' => $today->format('l, d F Y'),
            'todayDate' => $today->format('Y-m-d'),
            'runningSession' => $focusSessionModel->runningSessionForUser($userId),
            'latestCompletedChecklist' => $focusSessionModel->latestCompletedChecklistForUser($userId),
            'categoryOptions' => $focusSessionModel->categoryOptionsForUser($userId),
            'todaySessions' => $focusSessionModel->todaySessionsForUser($userId, $today->format('Y-m-d')),
            'summary' => $focusSessionModel->todaySummaryForUser($userId, $today->format('Y-m-d')),
        ]);
    }

    public function create(): void
    {
        $this->index();
    }

    public function start(): void
    {
        Csrf::ensureValid($_POST['_token'] ?? null);
        $userId = $this->userId();
        $focusSessionModel = new FocusSession();
        $focusSessionModel->ensureDefaultCategories($userId);

        if ($focusSessionModel->runningSessionForUser($userId) !== null) {
            Flash::set('message', 'A focus session is already running. End it before starting another one.', 'warning');
            $this->redirect('/focus-sessions');
        }

        $payload = [
            'focus_category_id' => filter_input(INPUT_POST, 'focus_category_id', FILTER_VALIDATE_INT),
            'link_latest_checklist' => (string) ($_POST['link_latest_checklist'] ?? '') === '1',
            'note' => $this->nullableString($_POST['note'] ?? null),
        ];

        with_old_input($_POST);

        $errors = [];

        if ($payload['focus_category_id'] === false || $payload['focus_category_id'] === null || $payload['focus_category_id'] < 1) {
            $errors['focus_category_id'] = 'Choose a focus category.';
        }

        if ($payload['note'] !== null && mb_strlen($payload['note']) > 1000) {
            $errors['note'] = 'Note must be 1000 characters or fewer.';
        }

        $today = $this->today();
        $categoryIds = array_map(
            static fn (array $category): int => (int) $category['id'],
            $focusSessionModel->categoryOptionsForUser($userId)
        );

        if (
            $payload['focus_category_id'] !== false
            && $payload['focus_category_id'] !== null
            && !in_array((int) $payload['focus_category_id'], $categoryIds, true)
        ) {
            $errors['focus_category_id'] = 'Choose a valid focus category.';
        }

        $latestChecklist = $focusSessionModel->latestCompletedChecklistForUser($userId);

        if ($errors !== []) {
            with_errors($errors);
            Flash::set('message', 'Please fix the highlighted fields.', 'danger');
            $this->redirect('/focus-sessions');
        }

        $focusSessionModel->startSession($userId, [
            'focus_category_id' => (int) $payload['focus_category_id'],
            'pre_work_checklist_run_id' => ($payload['link_latest_checklist'] && $latestChecklist !== null)
                ? (int) $latestChecklist['id']
                : null,
            'session_date' => $today->format('Y-m-d'),
            'start_time' => $today->format('Y-m-d H:i:s'),
            'note' => $payload['note'],
        ]);

        clear_old_input();
        clear_errors();

        $message = $latestChecklist !== null && $payload['link_latest_checklist']
            ? 'Focus session started and linked to the latest completed pre-work checklist.'
            : 'Focus session started.';

        $this->redirect('/focus-sessions', $message);
    }

    public function end(): void
    {
        Csrf::ensureValid($_POST['_token'] ?? null);

        $sessionId = filter_input(INPUT_POST, 'session_id', FILTER_VALIDATE_INT);

        if ($sessionId === false || $sessionId === null || $sessionId < 1) {
            throw new HttpException('Invalid focus session id.', 404);
        }

        $this->userId();

        $focusSessionModel = new FocusSession();
        $focusSessionModel->endSession(
            $sessionId,
            $this->userId(),
            $this->today()->format('Y-m-d H:i:s')
        );

        $this->redirect('/focus-sessions', 'Focus session ended successfully.');
    }

    public function delete(): void
    {
        Csrf::ensureValid($_POST['_token'] ?? null);

        $sessionId = filter_input(INPUT_POST, 'session_id', FILTER_VALIDATE_INT);

        if ($sessionId === false || $sessionId === null || $sessionId < 1) {
            throw new HttpException('Invalid focus session id.', 404);
        }

        (new FocusSession())->deleteForUser($sessionId, $this->userId());

        $this->redirect('/focus-sessions', 'Focus session deleted.');
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
