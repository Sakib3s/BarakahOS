<?php

declare(strict_types=1);

namespace App\Models;

class DailyReview extends BaseModel
{
    public function findForUserAndDate(int $userId, string $date): ?array
    {
        $statement = $this->db->prepare(
            'SELECT
                id,
                review_date,
                day_rating,
                what_went_well,
                what_failed,
                top_lesson,
                tomorrow_priority,
                sleep_note,
                created_at,
                updated_at
             FROM daily_reviews
             WHERE user_id = :user_id
                AND review_date = :review_date
             LIMIT 1'
        );

        $statement->execute([
            'user_id' => $userId,
            'review_date' => $date,
        ]);

        $review = $statement->fetch();

        if ($review === false) {
            return null;
        }

        $review['day_rating'] = $review['day_rating'] === null ? null : (int) $review['day_rating'];

        return $review;
    }

    public function upsertForUserAndDate(int $userId, string $date, array $data): void
    {
        $statement = $this->db->prepare(
            'INSERT INTO daily_reviews (
                user_id,
                review_date,
                day_rating,
                what_went_well,
                what_failed,
                top_lesson,
                tomorrow_priority,
                sleep_note
             ) VALUES (
                :user_id,
                :review_date,
                :day_rating,
                :what_went_well,
                :what_failed,
                :top_lesson,
                :tomorrow_priority,
                :sleep_note
             )
             ON DUPLICATE KEY UPDATE
                day_rating = VALUES(day_rating),
                what_went_well = VALUES(what_went_well),
                what_failed = VALUES(what_failed),
                top_lesson = VALUES(top_lesson),
                tomorrow_priority = VALUES(tomorrow_priority),
                sleep_note = VALUES(sleep_note),
                updated_at = CURRENT_TIMESTAMP'
        );

        $statement->execute([
            'user_id' => $userId,
            'review_date' => $date,
            'day_rating' => $data['day_rating'],
            'what_went_well' => $data['what_went_well'],
            'what_failed' => $data['what_failed'],
            'top_lesson' => $data['top_lesson'],
            'tomorrow_priority' => $data['tomorrow_priority'],
            'sleep_note' => $data['sleep_note'],
        ]);
    }

    public function dashboardSummary(int $userId, string $date): array
    {
        $review = $this->findForUserAndDate($userId, $date);

        if ($review === null) {
            return [
                'has_review' => false,
                'day_rating' => null,
                'top_lesson' => null,
                'tomorrow_priority' => null,
            ];
        }

        return [
            'has_review' => true,
            'day_rating' => $review['day_rating'],
            'top_lesson' => $review['top_lesson'],
            'tomorrow_priority' => $review['tomorrow_priority'],
        ];
    }

    public function summaryForRange(int $userId, string $startDate, string $endDate): array
    {
        $statement = $this->db->prepare(
            'SELECT
                COUNT(*) AS days_logged,
                COALESCE(ROUND(AVG(day_rating), 1), 0) AS average_day_rating,
                COALESCE(MIN(day_rating), 0) AS min_day_rating,
                COALESCE(MAX(day_rating), 0) AS max_day_rating
             FROM daily_reviews
             WHERE user_id = :user_id
                AND review_date BETWEEN :start_date AND :end_date'
        );

        $statement->execute([
            'user_id' => $userId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $summary = $statement->fetch();

        if ($summary === false) {
            return [
                'days_logged' => 0,
                'average_day_rating' => 0.0,
                'min_day_rating' => 0,
                'max_day_rating' => 0,
            ];
        }

        return [
            'days_logged' => (int) ($summary['days_logged'] ?? 0),
            'average_day_rating' => (float) ($summary['average_day_rating'] ?? 0),
            'min_day_rating' => (int) ($summary['min_day_rating'] ?? 0),
            'max_day_rating' => (int) ($summary['max_day_rating'] ?? 0),
        ];
    }

    public function detailsForRange(int $userId, string $startDate, string $endDate): array
    {
        return $this->fetchAll(
            'SELECT
                review_date,
                day_rating,
                what_went_well,
                what_failed,
                top_lesson,
                tomorrow_priority,
                sleep_note
             FROM daily_reviews
             WHERE user_id = :user_id
                AND review_date BETWEEN :start_date AND :end_date
             ORDER BY review_date DESC',
            [
                'user_id' => $userId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        );
    }

    private function fetchAll(string $sql, array $params): array
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }
}
