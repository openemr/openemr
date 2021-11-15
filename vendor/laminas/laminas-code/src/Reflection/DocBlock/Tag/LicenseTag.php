<?php

/**
 * @see       https://github.com/laminas/laminas-code for the canonical source repository
 * @copyright https://github.com/laminas/laminas-code/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-code/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Code\Reflection\DocBlock\Tag;

use function preg_match;
use function trim;

class LicenseTag implements TagInterface
{
    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $licenseName;

    /**
     * @return string
     */
    public function getName()
    {
        return 'license';
    }

    /**
     * Initializer
     *
     * @param  string $tagDocblockLine
     */
    public function initialize($tagDocblockLine)
    {
        $match = [];

        if (! preg_match('#^([\S]*)(?:\s+(.*))?$#m', $tagDocblockLine, $match)) {
            return;
        }

        if ($match[1] !== '') {
            $this->url = trim($match[1]);
        }

        if (isset($match[2]) && $match[2] !== '') {
            $this->licenseName = $match[2];
        }
    }

    /**
     * @return null|string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return null|string
     */
    public function getLicenseName()
    {
        return $this->licenseName;
    }

    public function __toString()
    {
        return 'DocBlock Tag [ * @' . $this->getName() . ' ]' . "\n";
    }
}
