<?php

/**
 * @see       https://github.com/laminas/laminas-stdlib for the canonical source repository
 * @copyright https://github.com/laminas/laminas-stdlib/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-stdlib/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Stdlib;

use Serializable;

use function is_array;
use function serialize;
use function unserialize;

/**
 * Serializable version of SplPriorityQueue
 *
 * Also, provides predictable heap order for datums added with the same priority
 * (i.e., they will be emitted in the same order they are enqueued).
 */
class SplPriorityQueue extends \SplPriorityQueue implements Serializable
{
    /**
     * @var int Seed used to ensure queue order for items of the same priority
     */
    protected $serial = PHP_INT_MAX;

    /**
     * Insert a value with a given priority
     *
     * Utilizes {@var $serial} to ensure that values of equal priority are
     * emitted in the same order in which they are inserted.
     *
     * @param  mixed $datum
     * @param  mixed $priority
     * @return void
     */
    public function insert($datum, $priority)
    {
        if (! is_array($priority)) {
            $priority = [$priority, $this->serial--];
        }
        parent::insert($datum, $priority);
    }

    /**
     * Serialize to an array
     *
     * Array will be priority => data pairs
     *
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach (clone $this as $item) {
            $array[] = $item;
        }
        return $array;
    }

    /**
     * Serialize
     *
     * @return string
     */
    public function serialize()
    {
        $clone = clone $this;
        $clone->setExtractFlags(self::EXTR_BOTH);

        $data = [];
        foreach ($clone as $item) {
            $data[] = $item;
        }

        return serialize($data);
    }

    /**
     * Deserialize
     *
     * @param  string $data
     * @return void
     */
    public function unserialize($data)
    {
        $this->serial = PHP_INT_MAX;
        foreach (unserialize($data) as $item) {
            $this->serial--;
            $this->insert($item['data'], $item['priority']);
        }
    }
}
