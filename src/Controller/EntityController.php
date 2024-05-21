<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\DateValidator;
use App\Repository\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


/**
 * Контроллер для управления просмотрами сущностей.
 */
class EntityController extends AbstractController
{
    /**
     * Репозиторий для работы с просмотрами сущностей.
     */
    private EntityRepository $entityRepository;

    /**
     * Сервис для валидации даты.
     */
    private DateValidator $dateValidator;

    /*
     * Менеджер сущностей для работы с базой данных
     * */
    private EntityManagerInterface $entityManager;

    /**
     * Конструктор класса управления просмотрами сущностей.
     */
    public function __construct(EntityRepository $entityRepository, DateValidator $dateValidator, EntityManagerInterface $entityManager)
    {
        $this->entityRepository = $entityRepository;
        $this->dateValidator    = $dateValidator;
        $this->entityManager    = $entityManager;
    }

    /**
     * Метод обновления количества просмотров пачкой.
     */
    #[Route('/{project}/{entity}/bulk/', methods: ['POST'])]
    public function updateViewCountsBulk(Request $request, string $project, string $entity): JsonResponse
    {
        $res = json_decode($request->getContent(), true);

        if (empty($res['data'])) {
            return new JsonResponse(['error' => 'Missing required parameters'], Response::HTTP_BAD_REQUEST);
        }

        $updates   = $res['data'];
        $responses = [];

        foreach ($updates as $id => $counts) {
            if (!isset($counts['page_views']) || !isset($counts['phone_views'])) {
                return new JsonResponse(['error' => 'Missing required parameters'], Response::HTTP_BAD_REQUEST);
            }

            $pageViews  = (int)$counts['page_views'];
            $phoneViews = (int)$counts['phone_views'];

            $view = $this->entityRepository->updateViewCounts($project, $entity, (int)$id, $pageViews,
                $phoneViews);
            $responses[$id] = [
                'page_views'  => $view->getPageViews(),
                'phone_views' => $view->getPhoneViews(),
            ];
        }

        return new JsonResponse(['data' => $responses]);
    }

    /**
     * Метод получения (возврата) количества просмотров сущностей пачкой.
     */
    #[Route('/{project}/{entity}/bulk/', methods: ['GET'])]
    public function getViewCountsBulk(Request $request, string $project, string $entity): JsonResponse
    {
        $ids = $request->query->get('ids');
        if (empty($ids)) {
            return new JsonResponse(['error' => 'No IDs provided'], Response::HTTP_BAD_REQUEST);
        }

        $idsArray = explode(',', $ids);
        $responses = [];

        foreach ($idsArray as $id) {
            $viewCount = $this->entityRepository->findViewCounts($project, $entity, (int)$id);
            if (!$viewCount) {
                $responses[$id] = ['error' => 'Entity not found'];
            } else {
                $responses[$id] = [
                    'page_views' => (int)$viewCount['page_views'],
                    'phone_views' => (int)$viewCount['phone_views'],
                ];
            }
        }

        return new JsonResponse(['data' => $responses]);
    }

    /**
     * Метод обновления количества просмотров
     */
    #[Route('/{project}/{entity}/{id}/', methods: ['POST'])]
    public function updateViewCounts(Request $request, string $project, string $entity, int $id): JsonResponse
    {
        if (empty($project) || empty($entity) || empty($id)) {
            return new JsonResponse(['error' => 'Missing required route parameters'], Response::HTTP_BAD_REQUEST);
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $project) || !preg_match('/^[a-zA-Z0-9_-]+$/', $entity) ||
            !filter_var($id, FILTER_VALIDATE_INT)) {
            return new JsonResponse(['error' => 'Invalid route parameters'], Response::HTTP_BAD_REQUEST);
        }

        if ($id < 0) {
            return new JsonResponse(['error' => 'No valid id'], Response::HTTP_BAD_REQUEST);
        }

        $res = json_decode($request->getContent(), true);

