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

namespace OpenEMR\Admin\Service;

use OpenEMR\Admin\Repository\SettingRepository;
use OpenEMR\Common\Database\Connector;

class SettingsService
{

    /**
     * @var SettingRepository
     */
    private $repository;

    public function __construct() {
        $db = Connector::Instance();
        $em = $db->entityManager;
        $this->repository = $em->getRepository('\OpenEMR\Admin\Entity\Setting');
    }

    public function getSettingByName($name) {
        return $this->repository->getSettingByName($name);
    }
}
