<?php

/**
 * @see       https://github.com/laminas/laminas-stdlib for the canonical source repository
 * @copyright https://github.com/laminas/laminas-stdlib/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-stdlib/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Stdlib\Guard;

use function sprintf;

/**
 * Provide a guard method against empty data
 */
trait EmptyGuardTrait
{
    /**
     * Verify that the data is not empty
     *
     * @param  mixed  $data           the data to verify
     * @param  string $dataName       the data name
     * @param  string $exceptionClass FQCN for the exception
     * @throws \Exception
     */
    protected function guardAgainstEmpty(
        $data,
        $dataName = 'Argument',
        $exceptionClass = 'Laminas\Stdlib\Exception\InvalidArgumentException'
    ) {
        if (empty($data)) {
            $message = sprintf('%s cannot be empty', $dataName);
            throw new $exceptionClass($message);
        }
    }
}
