<?php
/**
 * Chart tracker repository.

 * Copyright (C) 2017 Victor Kofia <victor.kofia@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Victor Kofia <victor.kofia@gmail.com>
 * @link    http://www.open-emr.org
 */

namespace OpenEMR\Repositories;

use OpenEMR\Entities\ChartTracker;
use Doctrine\ORM\EntityRepository;

class ChartTrackerRepository extends EntityRepository
{

    /**
     * Add chart tracker table entry.
     *
     * @param $tracker chart tracker information.
     * @return the pid.
     */
    public function save(ChartTracker $chartTracker)
    {
        $this->_em->persist($chartTracker);
        $this->_em->flush();
        return $chartTracker->getPid();
    }
}
