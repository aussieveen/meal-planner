<?php

declare(strict_types=1);

namespace App\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Mapping\Attribute as ODM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ODM\EmbeddedDocument]
class DayPlan
{
    #[ODM\EmbedOne(targetDocument: RecipeRef::class, nullable: true)]
    #[Groups(['plan:read'])]
    private ?RecipeRef $main = null;

    /** @var Collection<int, RecipeRef> */
    #[ODM\EmbedMany(targetDocument: RecipeRef::class)]
    #[Groups(['plan:read'])]
    private Collection $sides;

    #[ODM\Field(type: 'bool')]
    #[Groups(['plan:read'])]
    private bool $shopped = false;

    public function __construct()
    {
        $this->sides = new ArrayCollection();
    }

    public function getMain(): ?RecipeRef
    {
        return $this->main;
    }

    public function setMain(?RecipeRef $main): static
    {
        $this->main = $main;

        return $this;
    }

    /** @return Collection<int, RecipeRef> */
    public function getSides(): Collection
    {
        return $this->sides;
    }

    /** @param RecipeRef[] $sides */
    public function setSides(array $sides): static
    {
        $this->sides = new ArrayCollection($sides);

        return $this;
    }

    public function getShopped(): bool
    {
        return $this->shopped;
    }

    public function setShopped(bool $shopped): static
    {
        $this->shopped = $shopped;
        return $this;
    }
}
