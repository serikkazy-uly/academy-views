<?php

namespace App\Controller;

use App\Entity\EntityViewCounts;
use App\Repository\EntityRepository;
use App\Service\ViewService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\Persistence\ManagerRegistry;
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

//    private EntityManagerInterface $entityManager;

//    public function __construct(EntityManagerInterface $entityManager, EntityRepository $viewRepository)
//    {
//        $this->entityManager = $entityManager;
//        $this->viewRepository = $viewRepository;
//    }

    #[Route('/{project}/{entity}/{id}', methods: ['POST'])]
    public function updateViewCounts(int $id, string $project, string $entity, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $res = json_decode($request->getContent(), true);
//dump($res);die();

        if (empty($res['data']) || !isset($res['data']['page_views']) || !isset($res['data']['phone_views'])) {
            return new JsonResponse(['error' => 'Missing required parameters'], 400);
        }
        $data = $res['data'];
//        die('strstop');
        $pageViews = (int) $data['page_views'];
        $phoneViews = (int) $data['phone_views'];

        $view = $this->entityRepository->updateViewCounts($project, $entity, $id, $pageViews, $phoneViews);

        $response = [
            'data' => [
                'page_views' => $view->getPageViews(),
                'phone_views' => $view->getPhoneViews(),
            ],
        ];

        return new JsonResponse($response, 200);
    }
//        $pageViews = $request->request->get('page_views', 0);
//        $phoneViews = $request->request->get('phone_views', 0);
//
//        $viewCount = $em->getRepository(EntityViewCounts::class)->findOneBy(['entity_id' => $entityId, 'entity' => $entity]);
//
//        if (!$viewCount) {
//            $viewCount = new EntityViewCounts();
//            $viewCount->setEntityId($entityId);
//            $viewCount->setEntity($entity);
//
//            $viewCount->setPageViews(0);
//            $viewCount->setPhoneViews(0);
//        }
//        $viewCount->setPageViews($viewCount->getPageViews() + $pageViews);
//        $viewCount->setPhoneViews($viewCount->getPhoneViews() + $phoneViews);
//
//        $em->persist($viewCount);
//        $em->flush();
//
//        return new JsonResponse([
//            'data' => [
//                'page_views' => $viewCount->getPageViews(),
//                'phone_views' => $viewCount->getPhoneViews(),
//            ]
//        ]);
//    }

    #[Route('/project/entity/{id}', methods: ['GET'])]
    public function getViewCount(Request $request, int $id): Response
    {
        $entity = $request->query->get('type');
        if (empty($entity)) {
            return $this->json(['error' => 'Entity type is required'], 400);
        }

        $view = $this->viewService->getViewCounts($id, $entity);

        if ($view) {
            return $this->json([
                'data' => [
                    $id => [
                        'page_views' => $view->getPageViews(),
                        'phone_views' => $view->getPhoneViews()
                    ]
                ]
            ]);
        } else {
            return $this->json(['error' => 'Entity not found'], 404);
        }
    }

    #[Route('/project/entity/{id}/periods/', methods: ['GET'])]
    public function getStatistics(Request $request, int $id): Response
    {
        $entity = $request->query->get('type');
        if (empty($entity)) {
            return $this->json(['error' => 'Entity type is required'], 400);
        }

        $periods = $request->query->get('period', []);

        $statistics = [];

        foreach ($periods as $periodName => $range) {
            if (isset($range['from']) && isset($range['to'])) {
                $statistics[$periodName] = $this->viewService->getStatistics($id, $entity, $range['from'], $range['to']);
            } else {
                $statistics[$periodName] = ['error' => 'Invalid period range'];
            }
        }

        return $this->json(['data' => $statistics]);
    }

    #[Route('/project/entity/bulk/', methods: ['POST'])]
    public function updateViewsBulk(Request $request): Response
    {
        $entity = $request->query->get('type');

        if (empty($entity)) {
            return $this->json(['error' => 'Entity type is required'], 400);
        }

        $viewData = json_decode($request->getContent(), true)['data'] ?? [];

        $results = $this->viewService->updateViewsBulk($viewData, $entity);

        return $this->json(['data' => $results]);
    }

    #[Route('/project/entity/bulk/', methods: ['GET'])]
    public function getViewsBulk(Request $request): Response
    {
//        $entity = $request->query->get('type');
//
//        if (empty($entity)) {
//            return $this->json(['error' => 'Entity type is required'], 400);
//        }

        $entityIds = $request->query->all()['ids'] ?? [];

        if (empty($entityIds)) {
            return $this->json(['error' => 'No entity IDs provided'], 400);
        }

        $results = $this->viewService->getViewsBulk($entityIds);

        return $this->json(['data' => $results]);
    }

    #[Route('/project/entity/{id}/multiple-periods/', methods: ['GET'])]
    public function getStatisticsMultiplePeriods(Request $request, int $id): Response
    {
        $entity = $request->query->get('type');

        if (empty($entity)) {
            return $this->json(['error' => 'Entity type is required'], 400);
        }

        $periods = $request->query->get('period', []);

        if (empty($periods)) {
            return $this->json(['error' => 'Periods are required'], 400);
        }

        $statistics = $this->viewService->getStatisticsMultiplePeriods($id, $entity, $periods);

        return $this->json(['data' => $statistics]);
    }

}

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

//    #[Route('/views', name: 'view_list', methods: ['GET'])]
//    public function getViews(): JsonResponse
//    {
//        $views = $this->viewRepository->findAll();
//
//        $viewsData = array_map(function ($view) {
//            return $view->toArray();
//        }, $views);
//
//        return $this->json([
//            'views' => $viewsData
//        ]);
//    }
