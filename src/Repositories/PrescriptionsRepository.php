<?php
/**
 * Prescriptions Repository
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2019 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Repositories;

use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;
use OpenEMR\Entities\Prescriptions;

class PrescriptionsRepository extends EntityRepository
{
    /**
     * @param Prescriptions
     * @return array|object[]
     */
    public function getDrugsTx()
    {
        //Parameters used to find the drugs selected to be transmitted today
        $pid = $GLOBALS['pid'];
        $digit = 1;
        $txNow = date("Y-m-d");
        $drugQuery = $this->_em->getRepository($this->_entityName)->createQueryBuilder('p')
            ->select('p.id','p.drug','p.dosage','p.quantity','p.refills')
            ->andWhere('p.patient_id = :pid', 'p.ntx = :digit', 'p.txDate = :txNow')
            ->setParameters(['pid' => $pid, 'digit' => $digit, 'txNow' => $txNow])
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);

        return $drugQuery;
    }
}
