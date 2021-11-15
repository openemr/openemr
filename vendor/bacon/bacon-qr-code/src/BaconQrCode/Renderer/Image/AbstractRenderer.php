<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Renderer\Image;

use BaconQrCode\Encoder\QrCode;
use BaconQrCode\Renderer\Color;
use BaconQrCode\Renderer\Image\Decorator\DecoratorInterface;
use BaconQrCode\Exception;

/**
 * Image renderer, supporting multiple backends.
 */
abstract class AbstractRenderer implements RendererInterface
{
    /**
     * Margin around the QR code, also known as quiet zone.
     *
     * @var integer
     */
    protected $margin = 4;

    /**
     * Requested width of the rendered image.
     *
     * @var integer
     */
    protected $width = 0;

    /**
     * Requested height of the rendered image.
     *
     * @var integer
     */
    protected $height = 0;

    /**
     * Whether dimensions should be rounded down.
     *
     * @var boolean
     */
    protected $roundDimensions = true;

    /**
     * Final width of the image.
     *
     * @var integer
     */
    protected $finalWidth;

    /**
     * Final height of the image.
     *
     * @var integer
     */
    protected $finalHeight;

    /**
     * Size of each individual block.
     *
     * @var integer
     */
    protected $blockSize;

    /**
     * Background color.
     *
     * @var Color\ColorInterface
     */
    protected $backgroundColor;

    /**
     * Whether dimensions should be rounded down
     * 
     * @var boolean
     */
    protected $floorToClosestDimension;

    /**
     * Foreground color.
     *
     * @var Color\ColorInterface
     */
    protected $foregroundColor;

    /**
     * Decorators used on QR codes.
     *
     * @var array
     */
    protected $decorators = array();

    /**
     * Sets the margin around the QR code.
     *
     * @param  integer $margin
     * @return AbstractRenderer
     * @throws Exception\InvalidArgumentException
     */
    public function setMargin($margin)
    {
        if ($margin < 0) {
            throw new Exception\InvalidArgumentException('Margin must be equal to greater than 0');
        }

        $this->margin = (int) $margin;
        return $this;
    }

    /**
     * Gets the margin around the QR code.
     *
     * @return integer
     */
    public function getMargin()
    {
        return $this->margin;
    }

    /**
     * Sets the height around the renderd image.
     *
     * If the width is smaller than the matrix width plus padding, the renderer
     * will automatically use that as the width instead of the specified one.
     *
     * @param  integer $width
     * @return AbstractRenderer
     */
    public function setWidth($width)
    {
        $this->width = (int) $width;
        return $this;
    }

    /**
     * Gets the width of the rendered image.
     *
     * @return integer
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Sets the height around the renderd image.
     *
     * If the height is smaller than the matrix height plus padding, the
     * renderer will automatically use that as the height instead of the
     * specified one.
     *
     * @param  integer $height
     * @return AbstractRenderer
     */
    public function setHeight($height)
    {
        $this->height = (int) $height;
        return $this;
    }

    /**
     * Gets the height around the rendered image.
     *
     * @return integer
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Sets whether dimensions should be rounded down.
     *
     * @param  boolean $flag
     * @return AbstractRenderer
     */
    public function setRoundDimensions($flag)
    {
        $this->floorToClosestDimension = $flag;
        return $this;
    }

    /**
     * Gets whether dimensions should be rounded down.
     *
     * @return boolean
     */
    public function shouldRoundDimensions()
    {
        return $this->floorToClosestDimension;
    }

    /**
     * Sets background color.
     *
     * @param  Color\ColorInterface $color
     * @return AbstractRenderer
     */
    public function setBackgroundColor(Color\ColorInterface $color)
    {
        $this->backgroundColor = $color;
        return $this;
    }

    /**
     * Gets background color.
     *
     * @return Color\ColorInterface
     */
    public function getBackgroundColor()
    {
        if ($this->backgroundColor === null) {
            $this->backgroundColor = new Color\Gray(100);
        }

        return $this->backgroundColor;
    }

    /**
     * Sets foreground color.
     *
     * @param  Color\ColorInterface $color
     * @return AbstractRenderer
     */
    public function setForegroundColor(Color\ColorInterface $color)
    {
        $this->foregroundColor = $color;
        return $this;
    }

    /**
     * Gets foreground color.
     *
     * @return Color\ColorInterface
     */
    public function getForegroundColor()
    {
        if ($this->foregroundColor === null) {
            $this->foregroundColor = new Color\Gray(0);
        }

        return $this->foregroundColor;
    }

    /**
     * Adds a decorator to the renderer.
     *
     * @param  DecoratorInterface $decorator
     * @return AbstractRenderer
     */
    public function addDecorator(DecoratorInterface $decorator)
    {
        $this->decorators[] = $decorator;
        return $this;
    }

    /**
     * render(): defined by RendererInterface.
     *
     * @see    RendererInterface::render()
     * @param  QrCode $qrCode
     * @return string
     */
    public function render(QrCode $qrCode)
    {
        $input        = $qrCode->getMatrix();
        $inputWidth   = $input->getWidth();
        $inputHeight  = $input->getHeight();
        $qrWidth      = $inputWidth + ($this->getMargin() << 1);
        $qrHeight     = $inputHeight + ($this->getMargin() << 1);
        $outputWidth  = max($this->getWidth(), $qrWidth);
        $outputHeight = max($this->getHeight(), $qrHeight);
        $multiple     = (int) min($outputWidth / $qrWidth, $outputHeight / $qrHeight);

        if ($this->shouldRoundDimensions()) {
            $outputWidth  -= $outputWidth % $multiple;
            $outputHeight -= $outputHeight % $multiple;
        }

        // Padding includes both the quiet zone and the extra white pixels to
        // accommodate the requested dimensions. For example, if input is 25x25
        // the QR will be 33x33 including the quiet zone. If the requested size
        // is 200x160, the multiple will be 4, for a QR of 132x132. These will
        // handle all the padding from 100x100 (the actual QR) up to 200x160.
        $leftPadding = (int) (($outputWidth - ($inputWidth * $multiple)) / 2);
        $topPadding  = (int) (($outputHeight - ($inputHeight * $multiple)) / 2);

        // Store calculated parameters
        $this->finalWidth  = $outputWidth;
        $this->finalHeight = $outputHeight;
        $this->blockSize   = $multiple;

        $this->init();
        $this->addColor('background', $this->getBackgroundColor());
        $this->addColor('foreground', $this->getForegroundColor());
        $this->drawBackground('background');

        foreach ($this->decorators as $decorator) {
            $decorator->preProcess(
                $qrCode,
                $this,
                $outputWidth,
                $outputHeight,
                $leftPadding,
                $topPadding,
                $multiple
            );
        }

        for ($inputY = 0, $outputY = $topPadding; $inputY < $inputHeight; $inputY++, $outputY += $multiple) {
            for ($inputX = 0, $outputX = $leftPadding; $inputX < $inputWidth; $inputX++, $outputX += $multiple) {
                if ($input->get($inputX, $inputY) === 1) {
                    $this->drawBlock($outputX, $outputY, 'foreground');
                }
            }
        }

        foreach ($this->decorators as $decorator) {
            $decorator->postProcess(
                $qrCode,
                $this,
                $outputWidth,
                $outputHeight,
                $leftPadding,
                $topPadding,
                $multiple
            );
        }

        return $this->getByteStream();
    }
}