<?php

declare(strict_types=1);

namespace App\Controllers;

use App\HttpException;
use App\Models\WeeklyDisciplineScore;
use DateTimeImmutable;
use DateTimeZone;

class WeeklyScoreController extends BaseController
{
    public function index(): void
    {
        $today = new DateTimeImmutable(
            'now',
            new DateTimeZone((string) config('app.timezone', 'Asia/Dhaka'))
        );
        $startDate = $today->modify('monday this week')->format('Y-m-d');
        $endDate = $today->modify('monday this week')->modify('+6 days')->format('Y-m-d');
        $model = new WeeklyDisciplineScore();

        $this->render('weekly_score/index', [
            'pageTitle' => 'Weekly Score',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'rangeLabel' => (new DateTimeImmutable($startDate))->format('d M') . ' - ' . (new DateTimeImmutable($endDate))->format('d M Y'),
            'score' => $model->scoreForRange($this->userId(), $startDate, $endDate),
        ]);
    }

    private function userId(): int
    {
        $userId = auth_user_id();

        if ($userId === null) {
            throw new HttpException('Authentication is required.', 403);
        }

        return $userId;
    }
}
