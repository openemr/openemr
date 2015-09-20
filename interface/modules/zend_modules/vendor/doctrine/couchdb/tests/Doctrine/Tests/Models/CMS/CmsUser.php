<?php

namespace Doctrine\Tests\Models\CMS;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Document @Index
 */
class CmsUser
{
    /** @Id */
    public $id;
    /** @Field(type="string") @Index */
    public $status;
    /** @Field(type="string") @Index */
    public $username;
    /** @Field(type="string") @Index */
    public $name;

    /** @EmbedOne */
    public $address;

    /** @ReferenceOne(targetDocument="CmsUserRights") */
    public $rights;

    /**
     * @ReferenceMany(targetDocument="CmsArticle", mappedBy="user")
     */
    public $articles;

    /** @ReferenceMany(targetDocument="CmsGroup") */
    public $groups;

    /** @Attachments */
    public $attachments;
    
    public function __construct() {
        $this->articles = new ArrayCollection;
        $this->groups = new ArrayCollection;
    }

    public function getId() {
        return $this->id;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getName() {
        return $this->name;
    }

    public function addArticle(CmsArticle $article) {
        $this->articles[] = $article;
        $article->setAuthor($this);
    }

    public function addGroup(CmsGroup $group) {
        $this->groups[] = $group;
        $group->addUser($this);
    }

    public function getGroups() {
        return $this->groups;
    }
    
    public function getAddress() { return $this->address; }
    
    public function setAddress(CmsAddress $address) {
        if ($this->address !== $address) {
            $this->address = $address;
//            $address->setUser($this);
        }
    }
}
