<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Client\CookbookClient;
use App\Document\RecipeRef;
use App\Service\CookbookService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CookbookServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private CookbookService $unit;
    private CookbookClient $client;

    public function setUp(): void
    {
        $this->client = Mockery::mock(CookbookClient::class);
        $this->unit   = new CookbookService($this->client);
    }

    public function testGetRecipeRefBuildsFromResponse(): void
    {
        $response = Mockery::mock(ResponseInterface::class);
        $response->expects('toArray')->andReturn([
            'id'    => 42,
            'name'  => 'Spaghetti Bolognese',
            'image' => 'https://example.com/img.jpg',
        ]);

        $this->client->expects('fetchRecipe')->with(42)->andReturn($response);

        $ref = $this->unit->getRecipeRef(42);

        $this->assertInstanceOf(RecipeRef::class, $ref);
        $this->assertSame(42, $ref->getRecipeId());
        $this->assertSame('Spaghetti Bolognese', $ref->getName());
        $this->assertSame('https://example.com/img.jpg', $ref->getImage());
    }

    public function testGetRecipeRefHandlesNullImage(): void
    {
        $response = Mockery::mock(ResponseInterface::class);
        $response->expects('toArray')->andReturn(['id' => 1, 'name' => 'Salad']);

        $this->client->expects('fetchRecipe')->with(1)->andReturn($response);

        $ref = $this->unit->getRecipeRef(1);

        $this->assertNull($ref->getImage());
    }

    public function testGetRecipeRefThrowsRuntimeExceptionOnClientException(): void
    {
        $response = Mockery::mock(ResponseInterface::class);
        $response->expects('toArray')->andThrows($this->createStub(ClientExceptionInterface::class));

        $this->client->expects('fetchRecipe')->with(99)->andReturn($response);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Recipe 99 not found in cookbook');

        $this->unit->getRecipeRef(99);
    }
}
