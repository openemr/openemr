<?php

/**
 * @see       https://github.com/laminas/laminas-eventmanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-eventmanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-eventmanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\EventManager\Filter;

use Laminas\EventManager\Exception;
use Laminas\Stdlib\FastPriorityQueue;

use function get_class;
use function gettype;
use function is_callable;
use function is_object;
use function sprintf;

/**
 * Specialized priority queue implementation for use with an intercepting
 * filter chain.
 *
 * Allows removal
 */
class FilterIterator extends FastPriorityQueue
{
    /**
     * Does the queue contain a given value?
     *
     * @param  mixed $datum
     * @return bool
     */
    public function contains($datum)
    {
        foreach ($this as $item) {
            if ($item === $datum) {
                return true;
            }
        }
        return false;
    }

    /**
     * Insert a value into the queue.
     *
     * Requires a callable.
     *
     * @param callable $value
     * @param mixed $priority
     * @return void
     * @throws Exception\InvalidArgumentException for non-callable $value.
     */
    public function insert($value, $priority)
    {
        if (! is_callable($value)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s can only aggregate callables; received %s',
                __CLASS__,
                (is_object($value) ? get_class($value) : gettype($value))
            ));
        }
        parent::insert($value, $priority);
    }

    /**
     * Remove a value from the queue
     *
     * This is an expensive operation. It must first iterate through all values,
     * and then re-populate itself. Use only if absolutely necessary.
     *
     * @param  mixed $datum
     * @return bool
     */
    public function remove($datum)
    {
        $this->setExtractFlags(self::EXTR_BOTH);

        // Iterate and remove any matches
        $removed = false;
        $items   = [];
        $this->rewind();
        while (! $this->isEmpty()) {
            $item = $this->extract();
            if ($item['data'] === $datum) {
                $removed = true;
                continue;
            }
            $items[] = $item;
        }

        // Repopulate
        foreach ($items as $item) {
            $this->insert($item['data'], $item['priority']);
        }

        $this->setExtractFlags(self::EXTR_DATA);
        return $removed;
    }

    /**
     * Iterate the next filter in the chain
     *
     * Iterates and calls the next filter in the chain.
     *
     * @param  mixed $context
     * @param  array $params
     * @param  FilterIterator $chain
     * @return mixed
     */
    public function next($context = null, array $params = [], $chain = null)
    {
        if (empty($context) || ($chain instanceof FilterIterator && $chain->isEmpty())) {
            return;
        }

        //We can't extract from an empty heap
        if ($this->isEmpty()) {
            return;
        }

        $next = $this->extract();
        return $next($context, $params, $chain);
    }
}
