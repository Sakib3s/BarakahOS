<?php

declare(strict_types=1);

namespace App\Models;

class Task extends BaseModel
{
    public function findForUser(int $id, ?int $userId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT id, title, status, due_date, created_at, completed_at
             FROM tasks
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

    public function allForUser(?int $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT id, title, status, created_at
             FROM tasks
             WHERE user_id = :user_id
             ORDER BY created_at DESC'
        );

        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll();
    }

    public function countPendingForUser(?int $userId): int
    {
        $statement = $this->db->prepare(
            "SELECT COUNT(*)
             FROM tasks
             WHERE user_id = :user_id
             AND status = 'pending'"
        );

        $statement->execute(['user_id' => $userId]);

        return (int) $statement->fetchColumn();
    }

    public function countCompletedTodayForUser(?int $userId): int
    {
        $statement = $this->db->prepare(
            "SELECT COUNT(*)
             FROM tasks
             WHERE user_id = :user_id
             AND status = 'completed'
             AND DATE(updated_at) = CURDATE()"
        );

        $statement->execute(['user_id' => $userId]);

        return (int) $statement->fetchColumn();
    }

    public function create(?int $userId, string $title): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO tasks (user_id, title, status)
             VALUES (:user_id, :title, :status)'
        );

        $statement->execute([
            'user_id' => $userId,
            'title' => $title,
            'status' => 'pending',
        ]);
    }

    public function markCompleted(int $id, ?int $userId): void
    {
        $statement = $this->db->prepare(
            'UPDATE tasks
             SET status = :next_status,
                 completed_at = CURRENT_TIMESTAMP
             WHERE id = :id
                AND user_id = :user_id
                AND status <> :current_status'
        );

        $statement->execute([
            'next_status' => 'completed',
            'current_status' => 'completed',
            'id' => $id,
            'user_id' => $userId,
        ]);
    }

    public function deleteForUser(int $id, ?int $userId): void
    {
        $statement = $this->db->prepare(
            'DELETE FROM tasks
             WHERE id = :id
                AND user_id = :user_id'
        );

        $statement->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);
    }
}
