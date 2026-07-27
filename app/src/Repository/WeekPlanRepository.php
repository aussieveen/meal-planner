<?php

declare(strict_types=1);

namespace App\Repository;

use App\Document\WeekPlan;
use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;

class WeekPlanRepository extends ServiceDocumentRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeekPlan::class);
    }

    public function findByWeekStartDate(string $weekStartDate): ?WeekPlan
    {
        return $this->findOneBy(['weekStartDate' => $weekStartDate]);
    }

    public function findOrCreateForDate(string $weekStartDate): WeekPlan
    {
        return $this->findByWeekStartDate($weekStartDate) ?? new WeekPlan($weekStartDate);
    }

    /** All plans whose week starts on or after the Monday containing $fromDate, sorted ascending */
    public function findFromDate(string $fromDate): array
    {
        $monday = self::weekStartDateFor($fromDate);

        return $this->createQueryBuilder()
            ->field('weekStartDate')->gte($monday)
            ->sort('weekStartDate', 'asc')
            ->getQuery()
            ->execute()
            ->toArray();
    }

    /** Compute the ISO date string (Y-m-d) for the Monday of the current week */
    public static function currentWeekStartDate(): string
    {
        return self::weekStartDateFor((new \DateTimeImmutable())->format('Y-m-d'));
    }

    public static function weekStartDateFor(string $date): string
    {
        $d   = new \DateTimeImmutable($date);
        $dow = (int) $d->format('N'); // 1=Mon … 7=Sun

        return $d->modify(sprintf('-%d days', $dow - 1))->format('Y-m-d');
    }
}
