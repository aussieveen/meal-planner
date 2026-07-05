<?php

declare(strict_types=1);

namespace App\Client;

use RuntimeException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class CookbookClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $baseUrl,
    ) {
    }

    public function fetchRecipe(int $id): ResponseInterface
    {
        try {
            return $this->httpClient->request('GET', $this->baseUrl . '/api/v1/recipes/' . $id);
        } catch (TransportExceptionInterface $e) {
            throw new RuntimeException('Error fetching recipe ' . $id, 0, $e);
        }
    }
}
