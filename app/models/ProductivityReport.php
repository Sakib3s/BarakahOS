<?php

declare(strict_types=1);

namespace App\Models;

class ProductivityReport extends BaseModel
{
    public function getReport(int $userId, string $startDate, string $endDate): array
    {
        return [
            'tasks' => [
                'summary' => $this->taskSummary($userId, $startDate, $endDate),
                'details' => $this->taskDetails($userId, $startDate, $endDate),
            ],
            'fixed_tasks' => [
                'summary' => $this->fixedTaskSummary($userId, $startDate, $endDate),
                'details' => $this->fixedTaskDetails($userId, $startDate, $endDate),
            ],
            'focus' => [
                'summary' => $this->focusSummary($userId, $startDate, $endDate),
                'details' => $this->focusDetails($userId, $startDate, $endDate),
                'by_category' => $this->focusByCategory($userId, $startDate, $endDate),
            ],
            'distraction' => [
                'summary' => $this->distractionSummary($userId, $startDate, $endDate),
                'details' => $this->distractionDetails($userId, $startDate, $endDate),
            ],
            'prayer' => [
                'summary' => $this->prayerSummary($userId, $startDate, $endDate),
                'details' => $this->prayerDetails($userId, $startDate, $endDate),
            ],
            'sleep' => [
                'summary' => $this->sleepSummary($userId, $startDate, $endDate),
                'details' => $this->sleepDetails($userId, $startDate, $endDate),
            ],
            'review' => [
                'summary' => $this->reviewSummary($userId, $startDate, $endDate),
                'details' => $this->reviewDetails($userId, $startDate, $endDate),
            ],
        ];
    }

