<?php

namespace App\Service;

use App\Entity\EntityViewCounts;
use App\Repository\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
//class ViewService
//{
//    private EntityManagerInterface $entityManager;
//    private EntityRepository $viewRepository;
//
//    public function __construct(EntityManagerInterface $entityManager, EntityRepository $viewRepository)
//    {
//        $this->entityManager = $entityManager;
//        $this->viewRepository = $viewRepository;
//    }
//
//    public function updateViews(int $entityId, string $entity, int $pageViews, int $phoneViews): EntityViewCounts
//    {
//        $view = $this->viewRepository->findOneBy(['entityId' => $entityId, 'entity' => $entity]) ?? new EntityViewCounts();
//        $view->setEntityId($entityId)
//            ->setEntity($entity)
//            ->setPageViews($view->getPageViews() + $pageViews)
//            ->setPhoneViews($view->getPhoneViews() + $phoneViews);
//
//        $this->entityManager->persist($view);
//        $this->entityManager->flush();
//
//        return $view;
//    }
//
//    public function getViewCounts(int $entityId, string $entity): ?EntityViewCounts
//    {
//        return $this->viewRepository->findOneBy(['entityId' => $entityId, 'entity' => $entity]);
//    }

//    public function getStatistics(int $entityId, string $entity, string $from, string $to): array
//    {
//        $query = $this->entityManager->createQuery(
//            'SELECT SUM(e.pageViews) as pageViews, SUM(e.phoneViews) as phoneViews
//             FROM App\Entity\EntityViewCounts e
//             WHERE e.entityId = :entityId AND e.entity = :entity
//             AND e.date BETWEEN :from AND :to'
//        )
//            ->setParameter('entityId', $entityId)
//            ->setParameter('entity', $entity)
//            ->setParameter('from', $from)
//            ->setParameter('to', $to);
//
//        $result = $query->getSingleResult();
//
//        return [
//            'page_views' => $result['pageViews'] ?? 0,
//            'phone_views' => $result['phoneViews'] ?? 0,
//        ];
//    }

//    public function updateViewsBulk(array $viewData, string $entity): array
//    {
//        $results = [];
//
//        foreach ($viewData as $entityId => $views) {
//            $view = $this->viewRepository->findOneBy(['entityId' => $entityId, 'entity' => $entity]) ?? new EntityViewCounts();
//            $view->setEntityId($entityId)
//                ->setEntity($entity)
//                ->setPageViews($view->getPageViews() + ($views['page_views'] ?? 0))
//                ->setPhoneViews($view->getPhoneViews() + ($views['phone_views'] ?? 0));
//
//            $this->entityManager->persist($view);
//            $results[$entityId] = [
//                'page_views' => $view->getPageViews(),
//                'phone_views' => $view->getPhoneViews()
//            ];
//        }
//
//        $this->entityManager->flush();
//        return $results;
//    }
//
//    public function getViewsBulk(array $entityIds, string $entity): array
//    {
//        $views = $this->viewRepository->findBy(['entityId' => $entityIds, 'entity' => $entity]);
//        $results = [];
//
//        foreach ($views as $view) {
//            $results[$view->getEntityId()] = [
//                'page_views' => $view->getPageViews(),
//                'phone_views' => $view->getPhoneViews()
//            ];
//        }
//
//        foreach ($entityIds as $entityId) {
//            if (!isset($results[$entityId])) {
//                $results[$entityId] = ['page_views' => 0, 'phone_views' => 0];
//            }
//        }
//
//        return $results;
//    }
//
//    public function getStatisticsMultiplePeriods(int $entityId, string $entity, array $periods): array
//    {
//        $statistics = [];
//
//        foreach ($periods as $periodName => $range) {
//            $from = $range['from'];
//            $to = $range['to'];
//            $statistics[$periodName] = $this->getStatistics($entityId, $entity, $from, $to);
//        }
//
//        return $statistics;
//    }

//}