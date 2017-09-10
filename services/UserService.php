<?php
/**
 * UserService
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
 * @author Victor Kofia <victor.kofia@gmail.com>
 * @link    http://www.open-emr.org
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\Connector;

class UserService
{
    /**
     * The user repository to be used for db CRUD operations.
     */
    private $repository;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        $database = Connector::Instance();
        $entityManager = $database->entityManager;
        $this->repository = $entityManager->getRepository('\OpenEMR\Entities\User');
    }

    /**
     * @return Fully hydrated user object
     */
    public function getUser($userId)
    {
        return $this->repository->getUser($userId);
    }

    /**
     * @return active users (fully hydrated)
     */
    public function getActiveUsers()
    {
        return $this->repository->getActiveUsers();
    }

    /**
     * @return Fully hydrated user object.
     */
    public function getCurrentlyLoggedInUser()
    {
        return $this->repository->getCurrentlyLoggedInUser();
    }

    /**
     * Centralized holder of the `authProvider` session
     * value to encourage service ownership of global
     * session values.
     *
     * @return String of the current user group.
     */
    public function getCurrentlyLoggedInUserGroup()
    {
        return $_SESSION['authProvider'];
    }
}
