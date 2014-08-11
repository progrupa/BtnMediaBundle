<?php

namespace Btn\MediaBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;

class MediaRepository extends EntityRepository
{
    public function getCategories()
    {
        $qb = $this->createQueryBuilder('mf')
            ->select('mf.category')
            ->groupBy('mf.category')
        ;

        $result     = $qb->getQuery()->getResult();
        $categories = array();

        array_walk($result, function ($category) use (&$categories) {
            $categories[] = $category['category'];
        });

        return $categories;
    }

    public function getAllByIds($idList = array(), $groupBy = true)
    {
        $qb = $this->createQueryBuilder('mf')
            ->select()
            ->where('mf.id IN (:id_list)')
        ;

        if ($groupBy) {
            $qb->groupBy('mf.category');
        }

        $qb->setParameter('id_list', $idList);

        $result = $qb->getQuery()->getResult();

        if ($groupBy) {
            $categories = array();

            array_walk($result, function ($category) use (&$categories) {
                $categories[] = $category['category'];
            });

            $result = $categories;
        }

        return $result;
    }
}
