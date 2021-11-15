<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Renderer\Text;

use BaconQrCode\Encoder\QrCode;

/**
 * Html renderer.
 */
class Html extends Plain
{
    /**
     * HTML CSS class attribute value.
     *
     * @var string
     */
    protected $class = '';

    /**
     * HTML CSS style definition for the code element.
     *
     * @var string
     */
    protected $style = 'font-family: monospace; line-height: 0.65em; letter-spacing: -1px';

    /**
     * Set CSS class name.
     *
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * Get CSS class name.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set CSS style value.
     *
     * @param string $style
     */
    public function setStyle($style)
    {
        $this->style = $style;
    }

    /**
     * Get CSS style value.
     *
     * @return string
     */
    public function getStyle()
    {
        return $this->style;
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
        $textCode = parent::render($qrCode);

        $result = '<pre'
                . ' style="' . htmlspecialchars($this->style, ENT_QUOTES, 'utf-8') . '"'
                . ' class="' . htmlspecialchars($this->class, ENT_QUOTES, 'utf-8') . '"'
                . '>' . $textCode . '</pre>';

        return $result;
    }
}
