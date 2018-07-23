<?php
/**
 * @see       https://github.com/zendframework/zend-text for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-text/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Text\Table\Decorator;

use Zend\Text\Table\Decorator\DecoratorInterface as Decorator;

/**
 * ASCII Decorator for Zend\Text\Table
 */
class Ascii implements Decorator
{
    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getTopLeft()
    {
        return '+';
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getTopRight()
    {
        return '+';
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getBottomLeft()
    {
        return '+';
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getBottomRight()
    {
        return '+';
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getVertical()
    {
        return '|';
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getHorizontal()
    {
        return '-';
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getCross()
    {
        return '+';
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getVerticalRight()
    {
        return '+';
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getVerticalLeft()
    {
        return '+';
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getHorizontalDown()
    {
        return '+';
    }

    /**
     * Defined by Zend\Text\Table\Decorator\DecoratorInterface
     *
     * @return string
     */
    public function getHorizontalUp()
    {
        return '+';
    }
}
