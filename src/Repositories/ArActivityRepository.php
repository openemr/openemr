<?php


namespace OpenEMR\Repositories;

use Doctrine\ORM\EntityRepository;
use OpenEMR\Entities\ArActivity;

class ArActivityRepository extends EntityRepository
{

    /**
     * @param ArActivity
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getActivities()
    {
        $data = $this->_em->getRepository($this->_entityName)->createQueryBuilder('ara');
        return $data;
    }
}
