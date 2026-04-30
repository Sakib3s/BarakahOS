<?php

declare(strict_types=1);

namespace App\Models;

class RoutineTemplate extends BaseModel
{
    public const CATEGORIES = [
        'prayer',
        'health',
        'planning',
        'work',
        'trading',
        'trading_learning',
        'coding',
        'support',
        'class',
        'family',
        'rest',
        'other',
    ];

    public const WEEKDAYS = [
        'monday' => 'Mon',
        'tuesday' => 'Tue',
        'wednesday' => 'Wed',
        'thursday' => 'Thu',
        'friday' => 'Fri',
        'saturday' => 'Sat',
        'sunday' => 'Sun',
    ];

    public function allForUser(int $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT
                id,
                title,
                category,
                start_time,
                end_time,
                is_fixed_task,
                active_days,
                sort_order,
                created_at,
                updated_at
             FROM routine_templates
             WHERE user_id = :user_id
             ORDER BY sort_order ASC, COALESCE(start_time, \'23:59:59\') ASC, title ASC'
        );

        $statement->execute([
            'user_id' => $userId,
        ]);

        $templates = $statement->fetchAll();

        return array_map([$this, 'mapTemplate'], $templates);
    }

    public function findForUser(int $id, int $userId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT
                id,
                title,
                category,
                start_time,
                end_time,
                is_fixed_task,
                active_days,
                sort_order,
                created_at,
                updated_at
             FROM routine_templates
             WHERE id = :id
                AND user_id = :user_id
             LIMIT 1'
        );

        $statement->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);

        $template = $statement->fetch();

        return $template === false ? null : $this->mapTemplate($template);
    }

    public function create(int $userId, array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO routine_templates (
                user_id,
                title,
                category,
                start_time,
                end_time,
                is_fixed_task,
                active_days,
                sort_order
             ) VALUES (
                :user_id,
                :title,
                :category,
                :start_time,
                :end_time,
                :is_fixed_task,
                :active_days,
                :sort_order
             )'
        );

        $statement->execute([
            'user_id' => $userId,
            'title' => trim((string) $data['title']),
            'category' => (string) $data['category'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'is_fixed_task' => !empty($data['is_fixed_task']) ? 1 : 0,
            'active_days' => json_encode(array_values($data['active_days']), JSON_THROW_ON_ERROR),
            'sort_order' => (int) $data['sort_order'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, int $userId, array $data): void
    {
        $statement = $this->db->prepare(
            'UPDATE routine_templates
             SET
                title = :title,
                category = :category,
                start_time = :start_time,
                end_time = :end_time,
                is_fixed_task = :is_fixed_task,
                active_days = :active_days,
                sort_order = :sort_order
             WHERE id = :id
                AND user_id = :user_id'
        );

        $statement->execute([
            'id' => $id,
            'user_id' => $userId,
            'title' => trim((string) $data['title']),
            'category' => (string) $data['category'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'is_fixed_task' => !empty($data['is_fixed_task']) ? 1 : 0,
            'active_days' => json_encode(array_values($data['active_days']), JSON_THROW_ON_ERROR),
            'sort_order' => (int) $data['sort_order'],
        ]);
    }

    public function delete(int $id, int $userId): void
    {
        $statement = $this->db->prepare(
            'DELETE FROM routine_templates
             WHERE id = :id
                AND user_id = :user_id'
        );

        $statement->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);
    }

    private function mapTemplate(array $template): array
    {
        $decodedDays = json_decode((string) $template['active_days'], true);

        $template['active_days'] = is_array($decodedDays) ? $decodedDays : [];
        $template['is_fixed_task'] = (bool) $template['is_fixed_task'];
        $template['sort_order'] = (int) $template['sort_order'];

        return $template;
    }
}
