<?php
/**
 * User repository.
 *
 * Copyright (C) 2017 Matthew Vita <matthewvita48@gmail.com>
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
 * @author  Matthew Vita <matthewvita48@gmail.com>
 * @link    http://www.open-emr.org
 */

namespace repositories;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository {
    /**
     * Finds the user associated with the local session id.
     *
     * @return user.
     */
    public function getCurrentlyLoggedInUser() {
        $results = $this->_em->getRepository($this->_entityName)->findOneBy(array("username" => $_SESSION["authUser"]));
        return $results;
    }
}
