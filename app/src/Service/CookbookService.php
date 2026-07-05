<?php

declare(strict_types=1);

namespace App\Service;

use App\Client\CookbookClient;
use App\Document\RecipeRef;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

class CookbookService
{
    public function __construct(private readonly CookbookClient $client)
    {
    }

    public function getRecipeRef(int $id): RecipeRef
    {
        try {
            $data = $this->client->fetchRecipe($id)->toArray();
        } catch (ClientExceptionInterface $e) {
            throw new RuntimeException('Recipe ' . $id . ' not found in cookbook', 0, $e);
        }

        return new RecipeRef(
            recipeId: $data['id'],
            name: $data['name'],
            image: $data['image'] ?? null,
        );
    }
}
