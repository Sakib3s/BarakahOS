<?php

declare(strict_types=1);

namespace App\Models;

class WeeklyDisciplineScore extends BaseModel
{
    public function scoreForRange(int $userId, string $startDate, string $endDate): array
    {
        $dayCount = max(1, ((int) ((strtotime($endDate) - strtotime($startDate)) / 86400)) + 1);
        $sleep = (new SleepLog())->summaryForRange($userId, $startDate, $endDate);
        $report = (new ProductivityReport())->getReport($userId, $startDate, $endDate);

        $factors = [
            'sleep' => [
                'label' => 'Sleep',
                'score' => $this->sleepScore((int) $sleep['average_minutes']),
                'detail' => 'Avg ' . format_duration((int) $sleep['average_minutes']) . ', ' . (string) $sleep['days_logged'] . ' days logged',
            ],
            'prayer' => [
                'label' => 'Prayer',
                'score' => $this->prayerScore($report['prayer']['summary']),
                'detail' => (string) $report['prayer']['summary']['logged_count'] . '/' . (string) $report['prayer']['summary']['expected_count'] . ' logged',
            ],
            'focus' => [
                'label' => 'Focus',
                'score' => $this->focusScore((int) $report['focus']['summary']['total_minutes'], $dayCount),
                'detail' => format_duration((int) $report['focus']['summary']['total_minutes']) . ' total focus',
            ],
            'distraction' => [
                'label' => 'Distractions',
                'score' => $this->distractionScore((int) $report['distraction']['summary']['total_duration_minutes'], $dayCount),
                'detail' => format_duration((int) $report['distraction']['summary']['total_duration_minutes']) . ' wasted',
            ],
            'tasks' => [
                'label' => 'Tasks',
                'score' => $this->taskScore($report['tasks']['summary']),
                'detail' => (string) $report['tasks']['summary']['done_count'] . '/' . (string) $report['tasks']['summary']['total_count'] . ' done',
            ],
        ];

        $overall = (int) round(array_sum(array_column($factors, 'score')) / count($factors));

        return [
            'overall' => $overall,
            'label' => $this->scoreLabel($overall),
            'factors' => $factors,
        ];
    }

    private function sleepScore(int $averageMinutes): int
    {
        if ($averageMinutes <= 0) {
            return 0;
        }

        if ($averageMinutes >= 420 && $averageMinutes <= 540) {
            return 100;
        }

        if ($averageMinutes < 420) {
            return max(0, (int) round(($averageMinutes - 240) / 180 * 100));
        }

        return max(0, (int) round((720 - $averageMinutes) / 180 * 100));
    }

    private function prayerScore(array $summary): int
    {
        $expected = max(1, (int) $summary['expected_count']);
        $logged = (int) $summary['logged_count'];
        $onTime = (int) $summary['on_time_count'];

        return min(100, (int) round(($logged / $expected * 70) + ($onTime / $expected * 30)));
    }

    private function focusScore(int $totalMinutes, int $dayCount): int
    {
        return min(100, (int) round($totalMinutes / max(1, $dayCount * 120) * 100));
    }

    private function distractionScore(int $wastedMinutes, int $dayCount): int
    {
        return max(0, 100 - (int) round($wastedMinutes / max(1, $dayCount * 240) * 100));
    }

    private function taskScore(array $summary): int
    {
        $total = (int) $summary['total_count'];

        if ($total < 1) {
            return 0;
        }

        return min(100, (int) round((int) $summary['done_count'] / $total * 100));
    }

    private function scoreLabel(int $score): string
    {
        return match (true) {
            $score >= 85 => 'Strong week',
            $score >= 70 => 'Good week',
            $score >= 50 => 'Needs attention',
            default => 'Reset needed',
        };
    }
}