        if (empty($res['data']) && (!isset($res['data']['page_views']) || !isset($res['data']['phone_views']))) {
            return new JsonResponse(['error' => 'Missing required parameters'], Response::HTTP_BAD_REQUEST);
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

        return new JsonResponse($response);
    }

    /**
     * Метод получения количества просмотров сущности.
     */
    #[Route('/{project}/{entity}/{id}/', methods: ['GET'])]
    public function getViewCounts(int $id, string $project, string $entity): JsonResponse
    {
        if (empty($project) || empty($entity) || empty($id)) {
            return new JsonResponse(['error' => 'Missing required route parameters'], Response::HTTP_BAD_REQUEST);
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $project) ||
            !preg_match('/^[a-zA-Z0-9_-]+$/', $entity) ||
            !filter_var($id, FILTER_VALIDATE_INT)) {
            return new JsonResponse(['error' => 'Invalid route parameters'], Response::HTTP_BAD_REQUEST);
        }

        $viewCount = $this->entityRepository->findViewCounts($project, $entity, $id);
        if (!$viewCount) {
            return new JsonResponse(['error' => 'Entity not found'], Response::HTTP_NOT_FOUND);
        }

        $response = [
            'data' => [
                $id => [
                    'page_views'  => (int)$viewCount['page_views'],
                    'phone_views' => (int)$viewCount['phone_views'],
                ],
            ],
        ];

        return new JsonResponse($response);
    }

    /**
     * Метод получения статистики просмотров сущности за периоды.
     */
    #[Route('/{project}/{entity}/{id}/periods/', methods: ['GET'])]
    public function getStatistics(Request $request, string $project, string $entity, int $id): JsonResponse
    {
        $periods = $request->query->all()['period'] ?? [];
        if (empty($periods)) {
            return new JsonResponse(['error' => 'No periods provided'], Response::HTTP_BAD_REQUEST);
        }

        $statistics = [];
        foreach ($periods as $periodName => $range) {
            if (isset($range['from']) && isset($range['to'])) {
                if (!$this->dateValidator->isValidDate($range['from']) || !$this->dateValidator->isValidDate($range['to'])) {
                    return new JsonResponse(['error' => 'Invalid date format'], Response::HTTP_BAD_REQUEST);
                }

                $fromDate = \DateTime::createFromFormat('Y-m-d', $range['from']);
                $toDate   = \DateTime::createFromFormat('Y-m-d', $range['to']);

                if ($fromDate > $toDate) {
                    return new JsonResponse([
                        'error' => sprintf('The start date ("%s" from) must be earlier and before the end date ("%s" to)',
                            $range['from'], $range['to'])], Response::HTTP_BAD_REQUEST);
                }

                if ($fromDate == $range['to'] || $toDate == $range['from']) {
                    return new JsonResponse(['error' => 'Invalid date format'], Response::HTTP_BAD_REQUEST);
                }

                $stats                        = $this->entityRepository->findViewStatistics($project, $entity, $id, $range['from'], $range['to']);
                $statistics[$periodName][$id] = [
                    'page_views' => $stats['page_views'],
                    'phone_views' => $stats['phone_views'],
                ];

            } else {
                $statistics[$periodName][$id] = ['error' => 'Invalid period of range'];
            }
        }

        return new JsonResponse(['data' => $statistics]);
    }







    /*
     * Метод отдачи или возврата просмотров пачкой
     * */
    //    #[Route('/{project}/entity/{id}/bulk-stats', methods: ['GET'])]
//    public function getBulkStatistics(Request $request, string $project, int $id): JsonResponse
//    {
//        $periods = $request->query->get('periods', []);
//        $statistics = [];
//
//        foreach ($periods as $periodName => $range) {
//            if (isset($range['from'], $range['to'])) {
//                $stats = $this->entityRepository->findViewStatistics($id, $project, $range['from'], $range['to']);
//                $statistics[$periodName] = $stats;
//            } else {
//                $statistics[$periodName] = ['error' => 'Invalid period range'];
//            }
//        }
//
//        return $this->json(['data' => $statistics]);
//    }


}
