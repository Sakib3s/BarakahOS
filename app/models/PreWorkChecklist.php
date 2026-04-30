<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

class PreWorkChecklist extends BaseModel
{
    public const DEFAULT_ITEMS = [
        'clean_table' => 'Clean table',
        'phone_away' => 'Phone away',
        'water_ready' => 'Water ready',
        'energy_food_ready' => 'Energy food ready',
        'task_clear' => 'Task clear',
    ];

    public function ensureDefaultItems(int $userId): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO pre_work_checklist_items (user_id, code, label, sort_order, is_active)
             VALUES (:user_id, :code, :label, :sort_order, 1)
             ON DUPLICATE KEY UPDATE
                label = VALUES(label),
                sort_order = VALUES(sort_order),
                is_active = 1'
        );

        $sortOrder = 1;

        foreach (self::DEFAULT_ITEMS as $code => $label) {
            $statement->execute([
                'user_id' => $userId,
                'code' => $code,
                'label' => $label,
                'sort_order' => $sortOrder++,
            ]);
        }
    }

    public function itemsForUser(int $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT id, code, label, sort_order
             FROM pre_work_checklist_items
             WHERE user_id = :user_id
                AND is_active = 1
             ORDER BY sort_order ASC, id ASC'
        );

        $statement->execute([
            'user_id' => $userId,
        ]);

        return $statement->fetchAll();
    }

    public function latestCompletedRunForUser(int $userId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT
                r.id,
                r.checklist_date,
                r.completed_at,
                r.created_at
             FROM pre_work_checklist_runs r
             WHERE r.user_id = :user_id
                AND r.status = :status
             ORDER BY COALESCE(r.completed_at, r.created_at) DESC
             LIMIT 1'
        );

        $statement->execute([
            'user_id' => $userId,
            'status' => 'completed',
        ]);

        $run = $statement->fetch();

        if ($run === false) {
            return null;
        }

        $run['items'] = $this->runItems((int) $run['id']);

        return $run;
    }

    public function createRunWithItems(
        int $userId,
        string $date,
        string $status,
        array $items,
        array $checkedItemIds
    ): int {
        $this->db->beginTransaction();

        try {
            $timestamp = date('Y-m-d H:i:s');

            $runStatement = $this->db->prepare(
                'INSERT INTO pre_work_checklist_runs (
                    user_id,
                    checklist_date,
                    status,
                    completed_at
                 ) VALUES (
                    :user_id,
                    :checklist_date,
                    :status,
                    :completed_at
                 )'
            );

            $runStatement->execute([
                'user_id' => $userId,
                'checklist_date' => $date,
                'status' => $status,
                'completed_at' => $status === 'completed' ? $timestamp : null,
            ]);

            $runId = (int) $this->db->lastInsertId();

            $itemStatement = $this->db->prepare(
                'INSERT INTO pre_work_checklist_logs (
                    checklist_run_id,
                    checklist_item_id,
                    is_checked,
                    checked_at
                 ) VALUES (
                    :checklist_run_id,
                    :checklist_item_id,
                    :is_checked,
                    :checked_at
                 )'
            );

            foreach ($items as $item) {
                $isChecked = in_array((int) $item['id'], $checkedItemIds, true);

                $itemStatement->execute([
                    'checklist_run_id' => $runId,
                    'checklist_item_id' => (int) $item['id'],
                    'is_checked' => $isChecked ? 1 : 0,
                    'checked_at' => $isChecked ? $timestamp : null,
                ]);
            }

            $this->db->commit();

            return $runId;
        } catch (\Throwable $exception) {
            $this->db->rollBack();
            throw $exception;
        }
    }

    public function linkRunToFocusSession(int $runId, int $focusSessionId): void
    {
        $statement = $this->db->prepare(
            'UPDATE pre_work_checklist_runs
             SET focus_session_id = :focus_session_id,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        $statement->execute([
            'focus_session_id' => $focusSessionId,
            'id' => $runId,
        ]);
    }

    public function latestRunSummaryForDate(int $userId, string $date): array
    {
        $this->ensureDefaultItems($userId);
        $totalItems = count($this->itemsForUser($userId));

        $statement = $this->db->prepare(
            'SELECT id
             FROM pre_work_checklist_runs
             WHERE user_id = :user_id
                AND checklist_date = :checklist_date
             ORDER BY created_at DESC
             LIMIT 1'
        );

        $statement->execute([
            'user_id' => $userId,
            'checklist_date' => $date,
        ]);

        $run = $statement->fetch();

        if ($run === false) {
            return [
                'total_items' => $totalItems,
                'checked_count' => 0,
                'remaining_count' => $totalItems,
            ];
        }

        $countStatement = $this->db->prepare(
            'SELECT COUNT(*)
             FROM pre_work_checklist_logs
             WHERE checklist_run_id = :checklist_run_id
                AND is_checked = 1'
        );

        $countStatement->execute([
            'checklist_run_id' => (int) $run['id'],
        ]);

        $checkedCount = (int) $countStatement->fetchColumn();

        return [
            'total_items' => $totalItems,
            'checked_count' => $checkedCount,
            'remaining_count' => max($totalItems - $checkedCount, 0),
        ];
    }

    private function runItems(int $runId): array
    {
        $statement = $this->db->prepare(
            'SELECT i.code, i.label, l.is_checked, l.checked_at
             FROM pre_work_checklist_logs l
             INNER JOIN pre_work_checklist_items i
                ON i.id = l.checklist_item_id
             WHERE l.checklist_run_id = :checklist_run_id
             ORDER BY i.sort_order ASC, i.id ASC'
        );

        $statement->execute([
            'checklist_run_id' => $runId,
        ]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
