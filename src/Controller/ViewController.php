<?php

namespace App\Controller;

use App\Entity\View;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ViewController extends AbstractController
{
    #[Route('/views', name: 'view_list', methods: ['GET'])]
    public function entityList(): JsonResponse
    {
     $views = [
         ['id' =>1, 'name' => 'Gustav'],
         ['id' => 2, 'name' => 'Damir'],
         ['id' => 3, 'name' => 'Miras'],
     ];
     return $this->json($views);
    }

    #[Route('/view/{id}', name: 'view_show')]
    public function show(int $id): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
        $repository = $entityManager->getRepository(View::class);
        $entity = $repository->find($id);

        if (!$entity) {
//            return $this->json(['error' => 'Entity not found'], 404);
            return $this->json([
                'nbViews'      => 0,
                'nbPhoneViews' => 0,
            ]);
        }

        $nbViews = $entity->getNbViews();
        $nbPhoneViews = $entity->getNbPhoneViews();

        return $this->json([
            'nbViews' => $nbViews,
            'nbPhoneViews' => $nbPhoneViews,
        ]);
    }

}
