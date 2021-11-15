<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use BaconQrCode\Exception;
use BaconQrCode\Renderer\RendererInterface;

/**
 * QR code writer.
 */
class Writer
{
    /**
     * Renderer instance.
     *
     * @var RendererInterface
     */
    protected $renderer;

    /**
     * Creates a new writer with a specific renderer.
     *
     * @param RendererInterface $renderer
     */
    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Sets the renderer used to create a byte stream.
     *
     * @param  RendererInterface $renderer
     * @return Writer
     */
    public function setRenderer(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
        return $this;
    }

    /**
     * Gets the renderer used to create a byte stream.
     *
     * @return RendererInterface
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * Writes QR code and returns it as string.
     *
     * Content is a string which *should* be encoded in UTF-8, in case there are
     * non ASCII-characters present.
     *
     * @param  string  $content
     * @param  string  $encoding
     * @param  integer $ecLevel
     * @return string
     * @throws Exception\InvalidArgumentException
     */
    public function writeString(
        $content,
        $encoding = Encoder::DEFAULT_BYTE_MODE_ECODING,
        $ecLevel = ErrorCorrectionLevel::L
    ) {
        if (strlen($content) === 0) {
            throw new Exception\InvalidArgumentException('Found empty contents');
        }

        $qrCode = Encoder::encode($content, new ErrorCorrectionLevel($ecLevel), $encoding);

        return $this->getRenderer()->render($qrCode);
    }

    /**
     * Writes QR code to a file.
     *
     * @see    Writer::writeString()
     * @param  string  $content
     * @param  string  $filename
     * @param  string  $encoding
     * @param  integer $ecLevel
     * @return void
     */
    public function writeFile(
        $content,
        $filename,
        $encoding = Encoder::DEFAULT_BYTE_MODE_ECODING,
        $ecLevel = ErrorCorrectionLevel::L
    ) {
        file_put_contents($filename, $this->writeString($content, $encoding, $ecLevel));
    }
}
