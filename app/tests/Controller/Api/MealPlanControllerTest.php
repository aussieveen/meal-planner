<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api;

use App\Controller\Api\MealPlanController;
use App\Document\DayPlan;
use App\Document\RecipeRef;
use App\Document\WeekPlan;
use App\Repository\WeekPlanRepository;
use App\Service\CookbookService;
use Doctrine\ODM\MongoDB\DocumentManager;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MealPlanControllerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private MealPlanController $unit;
    private DocumentManager $dm;
    private WeekPlanRepository $repository;
    private CookbookService $cookbookService;

    public function setUp(): void
    {
        $this->dm              = Mockery::mock(DocumentManager::class);
        $this->repository      = Mockery::mock(WeekPlanRepository::class);
        $this->cookbookService = Mockery::mock(CookbookService::class);

        $container = Mockery::mock(ContainerInterface::class);
        $container->expects('has')->with('serializer')->andReturnFalse();

        $this->unit = new MealPlanController($this->dm, $this->repository, $this->cookbookService);
        $this->unit->setContainer($container);
    }

    public function testCurrentCreatesAndReturnsPlanWhenNotFound(): void
    {
        $plan = new WeekPlan('2026-06-30');

        $this->repository->expects('findOrCreateForDate')
            ->andReturn($plan);
        $this->dm->expects('persist')->with($plan);
        $this->dm->expects('flush');

        $response = $this->unit->current();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('2026-06-30', $data['weekStartDate']);
        $this->assertNull($data['days']['monday']);
    }

    public function testCurrentReturnsExistingPlanWithoutPersisting(): void
    {
        $plan = $this->existingPlan('2026-06-30');

        $this->repository->expects('findOrCreateForDate')
            ->andReturn($plan);
        $this->dm->shouldNotReceive('persist');
        $this->dm->shouldNotReceive('flush');

        $response = $this->unit->current();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testGetReturns404WhenPlanNotFound(): void
    {
        $this->repository->expects('findByWeekStartDate')
            ->with('2026-06-30')
            ->andReturnNull();

        $response = $this->unit->get('2026-06-30');

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testGetReturnsPlan(): void
    {
        $plan = $this->existingPlan('2026-06-30');

        $this->repository->expects('findByWeekStartDate')
            ->with('2026-06-30')
            ->andReturn($plan);

        $response = $this->unit->get('2026-06-30');

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame('2026-06-30', $data['weekStartDate']);
        $this->assertCount(7, $data['days']);
    }

    public function testPutDayReturns400ForInvalidDay(): void
    {
        $response = $this->unit->putDay('2026-06-30', 'blursday', new Request());

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testPutDayReturns400WhenMainRecipeIdMissing(): void
    {
        $request = Request::create('/', 'PUT', content: json_encode(['sideRecipeIds' => []]));

        $response = $this->unit->putDay('2026-06-30', 'monday', $request);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testPutDayReturns422WhenCookbookRecipeNotFound(): void
    {
        $this->cookbookService->expects('getRecipeRef')
            ->with(999)
            ->andThrows(new RuntimeException('Recipe 999 not found in cookbook'));

        $request = Request::create('/', 'PUT', content: json_encode(['mainRecipeId' => 999]));

        $response = $this->unit->putDay('2026-06-30', 'monday', $request);

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testPutDayAssignsMealAndReturnsPlan(): void
    {
        $main = new RecipeRef(42, 'Bolognese', null);
        $side = new RecipeRef(15, 'Garlic Bread', null);

        $this->cookbookService->expects('getRecipeRef')->with(42)->andReturn($main);
        $this->cookbookService->expects('getRecipeRef')->with(15)->andReturn($side);

        $plan = $this->existingPlan('2026-06-30');
        $this->repository->expects('findOrCreateForDate')
            ->with('2026-06-30')
            ->andReturn($plan);

        $this->dm->expects('persist')->with($plan);
        $this->dm->expects('flush');

        $request = Request::create('/', 'PUT', content: json_encode([
            'mainRecipeId'  => 42,
            'sideRecipeIds' => [15],
        ]));

        $response = $this->unit->putDay('2026-06-30', 'monday', $request);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertSame(42, $data['days']['monday']['main']['recipeId']);
        $this->assertSame(15, $data['days']['monday']['sides'][0]['recipeId']);
    }

    public function testDeleteDayReturns400ForInvalidDay(): void
    {
        $response = $this->unit->deleteDay('2026-06-30', 'blursday');

        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testDeleteDayReturns404WhenPlanNotFound(): void
    {
        $this->repository->expects('findByWeekStartDate')
            ->with('2026-06-30')
            ->andReturnNull();

        $response = $this->unit->deleteDay('2026-06-30', 'monday');

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testDeleteDayClearsDayAndReturnsPlan(): void
    {
        $plan = $this->existingPlan('2026-06-30');
        $plan->setDay('monday', (new DayPlan())->setMain(new RecipeRef(42, 'Bolognese', null)));

        $this->repository->expects('findByWeekStartDate')
            ->with('2026-06-30')
            ->andReturn($plan);

        $this->dm->expects('flush');

        $response = $this->unit->deleteDay('2026-06-30', 'monday');

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertNull($data['days']['monday']);
    }

    /** Creates a WeekPlan with a non-null id (simulates an existing persisted document) */
    private function existingPlan(string $weekStartDate): WeekPlan
    {
        $plan = new WeekPlan($weekStartDate);

        // Set private $id to simulate a persisted document (ODM would do this via reflection too)
        $prop = new \ReflectionProperty(WeekPlan::class, 'id');
        $prop->setValue($plan, 'some-object-id');

        return $plan;
    }
}
