<?php

/**
 * ArSession Repository
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Repositories;

use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;
use OpenEMR\Entities\ArSession;
use OpenEMR\Entities\ArActivity;
use OpenEMR\Entities\Billing;

class ArSessionRepository extends EntityRepository
{
    /**
     *
     * @param ArSession $payer_id
     * @return array pid.
     */
    public function getInsurerPaid($payer_id)
    {
        $data = $this->_em->getRepository($this->_entityName)->createQueryBuilder('ars')
            ->select('ars.payer_id','ars.description','ars.pay_total','ars.payment_method','ars.reference',
                'ars.deposit_date','ars.post_to_date','ara.pid','ara.post_user', 'ara.memo', 'ara.encounter'
                 )
            ->addSelect('SUM(ara.pay_amount) AS paidAmount','SUM(ara.adj_amount) AS Adjustments')
            ->andWhere('ars.payer_id = :searchTerm')
            ->leftJoin(ArActivity::class, 'ara',
                Query\Expr\Join::WITH, 'ara.session_id = ars.session_id' )
            ->groupBy('ara.encounter')
            ->setParameter('searchTerm', $payer_id)
            ->setMaxResults(100)
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);

        return $data;

    }


}
