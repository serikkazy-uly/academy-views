<?php

namespace App\Controller;

use App\Repository\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class EntityController extends AbstractController
{
    private EntityRepository $entityRepository;
    private EntityManagerInterface $entityManager;
    private CacheInterface $cache;

    public function __construct(EntityRepository $entityRepository, EntityManagerInterface $entityManager, CacheInterface $cache)
    {
        $this->entityRepository = $entityRepository;
        $this->entityManagerInterface = $entityManager;
        $this->cache = $cache;
    }

    #[Route('/{project}/{entity}/{id}', methods: ['POST'])]
    public function updateViewCounts(int $id, string $project, string $entity, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $res = json_decode($request->getContent(), true);

        if (empty($res['data']) || !isset($res['data']['page_views']) || !isset($res['data']['phone_views'])) {
            return new JsonResponse(['error' => 'Missing required parameters'], 400);
        }
        $data = $res['data'];
        $pageViews = (int)$data['page_views'];
        $phoneViews = (int)$data['phone_views'];

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
    public function getViewCounts(int $id, string $project, string $entity, EntityManagerInterface $em): JsonResponse
    {
        $viewCount = $this->entityRepository->findViewCounts($project, $entity, $id);
        if (!$viewCount) {
            return new JsonResponse(['error' => 'Entity not found'], 404);
        }

        $response = [
            'data' => [
                $id => [
                    'page_views' => $viewCount->getPageViews(),
                    'phone_views' => $viewCount->getPhoneViews(),
                ],
            ],
        ];

        return new JsonResponse($response, 200);
    }

    /* @throws \Psr\Cache\InvalidArgumentException
     */
    #[Route('/{project}/{entity}/{id}/periods/', methods: ['GET'])]
    public function getStatistics(Request $request, int $id, string $project, string $entity, CacheInterface $cache): JsonResponse
    {
        $periods = $request->query->all()['period'] ?? [];
        if (empty($periods)) {
            return new JsonResponse(['error' => 'No periods provided'], 400);
        }

        $statistics = [];

        foreach ($periods as $periodName => $range) {
            if (isset($range['from']) && isset($range['to'])) {

//                $cacheKey = sprintf('stats_%s_%s_%d_%s_%s', $project, $entity, $id, $range['from'], $range['to']);
//                $stats = $cache->get($cacheKey, function (ItemInterface $item) use ($id, $project, $entity, $range) {
//                    $item->expiresAfter(3600);
//                    return $this->entityRepository->findViewStatistics($id, $project, $entity, $range['from'], $range['to']);
//                });
                $stats = $this->entityRepository->findViewStatistics($id, $project, $entity, $range['from'], $range['to']);
                $statistics[$periodName] = [
                    'id' => $id,
                    'page_views' => $stats['page_views'],
                    'phone_views' => $stats['phone_views']
                ];
            } else {
                $statistics[$periodName] = ['error' => 'Invalid period of range'];
            }
        }
        return $this->json(['data' => $statistics]);
    }
}




// First variant realization (below):

//    #[Route('/project/entity/bulk/', methods: ['POST'])]
//    public function updateViewsBulk(Request $request): Response
//    {
//        $entity = $request->query->get('type');
//
//        if (empty($entity)) {
//            return $this->json(['error' => 'Entity type is required'], 400);
//        }
//
//        $viewData = json_decode($request->getContent(), true)['data'] ?? [];
//
//        $results = $this->viewService->updateViewsBulk($viewData, $entity);
//
//        return $this->json(['data' => $results]);
//    }
//
//    #[Route('/project/entity/bulk/', methods: ['GET'])]
//    public function getViewsBulk(Request $request): Response
//    {
////        $entity = $request->query->get('type');
////
////        if (empty($entity)) {
////            return $this->json(['error' => 'Entity type is required'], 400);
////        }
//
//        $entityIds = $request->query->all()['ids'] ?? [];
//
//        if (empty($entityIds)) {
//            return $this->json(['error' => 'No entity IDs provided'], 400);
//        }
//
//        $results = $this->viewService->getViewsBulk($entityIds);
//
//        return $this->json(['data' => $results]);
//    }
//
//    #[Route('/project/entity/{id}/multiple-periods/', methods: ['GET'])]
//    public function getStatisticsMultiplePeriods(Request $request, int $id): Response
//    {
//        $entity = $request->query->get('type');
//
//        if (empty($entity)) {
//            return $this->json(['error' => 'Entity type is required'], 400);
//        }
//
//        $periods = $request->query->get('period', []);
//
//        if (empty($periods)) {
//            return $this->json(['error' => 'Periods are required'], 400);
//        }
//
//        $statistics = $this->viewService->getStatisticsMultiplePeriods($id, $entity, $periods);
//
//        return $this->json(['data' => $statistics]);
//    }
//
//}

//---------------------------------
//    #[Route('/views/{entityType}/{entityId}', name: 'views_create', methods: ['POST'])]
//
//    #[Route('/project/entity/id/', name: 'views_create', methods: ['POST'])]
//    public function saveViews(Request $request, string $entity, int $entityId): JsonResponse
//    {
//        $data = json_decode($request->getContent(), true);
//
//        $view = $this->entityManager->getRepository(EntityViewCounts::class)->findOneBy([
//            'entityId' => $entityId,
//            'entity' => $entity]);
//        if (!$view) {
//            $view = new EntityViewCounts();
//            $view->setEntityId($entityId);
//            $view->setEntity($entity);
//        } else {
//            return $this->json(['error' => 'View already exists'], 400);
//        }
//
//        $view->setPageViews($data['pageViews'] ?? null);
//        $view->setPhoneViews($data['phoneViews'] ?? null);
//        $this->entityManager->persist($view);
//        $this->entityManager->flush();
//        return $this->json([
//            'message' => 'View saved',
//            'data' => [
//                'pageViews' => $view->getPageViews(),
//                'phoneViews' => $view->getPhoneViews()
//            ]], 201);
//    }
//
//    #[Route('/views/{entityType}/{entityId}', name: 'views_update', methods: ['PUT'])]
//    public function updateViews(Request $request, string $entity, int $entityId): JsonResponse
//    {
//        $data = json_decode($request->getContent(), true);
//
//        $view = $this->entityManager->getRepository(EntityViewCounts::class)->findOneBy([
//            'entityId' => $entityId,
//            'entityType' => $entity
//        ]);
//
//        if (!$view) {
//            return $this->json(['error' => 'View not found'], 404);
//        }
//
//        $view->setPageViews($data['pageViews'] ?? $view->getPageViews());
//        $view->setPhoneViews($data['phoneViews'] ?? $view->getPhoneViews());
//
//        try {
//            $this->entityManager->flush();
//        } catch (\Exception $e) {
//            return $this->json(['error' => 'Failed to update views'], 500);
//        }
//        $this->entityManager->persist($view);
//        $this->entityManager->flush();
//
//        return $this->json([
//            'message' => 'Views updated successfully',
//            'data' => [
//                'pageViews' => $view->getPageViews(),
//                'phoneViews' => $view->getPhoneViews()
//            ]
//        ], 200);
//    }
//
//    #[Route('/entity/{id}/periods/', name: 'entity_statistics', methods: ['GET'])]
//    public function getViewsStatistics(Request $request, int $id, EntityRepository $viewRepository): JsonResponse
//    {
//        $periods = $request->query->get('periods');
////        dump($periods);die();
//        if (!$periods) {
//            return new JsonResponse(['error' => 'No periods provided'], 400);
//        }
//        $statistics = [];
//        foreach ($periods as $period => $dates) {
//            if (!isset($dates['from']) || !isset($dates['to'])) {
//                return new JsonResponse(['error' => 'Invalid date format provided'], 400);
//            }
//            $from = $dates['from'];
//            $to = $dates['to'];
//            $totalViews = $viewRepository->getTotalViewsByPeriod($id, $from, $to);
//            $statistics[$period] = [
//                'page_views' => $totalViews['page_sum'] ?? 0,
//                'phone_views' => $totalViews['phone_sum'] ?? 0,
//            ];
//        }
//
//        return new JsonResponse(['data' => $statistics]);
//    }
//
////------------------------------------------
////    #[Route('/views/{entityType}/{entityId}/periods/{from}/{to}', name: 'views_statistics', methods: ['GET'])]
////    public function getViewsStatistics(Request $request, string $entity, int $entityId, string $from, string $to): JsonResponse
////    {
////        $periods = $request->query->get('periods');
//////        dump($to);die();
////        if (!$periods) {
////            return new JsonResponse(['error' => 'No periods provided'], 400);
////        }
////
////        $statistics = [];
////
////        foreach ($periods as $period => $dates) {
////            if (!isset($dates['from']) || !isset($dates['to'])) {
////                $statistics[$period] = ['error' => 'Invalid date format provided'];
////                continue;
////            }
////
////            $from = $dates['from'];
////            $to = $dates['to'];
////
////            $pageViews = $this->viewRepository->getPageViewsStatistics($entityId, $from, $to);
////            $phoneViews = $this->viewRepository->getPhoneViewsStatistics($entityId, $from, $to);
////
////            $statistics[$period] = [
////                'page_views' => $pageViews,
////                'phone_views' => $phoneViews,
////            ];
////        }
////
////        return $this->json(['data' => $statistics]);
////    }
//
//
//
//    #[Route('/views/{entityType}/{entityId}', name: 'view_show', methods: ['GET'])]
//    public function getView(string $entity, int $entityId): JsonResponse
//    {
////        $view = $this->viewRepository->find($id);
//        $view = $this->viewRepository->findOneBy(['entityType' => $entity, 'entityId' => $entityId]);
//
//        if (!$view) {
//            return $this->json(['error' => 'View not found'], 404);
//        }
//        return $this->json([
//            'entity_id' => $view->getEntityId(),
//            'entity_type' => $view->getEntityType(),
//            'page_views' => $view->getPageViews(),
//            'phone_views' => $view->getPhoneViews(),
//        ]);
//    }
//}

