<?php

declare(strict_types=1);

namespace App\Models;

class DailyChecklistTask extends BaseModel
{
    public const STATUSES = [
        'pending',
        'done',
        'partial',
        'missed',
    ];

    public const PRIORITIES = [
        'low',
        'medium',
        'high',
    ];

    public function create(int $userId, array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO daily_checklist_tasks (
                user_id,
                task_date,
                title,
                category,
                priority,
                status,
                estimated_duration_minutes,
                actual_duration_minutes,
                note
             ) VALUES (
                :user_id,
                :task_date,
                :title,
                :category,
                :priority,
                :status,
                :estimated_duration_minutes,
                :actual_duration_minutes,
                :note
             )'
        );

        $statement->execute([
            'user_id' => $userId,
            'task_date' => $data['task_date'],
            'title' => trim((string) $data['title']),
            'category' => trim((string) $data['category']),
            'priority' => $data['priority'],
            'status' => $data['status'],
            'estimated_duration_minutes' => $data['estimated_duration_minutes'],
            'actual_duration_minutes' => $data['actual_duration_minutes'],
            'note' => $data['note'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function getTodayForUser(int $userId, string $date, array $filters = []): array
    {
        $sql = 'SELECT
                    id,
                    task_date,
                    title,
                    category,
                    priority,
                    status,
                    estimated_duration_minutes,
                    actual_duration_minutes,
                    note,
                    created_at,
                    updated_at
                FROM daily_checklist_tasks
                WHERE user_id = :user_id
                    AND task_date = :task_date';

        $params = [
            'user_id' => $userId,
            'task_date' => $date,
        ];

        if (($filters['status'] ?? '') !== '') {
            $sql .= ' AND status = :status';
            $params['status'] = $filters['status'];
        }

        if (($filters['category'] ?? '') !== '') {
            $sql .= ' AND category = :category';
            $params['category'] = $filters['category'];
        }

        $sql .= ' ORDER BY
            FIELD(priority, \'high\', \'medium\', \'low\'),
            FIELD(status, \'pending\', \'partial\', \'missed\', \'done\'),
            created_at DESC';

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public function summaryForUser(int $userId, string $date, array $filters = []): array
    {
        $sql = 'SELECT
                    COUNT(*) AS total_count,
                    COALESCE(SUM(CASE WHEN status = \'pending\' THEN 1 ELSE 0 END), 0) AS pending_count,
                    COALESCE(SUM(CASE WHEN status = \'done\' THEN 1 ELSE 0 END), 0) AS done_count,
                    COALESCE(SUM(CASE WHEN status = \'partial\' THEN 1 ELSE 0 END), 0) AS partial_count,
                    COALESCE(SUM(CASE WHEN status = \'missed\' THEN 1 ELSE 0 END), 0) AS missed_count
                FROM daily_checklist_tasks
                WHERE user_id = :user_id
                    AND task_date = :task_date';

        $params = [
            'user_id' => $userId,
            'task_date' => $date,
        ];

        if (($filters['status'] ?? '') !== '') {
            $sql .= ' AND status = :status';
            $params['status'] = $filters['status'];
        }

        if (($filters['category'] ?? '') !== '') {
            $sql .= ' AND category = :category';
            $params['category'] = $filters['category'];
        }

        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        $summary = $statement->fetch();

        return $summary === false ? [
            'total_count' => 0,
            'pending_count' => 0,
            'done_count' => 0,
            'partial_count' => 0,
            'missed_count' => 0,
        ] : array_map('intval', $summary);
    }

    public function categoryOptionsForUser(int $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT DISTINCT category
             FROM daily_checklist_tasks
             WHERE user_id = :user_id
             ORDER BY category ASC'
        );

        $statement->execute([
            'user_id' => $userId,
        ]);

        return array_map(
            static fn (array $row): string => (string) $row['category'],
            $statement->fetchAll()
        );
    }

    public function findForUser(int $id, int $userId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT
                id,
                task_date,
                title,
                category,
                priority,
                status,
                estimated_duration_minutes,
                actual_duration_minutes,
                note
             FROM daily_checklist_tasks
             WHERE id = :id
                AND user_id = :user_id
             LIMIT 1'
        );

        $statement->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);

        $task = $statement->fetch();

        return $task === false ? null : $task;
    }

    public function updateStatus(int $id, int $userId, string $status): void
    {
        $statement = $this->db->prepare(
            'UPDATE daily_checklist_tasks
             SET status = :status, updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
                AND user_id = :user_id'
        );

        $statement->execute([
            'id' => $id,
            'user_id' => $userId,
            'status' => $status,
        ]);
    }

    public function update(int $id, int $userId, array $data): void
    {
        $statement = $this->db->prepare(
            'UPDATE daily_checklist_tasks
             SET
                title = :title,
                category = :category,
                priority = :priority,
                status = :status,
                estimated_duration_minutes = :estimated_duration_minutes,
                actual_duration_minutes = :actual_duration_minutes,
                note = :note,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
                AND user_id = :user_id'
        );

        $statement->execute([
            'id' => $id,
            'user_id' => $userId,
            'title' => trim((string) $data['title']),
            'category' => trim((string) $data['category']),
            'priority' => $data['priority'],
            'status' => $data['status'],
            'estimated_duration_minutes' => $data['estimated_duration_minutes'],
            'actual_duration_minutes' => $data['actual_duration_minutes'],
            'note' => $data['note'],
        ]);
    }
}
