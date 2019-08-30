<?php
/**
 * This file is part of OpenEMR.
 *
 * @package     OpenEMR
 * @subpackage
 * @link        https://www.open-emr.org
 * @author      Robert Down <robertdown@live.com>
 * @copyright   Copyright (c) 2019 Robert Down <robertdown@live.com
 * @license     https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Admin\Repository;

use Doctrine\ORM\EntityRepository;

class SettingRepository extends EntityRepository
{
    public function getSettingByName($settingName) {
        $results = $this->_em->getRepository($this->_entityName)->findBy(['name' => $settingName]);
        return $results;
    }
}
