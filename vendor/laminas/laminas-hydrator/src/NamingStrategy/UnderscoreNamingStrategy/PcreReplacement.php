<?php

/**
 * @see       https://github.com/laminas/laminas-hydrator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-hydrator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Hydrator\NamingStrategy\UnderscoreNamingStrategy;

/**
 * Describe a PCRE pattern and a callback for providing a replacement.
 *
 * @internal
 */
class PcreReplacement
{
    /**
     * @var string
     */
    public $pattern;

    /**
     * @var callable
     */
    public $replacement;

    public function __construct(string $pattern, callable $replacement)
    {
        $this->pattern     = $pattern;
        $this->replacement = $replacement;
    }
}
