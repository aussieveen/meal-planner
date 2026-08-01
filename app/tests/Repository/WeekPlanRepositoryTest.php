<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Repository\WeekPlanRepository;
use PHPUnit\Framework\TestCase;

class WeekPlanRepositoryTest extends TestCase
{
    public function testCurrentWeekStartDateIsAlwaysAMonday(): void
    {
        $date = WeekPlanRepository::currentWeekStartDate();

        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $date);
        $this->assertSame('1', (new \DateTimeImmutable($date))->format('N'), 'Week start date must be a Monday (N=1)');
    }

    /** @dataProvider weekStartProvider */
    #[\PHPUnit\Framework\Attributes\DataProvider('weekStartProvider')]
    public function testWeekStartDateForReturnsMonday(string $input, string $expected): void
    {
        $this->assertSame($expected, WeekPlanRepository::weekStartDateFor($input));
    }

    public static function weekStartProvider(): array
    {
        return [
            'monday'    => ['2026-07-27', '2026-07-27'],
            'wednesday' => ['2026-07-29', '2026-07-27'],
            'saturday'  => ['2026-08-01', '2026-07-27'],
            'sunday'    => ['2026-08-02', '2026-07-27'],
        ];
    }
}
