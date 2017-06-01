<?php
/**
 * User repository.
 *
 * @package OpenEMR
 * @subpackage User
 *
 * @author  Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2017 Matthew Vita
 *
 * @author Victor Kofia <victor.kofia@gmail.com>
 * @copyright Copyright (c) 2017 Victor Kofia
 *
 * @author Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2017 Robert Down
 *
 * @link    http://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace repositories;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Collections\Criteria;

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

    /**
     * Finds the user associated with the specified id
     *
     * @return user.
     */
    public function getUser($userId) {
        $results = $this->_em->getRepository($this->_entityName)->findOneBy(array("id" => $userId));
        return $results;
    }

    /**
     * Returns all active users
     *
     * @return users
     */
    public function getActiveUsers() {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->neq("username", ""));
        $criteria->andWhere(Criteria::expr()->eq("active", 1));
        $criteria->orderBy(array("lname" => "ASC", "fname" => "ASC", "mname" => "ASC"));
        $results = $this->_em->getRepository($this->_entityName)->matching($criteria);
        return $results;
    }

    public function getAllUsers()
    {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->neq("username", ""));
        $criteria->orderBy(array("lname" => "ASC", "fname" => "ASC", "mname" => "ASC"));
        $results = $this->_em->getRepository($this->_entityName)->matching($criteria);
        return $results;
    }
}
