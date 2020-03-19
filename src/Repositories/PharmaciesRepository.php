<?php
/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c )2019. Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Repositories;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use OpenEMR\Entities\Addresses;
use OpenEMR\Entities\Pharmacies;

/**
 * Class PharmaciesRepository
 *
 * @package OpenEMR\Repositories
 */
class PharmaciesRepository extends EntityRepository
{
    /**
     * @param  Pharmacies
     * @param  $term
     * @return \Doctrine\ORM\QueryBuilder
     */

    public function findAllMatch($term)
    {
        $find = $this->_em->getRepository($this->_entityName)->createQueryBuilder('pa')
            ->select('pa.id', 'pa.name', 'a.line1', 'a.city', 'a.state')
            ->leftJoin(
                Addresses::class,
                'a',
                Query\Expr\Join::WITH,
                'pa.id = a.foreign_id'
            )
            ->where('pa.name LIKE :term')
            ->setParameter('term', '%'.$term.'%')
            ->orderBy('pa.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $find;
    }
}
