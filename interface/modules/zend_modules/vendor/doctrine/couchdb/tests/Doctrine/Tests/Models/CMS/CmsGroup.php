<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Doctrine\Tests\Models\CMS;

/**
 * @Document
 */
class CmsGroup
{
    /** @Id */
    public $id;
    /** @Field(type="string") */
    public $name;

    /** @ReferenceMany(targetDocument="CmsUser", mappedBy="groups") */
    public $users;

    public function setName($name) {
        $this->name = $name;
    }

    public function getName() {
        return $this->name;
    }

    public function addUser(CmsUser $user) {
        $this->users[] = $user;
    }

    public function getUsers() {
        return $this->users;
    }
}

