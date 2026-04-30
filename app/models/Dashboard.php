<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;

class Dashboard extends BaseModel
{
    public function getSummary(int $userId, string $date): array
    {
        return [
            'routine' => $this->routineSummary($userId, $date),
            'prayer' => $this->prayerSummary($userId, $date),
            'tasks' => $this->taskSummary($userId, $date),
            'fixedTasks' => $this->fixedTaskSummary($userId, $date),
            'focus' => $this->focusSummary($userId, $date),
            'checklist' => $this->checklistSummary($userId, $date),
            'distraction' => $this->distractionSummary($userId, $date),
            'sleep' => $this->sleepSummary($userId, $date),
            'review' => $this->reviewSummary($userId, $date),
        ];
    }

    public function getFixedTaskStatuses(int $userId, string $date): array
    {
        $tracker = new FixedTaskTracker();

        return $tracker->getTodayPlannedTasks(
            $userId,
            $date,
            strtolower((new DateTimeImmutable($date))->format('l'))
        );
    }

    public function getRecentTasks(int $userId, ?int $limit = 5): array
    {
        $limitSql = $limit === null ? '' : ' LIMIT ' . max(1, $limit);

        $statement = $this->db->prepare(
            'SELECT id, title, status, due_date, created_at
             FROM tasks
             WHERE user_id = :user_id
             ORDER BY created_at DESC' . $limitSql
        );

        $statement->execute([
            'user_id' => $userId,
        ]);

        return $statement->fetchAll();
    }

