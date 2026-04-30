<?php

declare(strict_types=1);

namespace App\Models;

class SleepLog extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->ensureSchema();
    }

    public function findForUserAndDate(int $userId, string $date): ?array
    {
        $statement = $this->db->prepare(
            'SELECT
                id,
                sleep_date,
                sleep_started_at,
                woke_up_at,
                duration_minutes,
                note,
                created_at,
                updated_at
             FROM sleep_logs
             WHERE user_id = :user_id
                AND sleep_date = :sleep_date
             LIMIT 1'
        );

        $statement->execute([
            'user_id' => $userId,
            'sleep_date' => $date,
        ]);

        $log = $statement->fetch();

        if ($log === false) {
            return null;
        }

        $log['duration_minutes'] = (int) $log['duration_minutes'];

        return $log;
    }

    public function upsertForUserAndDate(int $userId, string $sleepDate, array $data): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO sleep_logs (
                user_id,
                sleep_date,
                sleep_started_at,
                woke_up_at,
                duration_minutes,
                note
             ) VALUES (
                :user_id,
                :sleep_date,
                :sleep_started_at,
                :woke_up_at,
                :duration_minutes,
                :note
             )
             ON DUPLICATE KEY UPDATE
                sleep_started_at = VALUES(sleep_started_at),
                woke_up_at = VALUES(woke_up_at),
                duration_minutes = VALUES(duration_minutes),
                note = VALUES(note),
                updated_at = CURRENT_TIMESTAMP'
        );

        $statement->execute([
            'user_id' => $userId,
            'sleep_date' => $sleepDate,
            'sleep_started_at' => $data['sleep_started_at'],
            'woke_up_at' => $data['woke_up_at'],
            'duration_minutes' => $data['duration_minutes'],
            'note' => $data['note'],
        ]);
    }

    public function recentForUser(int $userId, int $limit = 14): array
    {
        $statement = $this->db->prepare(
            'SELECT
                id,
                sleep_date,
                sleep_started_at,
                woke_up_at,
                duration_minutes,
                note
             FROM sleep_logs
             WHERE user_id = :user_id
             ORDER BY sleep_date DESC, woke_up_at DESC
             LIMIT ' . max(1, $limit)
        );

        $statement->execute([
            'user_id' => $userId,
        ]);

        return $statement->fetchAll();
    }

    public function deleteForUser(int $id, int $userId): void
    {
        $statement = $this->db->prepare(
            'DELETE FROM sleep_logs
             WHERE id = :id
                AND user_id = :user_id'
        );

        $statement->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);
    }

    public function summaryForRange(int $userId, string $startDate, string $endDate): array
    {
        $statement = $this->db->prepare(
            'SELECT
                COUNT(*) AS days_logged,
                COALESCE(SUM(duration_minutes), 0) AS total_minutes,
                COALESCE(ROUND(AVG(duration_minutes)), 0) AS average_minutes,
                COALESCE(MIN(duration_minutes), 0) AS shortest_minutes,
                COALESCE(MAX(duration_minutes), 0) AS longest_minutes
             FROM sleep_logs
             WHERE user_id = :user_id
                AND sleep_date BETWEEN :start_date AND :end_date'
        );

        $statement->execute([
            'user_id' => $userId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $summary = $statement->fetch();

        if ($summary === false) {
            return $this->emptySummary();
        }

        return [
            'days_logged' => (int) ($summary['days_logged'] ?? 0),
            'total_minutes' => (int) ($summary['total_minutes'] ?? 0),
            'average_minutes' => (int) ($summary['average_minutes'] ?? 0),
            'shortest_minutes' => (int) ($summary['shortest_minutes'] ?? 0),
            'longest_minutes' => (int) ($summary['longest_minutes'] ?? 0),
        ];
    }

    public function detailsForRange(int $userId, string $startDate, string $endDate): array
    {
        $statement = $this->db->prepare(
            'SELECT
                sleep_date,
                sleep_started_at,
                woke_up_at,
                duration_minutes,
                note
             FROM sleep_logs
             WHERE user_id = :user_id
                AND sleep_date BETWEEN :start_date AND :end_date
             ORDER BY sleep_date DESC, woke_up_at DESC'
        );

        $statement->execute([
            'user_id' => $userId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return $statement->fetchAll();
    }

    private function emptySummary(): array
    {
        return [
            'days_logged' => 0,
            'total_minutes' => 0,
            'average_minutes' => 0,
            'shortest_minutes' => 0,
            'longest_minutes' => 0,
        ];
    }

    private function ensureSchema(): void
    {
        $this->db->exec(
            'CREATE TABLE IF NOT EXISTS sleep_logs (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                sleep_date DATE NOT NULL,
                sleep_started_at DATETIME NOT NULL,
                woke_up_at DATETIME NOT NULL,
                duration_minutes SMALLINT UNSIGNED NOT NULL,
                note TEXT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uq_sleep_logs_user_date (user_id, sleep_date),
                KEY idx_sleep_logs_user_date (user_id, sleep_date),
                KEY idx_sleep_logs_user_woke (user_id, woke_up_at),
                CONSTRAINT chk_sleep_logs_duration_minutes
                    CHECK (duration_minutes > 0),
                CONSTRAINT chk_sleep_logs_time_range
                    CHECK (woke_up_at > sleep_started_at),
                CONSTRAINT fk_sleep_logs_user
                    FOREIGN KEY (user_id) REFERENCES users (id)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }
}
