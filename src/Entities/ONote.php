<?php
/**
 * Office note entity.
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

namespace OpenEMR\Entities;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn   ;
use Doctrine\ORM\Mapping\GeneratedValue;

/**
 * @Table(name="onotes")
 * @Entity(repositoryClass="OpenEMR\Repositories\ONoteRepository")
 */
class ONote
{
    /**
     * Default constructor.
     */
    public function __construct()
    {
    }

    /**
     * @Id
     * @Column(name="id", type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @Column(name="body", type="text")
     */
    private $body;

    /**
     * @Column(name="groupname", type="string", length=255)
     */
    private $groupName;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user", referencedColumnName="username")
     */
    private $user;

    /**
     * @Column(name="activity", type="integer", length=4)
     */
    private $activity;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($value)
    {
        $this->date = $value;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($value)
    {
        $this->body = $value;
    }

    public function getGroupName()
    {
        return $this->groupName;
    }

    public function setGroupName($value)
    {
        $this->groupName = $value;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($value)
    {
        $this->user = $value;
    }

    public function getActivity()
    {
        return $this->activity;
    }

    public function setActivity($value)
    {
        $this->activity = $value;
    }

    /**
     * ToString of the entire object.
     *
     * @return object as string
     */
    public function __toString()
    {
        return "id: '" . $this->getId() . "' " .
               "date: '" . $this->getDate()->format('Y-m-d H:i:s') . "' " .
               "activity: '" . $this->getActivity() . "' " .
               "groupname: '" . $this->getGroupName() . "' " .
               "body: '" . $this->getBody() . "' ";
    }

    /**
     * ToSerializedObject of the entire object.
     *
     * @return object as serialized object.
     */
    public function toSerializedObject()
    {
        return get_object_vars($this);
    }
}
