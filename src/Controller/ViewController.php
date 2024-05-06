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

    #[Route('/project/entity/{id}', name: 'save_views', methods: ['POST'])]
    public function saveViews(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $entity = $entityManager->getRepository(Entity::class)->find($id);

        if (!$entity) {
            return $this->json(['error' => 'Entity not found'], 404);
        }

        $nbViews = $entity->getNbViews();
        $nbPhoneViews = $entity->getNbPhoneViews();

        $nbViews += $data['nb_views'] ?? 0;
        $nbPhoneViews += $data['nb_phone_views'] ?? 0;

        $entity->setNbViews($nbViews);
        $entity->setNbPhoneViews($nbPhoneViews);

        $entityManager->flush();

        return $this->json(['data' => [
            'nb_views' => $nbViews,
            'nb_phone_views' => $nbPhoneViews
        ]]);
    }

    //    #[Route('/view/{id}', name: 'view_update', methods: ['PUT'])]
//    public function update(Request $request, int $id, ViewRepository $viewRepository, EntityManagerInterface $entityManager): JsonResponse
//    {
//        $view = $viewRepository->find($id);
//
//        if (!$view) {
//            return $this->json(['error' => 'View not found'], 404);
//        }
//
//        $data = json_decode($request->getContent(), true);
//
//        $view->setPageViews($data['pageViews'] ?? null);
//        $view->setPhoneViews($data['phoneViews'] ?? null);
//
//        $entityManager->flush();
//
//        return $this->json(['message' => 'View updated'], 200);
//    }
//
//    #[Route('/view/{id}', name: 'view_delete', methods: ['DELETE'])]
//    public function delete(int $id, ViewRepository $viewRepository, EntityManagerInterface $entityManager): JsonResponse
//    {
//        $view = $viewRepository->find($id);
//
//        if (!$view) {
//            return $this->json(['error' => 'View not found'], 404);
//        }
//
//        $entityManager->remove($view);
//        $entityManager->flush();
//
//        return $this->json(['message' => 'View deleted'], 200);
//    }

    // Первый вариант реализации с данными
    //    #[Route('/views', name: 'view_list', methods: ['GET'])]
//    public function entityList(): JsonResponse
//    {
//     $views = [
//         ['id' =>1, 'name' => 'Gustav'],
//         ['id' => 2, 'name' => 'Damir'],
//         ['id' => 3, 'name' => 'Miras'],
//     ];
//     return $this->json($views);
//    }

    //    #[Route('/view/{id}', name: 'view_show')]
//    public function show(int $id): JsonResponse
//    {
//        $entityManager = $this->getDoctrine()->getManager();
//        $repository = $entityManager->getRepository(View::class);
//        $entity = $repository->find($id);
//
//        if (!$entity) {
////            return $this->json(['error' => 'Entity not found'], 404);
//            return $this->json([
//                'nbViews'      => 0,
//                'nbPhoneViews' => 0,
//            ]);
//        }
//
//        $nbViews = $entity->getNbViews();
//        $nbPhoneViews = $entity->getNbPhoneViews();
//
//        return $this->json([
//            'nbViews' => $nbViews,
//            'nbPhoneViews' => $nbPhoneViews,
//        ]);
//    }

}
