<?php

/**
 * @see       https://github.com/laminas/laminas-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Console\Charset;

/**
 * UTF-8 box drawing (modified to use heavy single lines)
 *
 * @link http://en.wikipedia.org/wiki/Box-drawing_characters
 */
class Utf8Heavy extends Utf8
{
    const LINE_SINGLE_EW = "━";
    const LINE_SINGLE_NS = "┃";
    const LINE_SINGLE_NW = "┏";
    const LINE_SINGLE_NE = "┓";
    const LINE_SINGLE_SE = "┛";
    const LINE_SINGLE_SW = "┗";
    const LINE_SINGLE_CROSS = "╋";
}
