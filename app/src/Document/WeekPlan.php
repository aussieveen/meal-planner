<?php

declare(strict_types=1);

namespace App\Document;

use App\Repository\WeekPlanRepository;
use Doctrine\ODM\MongoDB\Mapping\Attribute as ODM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ODM\Document(
    repositoryClass: WeekPlanRepository::class,
    indexes: [new ODM\Index(keys: ['weekStartDate' => 'asc'], unique: true)]
)]
class WeekPlan
{
    public const array DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    #[ODM\Id]
    private ?string $id = null;

    #[ODM\Field(type: 'string')]
    #[Groups(['plan:read'])]
    private string $weekStartDate;

    #[ODM\EmbedOne(targetDocument: DayPlan::class, nullable: true)]
    #[Groups(['plan:read'])]
    private ?DayPlan $monday = null;

    #[ODM\EmbedOne(targetDocument: DayPlan::class, nullable: true)]
    #[Groups(['plan:read'])]
    private ?DayPlan $tuesday = null;

    #[ODM\EmbedOne(targetDocument: DayPlan::class, nullable: true)]
    #[Groups(['plan:read'])]
    private ?DayPlan $wednesday = null;

    #[ODM\EmbedOne(targetDocument: DayPlan::class, nullable: true)]
    #[Groups(['plan:read'])]
    private ?DayPlan $thursday = null;

    #[ODM\EmbedOne(targetDocument: DayPlan::class, nullable: true)]
    #[Groups(['plan:read'])]
    private ?DayPlan $friday = null;

    #[ODM\EmbedOne(targetDocument: DayPlan::class, nullable: true)]
    #[Groups(['plan:read'])]
    private ?DayPlan $saturday = null;

    #[ODM\EmbedOne(targetDocument: DayPlan::class, nullable: true)]
    #[Groups(['plan:read'])]
    private ?DayPlan $sunday = null;

    public function __construct(string $weekStartDate)
    {
        $this->weekStartDate = $weekStartDate;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getWeekStartDate(): string
    {
        return $this->weekStartDate;
    }

    public function getDay(string $day): ?DayPlan
    {
        return $this->$day;
    }

    public function setDay(string $day, ?DayPlan $dayPlan): static
    {
        $this->$day = $dayPlan;

        return $this;
    }

    public function __toString(): string
    {
        return 'Week of ' . $this->weekStartDate;
    }
}
