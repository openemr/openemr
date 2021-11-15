<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Filter\Word;

use Laminas\Filter\AbstractFilter;
use Laminas\Filter\Exception;

class SeparatorToSeparator extends AbstractFilter
{
    protected $searchSeparator;
    protected $replacementSeparator;

    /**
     * Constructor
     *
     * @param  string $searchSeparator      Separator to search for
     * @param  string $replacementSeparator Separator to replace with
     */
    public function __construct($searchSeparator = ' ', $replacementSeparator = '-')
    {
        $this->setSearchSeparator($searchSeparator);
        $this->setReplacementSeparator($replacementSeparator);
    }

    /**
     * Sets a new separator to search for
     *
     * @param  string $separator Separator to search for
     * @return self
     */
    public function setSearchSeparator($separator)
    {
        $this->searchSeparator = $separator;
        return $this;
    }

    /**
     * Returns the actual set separator to search for
     *
     * @return string
     */
    public function getSearchSeparator()
    {
        return $this->searchSeparator;
    }

    /**
     * Sets a new separator which replaces the searched one
     *
     * @param  string $separator Separator which replaces the searched one
     * @return self
     */
    public function setReplacementSeparator($separator)
    {
        $this->replacementSeparator = $separator;
        return $this;
    }

    /**
     * Returns the actual set separator which replaces the searched one
     *
     * @return string
     */
    public function getReplacementSeparator()
    {
        return $this->replacementSeparator;
    }

    /**
     * Defined by Laminas\Filter\Filter
     *
     * Returns the string $value, replacing the searched separators with the defined ones
     *
     * @param  string|array $value
     * @return string|array
     */
    public function filter($value)
    {
        if (! is_scalar($value) && ! is_array($value)) {
            return $value;
        }

        if ($this->searchSeparator === null) {
            throw new Exception\RuntimeException('You must provide a search separator for this filter to work.');
        }

        $pattern = '#' . preg_quote($this->searchSeparator, '#') . '#';
        return preg_replace($pattern, $this->replacementSeparator, $value);
    }
}
