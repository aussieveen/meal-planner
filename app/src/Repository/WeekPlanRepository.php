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

    /** Compute the ISO date string (Y-m-d) for the Monday of the current week */
    public static function currentWeekStartDate(): string
    {
        $today = new \DateTimeImmutable();
        $dow   = (int) $today->format('N'); // 1=Mon … 7=Sun

        return $today->modify(sprintf('-%d days', $dow - 1))->format('Y-m-d');
    }
}
