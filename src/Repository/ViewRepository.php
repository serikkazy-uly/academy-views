<?php

namespace App\Repository;

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
        parent::__construct($registry, View::class);
    }

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
