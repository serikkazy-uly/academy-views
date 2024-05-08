<?php

namespace App\Controller;

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


    #[Route('/views', name: 'view_list', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $views = $this->viewRepository->findAll();

        foreach ($views as $view) {
            $pageViews[] = [
                'id' => $view->getId(),
                'page_views' => $view->getPageViews(),
                'phone_views' => $view->getPhoneViews(),
            ];
        }

        return $this->json([
            'page_views' => $pageViews
        ]);
    }

    #[Route('/view/{id}', name: 'view_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $view = $this->viewRepository->find($id);

        if (!$view) {
            return $this->json(['error' => 'View not found'], 404);
        }
        return $this->json([
            'page_view' => $view->getPhoneViews(),
            'phone_view' => $view->getPageViews(),
        ]);

    }

    #[Route('/views', name: 'views_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $view = new View();
        $view->setPageViews($data['pageViews'] ?? null);
        $view->setPhoneViews($data['phoneViews'] ?? null);

        $this->entityManager->persist($view);
        $this->entityManager->flush();

        return $this->json(['message' => 'View created', 'id' => $view->getId()], 201);
    }

}
