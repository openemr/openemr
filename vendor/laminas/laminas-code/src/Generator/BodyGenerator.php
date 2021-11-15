<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Generator;

class BodyGenerator extends AbstractGenerator
{
    /**
     * @var string
     */
    protected $content;

    /**
     * @param  string $content
     * @return BodyGenerator
     */
    public function setContent($content)
    {
        $this->content = (string) $content;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function generate()
    {
        return $this->getContent();
    }
}
