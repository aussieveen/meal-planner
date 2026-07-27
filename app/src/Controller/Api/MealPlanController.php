<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Document\DayPlan;
use App\Document\RecipeRef;
use App\Document\WeekPlan;
use App\Repository\WeekPlanRepository;
use App\Service\CookbookService;
use Doctrine\ODM\MongoDB\DocumentManager;
use OpenApi\Attributes as OA;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1', format: 'json')]
#[OA\Tag(name: 'Meal Plan')]
class MealPlanController extends AbstractController
{
    public function __construct(
        private readonly DocumentManager $dm,
        private readonly WeekPlanRepository $repository,
        private readonly CookbookService $cookbookService,
    ) {
    }

    #[Route('/plan/recipe-ids', name: 'api_plan_recipe_ids', methods: ['GET'])]
    #[OA\Get(summary: 'Get all recipe IDs from a date until the last planned meal')]
    #[OA\Parameter(name: 'from', in: 'query', schema: new OA\Schema(type: 'string', example: '2026-07-26'))]
    #[OA\Response(response: 200, description: 'Flat list of recipe IDs in plan order')]
    public function recipeIds(Request $request): JsonResponse
    {
        $from  = $request->query->get('from', date('Y-m-d'));
        $plans = $this->repository->findFromDate($from);

        $ids = [];
        foreach ($plans as $plan) {
            $weekStart = new \DateTimeImmutable($plan->getWeekStartDate());
            foreach (WeekPlan::DAYS as $i => $day) {
                $date    = $weekStart->modify("+{$i} days")->format('Y-m-d');
                $dayPlan = $plan->getDay($day);
                if ($date < $from || $dayPlan === null) {
                    continue;
                }
                $ids[] = $dayPlan->getMain()->getRecipeId();
                foreach ($dayPlan->getSides()->toArray() as $side) {
                    $ids[] = $side->getRecipeId();
                }
            }
        }

        return $this->json(['recipeIds' => $ids]);
    }

