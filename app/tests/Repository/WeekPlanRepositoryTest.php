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
}