    private function routineSummary(int $userId, string $date): array
    {
        $activeTemplates = $this->fetchOne(
            'SELECT COUNT(*) AS active_count
             FROM routine_templates
             WHERE user_id = :user_id',
            ['user_id' => $userId]
        );

        $entries = $this->fetchOne(
            'SELECT
                COUNT(*) AS logged_count,
                COALESCE(SUM(CASE WHEN status = \'done\' THEN 1 ELSE 0 END), 0) AS done_count,
                COALESCE(SUM(CASE WHEN status = \'partial\' THEN 1 ELSE 0 END), 0) AS partial_count,
                COALESCE(SUM(CASE WHEN status = \'skipped\' THEN 1 ELSE 0 END), 0) AS skipped_count
             FROM daily_routine_entries
             WHERE user_id = :user_id
                AND entry_date = :entry_date',
            [
                'user_id' => $userId,
                'entry_date' => $date,
            ]
        );

        $activeCount = (int) ($activeTemplates['active_count'] ?? 0);
        $loggedCount = (int) ($entries['logged_count'] ?? 0);

        return [
            'active_count' => $activeCount,
            'logged_count' => $loggedCount,
            'done_count' => (int) ($entries['done_count'] ?? 0),
            'partial_count' => (int) ($entries['partial_count'] ?? 0),
            'skipped_count' => (int) ($entries['skipped_count'] ?? 0),
            'remaining_count' => max($activeCount - $loggedCount, 0),
        ];
    }

    private function prayerSummary(int $userId, string $date): array
    {
        $summary = $this->fetchOne(
            'SELECT
                COUNT(pd.id) AS expected_count,
                COALESCE(SUM(CASE WHEN pl.status = \'on_time\' THEN 1 ELSE 0 END), 0) AS on_time_count,
                COALESCE(SUM(CASE WHEN pl.status = \'delayed\' THEN 1 ELSE 0 END), 0) AS delayed_count,
                COALESCE(SUM(CASE WHEN pl.status = \'missed\' THEN 1 ELSE 0 END), 0) AS missed_count,
                COALESCE(SUM(CASE WHEN pl.id IS NOT NULL THEN 1 ELSE 0 END), 0) AS logged_count
             FROM prayer_definitions pd
             LEFT JOIN prayer_logs pl
                ON pl.prayer_definition_id = pd.id
                AND pl.user_id = :user_id
                AND pl.log_date = :log_date',
            [
                'user_id' => $userId,
                'log_date' => $date,
            ]
        );

        $expectedCount = (int) ($summary['expected_count'] ?? 0);
        $loggedCount = (int) ($summary['logged_count'] ?? 0);

        return [
            'expected_count' => $expectedCount,
            'logged_count' => $loggedCount,
            'on_time_count' => (int) ($summary['on_time_count'] ?? 0),
            'delayed_count' => (int) ($summary['delayed_count'] ?? 0),
            'missed_count' => (int) ($summary['missed_count'] ?? 0),
            'remaining_count' => max($expectedCount - $loggedCount, 0),
        ];
    }

    private function taskSummary(int $userId, string $date): array
    {
        $summary = $this->fetchOne(
            'SELECT
                COUNT(*) AS total_count,
                COALESCE(SUM(CASE WHEN status = \'pending\' THEN 1 ELSE 0 END), 0) AS pending_count,
                COALESCE(SUM(CASE WHEN status = \'in_progress\' THEN 1 ELSE 0 END), 0) AS in_progress_count,
                COALESCE(SUM(CASE WHEN status = \'completed\' THEN 1 ELSE 0 END), 0) AS completed_count,
                COALESCE(SUM(CASE WHEN status = \'cancelled\' THEN 1 ELSE 0 END), 0) AS cancelled_count,
                COALESCE(SUM(
                    CASE
                        WHEN status = \'completed\'
                            AND completed_at IS NOT NULL
                            AND DATE(completed_at) = :log_date
                        THEN 1
                        ELSE 0
                    END
                ), 0) AS completed_today_count
             FROM tasks
             WHERE user_id = :user_id',
            [
                'user_id' => $userId,
                'log_date' => $date,
            ]
        );

        return array_map('intval', $summary);
    }

    private function fixedTaskSummary(int $userId, string $date): array
    {
        $tracker = new FixedTaskTracker();
        $tasks = $tracker->getTodayPlannedTasks(
            $userId,
            $date,
            strtolower((new DateTimeImmutable($date))->format('l'))
        );
        $summary = $tracker->summarize($tasks);

        return [
            'expected_count' => (int) ($summary['total_count'] ?? 0),
            'done_count' => (int) ($summary['done_count'] ?? 0),
            'partial_count' => (int) ($summary['partial_count'] ?? 0),
            'skipped_with_note_count' => (int) ($summary['skipped_with_note_count'] ?? 0),
            'missed_count' => (int) ($summary['missed_count'] ?? 0),
            'pending_count' => (int) ($summary['pending_count'] ?? 0),
        ];
    }

    private function focusSummary(int $userId, string $date): array
    {
        $summary = $this->fetchOne(
            'SELECT
                COUNT(*) AS session_count,
                COALESCE(SUM(duration_minutes), 0) AS total_minutes
             FROM focus_sessions
             WHERE user_id = :user_id
                AND session_date = :session_date',
            [
                'user_id' => $userId,
                'session_date' => $date,
            ]
        );

        return [
            'session_count' => (int) ($summary['session_count'] ?? 0),
            'total_minutes' => (int) ($summary['total_minutes'] ?? 0),
        ];
    }

    private function checklistSummary(int $userId, string $date): array
    {
        $checklist = new PreWorkChecklist();

        return $checklist->latestRunSummaryForDate($userId, $date);
    }

    private function distractionSummary(int $userId, string $date): array
    {
        $model = new DistractionLog();

        return $model->dashboardSummary($userId, $date);
    }

    private function sleepSummary(int $userId, string $date): array
    {
        $model = new SleepLog();
        $sleepLog = $model->findForUserAndDate($userId, $date);

        return [
            'has_log' => $sleepLog !== null,
            'duration_minutes' => $sleepLog === null ? 0 : (int) $sleepLog['duration_minutes'],
            'sleep_started_at' => $sleepLog['sleep_started_at'] ?? null,
            'woke_up_at' => $sleepLog['woke_up_at'] ?? null,
        ];
    }

    private function reviewSummary(int $userId, string $date): array
    {
        $model = new DailyReview();

        return $model->dashboardSummary($userId, $date);
    }

    private function fetchOne(string $sql, array $params): array
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        $result = $statement->fetch();

        return $result === false ? [] : $result;
    }
}