    private function taskSummary(int $userId, string $startDate, string $endDate): array
    {
        $summary = $this->fetchOne(
            'SELECT
                COUNT(*) AS total_count,
                COALESCE(SUM(CASE WHEN status = \'pending\' THEN 1 ELSE 0 END), 0) AS pending_count,
                COALESCE(SUM(CASE WHEN status = \'done\' THEN 1 ELSE 0 END), 0) AS done_count,
                COALESCE(SUM(CASE WHEN status = \'partial\' THEN 1 ELSE 0 END), 0) AS partial_count,
                COALESCE(SUM(CASE WHEN status = \'missed\' THEN 1 ELSE 0 END), 0) AS missed_count,
                COALESCE(SUM(actual_duration_minutes), 0) AS total_actual_minutes
             FROM daily_checklist_tasks
             WHERE user_id = :user_id
                AND task_date BETWEEN :start_date AND :end_date',
            [
                'user_id' => $userId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        );

        return [
            'total_count' => (int) ($summary['total_count'] ?? 0),
            'pending_count' => (int) ($summary['pending_count'] ?? 0),
            'done_count' => (int) ($summary['done_count'] ?? 0),
            'partial_count' => (int) ($summary['partial_count'] ?? 0),
            'missed_count' => (int) ($summary['missed_count'] ?? 0),
            'total_actual_minutes' => (int) ($summary['total_actual_minutes'] ?? 0),
        ];
    }

    private function taskDetails(int $userId, string $startDate, string $endDate): array
    {
        return $this->fetchAll(
            'SELECT
                task_date,
                title,
                category,
                priority,
                status,
                estimated_duration_minutes,
                actual_duration_minutes,
                note
             FROM daily_checklist_tasks
             WHERE user_id = :user_id
                AND task_date BETWEEN :start_date AND :end_date
             ORDER BY task_date DESC, FIELD(priority, \'high\', \'medium\', \'low\'), title ASC',
            [
                'user_id' => $userId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        );
    }

    private function fixedTaskSummary(int $userId, string $startDate, string $endDate): array
    {
        $summary = $this->fetchOne(
            'SELECT
                COUNT(*) AS total_count,
                COALESCE(SUM(CASE WHEN status = \'done\' THEN 1 ELSE 0 END), 0) AS done_count,
                COALESCE(SUM(CASE WHEN status = \'partial\' THEN 1 ELSE 0 END), 0) AS partial_count,
                COALESCE(SUM(CASE WHEN status = \'skipped_with_note\' THEN 1 ELSE 0 END), 0) AS skipped_with_note_count,
                COALESCE(SUM(CASE WHEN status = \'missed\' THEN 1 ELSE 0 END), 0) AS missed_count
             FROM fixed_task_logs
             WHERE user_id = :user_id
                AND log_date BETWEEN :start_date AND :end_date',
            [
                'user_id' => $userId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        );

        return [
            'total_count' => (int) ($summary['total_count'] ?? 0),
            'done_count' => (int) ($summary['done_count'] ?? 0),
            'partial_count' => (int) ($summary['partial_count'] ?? 0),
            'skipped_with_note_count' => (int) ($summary['skipped_with_note_count'] ?? 0),
            'missed_count' => (int) ($summary['missed_count'] ?? 0),
        ];
    }

    private function fixedTaskDetails(int $userId, string $startDate, string $endDate): array
    {
        return $this->fetchAll(
            'SELECT
                ftl.log_date,
                COALESCE(rt.title, ft.name) AS title,
                CASE
                    WHEN ftl.routine_template_id IS NOT NULL THEN \'routine_template\'
                    ELSE \'fixed_task\'
                END AS source_type,
                ftl.planned_start_time,
                ftl.planned_end_time,
                ftl.status,
                ftl.skip_note,
                ftl.general_note
             FROM fixed_task_logs ftl
             LEFT JOIN routine_templates rt
                ON rt.id = ftl.routine_template_id
             LEFT JOIN fixed_tasks ft
                ON ft.id = ftl.fixed_task_id
             WHERE ftl.user_id = :user_id
                AND ftl.log_date BETWEEN :start_date AND :end_date
             ORDER BY ftl.log_date DESC, COALESCE(ftl.planned_start_time, \'23:59:59\') ASC, title ASC',
            [
                'user_id' => $userId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        );
    }

    private function focusSummary(int $userId, string $startDate, string $endDate): array
    {
        $summary = $this->fetchOne(
            'SELECT
                COUNT(*) AS session_count,
                COALESCE(SUM(duration_minutes), 0) AS total_minutes,
                COALESCE(ROUND(AVG(duration_minutes)), 0) AS average_minutes,
                COALESCE(MAX(duration_minutes), 0) AS longest_minutes
             FROM focus_sessions
             WHERE user_id = :user_id
                AND session_date BETWEEN :start_date AND :end_date',
            [
                'user_id' => $userId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        );

        return [
            'session_count' => (int) ($summary['session_count'] ?? 0),
            'total_minutes' => (int) ($summary['total_minutes'] ?? 0),
            'average_minutes' => (int) ($summary['average_minutes'] ?? 0),
            'longest_minutes' => (int) ($summary['longest_minutes'] ?? 0),
        ];
    }

    private function focusByCategory(int $userId, string $startDate, string $endDate): array
    {
        return $this->fetchAll(
            'SELECT
                fc.name AS category_name,
                COUNT(*) AS session_count,
                COALESCE(SUM(fs.duration_minutes), 0) AS total_minutes
             FROM focus_sessions fs
             INNER JOIN focus_categories fc
                ON fc.id = fs.focus_category_id
             WHERE fs.user_id = :user_id
                AND fs.session_date BETWEEN :start_date AND :end_date
             GROUP BY fc.name
             ORDER BY total_minutes DESC, fc.name ASC',
            [
                'user_id' => $userId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        );
    }

    private function focusDetails(int $userId, string $startDate, string $endDate): array
    {
        return $this->fetchAll(
            'SELECT
                fs.session_date,
                fs.start_time,
                fs.end_time,
                fs.duration_minutes,
                fs.note,
                fc.name AS category_name
             FROM focus_sessions fs
             INNER JOIN focus_categories fc
                ON fc.id = fs.focus_category_id
             WHERE fs.user_id = :user_id
                AND fs.session_date BETWEEN :start_date AND :end_date
             ORDER BY fs.session_date DESC, fs.start_time DESC',
            [
                'user_id' => $userId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        );
    }

    private function distractionSummary(int $userId, string $startDate, string $endDate): array
    {
        new DistractionLog();

        $summary = $this->fetchOne(
            'SELECT
                COUNT(*) AS total_count,
                COALESCE(SUM(duration_minutes), 0) AS total_duration_minutes,
                COALESCE(SUM(CASE WHEN distraction_type IN (\'mobile_used\', \'phone_near\') THEN 1 ELSE 0 END), 0) AS mobile_used_count,
                COALESCE(SUM(CASE WHEN distraction_type = \'social_media_used\' THEN 1 ELSE 0 END), 0) AS social_media_used_count,
                COALESCE(SUM(CASE WHEN distraction_type IN (\'waste_time\', \'too_many_breaks\') THEN 1 ELSE 0 END), 0) AS waste_time_count
             FROM distraction_logs
             WHERE user_id = :user_id
                AND log_date BETWEEN :start_date AND :end_date',
            [
                'user_id' => $userId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        );

        return [
            'total_count' => (int) ($summary['total_count'] ?? 0),
            'total_duration_minutes' => (int) ($summary['total_duration_minutes'] ?? 0),
            'mobile_used_count' => (int) ($summary['mobile_used_count'] ?? 0),
            'social_media_used_count' => (int) ($summary['social_media_used_count'] ?? 0),
            'waste_time_count' => (int) ($summary['waste_time_count'] ?? 0),
        ];
    }

    private function distractionDetails(int $userId, string $startDate, string $endDate): array
    {
        new DistractionLog();

        return $this->fetchAll(
            'SELECT
                log_date,
                occurred_at,
                distraction_type,
                duration_minutes,
                note
             FROM distraction_logs
             WHERE user_id = :user_id
                AND log_date BETWEEN :start_date AND :end_date
             ORDER BY occurred_at DESC',
            [
                'user_id' => $userId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        );
    }

    private function prayerSummary(int $userId, string $startDate, string $endDate): array
    {
        $dayCount = ((strtotime($endDate) - strtotime($startDate)) / 86400) + 1;
        $expectedCount = count(PrayerLog::PRAYERS) * max(1, (int) $dayCount);

        $summary = $this->fetchOne(
            'SELECT
                COUNT(*) AS logged_count,
                COALESCE(SUM(CASE WHEN status = \'on_time\' THEN 1 ELSE 0 END), 0) AS on_time_count,
                COALESCE(SUM(CASE WHEN status = \'delayed\' THEN 1 ELSE 0 END), 0) AS delayed_count,
                COALESCE(SUM(CASE WHEN status = \'missed\' THEN 1 ELSE 0 END), 0) AS missed_count
             FROM prayer_logs
             WHERE user_id = :user_id
                AND log_date BETWEEN :start_date AND :end_date',
            [
                'user_id' => $userId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        );

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

    private function prayerDetails(int $userId, string $startDate, string $endDate): array
    {
        return $this->fetchAll(
            'SELECT
                pl.log_date,
                pd.name AS prayer_name,
                pl.status,
                pl.prayed_at,
                pl.note
             FROM prayer_logs pl
             INNER JOIN prayer_definitions pd
                ON pd.id = pl.prayer_definition_id
             WHERE pl.user_id = :user_id
                AND pl.log_date BETWEEN :start_date AND :end_date
             ORDER BY pl.log_date DESC, pd.sort_order ASC',
            [
                'user_id' => $userId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        );
    }

    private function sleepSummary(int $userId, string $startDate, string $endDate): array
    {
        $model = new SleepLog();

        return $model->summaryForRange($userId, $startDate, $endDate);
    }

    private function sleepDetails(int $userId, string $startDate, string $endDate): array
    {
        $model = new SleepLog();

        return $model->detailsForRange($userId, $startDate, $endDate);
    }

    private function reviewSummary(int $userId, string $startDate, string $endDate): array
    {
        $model = new DailyReview();

        return $model->summaryForRange($userId, $startDate, $endDate);
    }

    private function reviewDetails(int $userId, string $startDate, string $endDate): array
    {
        $model = new DailyReview();

        return $model->detailsForRange($userId, $startDate, $endDate);
    }

    private function fetchOne(string $sql, array $params): array
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        $result = $statement->fetch();

        return $result === false ? [] : $result;
    }

    private function fetchAll(string $sql, array $params): array
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }
}
