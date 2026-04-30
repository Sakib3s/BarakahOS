<?php

declare(strict_types=1);

namespace App\Models;

class FixedTaskTracker extends BaseModel
{
    public const STATUSES = [
        'done',
        'partial',
        'skipped_with_note',
        'missed',
    ];

    public function getTodayPlannedTasks(int $userId, string $date, string $weekday): array
    {
        $tasks = array_merge(
            $this->routineTemplateTasks($userId, $date, $weekday),
            $this->standaloneFixedTasks($userId, $date)
        );

        usort($tasks, static function (array $left, array $right): int {
            $leftTime = $left['planned_start_time'] ?? '99:99:99';
            $rightTime = $right['planned_start_time'] ?? '99:99:99';

            return [$leftTime, $left['sort_order'], $left['title']]
                <=> [$rightTime, $right['sort_order'], $right['title']];
        });

        return $tasks;
    }

    public function summarize(array $tasks): array
    {
        $summary = [
            'total_count' => count($tasks),
            'pending_count' => 0,
            'done_count' => 0,
            'partial_count' => 0,
            'skipped_with_note_count' => 0,
            'missed_count' => 0,
        ];

        foreach ($tasks as $task) {
            $status = $task['status'] ?? 'pending';

            if ($status === 'pending') {
                $summary['pending_count']++;

                continue;
            }

            $key = $status . '_count';

            if (array_key_exists($key, $summary)) {
                $summary[$key]++;
            }
        }

        return $summary;
    }

    public function findPlannedTaskForToday(
        int $userId,
        string $date,
        string $weekday,
        string $sourceType,
        int $sourceId
    ): ?array {
        return match ($sourceType) {
            'routine_template' => $this->findRoutineTemplateTaskForToday($userId, $date, $weekday, $sourceId),
            'fixed_task' => $this->findFixedTaskForToday($userId, $date, $sourceId),
            default => null,
        };
    }

    public function saveLog(
        int $userId,
        array $task,
        string $date,
        string $status,
        ?string $skipNote,
        ?string $generalNote
    ): void {
        $statement = $this->db->prepare(
            'INSERT INTO fixed_task_logs (
                user_id,
                routine_template_id,
                fixed_task_id,
                log_date,
                planned_start_time,
                planned_end_time,
                status,
                skip_note,
                general_note,
                logged_at
             ) VALUES (
                :user_id,
                :routine_template_id,
                :fixed_task_id,
                :log_date,
                :planned_start_time,
                :planned_end_time,
                :status,
                :skip_note,
                :general_note,
                :logged_at
             )
             ON DUPLICATE KEY UPDATE
                planned_start_time = VALUES(planned_start_time),
                planned_end_time = VALUES(planned_end_time),
                status = VALUES(status),
                skip_note = VALUES(skip_note),
                general_note = VALUES(general_note),
                logged_at = VALUES(logged_at),
                updated_at = CURRENT_TIMESTAMP'
        );

        $statement->execute([
            'user_id' => $userId,
            'routine_template_id' => $task['routine_template_id'],
            'fixed_task_id' => $task['fixed_task_id'],
            'log_date' => $date,
            'planned_start_time' => $task['planned_start_time'],
            'planned_end_time' => $task['planned_end_time'],
            'status' => $status,
            'skip_note' => $skipNote,
            'general_note' => $generalNote,
            'logged_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function routineTemplateTasks(int $userId, string $date, string $weekday): array
    {
        $statement = $this->db->prepare(
            'SELECT
                rt.id AS routine_template_id,
                rt.title,
                rt.category,
                rt.start_time AS planned_start_time,
                rt.end_time AS planned_end_time,
                rt.sort_order,
                rt.active_days,
                ftl.status,
                ftl.skip_note,
                ftl.general_note
             FROM routine_templates rt
             LEFT JOIN fixed_task_logs ftl
                ON ftl.routine_template_id = rt.id
                AND ftl.user_id = :join_user_id
                AND ftl.log_date = :join_log_date
             WHERE rt.user_id = :where_user_id
                AND rt.is_fixed_task = 1'
        );

        $statement->execute([
            'join_user_id' => $userId,
            'join_log_date' => $date,
            'where_user_id' => $userId,
        ]);

        $tasks = [];

        foreach ($statement->fetchAll() as $row) {
            $activeDays = json_decode((string) $row['active_days'], true);

            if (!is_array($activeDays) || !in_array($weekday, $activeDays, true)) {
                continue;
            }

            $tasks[] = [
                'source_type' => 'routine_template',
                'source_id' => (int) $row['routine_template_id'],
                'routine_template_id' => (int) $row['routine_template_id'],
                'fixed_task_id' => null,
                'title' => (string) $row['title'],
                'category' => (string) $row['category'],
                'planned_start_time' => $row['planned_start_time'],
                'planned_end_time' => $row['planned_end_time'],
                'sort_order' => (int) $row['sort_order'],
                'status' => $row['status'] ?? 'pending',
                'skip_note' => $row['skip_note'],
                'general_note' => $row['general_note'],
                'description' => null,
                'source_label' => 'Routine Template',
            ];
        }

        return $tasks;
    }

    private function standaloneFixedTasks(int $userId, string $date): array
    {
        $statement = $this->db->prepare(
            'SELECT
                ft.id AS fixed_task_id,
                ft.name AS title,
                ft.description,
                ft.scheduled_time AS planned_start_time,
                CASE
                    WHEN ft.scheduled_time IS NOT NULL AND ft.expected_minutes IS NOT NULL
                    THEN ADDTIME(ft.scheduled_time, SEC_TO_TIME(ft.expected_minutes * 60))
                    ELSE NULL
                END AS planned_end_time,
                ft.sort_order,
                ftl.status,
                ftl.skip_note,
                ftl.general_note
             FROM fixed_tasks ft
             LEFT JOIN fixed_task_logs ftl
                ON ftl.fixed_task_id = ft.id
                AND ftl.user_id = :join_user_id
                AND ftl.log_date = :join_log_date
             WHERE ft.user_id = :where_user_id
                AND ft.is_active = 1'
        );

        $statement->execute([
            'join_user_id' => $userId,
            'join_log_date' => $date,
            'where_user_id' => $userId,
        ]);

        return array_map(static function (array $row): array {
            return [
                'source_type' => 'fixed_task',
                'source_id' => (int) $row['fixed_task_id'],
                'routine_template_id' => null,
                'fixed_task_id' => (int) $row['fixed_task_id'],
                'title' => (string) $row['title'],
                'category' => 'fixed_task',
                'planned_start_time' => $row['planned_start_time'],
                'planned_end_time' => $row['planned_end_time'],
                'sort_order' => (int) $row['sort_order'],
                'status' => $row['status'] ?? 'pending',
                'skip_note' => $row['skip_note'],
                'general_note' => $row['general_note'],
                'description' => $row['description'],
                'source_label' => 'Standalone Fixed Task',
            ];
        }, $statement->fetchAll());
    }

    private function findRoutineTemplateTaskForToday(int $userId, string $date, string $weekday, int $sourceId): ?array
    {
        foreach ($this->routineTemplateTasks($userId, $date, $weekday) as $task) {
            if ($task['source_id'] === $sourceId) {
                return $task;
            }
        }

        return null;
    }

    private function findFixedTaskForToday(int $userId, string $date, int $sourceId): ?array
    {
        foreach ($this->standaloneFixedTasks($userId, $date) as $task) {
            if ($task['source_id'] === $sourceId) {
                return $task;
            }
        }

        return null;
    }
}
