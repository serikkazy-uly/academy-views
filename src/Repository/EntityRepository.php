<?php

namespace App\Repository;

use App\Entity\EntityViewCounts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EntityViewCounts>
 */
class EntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntityViewCounts::class);
    }

    public function updateViewCounts( string $project, string $entity, int $id, int $pageViews, int $phoneViews): EntityViewCounts
    {
        $viewCount = $this->findOneBy(['entityId' => $id, 'entity' => $entity]);
        if (!$viewCount) {
            $viewCount = new EntityViewCounts();
            $viewCount->setProject($project);
            $viewCount->setEntity($entity);
            $viewCount->setEntityId($id);

            $viewCount->setPageViews(0);
            $viewCount->setPhoneViews(0);
        }

        $viewCount->setPageViews($viewCount->getPageViews() + $pageViews);
        $viewCount->setPhoneViews($viewCount->getPhoneViews() + $phoneViews);

        $this->getEntityManager()->persist($viewCount);
        $this->getEntityManager()->flush();

        return $viewCount;
    }

    public function findViewCountsById(int $entityId): ?EntityViewCounts
    {
        return $this->findOneBy(['entityId' => $entityId]);
    }


//    public function getTotalViewsByPeriod(int $entityId, string $from, string $to): array
//    {
//        return $this->createQueryBuilder('v')
//            ->select('SUM(v.pageViews) as page_sum, SUM(v.phoneViews) as phone_sum')
//            ->andWhere('v.entityId = :entityId')
//            ->andWhere('v.created BETWEEN :from AND :to')
//            ->setParameter('entityId', $entityId)
//            ->setParameter('from', $from)
//            ->setParameter('to', $to)
//            ->groupBy('v.entityId')
//            ->getQuery()
//            ->getSingleResult();
//    }

//    public function getTotalViewsByPeriod(int $entityId, string $from, string $to): array
//    {
//        $this->createQueryBuilder('v')
//            ->select('SUM(v.pageViews) as page_sum, SUM(v.phoneViews) as phone_sum')
//            ->andWhere('v.entityId = :entityId')
//            ->andWhere('v.created BETWEEN :from AND :to')
//            ->setParameter('entityId', $entityId)
//            ->setParameter('from', $from)
//            ->setParameter('to', $to)
//            ->getQuery('v.entityID')
//            ->getQuery()
//            ->getSingleScalarResult();
//    }

    public function getPageViewsById(int $id): ?int
    {
        $view = $this->find($id);
        return $view ? $view->getPageViews() : null;
    }

    public function getPhoneViewsById(int $id): ?int
    {
        $view = $this->find($id);
        return $view ? $view->getPhoneViews() : null;
    }
}
