<?php

namespace App\Controller;

use App\Entity\EntityViewCounts;
use App\Entity\View;
use App\Repository\ViewRepository;
use Doctrine\ORM\EntityManagerInterface;

//use Doctrine\ORM\Mapping\Entity;
//use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ViewController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ViewRepository $viewRepository;

    public function __construct(EntityManagerInterface $entityManager, ViewRepository $viewRepository)
    {
        $this->entityManager = $entityManager;
        $this->viewRepository = $viewRepository;
    }

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

    #[Route('/views/{entityType}/{entityId}', name: 'view_show', methods: ['GET'])]
    public function getView(string $entityType, int $entityId): JsonResponse
    {
//        $view = $this->viewRepository->find($id);
        $view = $this->viewRepository->findOneBy(['entityType' => $entityType, 'entityId' => $entityId]);

        if (!$view) {
            return $this->json(['error' => 'View not found'], 404);
        }
        return $this->json([
            'entity_id' => $view->getEntityId(),
            'entity_type' => $view->getEntityType(),
            'page_views' => $view->getPageViews(),
            'phone_views' => $view->getPhoneViews(),
        ]);
    }

//    #[Route('/views/{entityType}/{entityId}', name: 'views_create', methods: ['POST'])]
    #[Route('/project/entity/id/', name: 'views_create', methods: ['POST'])]

    public function saveViews(Request $request, string $entityType, int $entityId): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $view = $this->entityManager->getRepository(EntityViewCounts::class)->findOneBy([
            'entityId' => $entityId,
            'entityType' => $entityType]);
        if (!$view) {
            $view = new EntityViewCounts();
            $view->setEntityId($entityId);
            $view->setEntityType($entityType);
        } else {
            return $this->json(['error' => 'View already exists'], 400);
        }

        $view->setPageViews($data['pageViews'] ?? null);
        $view->setPhoneViews($data['phoneViews'] ?? null);
        $this->entityManager->persist($view);
        $this->entityManager->flush();
        return $this->json([
            'message' => 'View saved',
            'data' => [
                'pageViews' => $view->getPageViews(),
                'phoneViews' => $view->getPhoneViews()
            ]], 201);
    }

    #[Route('/views/{entityType}/{entityId}', name: 'views_update', methods: ['PUT'])]
    public function updateViews(Request $request, string $entityType, int $entityId): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $view = $this->entityManager->getRepository(EntityViewCounts::class)->findOneBy([
            'entityId' => $entityId,
            'entityType' => $entityType
        ]);

        if (!$view) {
            return $this->json(['error' => 'View not found'], 404);
        }

        $view->setPageViews($data['pageViews'] ?? $view->getPageViews());
        $view->setPhoneViews($data['phoneViews'] ?? $view->getPhoneViews());

        try {
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to update views'], 500);
        }
        $this->entityManager->persist($view);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Views updated successfully',
            'data' => [
                'pageViews' => $view->getPageViews(),
                'phoneViews' => $view->getPhoneViews()
            ]
        ], 200);
    }

    #[Route('/entity/{id}/periods/', name: 'entity_statistics', methods: ['GET'])]
    public function getViewsStatistics(Request $request, int $id, ViewRepository $viewRepository): JsonResponse
    {
        $periods = $request->query->get('periods');
//        dump($periods);die();
        if (!$periods) {
            return new JsonResponse(['error' => 'No periods provided'], 400);
        }
        $statistics = [];
        foreach ($periods as $period => $dates) {
            if (!isset($dates['from']) || !isset($dates['to'])) {
                return new JsonResponse(['error' => 'Invalid date format provided'], 400);
            }
            $from = $dates['from'];
            $to = $dates['to'];
            $totalViews = $viewRepository->getTotalViewsByPeriod($id, $from, $to);
            $statistics[$period] = [
                'page_views' => $totalViews['page_sum'] ?? 0,
                'phone_views' => $totalViews['phone_sum'] ?? 0,
            ];
        }

        return new JsonResponse(['data' => $statistics]);
    }


//    #[Route('/views/{entityType}/{entityId}/periods/{from}/{to}', name: 'views_statistics', methods: ['GET'])]
//    public function getViewsStatistics(Request $request, string $entityType, int $entityId, string $from, string $to): JsonResponse
//    {
//        $periods = $request->query->get('periods');
////        dump($to);die();
//        if (!$periods) {
//            return new JsonResponse(['error' => 'No periods provided'], 400);
//        }
//
//        $statistics = [];
//
//        foreach ($periods as $period => $dates) {
//            if (!isset($dates['from']) || !isset($dates['to'])) {
//                $statistics[$period] = ['error' => 'Invalid date format provided'];
//                continue;
//            }
//
//            $from = $dates['from'];
//            $to = $dates['to'];
//
//            $pageViews = $this->viewRepository->getPageViewsStatistics($entityId, $from, $to);
//            $phoneViews = $this->viewRepository->getPhoneViewsStatistics($entityId, $from, $to);
//
//            $statistics[$period] = [
//                'page_views' => $pageViews,
//                'phone_views' => $phoneViews,
//            ];
//        }
//
//        return $this->json(['data' => $statistics]);
//    }

}
