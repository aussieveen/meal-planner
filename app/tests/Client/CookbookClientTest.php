<?php

declare(strict_types=1);

namespace App\Tests\Client;

use App\Client\CookbookClient;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CookbookClientTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private CookbookClient $unit;
    private HttpClientInterface $httpClient;

    public function setUp(): void
    {
        $this->httpClient = Mockery::mock(HttpClientInterface::class);
        $this->unit       = new CookbookClient($this->httpClient, 'http://cookbook');
    }

    public function testFetchRecipeCallsCorrectUrl(): void
    {
        $this->httpClient->expects('request')
            ->with('GET', 'http://cookbook/api/v1/recipes/42');

        $this->unit->fetchRecipe(42);
    }

    public function testFetchRecipeThrowsRuntimeExceptionOnTransportException(): void
    {
        $this->httpClient->expects('request')
            ->andThrows($this->createStub(TransportExceptionInterface::class));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error fetching recipe 42');

        $this->unit->fetchRecipe(42);
    }
}
