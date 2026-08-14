<?php

declare(strict_types=1);

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Attribute as ODM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ODM\EmbeddedDocument]
class RecipeRef
{
    #[ODM\Field(type: 'int', nullable: true)]
    #[Groups(['plan:read'])]
    private ?int $recipeId;

    #[ODM\Field(type: 'string')]
    #[Groups(['plan:read'])]
    private string $name;

    #[ODM\Field(type: 'string', nullable: true)]
    #[Groups(['plan:read'])]
    private ?string $image = null;

    public function __construct(?int $recipeId, string $name, ?string $image = null)
    {
        $this->recipeId = $recipeId;
        $this->name     = $name;
        $this->image    = $image;
    }

    public function getRecipeId(): ?int
    {
        return $this->recipeId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }
}
