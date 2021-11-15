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

/**
 * Decorator interface.
 */
interface DecoratorInterface
{
    /**
     * Pre-process a QR code.
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
    public function preProcess(
        QrCode $qrCode,
        RendererInterface $renderer,
        $outputWidth,
        $outputHeight,
        $leftPadding,
        $topPadding,
        $multiple
    );

    /**
     * Post-process a QR code.
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
    );
}