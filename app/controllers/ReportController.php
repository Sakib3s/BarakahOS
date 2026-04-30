<?php

declare(strict_types=1);

namespace App\Controllers;

use App\HttpException;
use App\Models\ProductivityReport;
use DateTimeImmutable;
use DateTimeZone;

class ReportController extends BaseController
{
    public function daily(): void
    {
        [$startDate, $endDate] = $this->resolveRange('daily');
        $this->renderReport('daily', $startDate, $endDate);
    }

    public function weekly(): void
    {
        [$startDate, $endDate] = $this->resolveRange('weekly');
        $this->renderReport('weekly', $startDate, $endDate);
    }

    public function monthly(): void
    {
        [$startDate, $endDate] = $this->resolveRange('monthly');
        $this->renderReport('monthly', $startDate, $endDate);
    }

    private function renderReport(string $period, string $startDate, string $endDate): void
    {
        $report = new ProductivityReport();

        $this->render('reports/' . $period, [
            'pageTitle' => ucfirst($period) . ' Report',
            'report' => $report->getReport($this->userId(), $startDate, $endDate),
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'rangeLabel' => $this->rangeLabel($startDate, $endDate),
        ]);
    }

    private function resolveRange(string $period): array
    {
        $queryStart = trim((string) ($_GET['start_date'] ?? ''));
        $queryEnd = trim((string) ($_GET['end_date'] ?? ''));

        if ($queryStart !== '' && $queryEnd !== '' && $this->isValidDate($queryStart) && $this->isValidDate($queryEnd) && $queryStart <= $queryEnd) {
            return [$queryStart, $queryEnd];
        }

        $today = new DateTimeImmutable(
            'now',
            new DateTimeZone((string) config('app.timezone', 'Asia/Dhaka'))
        );

        return match ($period) {
            'daily' => [$today->format('Y-m-d'), $today->format('Y-m-d')],
            'weekly' => [
                $today->modify('monday this week')->format('Y-m-d'),
                $today->modify('monday this week')->modify('+6 days')->format('Y-m-d'),
            ],
            'monthly' => [
                $today->modify('first day of this month')->format('Y-m-d'),
                $today->modify('last day of this month')->format('Y-m-d'),
            ],
            default => [$today->format('Y-m-d'), $today->format('Y-m-d')],
        };
    }

    private function rangeLabel(string $startDate, string $endDate): string
    {
        $start = new DateTimeImmutable($startDate);
        $end = new DateTimeImmutable($endDate);

        if ($startDate === $endDate) {
            return $start->format('l, d F Y');
        }

        return $start->format('d M Y') . ' - ' . $end->format('d M Y');
    }

    private function isValidDate(string $value): bool
    {
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $value);

        return $date instanceof DateTimeImmutable && $date->format('Y-m-d') === $value;
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
