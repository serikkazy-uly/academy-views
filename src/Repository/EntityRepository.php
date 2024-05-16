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

    public function updateViewCounts(string $project, string $entity, int $id, int $pageViews, int $phoneViews): EntityViewCounts
    {
        $currentDate = new \DateTime();

        $viewCount = $this->findOneBy([
            'entityId' => $id,
            'entity' => $entity,
            'project' => $project,
            'date' => $currentDate
        ]);

        if (!$viewCount) {
            $viewCount = new EntityViewCounts();
            $viewCount->setProject($project);
            $viewCount->setEntity($entity);
            $viewCount->setEntityId($id);
            $viewCount->setPageViews(0);
            $viewCount->setPhoneViews(0);
            $viewCount->setDate($currentDate);
        }

        $viewCount->setPageViews($viewCount->getPageViews() + $pageViews);
        $viewCount->setPhoneViews($viewCount->getPhoneViews() + $phoneViews);

        $this->getEntityManager()->persist($viewCount);
        $this->getEntityManager()->flush();

        return $viewCount;
    }

    public function findViewCounts(string $project, string $entity, int $id): ?array
    {
        $qb = $this->createQueryBuilder('e')
            ->select('SUM(e.pageViews) as page_views, SUM(e.phoneViews) as phone_views')
            ->where('e.project = :project')
            ->andWhere('e.entity = :entity')
            ->andWhere('e.entityId = :id')
            ->setParameter('project', $project)
            ->setParameter('entity', $entity)
            ->setParameter('id', $id)
            ->getQuery();

        return $qb->getOneOrNullResult();
    }

    public function findViewStatistics(int $id, string $project, string $entity, string $fromDate, string $toDate): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb->select('SUM(e.pageViews) as pageViews', 'SUM(e.phoneViews) as phoneViews')
            ->where('e.entityId = :entityId')
            ->andWhere('e.project = :project')
            ->andWhere('e.entity = :entity')
            ->andWhere('e.date BETWEEN :fromDate AND :toDate')
            ->setParameter('entityId', $id)
            ->setParameter('project', $project)
            ->setParameter('entity', $entity)
            ->setParameter('fromDate', new \DateTime($fromDate))
            ->setParameter('toDate', new \DateTime($toDate))
            ->groupBy('e.entityId');

        $result = $qb->getQuery()->getOneOrNullResult();

        if (!$result) {
            return [
                'page_views' => 0,
                'phone_views' => 0
            ];
        }

        return [
            'page_views' => (int)$result['pageViews'],
            'phone_views' => (int)$result['phoneViews']
        ];
    }
}
