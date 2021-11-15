<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Renderer\Image;

use BaconQrCode\Renderer\Color\ColorInterface;
use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Color\Cmyk;
use BaconQrCode\Renderer\Color\Gray;

/**
 * EPS backend.
 */
class Eps extends AbstractRenderer
{
    /**
     * EPS string.
     *
     * @var string
     */
    protected $eps;

    /**
     * Colors used for drawing.
     *
     * @var array
     */
    protected $colors = array();

    /**
     * Current color.
     *
     * @var string
     */
    protected $currentColor;

    /**
     * init(): defined by RendererInterface.
     *
     * @see    ImageRendererInterface::init()
     * @return void
     */
    public function init()
    {
        $this->eps = "%!PS-Adobe-3.0 EPSF-3.0\n"
                   . "%%BoundingBox: 0 0 " . $this->finalWidth . " " . $this->finalHeight . "\n"
                   . "/F { rectfill } def\n";
    }

    /**
     * addColor(): defined by RendererInterface.
     *
     * @see    ImageRendererInterface::addColor()
     * @param  string         $id
     * @param  ColorInterface $color
     * @return void
     */
    public function addColor($id, ColorInterface $color)
    {
        if (
            !$color instanceof Rgb
            && !$color instanceof Cmyk
            && !$color instanceof Gray
        ) {
            $color = $color->toCmyk();
        }

        $this->colors[$id] = $color;
    }

    /**
     * drawBackground(): defined by RendererInterface.
     *
     * @see    ImageRendererInterface::drawBackground()
     * @param  string $colorId
     * @return void
     */
    public function drawBackground($colorId)
    {
        $this->setColor($colorId);
        $this->eps .= "0 0 " . $this->finalWidth . " " . $this->finalHeight . " F\n";
    }

    /**
     * drawBlock(): defined by RendererInterface.
     *
     * @see    ImageRendererInterface::drawBlock()
     * @param  integer $x
     * @param  integer $y
     * @param  string  $colorId
     * @return void
     */
    public function drawBlock($x, $y, $colorId)
    {
        $this->setColor($colorId);
        $this->eps .= $x . " " . ($this->finalHeight - $y - $this->blockSize) . " " . $this->blockSize . " " . $this->blockSize . " F\n";
    }

    /**
     * getByteStream(): defined by RendererInterface.
     *
     * @see    ImageRendererInterface::getByteStream()
     * @return string
     */
    public function getByteStream()
    {
        return $this->eps;
    }

    /**
     * Sets color to use.
     *
     * @param  string $colorId
     * @return void
     */
    protected function setColor($colorId)
    {
        if ($colorId !== $this->currentColor) {
            $color = $this->colors[$colorId];

            if ($color instanceof Rgb) {
                $this->eps .= sprintf(
                    "%F %F %F setrgbcolor\n",
                    $color->getRed() / 100,
                    $color->getGreen() / 100,
                    $color->getBlue() / 100
                );
            } elseif ($color instanceof Cmyk) {
                $this->eps .= sprintf(
                    "%F %F %F %F setcmykcolor\n",
                    $color->getCyan() / 100,
                    $color->getMagenta() / 100,
                    $color->getYellow() / 100,
                    $color->getBlack() / 100
                );
            } elseif ($color instanceof Gray) {
                $this->eps .= sprintf(
                    "%F setgray\n",
                    $color->getGray() / 100
                );
            }

            $this->currentColor = $colorId;
        }
    }
}
