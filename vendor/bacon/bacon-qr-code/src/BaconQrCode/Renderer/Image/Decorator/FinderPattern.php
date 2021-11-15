<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Renderer\Image\Decorator;

use BaconQrCode\Encoder\QrCode;
use BaconQrCode\Renderer\Image\RendererInterface;
use BaconQrCode\Renderer\Color;

/**
 * Finder pattern decorator.
 */
class FinderPattern implements DecoratorInterface
{
    /**
     * @var Color\ColorInterface
     */
    protected $innerColor;

    /**
     * @varColor\ColorInterface
     */
    protected $outerColor;

    /**
     * Outer position detection pattern.
     *
     * @var array
     */
    protected static $outerPositionDetectionPattern = array(
        array(1, 1, 1, 1, 1, 1, 1),
        array(1, 0, 0, 0, 0, 0, 1),
        array(1, 0, 0, 0, 0, 0, 1),
        array(1, 0, 0, 0, 0, 0, 1),
        array(1, 0, 0, 0, 0, 0, 1),
        array(1, 0, 0, 0, 0, 0, 1),
        array(1, 1, 1, 1, 1, 1, 1),
    );

    /**
     * Inner position detection pattern.
     *
     * @var array
     */
    protected static $innerPositionDetectionPattern = array(
        array(0, 0, 0, 0, 0, 0, 0),
        array(0, 0, 0, 0, 0, 0, 0),
        array(0, 0, 1, 1, 1, 0, 0),
        array(0, 0, 1, 1, 1, 0, 0),
        array(0, 0, 1, 1, 1, 0, 0),
        array(0, 0, 0, 0, 0, 0, 0),
        array(0, 0, 0, 0, 0, 0, 0),
    );

    /**
     * Sets outer color.
     *
     * @param  Color\ColorInterface $color
     * @return FinderPattern
     */
    public function setOuterColor(Color\ColorInterface $color)
    {
        $this->outerColor = $color;
        return $this;
    }

    /**
     * Gets outer color.
     *
     * @return Color\ColorInterface
     */
    public function getOuterColor()
    {
        if ($this->outerColor === null) {
            $this->outerColor = new Color\Gray(100);
        }

        return $this->outerColor;
    }

    /**
     * Sets inner color.
     *
     * @param  Color\ColorInterface $color
     * @return FinderPattern
     */
    public function setInnerColor(Color\ColorInterface $color)
    {
        $this->innerColor = $color;
        return $this;
    }

    /**
     * Gets inner color.
     *
     * @return Color\ColorInterface
     */
    public function getInnerColor()
    {
        if ($this->innerColor === null) {
            $this->innerColor = new Color\Gray(0);
        }

        return $this->innerColor;
    }

    /**
     * preProcess(): defined by DecoratorInterface.
     *
     * @see    DecoratorInterface::preProcess()
     * @param  QrCode            $qrCode
     * @param  RendererInterface $renderer
     * @param  integer           $outputWidth
     * @param  integer           $outputHeight
     * @param  integer           $leftPadding
     * @param  integer           $topPadding
     * @param  integer           $multiple
     * @return void
     */
    public function preProcess(
        QrCode $qrCode,
        RendererInterface $renderer,
        $outputWidth,
        $outputHeight,
        $leftPadding,
        $topPadding,
        $multiple
    ) {
        $matrix    = $qrCode->getMatrix();
        $positions = array(
            array(0, 0),
            array($matrix->getWidth() - 7, 0),
            array(0, $matrix->getHeight() - 7),
        );

        foreach (self::$outerPositionDetectionPattern as $y => $row) {
            foreach ($row as $x => $isSet) {
                foreach ($positions as $position) {
                    $matrix->set($x + $position[0], $y + $position[1], 0);
                }
            }
        }
    }

    /**
     * postProcess(): defined by DecoratorInterface.
     *
     * @see    DecoratorInterface::postProcess()
     *
     * @param  QrCode            $qrCode
     * @param  RendererInterface $renderer
     * @param  integer           $outputWidth
     * @param  integer           $outputHeight
     * @param  integer           $leftPadding
     * @param  integer           $topPadding
     * @param  integer           $multiple
     * @return void
     */
    public function postProcess(
        QrCode $qrCode,
        RendererInterface $renderer,
        $outputWidth,
        $outputHeight,
        $leftPadding,
        $topPadding,
        $multiple
    ) {
        $matrix    = $qrCode->getMatrix();
        $positions = array(
            array(0, 0),
            array($matrix->getWidth() - 7, 0),
            array(0, $matrix->getHeight() - 7),
        );

        $renderer->addColor('finder-outer', $this->getOuterColor());
        $renderer->addColor('finder-inner', $this->getInnerColor());

        foreach (self::$outerPositionDetectionPattern as $y => $row) {
            foreach ($row as $x => $isOuterSet) {
                $isInnerSet = self::$innerPositionDetectionPattern[$y][$x];

                if ($isOuterSet) {
                    foreach ($positions as $position) {
                        $renderer->drawBlock(
                            $leftPadding + $x * $multiple + $position[0] * $multiple,
                            $topPadding + $y * $multiple + $position[1] * $multiple,
                            'finder-outer'
                        );
                    }
                }

                if ($isInnerSet) {
                    foreach ($positions as $position) {
                        $renderer->drawBlock(
                            $leftPadding + $x * $multiple + $position[0] * $multiple,
                            $topPadding + $y * $multiple + $position[1] * $multiple,
                            'finder-inner'
                        );
                    }
                }
            }
        }
    }
}