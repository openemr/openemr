<?php

namespace Doctrine\Tests\Models\CMS;

/**
 * @Document
 */
class CmsArticle
{
    /** @Id */
    public $id;
    /** @Field(type="string") */
    public $topic;
    /** @Field(type="string") */
    public $text;
    /** @ReferenceOne(targetDocument="CmsUser") */
    public $user;
    public $comments;
    /** @Version */
    public $version;

    /** @Attachments */
    public $attachments;
    
    public function setAuthor(CmsUser $author) {
        $this->user = $author;
    }

    public function addComment(CmsComment $comment) {
        $this->comments[] = $comment;
        $comment->setArticle($this);
    }
}
