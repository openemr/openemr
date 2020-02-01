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
use OpenEMR\Entities\PatientData;

class PatientDataRepository extends EntityRepository
{
    /**
     * @param PatientData
     * @param $id
     * @param $pid
     */
    public function updatePatientPharmacy($id,$pid)
    {
        $set = $this->_em->getRepository($this->_entityName)->createQueryBuilder('p')
            ->update()
            ->set('p.pharmacy_id', '?1')
            ->where('p.pid = :pid')
            ->setParameter(1, $id)
            ->setParameter('pid', $pid)
            ->getQuery();
        $store = $set->execute();
        return $store;
    }

    public function findPatientPharmacy()
    {
        return "Arbys";
    }

}
