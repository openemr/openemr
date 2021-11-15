<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Renderer\Text;

use BaconQrCode\Exception;
use BaconQrCode\Encoder\QrCode;
use BaconQrCode\Renderer\RendererInterface;

/**
 * Plaintext renderer.
 */
class Plain implements RendererInterface
{
    /**
     * Margin around the QR code, also known as quiet zone.
     *
     * @var integer
     */
    protected $margin = 1;

    /**
     * Char used for full block.
     *
     * UTF-8 FULL BLOCK (U+2588)
     *
     * @var  string
     * @link http://www.fileformat.info/info/unicode/char/2588/index.htm
     */
    protected $fullBlock = "\xE2\x96\x88";

    /**
     * Char used for empty space
     *
     * @var string
     */
    protected $emptyBlock = ' ';

    /**
     * Set char used as full block (occupied space, "black").
     *
     * @param string $fullBlock
     */
    public function setFullBlock($fullBlock)
    {
        $this->fullBlock = $fullBlock;
    }

    /**
     * Get char used as full block (occupied space, "black").
     *
     * @return string
     */
    public function getFullBlock()
    {
        return $this->fullBlock;
    }

    /**
     * Set char used as empty block (empty space, "white").
     *
     * @param string $emptyBlock
     */
    public function setEmptyBlock($emptyBlock)
    {
        $this->emptyBlock = $emptyBlock;
    }

    /**
     * Get char used as empty block (empty space, "white").
     *
     * @return string
     */
    public function getEmptyBlock()
    {
        return $this->emptyBlock;
    }

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
     * render(): defined by RendererInterface.
     *
     * @see    RendererInterface::render()
     * @param  QrCode $qrCode
     * @return string
     */
    public function render(QrCode $qrCode)
    {
        $result = '';
        $matrix = $qrCode->getMatrix();
        $width  = $matrix->getWidth();

        // Top margin
        for ($x = 0; $x < $this->margin; $x++) {
            $result .= str_repeat($this->emptyBlock, $width + 2 * $this->margin)."\n";
        }

        // Body
        $array = $matrix->getArray();

        foreach ($array as $row) {
            $result .= str_repeat($this->emptyBlock, $this->margin); // left margin
            foreach ($row as $byte) {
                $result .= $byte ? $this->fullBlock : $this->emptyBlock;
            }
            $result .= str_repeat($this->emptyBlock, $this->margin); // right margin
            $result .= "\n";
        }

        // Bottom margin
        for ($x = 0; $x < $this->margin; $x++) {
            $result .= str_repeat($this->emptyBlock, $width + 2 * $this->margin)."\n";
        }

        return $result;
    }
}
