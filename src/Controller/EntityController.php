<?php

namespace App\Controller;

use App\Repository\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class EntityController extends AbstractController
{
    private EntityRepository $entityRepository;

    public function __construct(EntityRepository $entityRepository)
    {
        $this->entityRepository = $entityRepository;
    }

    #[Route('/{project}/{entity}/{id}/', methods: ['POST'])]
    public function updateViewCounts(int $id, string $project, string $entity, Request $request): JsonResponse
    {
        if (empty($project) || empty($entity) || empty($id)) {
            return new JsonResponse(['error' => 'Missing required route parameters'], 400);
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $project) || !preg_match('/^[a-zA-Z0-9_-]+$/', $entity) ||
            !filter_var($id, FILTER_VALIDATE_INT)) {
            return new JsonResponse(['error' => 'Invalid route parameters'], 400);
        }

        if ($id < 0) {
            return new JsonResponse(['error' => 'No valid id'], 400);
        }

        $res = json_decode($request->getContent(), true);

        if (empty($res['data']) && (!isset($res['data']['page_views']) || !isset($res['data']['phone_views']))) {
            return new JsonResponse(['error' => 'Missing required parameters'], 400);
        }

        $pageViews  = isset($res['data']['page_views']) ? (int)$res['data']['page_views'] : 0;
        $phoneViews = isset($res['data']['phone_views']) ? (int)$res['data']['phone_views'] : 0;

        $view = $this->entityRepository->updateViewCounts($project, $entity, $id, $pageViews, $phoneViews);

        $response = [
            'data' => [
                'page_views' => $view->getPageViews(),
                'phone_views' => $view->getPhoneViews(),
            ],
        ];

        return new JsonResponse($response, 200);
    }

    #[Route('/{project}/{entity}/{id}', methods: ['GET'])]
    public function getViewCounts(int $id, string $project, string $entity): JsonResponse
    {
        if (empty($project) || empty($entity) || empty($id)) {
            return new JsonResponse(['error' => 'Missing required route parameters'], 400);
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $project) || !preg_match('/^[a-zA-Z0-9_-]+$/', $entity) || !filter_var($id, FILTER_VALIDATE_INT)) {
            return new JsonResponse(['error' => 'Invalid route parameters'], 400);
        }

        $viewCount = $this->entityRepository->findViewCounts($project, $entity, $id);
        if (!$viewCount) {
            return new JsonResponse(['error' => 'Entity not found'], 404);
        }

        $response = [
            'data' => [
                $id => [
                    'page_views' => (int)$viewCount['page_views'],
                    'phone_views' => (int)$viewCount['phone_views']
                ],
            ],
        ];

        return new JsonResponse($response, 200);
    }

    #[Route('/{project}/{entity}/{id}/periods/', methods: ['GET'])]
    public function getStatistics(Request $request, int $id, string $project, string $entity): JsonResponse
    {
        $periods = $request->query->all()['period'] ?? [];
        if (empty($periods)) {
            return new JsonResponse(['error' => 'No periods provided'], 400);
        }

        $statistics = [];
        foreach ($periods as $periodName => $range) {
            if (isset($range['from']) && isset($range['to'])) {
                if (!$this->isValidDate($range['from']) || !$this->isValidDate($range['to'])) {
                    return new JsonResponse(['error' => 'Invalid date format'], 400);
                }

                $fromDate = \DateTime::createFromFormat('Y-m-d', $range['from']);
                $toDate   = \DateTime::createFromFormat('Y-m-d', $range['to']);

                if ($fromDate > $toDate){
                    return new JsonResponse([
                        'error' => sprintf('The start date ("%s" from) must be earlier and before the end date ("%s" to)',
                            $range['from'], $range['to'])], 400);
                }

                if ($fromDate == $range['to'] || $toDate == $range['from']) {
                    return new JsonResponse(['error' => 'Invalid date format'], 400);
                }

                $stats                        = $this->entityRepository->findViewStatistics($id, $project, $entity, $range['from'], $range['to']);
                $statistics[$periodName][$id] = [
                    'page_views' => $stats['page_views'],
                    'phone_views' => $stats['phone_views']
                ];

            } else {
                $statistics[$periodName][$id] = ['error' => 'Invalid period of range'];
            }
        }
        $response = ['data' => $statistics];
        return new JsonResponse($response, 200);
    }

    private function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

}
