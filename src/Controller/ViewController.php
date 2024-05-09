<?php

namespace App\Controller;

use App\Entity\View;
use App\Repository\ViewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\Persistence\ManagerRegistry;
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

    #[Route('/views', name: 'view_list', methods: ['GET'])]
    public function getViews(): JsonResponse
    {
        $views = $this->viewRepository->findAll();

        $viewsData = array_map(function($view) {
            return $view->toArray();
        }, $views);

        return $this->json([
            'views' => $viewsData
        ]);
    }

    #[Route('/view/{entityType}/{entityId}', name: 'view_show', methods: ['GET'])]
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

    #[Route('/views', name: 'views_create', methods: ['POST'])]
    public function saveViews(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $view = new View();
        $view->setEntityId($data['entityId'] ?? null);
        $view->setEntityType($data['entityType'] ?? null);
        $view->setPageViews($data['pageViews'] ?? null);
        $view->setPhoneViews($data['phoneViews'] ?? null);
        $this->entityManager->persist($view);
        $this->entityManager->flush();

        return $this->json(['message' => 'View created', 'id' => $view->getId()], 201);
    }

//    #[Route('/views/{id}/periods/', name: 'view_period_statistics', methods: ['GET'])]
//    public function getViewsStatistics(Request $request, int $id): JsonResponse
//    {
//        $periods = $request->query->get('period');
//
//        if (!$periods) {
//            return new JsonResponse(['error' => 'No periods provided'], 400);
//        }
//
//        $statistics = [];
//
//        foreach ($periods as $period => $dates) {
//            $from = $dates['from'];
//            $to = $dates['to'];
//
//            $pageViews = $this->viewRepository->getPageViewsStatistics($id, $from, $to);
//            $phoneViews = $this->viewRepository->getPhoneViewsStatistics($id, $from, $to);
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
