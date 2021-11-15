<?php

/**
 * @see       https://github.com/laminas/laminas-hydrator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-hydrator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Hydrator\NamingStrategy;

use Laminas\Hydrator\NamingStrategy\UnderscoreNamingStrategy\CamelCaseToUnderscoreFilter;
use Laminas\Hydrator\NamingStrategy\UnderscoreNamingStrategy\UnderscoreToCamelCaseFilter;

class UnderscoreNamingStrategy implements NamingStrategyInterface
{
    /**
     * @var CamelCaseToUnderscoreFilter|null
     */
    private static $camelCaseToUnderscoreFilter;

    /**
     * @var UnderscoreToCamelCaseFilter|null
     */
    private static $underscoreToCamelCaseFilter;

    /**
     * Remove underscores and capitalize letters
     */
    public function hydrate(string $name, ?array $data = null) : string
    {
        return $this->getUnderscoreToCamelCaseFilter()->filter($name);
    }

    /**
     * Remove capitalized letters and prepend underscores.
     */
    public function extract(string $name, ?object $object = null) : string
    {
        return $this->getCamelCaseToUnderscoreFilter()->filter($name);
    }

    private function getUnderscoreToCamelCaseFilter() : UnderscoreToCamelCaseFilter
    {
        if (! static::$underscoreToCamelCaseFilter) {
            static::$underscoreToCamelCaseFilter = new UnderscoreToCamelCaseFilter();
        }

        return static::$underscoreToCamelCaseFilter;
    }

    private function getCamelCaseToUnderscoreFilter() : CamelCaseToUnderscoreFilter
    {
        if (! static::$camelCaseToUnderscoreFilter) {
            static::$camelCaseToUnderscoreFilter = new CamelCaseToUnderscoreFilter();
        }

        return static::$camelCaseToUnderscoreFilter;
    }
}
