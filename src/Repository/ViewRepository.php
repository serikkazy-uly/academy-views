<?php

namespace App\Repository;

use App\Entity\EntityViewCount;
use App\Entity\EntityViewCounts;
use App\Entity\View;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<View>
 */
class ViewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntityViewCounts::class);
    }

    public function getTotalViewsByPeriod(int $entityId, string $from, string $to): array
    {
        return $this->createQueryBuilder('v')
            ->select('SUM(v.pageViews) as page_sum, SUM(v.phoneViews) as phone_sum')
            ->andWhere('v.entityId = :entityId')
            ->andWhere('v.created BETWEEN :from AND :to')
            ->setParameter('entityId', $entityId)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->groupBy('v.entityId')
            ->getQuery()
            ->getSingleResult();
    }

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
