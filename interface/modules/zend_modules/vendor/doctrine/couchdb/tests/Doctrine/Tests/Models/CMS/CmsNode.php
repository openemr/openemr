<?php

namespace Doctrine\Tests\Models\CMS;

use Doctrine\Common\Collections\ArrayCollection;

/** @Document */
class CmsNode
{
    /** @Id */
    public $id;

    /** @Field */
    public $path;

    /** @ReferenceOne */
    public $parent;

    /** @ReferenceOne */
    public $content;

    /** @ReferenceMany */
    public $children;

    /**
     * @ReferenceMany
     */
    public $references;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->references = new ArrayCollection();
    }
}