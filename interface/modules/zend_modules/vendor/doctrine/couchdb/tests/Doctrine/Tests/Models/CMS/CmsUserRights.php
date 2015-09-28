<?php

namespace Doctrine\Tests\Models\CMS;

/** @Document */
class CmsUserRights
{
    /** @Id */
    public $id;

    /** @Boolean */
    public $canWriteArticle = false;
    /** @Boolean */
    public $canDeleteArticle = false;
}