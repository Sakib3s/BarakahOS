<?php

declare(strict_types=1);

namespace App\Models;

class PrayerLog extends BaseModel
{
    public const PRAYERS = [
        ['code' => 'fajr', 'name' => 'Fajr', 'sort_order' => 1],
        ['code' => 'dhuhr', 'name' => 'Dhuhr', 'sort_order' => 2],
        ['code' => 'asr', 'name' => 'Asr', 'sort_order' => 3],
        ['code' => 'maghrib', 'name' => 'Maghrib', 'sort_order' => 4],
        ['code' => 'isha', 'name' => 'Isha', 'sort_order' => 5],
    ];

    public const STATUSES = [
        'on_time',
        'delayed',
        'missed',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->ensureSchema();
    }

    public function ensureDefaultDefinitions(): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO prayer_definitions (code, name, sort_order)
             VALUES (:code, :name, :sort_order)
             ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                sort_order = VALUES(sort_order)'
        );

        foreach (self::PRAYERS as $prayer) {
            $statement->execute($prayer);
        }
    }

    public function dailyChecklistForUser(int $userId, string $date): array
    {
        $statement = $this->db->prepare(
            'SELECT
                pd.id,
                pd.code,
                pd.name,
                pd.sort_order,
                pl.status,
                pl.note,
                pl.prayed_at
             FROM prayer_definitions pd
             LEFT JOIN prayer_logs pl
                ON pl.prayer_definition_id = pd.id
                AND pl.user_id = :user_id
                AND pl.log_date = :log_date
             ORDER BY pd.sort_order ASC'
        );

        $statement->execute([
            'user_id' => $userId,
            'log_date' => $date,
        ]);

        return $statement->fetchAll();
    }

    public function dailySummaryForUser(int $userId, string $date): array
    {
        $statement = $this->db->prepare(
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
                AND pl.log_date = :log_date'
        );

        $statement->execute([
            'user_id' => $userId,
            'log_date' => $date,
        ]);

        $summary = $statement->fetch();

        if ($summary === false) {
            return [
                'expected_count' => 0,
                'logged_count' => 0,
                'on_time_count' => 0,
                'delayed_count' => 0,
                'missed_count' => 0,
                'remaining_count' => 0,
            ];
        }

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

    public function weeklySummaryForUser(int $userId, string $startDate, string $endDate): array
    {
        $totalsStatement = $this->db->prepare(
            'SELECT
                COUNT(*) AS logged_count,
                COALESCE(SUM(CASE WHEN status = \'on_time\' THEN 1 ELSE 0 END), 0) AS on_time_count,
                COALESCE(SUM(CASE WHEN status = \'delayed\' THEN 1 ELSE 0 END), 0) AS delayed_count,
                COALESCE(SUM(CASE WHEN status = \'missed\' THEN 1 ELSE 0 END), 0) AS missed_count
             FROM prayer_logs
             WHERE user_id = :user_id
                AND log_date BETWEEN :start_date AND :end_date'
        );

        $totalsStatement->execute([
            'user_id' => $userId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $totals = $totalsStatement->fetch() ?: [];
        $dayCount = ((strtotime($endDate) - strtotime($startDate)) / 86400) + 1;
        $expectedCount = count(self::PRAYERS) * max(1, (int) $dayCount);

        $byPrayerStatement = $this->db->prepare(
            'SELECT
                pd.id,
                pd.code,
                pd.name,
                pd.sort_order,
                COALESCE(SUM(CASE WHEN pl.status = \'on_time\' THEN 1 ELSE 0 END), 0) AS on_time_count,
                COALESCE(SUM(CASE WHEN pl.status = \'delayed\' THEN 1 ELSE 0 END), 0) AS delayed_count,
                COALESCE(SUM(CASE WHEN pl.status = \'missed\' THEN 1 ELSE 0 END), 0) AS missed_count,
                COALESCE(COUNT(pl.id), 0) AS logged_count
             FROM prayer_definitions pd
             LEFT JOIN prayer_logs pl
                ON pl.prayer_definition_id = pd.id
                AND pl.user_id = :user_id
                AND pl.log_date BETWEEN :start_date AND :end_date
             GROUP BY pd.id, pd.code, pd.name, pd.sort_order
             ORDER BY pd.sort_order ASC'
        );

        $byPrayerStatement->execute([
            'user_id' => $userId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return [
            'totals' => [
                'expected_count' => $expectedCount,
                'logged_count' => (int) ($totals['logged_count'] ?? 0),
                'on_time_count' => (int) ($totals['on_time_count'] ?? 0),
                'delayed_count' => (int) ($totals['delayed_count'] ?? 0),
                'missed_count' => (int) ($totals['missed_count'] ?? 0),
                'remaining_count' => max($expectedCount - (int) ($totals['logged_count'] ?? 0), 0),
            ],
            'by_prayer' => $byPrayerStatement->fetchAll(),
        ];
    }

    public function definitionExists(int $definitionId): bool
    {
        $statement = $this->db->prepare(
            'SELECT id
             FROM prayer_definitions
             WHERE id = :id
             LIMIT 1'
        );

        $statement->execute([
            'id' => $definitionId,
        ]);

        return $statement->fetch() !== false;
    }

    public function upsertForUser(int $userId, int $definitionId, string $date, string $status, ?string $note, ?string $prayedAt): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO prayer_logs (
                user_id,
                prayer_definition_id,
                log_date,
                status,
                prayed_at,
                note
             ) VALUES (
                :user_id,
                :prayer_definition_id,
                :log_date,
                :status,
                :prayed_at,
                :note
             )
             ON DUPLICATE KEY UPDATE
                status = VALUES(status),
                prayed_at = VALUES(prayed_at),
                note = VALUES(note),
                updated_at = CURRENT_TIMESTAMP'
        );

        $statement->execute([
            'user_id' => $userId,
            'prayer_definition_id' => $definitionId,
            'log_date' => $date,
            'status' => $status,
            'prayed_at' => $prayedAt,
            'note' => $note,
        ]);
    }

    private function ensureSchema(): void
    {
        $definitionColumn = $this->db->query(
            "SELECT DATA_TYPE
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'prayer_definitions'
                AND COLUMN_NAME = 'id'
             LIMIT 1"
        )->fetch();

        if (($definitionColumn['DATA_TYPE'] ?? '') !== 'tinyint') {
            return;
        }

        $constraint = $this->db->query(
            "SELECT CONSTRAINT_NAME
             FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'prayer_logs'
                AND COLUMN_NAME = 'prayer_definition_id'
                AND REFERENCED_TABLE_NAME = 'prayer_definitions'
             LIMIT 1"
        )->fetch();

        if ($constraint !== false && !empty($constraint['CONSTRAINT_NAME'])) {
            $this->db->exec('ALTER TABLE prayer_logs DROP FOREIGN KEY ' . $constraint['CONSTRAINT_NAME']);
        }

        $this->db->exec('ALTER TABLE prayer_definitions MODIFY id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT');
        $this->db->exec('ALTER TABLE prayer_logs MODIFY prayer_definition_id SMALLINT UNSIGNED NOT NULL');
        $this->db->exec(
            'ALTER TABLE prayer_logs
             ADD CONSTRAINT fk_prayer_logs_definition
             FOREIGN KEY (prayer_definition_id) REFERENCES prayer_definitions (id)
             ON DELETE RESTRICT'
        );
    }
}