    #[Route('/plan/current', name: 'api_plan_current', methods: ['GET'])]
    #[OA\Get(summary: 'Get or create the current week plan')]
    #[OA\Response(
        response: 200,
        description: 'Week plan',
        content: new OA\JsonContent(ref: '#/components/schemas/WeekPlan')
    )]
    public function current(): JsonResponse
    {
        $weekStartDate = WeekPlanRepository::currentWeekStartDate();
        $plan          = $this->repository->findOrCreateForDate($weekStartDate);

        if ($plan->getId() === null) {
            $this->dm->persist($plan);
            $this->dm->flush();
        }

        return $this->json($this->formatPlan($plan));
    }

    #[Route(
        '/plan/{weekStartDate}',
        name: 'api_plan_get',
        methods: ['GET'],
        requirements: ['weekStartDate' => '\d{4}-\d{2}-\d{2}']
    )]
    #[OA\Get(summary: 'Get a week plan by start date (YYYY-MM-DD)')]
    #[OA\Parameter(name: 'weekStartDate', in: 'path', schema: new OA\Schema(type: 'string', example: '2026-06-30'))]
    #[OA\Response(
        response: 200,
        description: 'Week plan',
        content: new OA\JsonContent(ref: '#/components/schemas/WeekPlan')
    )]
    #[OA\Response(response: 404, description: 'Not found')]
    public function get(string $weekStartDate): JsonResponse
    {
        $plan = $this->repository->findByWeekStartDate($weekStartDate);

        if ($plan === null) {
            return $this->json(['error' => 'Week plan not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($this->formatPlan($plan));
    }

    #[Route(
        '/plan/{weekStartDate}/{day}',
        name: 'api_plan_put_day',
        methods: ['PUT'],
        requirements: ['weekStartDate' => '\d{4}-\d{2}-\d{2}']
    )]
    #[OA\Put(
        summary: 'Assign a meal to a day',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['mainRecipeId'],
                properties: [
                    new OA\Property(property: 'mainRecipeId', type: 'integer', example: 42),
                    new OA\Property(
                        property: 'sideRecipeIds',
                        type: 'array',
                        items: new OA\Items(type: 'integer'),
                        example: [15, 23]
                    ),
                ]
            )
        )
    )]
    #[OA\Parameter(name: 'weekStartDate', in: 'path', schema: new OA\Schema(type: 'string', example: '2026-06-30'))]
    #[OA\Parameter(name: 'day', in: 'path', schema: new OA\Schema(type: 'string', enum: WeekPlan::DAYS))]
    #[OA\Response(
        response: 200,
        description: 'Updated week plan',
        content: new OA\JsonContent(ref: '#/components/schemas/WeekPlan')
    )]
    #[OA\Response(response: 400, description: 'Invalid day or missing mainRecipeId')]
    #[OA\Response(response: 422, description: 'Recipe not found in cookbook')]
    public function putDay(string $weekStartDate, string $day, Request $request): JsonResponse
    {
        if (!in_array($day, WeekPlan::DAYS, strict: true)) {
            return $this->json(['error' => 'Invalid day: ' . $day], Response::HTTP_BAD_REQUEST);
        }

        $body = json_decode($request->getContent(), true) ?? [];

        if (!isset($body['mainRecipeId'])) {
            return $this->json(['error' => 'mainRecipeId is required'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $main  = $this->cookbookService->getRecipeRef((int) $body['mainRecipeId']);
            $sides = array_map(
                fn(int $id) => $this->cookbookService->getRecipeRef($id),
                array_map('intval', $body['sideRecipeIds'] ?? [])
            );
        } catch (RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $plan    = $this->repository->findOrCreateForDate($weekStartDate);
        $dayPlan = (new DayPlan())->setMain($main)->setSides($sides);
        $plan->setDay($day, $dayPlan);

        $this->dm->persist($plan);
        $this->dm->flush();

        return $this->json($this->formatPlan($plan));
    }

    #[Route(
        '/plan/{weekStartDate}/{day}',
        name: 'api_plan_delete_day',
        methods: ['DELETE'],
        requirements: ['weekStartDate' => '\d{4}-\d{2}-\d{2}']
    )]
    #[OA\Delete(summary: 'Clear a day from the week plan')]
    #[OA\Parameter(name: 'weekStartDate', in: 'path', schema: new OA\Schema(type: 'string', example: '2026-06-30'))]
    #[OA\Parameter(name: 'day', in: 'path', schema: new OA\Schema(type: 'string', enum: WeekPlan::DAYS))]
    #[OA\Response(
        response: 200,
        description: 'Updated week plan',
        content: new OA\JsonContent(ref: '#/components/schemas/WeekPlan')
    )]
    #[OA\Response(response: 400, description: 'Invalid day')]
    #[OA\Response(response: 404, description: 'Week plan not found')]
    public function deleteDay(string $weekStartDate, string $day): JsonResponse
    {
        if (!in_array($day, WeekPlan::DAYS, strict: true)) {
            return $this->json(['error' => 'Invalid day: ' . $day], Response::HTTP_BAD_REQUEST);
        }

        $plan = $this->repository->findByWeekStartDate($weekStartDate);

        if ($plan === null) {
            return $this->json(['error' => 'Week plan not found'], Response::HTTP_NOT_FOUND);
        }

        $plan->setDay($day, null);
        $this->dm->flush();

        return $this->json($this->formatPlan($plan));
    }

    private function formatPlan(WeekPlan $plan): array
    {
        $days = [];
        foreach (WeekPlan::DAYS as $day) {
            $dayPlan    = $plan->getDay($day);
            $days[$day] = $dayPlan === null ? null : [
                'main'  => $this->formatRef($dayPlan->getMain()),
                'sides' => array_map($this->formatRef(...), $dayPlan->getSides()->toArray()),
            ];
        }

        return ['weekStartDate' => $plan->getWeekStartDate(), 'days' => $days];
    }

    private function formatRef(?RecipeRef $ref): ?array
    {
        if ($ref === null) {
            return null;
        }

        return ['recipeId' => $ref->getRecipeId(), 'name' => $ref->getName(), 'image' => $ref->getImage()];
    }
}
