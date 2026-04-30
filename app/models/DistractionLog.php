<?php

declare(strict_types=1);

namespace App\Models;

class DistractionLog extends BaseModel
{
    public const TYPES = [
        'mobile_used',
        'social_media_used',
        'waste_time',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->ensureSchema();
    }

    public function create(int $userId, array $data): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO distraction_logs (
                user_id,
                focus_session_id,
                log_date,
                distraction_type,
                note,
                occurred_at,
                duration_minutes
             ) VALUES (
                :user_id,
                :focus_session_id,
                :log_date,
                :distraction_type,
                :note,
                :occurred_at,
                :duration_minutes
             )'
        );

        $statement->execute([
            'user_id' => $userId,
            'focus_session_id' => $data['focus_session_id'] ?? null,
            'log_date' => $data['log_date'],
            'distraction_type' => $data['distraction_type'],
            'note' => $data['note'],
            'occurred_at' => $data['occurred_at'],
            'duration_minutes' => $data['duration_minutes'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function todayLogsForUser(int $userId, string $date): array
    {
        $statement = $this->db->prepare(
            'SELECT
                id,
                distraction_type,
                note,
                occurred_at,
                duration_minutes,
                created_at
             FROM distraction_logs
             WHERE user_id = :user_id
                AND log_date = :log_date
             ORDER BY occurred_at DESC, id DESC'
        );

        $statement->execute([
            'user_id' => $userId,
            'log_date' => $date,
        ]);

        return $statement->fetchAll();
    }

    public function findForUser(int $id, int $userId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT
                id,
                log_date,
                distraction_type,
                note,
                occurred_at,
                duration_minutes
             FROM distraction_logs
             WHERE id = :id
                AND user_id = :user_id
             LIMIT 1'
        );

        $statement->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);

        $log = $statement->fetch();

        return $log === false ? null : $log;
    }

    public function update(int $id, int $userId, array $data): void
    {
        $statement = $this->db->prepare(
            'UPDATE distraction_logs
             SET distraction_type = :distraction_type,
                 duration_minutes = :duration_minutes,
                 note = :note
             WHERE id = :id
                AND user_id = :user_id'
        );

        $statement->execute([
            'id' => $id,
            'user_id' => $userId,
            'distraction_type' => $data['distraction_type'],
            'duration_minutes' => $data['duration_minutes'],
            'note' => $data['note'],
        ]);
    }

    public function deleteForUser(int $id, int $userId): void
    {
        $statement = $this->db->prepare(
            'DELETE FROM distraction_logs
             WHERE id = :id
                AND user_id = :user_id'
        );

        $statement->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);
    }

    public function todayCounts(int $userId, string $date): array
    {
        return $this->countsForRange($userId, $date, $date);
    }

    public function weeklyCounts(int $userId, string $startDate, string $endDate): array
    {
        return $this->countsForRange($userId, $startDate, $endDate);
    }

    public function monthlyCounts(int $userId, string $startDate, string $endDate): array
    {
        return $this->countsForRange($userId, $startDate, $endDate);
    }

    public function dashboardSummary(int $userId, string $date): array
    {
        return $this->todayCounts($userId, $date);
    }

    private function countsForRange(int $userId, string $startDate, string $endDate): array
    {
        $statement = $this->db->prepare(
            'SELECT
                COUNT(*) AS total_count,
                COALESCE(SUM(duration_minutes), 0) AS total_duration_minutes,
                COALESCE(SUM(CASE WHEN distraction_type IN (\'mobile_used\', \'phone_near\') THEN 1 ELSE 0 END), 0) AS mobile_used_count,
                COALESCE(SUM(CASE WHEN distraction_type = \'social_media_used\' THEN 1 ELSE 0 END), 0) AS social_media_used_count,
                COALESCE(SUM(CASE WHEN distraction_type IN (\'waste_time\', \'too_many_breaks\') THEN 1 ELSE 0 END), 0) AS waste_time_count
             FROM distraction_logs
             WHERE user_id = :user_id
                AND log_date BETWEEN :start_date AND :end_date'
        );

        $statement->execute([
            'user_id' => $userId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $summary = $statement->fetch();

        if ($summary === false) {
            return $this->emptyCounts();
        }

        return [
            'total_count' => (int) ($summary['total_count'] ?? 0),
            'total_duration_minutes' => (int) ($summary['total_duration_minutes'] ?? 0),
            'mobile_used_count' => (int) ($summary['mobile_used_count'] ?? 0),
            'social_media_used_count' => (int) ($summary['social_media_used_count'] ?? 0),
            'waste_time_count' => (int) ($summary['waste_time_count'] ?? 0),
        ];
    }

    private function emptyCounts(): array
    {
        return [
            'total_count' => 0,
            'total_duration_minutes' => 0,
            'mobile_used_count' => 0,
            'social_media_used_count' => 0,
            'waste_time_count' => 0,
        ];
    }

    private function ensureSchema(): void
    {
        $durationColumn = $this->db->query(
            "SELECT COUNT(*) AS column_count
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'distraction_logs'
                AND COLUMN_NAME = 'duration_minutes'"
        )->fetch();

        if ((int) ($durationColumn['column_count'] ?? 0) === 0) {
            $this->db->exec(
                'ALTER TABLE distraction_logs
                 ADD COLUMN duration_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER occurred_at'
            );
        }

        $typeColumn = $this->db->query(
            "SELECT COLUMN_TYPE
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'distraction_logs'
                AND COLUMN_NAME = 'distraction_type'
             LIMIT 1"
        )->fetch();

        $columnType = (string) ($typeColumn['COLUMN_TYPE'] ?? '');

        if (!str_contains($columnType, 'mobile_used') || !str_contains($columnType, 'waste_time')) {
            $this->db->exec(
                "ALTER TABLE distraction_logs
                 MODIFY distraction_type ENUM('mobile_used', 'phone_near', 'social_media_used', 'waste_time', 'too_many_breaks') NOT NULL"
            );
        }
    }
}
