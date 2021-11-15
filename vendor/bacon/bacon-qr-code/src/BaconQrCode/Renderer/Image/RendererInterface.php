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
use BaconQrCode\Renderer\RendererInterface as GeneralRendererInterface;

/**
 * Renderer interface.
 */
interface RendererInterface extends GeneralRendererInterface
{
    /**
     * Initiates the drawing area.
     *
     * @return void
     */
    public function init();

    /**
     * Adds a color to the drawing area.
     *
     * @param  string         $id
     * @param  ColorInterface $color
     * @return void
     */
    public function addColor($id, ColorInterface $color);

    /**
     * Draws the background.
     *
     * @param  string $colorId
     * @return void
     */
    public function drawBackground($colorId);

    /**
     * Draws a block at a specified position.
     *
     * @param  integer $x
     * @param  integer $y
     * @param  string  $colorId
     * @return void
     */
    public function drawBlock($x, $y, $colorId);

    /**
     * Returns the byte stream representing the QR code.
     *
     * @return string
     */
    public function getByteStream();

}
