<?php
/**
 * UserService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Victor Kofia <victor.kofia@gmail.com>
 * @copyright Copyright (c) 2017 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2017 Victor Kofia <victor.kofia@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
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
