<?php

/**
 * @see       https://github.com/laminas/laminas-i18n for the canonical source repository
 * @copyright https://github.com/laminas/laminas-i18n/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-i18n/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\I18n\Filter;

use Laminas\Stdlib\ErrorHandler;

class NumberFormat extends NumberParse
{
    /**
     * Defined by Laminas\Filter\FilterInterface
     *
     * @see    \Laminas\Filter\FilterInterface::filter()
     * @param  mixed $value
     * @return mixed
     */
    public function filter($value)
    {
        if (! is_scalar($value)) {
            return $value;
        }

        if (! is_int($value) && ! is_float($value)) {
            $result = parent::filter($value);
        } else {
            ErrorHandler::start();

            $result = $this->getFormatter()->format($value, $this->getType());

            ErrorHandler::stop();
        }

        if (false !== $result) {
            return $result;
        }

        return $value;
    }
}
