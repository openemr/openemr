<?php

/**
 * @see       https://github.com/laminas/laminas-text for the canonical source repository
 * @copyright https://github.com/laminas/laminas-text/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-text/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Text\Table\Decorator;

use Laminas\Text\Table\Decorator\DecoratorInterface as Decorator;

/**
 * ASCII Decorator for Laminas\Text\Table
 */
class Ascii implements Decorator
{
    /**
     * Defined by Laminas\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getTopLeft()
    {
        return '+';
    }

    /**
     * Defined by Laminas\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getTopRight()
    {
        return '+';
    }

    /**
     * Defined by Laminas\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getBottomLeft()
    {
        return '+';
    }

    /**
     * Defined by Laminas\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getBottomRight()
    {
        return '+';
    }

    /**
     * Defined by Laminas\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getVertical()
    {
        return '|';
    }

    /**
     * Defined by Laminas\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getHorizontal()
    {
        return '-';
    }

    /**
     * Defined by Laminas\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getCross()
    {
        return '+';
    }

    /**
     * Defined by Laminas\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getVerticalRight()
    {
        return '+';
    }

    /**
     * Defined by Laminas\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getVerticalLeft()
    {
        return '+';
    }

    /**
     * Defined by Laminas\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getHorizontalDown()
    {
        return '+';
    }

    /**
     * Defined by Laminas\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getHorizontalUp()
    {
        return '+';
    }
}
