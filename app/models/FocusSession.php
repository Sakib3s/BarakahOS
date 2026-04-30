<?php

declare(strict_types=1);

namespace App\Models;

class FocusSession extends BaseModel
{
    public const DEFAULT_CATEGORIES = [
        'work',
        'coding',
        'trading',
        'trading_learning',
        'support',
        'planning',
        'admin',
        'other',
    ];

    public function ensureDefaultCategories(int $userId): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO focus_categories (user_id, name, is_active)
             VALUES (:user_id, :name, 1)
             ON DUPLICATE KEY UPDATE is_active = 1'
        );

        foreach (self::DEFAULT_CATEGORIES as $name) {
            $statement->execute([
                'user_id' => $userId,
                'name' => $name,
            ]);
        }
    }

    public function categoryOptionsForUser(int $userId): array
    {
        $placeholders = implode(', ', array_fill(0, count(self::DEFAULT_CATEGORIES), '?'));
        $statement = $this->db->prepare(
            'SELECT id, name
             FROM focus_categories
             WHERE user_id = ?
                AND is_active = 1
                AND name IN (' . $placeholders . ')
             ORDER BY FIELD(name, ' . $placeholders . ')'
        );

        $statement->execute([
            $userId,
            ...self::DEFAULT_CATEGORIES,
            ...self::DEFAULT_CATEGORIES,
        ]);

        return $statement->fetchAll();
    }

    public function latestCompletedChecklistForUser(int $userId): ?array
    {
        $checklist = new PreWorkChecklist();

        return $checklist->latestCompletedRunForUser($userId);
    }

    public function runningSessionForUser(int $userId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT
                fs.id,
                fs.start_time,
                fs.note,
                fc.name AS category_name,
                pcr.id AS checklist_run_id,
                pcr.completed_at AS checklist_completed_at
             FROM focus_sessions fs
             INNER JOIN focus_categories fc
                ON fc.id = fs.focus_category_id
             LEFT JOIN pre_work_checklist_runs pcr
                ON pcr.id = fs.pre_work_checklist_run_id
             WHERE fs.user_id = :user_id
                AND fs.end_time IS NULL
             ORDER BY fs.start_time DESC
             LIMIT 1'
        );

        $statement->execute([
            'user_id' => $userId,
        ]);

        $session = $statement->fetch();

        return $session === false ? null : $session;
    }

    public function startSession(int $userId, array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO focus_sessions (
                user_id,
                focus_category_id,
                pre_work_checklist_run_id,
                session_date,
                start_time,
                end_time,
                duration_minutes,
                note
             ) VALUES (
                :user_id,
                :focus_category_id,
                :pre_work_checklist_run_id,
                :session_date,
                :start_time,
                :end_time,
                :duration_minutes,
                :note
             )'
        );

        $statement->execute([
            'user_id' => $userId,
            'focus_category_id' => $data['focus_category_id'],
            'pre_work_checklist_run_id' => $data['pre_work_checklist_run_id'],
            'session_date' => $data['session_date'],
            'start_time' => $data['start_time'],
            'end_time' => null,
            'duration_minutes' => null,
            'note' => $data['note'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function endSession(int $sessionId, int $userId, string $endTime): void
    {
        $statement = $this->db->prepare(
            'SELECT start_time
             FROM focus_sessions
             WHERE id = :id
                AND user_id = :user_id
                AND end_time IS NULL
             LIMIT 1'
        );

        $statement->execute([
            'id' => $sessionId,
            'user_id' => $userId,
        ]);

        $session = $statement->fetch();

        if ($session === false) {
            return;
        }

        $startTimestamp = strtotime((string) $session['start_time']);
        $endTimestamp = strtotime($endTime);
        $durationMinutes = max(1, (int) ceil(($endTimestamp - $startTimestamp) / 60));

        $updateStatement = $this->db->prepare(
            'UPDATE focus_sessions
             SET end_time = :end_time,
                 duration_minutes = :duration_minutes
             WHERE id = :id
                AND user_id = :user_id
                AND end_time IS NULL'
        );

        $updateStatement->execute([
            'end_time' => $endTime,
            'duration_minutes' => $durationMinutes,
            'id' => $sessionId,
            'user_id' => $userId,
        ]);
    }

    public function deleteForUser(int $sessionId, int $userId): void
    {
        $statement = $this->db->prepare(
            'DELETE FROM focus_sessions
             WHERE id = :id
                AND user_id = :user_id'
        );

        $statement->execute([
            'id' => $sessionId,
            'user_id' => $userId,
        ]);
    }

    public function todaySessionsForUser(int $userId, string $date): array
    {
        $statement = $this->db->prepare(
            'SELECT
                fs.id,
                fs.start_time,
                fs.end_time,
                fs.duration_minutes,
                fs.note,
                fc.name AS category_name,
                pcr.completed_at AS checklist_completed_at
             FROM focus_sessions fs
             INNER JOIN focus_categories fc
                ON fc.id = fs.focus_category_id
             LEFT JOIN pre_work_checklist_runs pcr
                ON pcr.id = fs.pre_work_checklist_run_id
             WHERE fs.user_id = :user_id
                AND fs.session_date = :session_date
             ORDER BY fs.start_time DESC'
        );

        $statement->execute([
            'user_id' => $userId,
            'session_date' => $date,
        ]);

        return $statement->fetchAll();
    }

    public function todaySummaryForUser(int $userId, string $date): array
    {
        $summaryStatement = $this->db->prepare(
            'SELECT
                COUNT(*) AS total_sessions,
                COALESCE(SUM(duration_minutes), 0) AS total_focus_minutes,
                COALESCE(ROUND(AVG(duration_minutes)), 0) AS average_session_length,
                COALESCE(MAX(duration_minutes), 0) AS longest_session
             FROM focus_sessions
             WHERE user_id = :user_id
                AND session_date = :session_date'
        );

        $summaryStatement->execute([
            'user_id' => $userId,
            'session_date' => $date,
        ]);

        $summary = $summaryStatement->fetch();

        if ($summary === false) {
            $summary = [
                'total_sessions' => 0,
                'total_focus_minutes' => 0,
                'average_session_length' => 0,
                'longest_session' => 0,
            ];
        }

        $categoryParams = [
            'join_user_id' => $userId,
            'join_session_date' => $date,
            'where_user_id' => $userId,
        ];
        $inCategoryPlaceholders = [];
        $orderCategoryPlaceholders = [];

        foreach (self::DEFAULT_CATEGORIES as $index => $categoryName) {
            $inPlaceholder = 'in_category_' . $index;
            $orderPlaceholder = 'order_category_' . $index;

            $inCategoryPlaceholders[] = ':' . $inPlaceholder;
            $orderCategoryPlaceholders[] = ':' . $orderPlaceholder;
            $categoryParams[$inPlaceholder] = $categoryName;
            $categoryParams[$orderPlaceholder] = $categoryName;
        }

        $categoryStatement = $this->db->prepare(
            'SELECT
                fc.name AS category_name,
                COUNT(fs.id) AS total_sessions,
                COALESCE(SUM(fs.duration_minutes), 0) AS total_minutes
             FROM focus_categories fc
             LEFT JOIN focus_sessions fs
                ON fs.focus_category_id = fc.id
                AND fs.user_id = :join_user_id
                AND fs.session_date = :join_session_date
             WHERE fc.user_id = :where_user_id
                AND fc.name IN (' . implode(', ', $inCategoryPlaceholders) . ')
             GROUP BY fc.id, fc.name
             ORDER BY FIELD(fc.name, ' . implode(', ', $orderCategoryPlaceholders) . ')'
        );

        $categoryStatement->execute($categoryParams);

        return [
            'totals' => array_map('intval', $summary),
            'by_category' => array_map(static function (array $row): array {
                return [
                    'category_name' => (string) $row['category_name'],
                    'total_sessions' => (int) $row['total_sessions'],
                    'total_minutes' => (int) $row['total_minutes'],
                ];
            }, $categoryStatement->fetchAll()),
        ];
    }

}
