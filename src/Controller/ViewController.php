<?php

namespace App\Controller;

use App\Entity\View;
use App\Repository\ViewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ViewController extends AbstractController
{
    private ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/views', name: 'view_list', methods: ['GET'])]
    public function entityList(ViewRepository $viewRepository): JsonResponse
    {
        $viewRepository = $this->doctrine->getRepository(View::class);
        $views = $viewRepository->findAll();
        return $this->json($views);
    }


//    #[Route('/view/{id}', name: 'view_show')]
//    public function show(int $id, ViewRepository $viewRepository): Response
//    {
//        $pageViews = $viewRepository->getPageViewsById($id);
//        $phoneViews = $viewRepository->getPhoneViewsById($id);
//
//        return $this->render('view/show.html.twig', [
//            'pageViews' => $pageViews,
//            'phoneViews' => $phoneViews,
//        ]);
//    }

    #[Route('/view/{id}', name: 'view_show', methods: ['GET'])]
    public function show(int $id, ViewRepository $viewRepository): JsonResponse
    {
        $view = $viewRepository->find($id);

        if (!$view) {
            return $this->json(['error' => 'View not found'], 404);
        }

        return $this->json($view);
    }

    #[Route('/views', name: 'views_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $view = new View();
        $view->setPageViews($data['pageViews'] ?? null);
        $view->setPhoneViews($data['phoneViews'] ?? null);

        $entityManager->persist($view);
        $entityManager->flush();

        return $this->json(['message' => 'View created', 'id' => $view->getId()], 201);
    }

    #[Route('/view/{id}', name: 'view_update', methods: ['PUT'])]
    public function update(Request $request, int $id, ViewRepository $viewRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $view = $viewRepository->find($id);

        if (!$view) {
            return $this->json(['error' => 'View not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $view->setPageViews($data['pageViews'] ?? null);
        $view->setPhoneViews($data['phoneViews'] ?? null);

        $entityManager->flush();

        return $this->json(['message' => 'View updated'], 200);
    }

    #[Route('/view/{id}', name: 'view_delete', methods: ['DELETE'])]
    public function delete(int $id, ViewRepository $viewRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $view = $viewRepository->find($id);

        if (!$view) {
            return $this->json(['error' => 'View not found'], 404);
        }

        $entityManager->remove($view);
        $entityManager->flush();

        return $this->json(['message' => 'View deleted'], 200);
    }


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
