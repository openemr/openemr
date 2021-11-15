<?php
/**
 * BaconQrCode
 *
 * @link      http://github.com/Bacon/BaconQrCode For the canonical source repository
 * @copyright 2013 Ben 'DASPRiD' Scholzen
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconQrCode\Encoder;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Renderer\Text\Html;
use BaconQrCode\Writer;
use PHPUnit_Framework_TestCase as TestCase;

class HtmlTest extends TestCase
{
    /**
     * @var Html
     */
    protected $renderer;

    /**
     * @var Writer
     */
    protected $writer;

    public function setUp()
    {
        $this->renderer = new Html();
        $this->writer = new Writer($this->renderer);
    }

    public function testBasicRender()
    {
        $content = 'foobar';
        $expected =
            '<pre style="font-family: monospace; line-height: 0.65em; letter-spacing: -1px" class="">' .
            "                       \n" .
            " ███████ █████ ███████ \n" .
            " █     █  █ █  █     █ \n" .
            " █ ███ █  ██   █ ███ █ \n" .
            " █ ███ █  ███  █ ███ █ \n" .
            " █ ███ █   █ █ █ ███ █ \n" .
            " █     █    ██ █     █ \n" .
            " ███████ █ █ █ ███████ \n" .
            "         █████         \n" .
            " ██ ██ █  ██ █ █     █ \n" .
            "    ██    ██ █ █ ██    \n" .
            "  ████████ █  ██ █  ██ \n" .
            "           ██      █ █ \n" .
            "  ██  ███  █   █  █  █ \n" .
            "         █ ███    █ █  \n" .
            " ███████  ██ ██████    \n" .
            " █     █   ████   ██   \n" .
            " █ ███ █ ██ ██ ██ █ ██ \n" .
            " █ ███ █ ██ ██  █ ██   \n" .
            " █ ███ █   █   █ ██ ██ \n" .
            " █     █ ███  ███ ████ \n" .
            " ███████ ████   ██     \n" .
            "                       \n" .
            '</pre>'
        ;

        $qrCode = Encoder::encode(
            $content,
            new ErrorCorrectionLevel(ErrorCorrectionLevel::L),
            Encoder::DEFAULT_BYTE_MODE_ECODING
        );
        $this->assertEquals($expected, $this->renderer->render($qrCode));
    }

    public function testSetStyle()
    {
        $content = 'foobar';
        $qrCode = Encoder::encode(
            $content,
            new ErrorCorrectionLevel(ErrorCorrectionLevel::L),
            Encoder::DEFAULT_BYTE_MODE_ECODING
        );
        $this->renderer->setStyle('bar');
        $this->assertEquals('bar', $this->renderer->getStyle());
        $this->assertStringMatchesFormat('%astyle="bar"%a', $this->renderer->render($qrCode));
    }

    public function testSetClass()
    {
        $content = 'foobar';
        $qrCode = Encoder::encode(
            $content,
            new ErrorCorrectionLevel(ErrorCorrectionLevel::L),
            Encoder::DEFAULT_BYTE_MODE_ECODING
        );
        $this->renderer->setClass('bar');
        $this->assertEquals('bar', $this->renderer->getClass());
        $this->assertStringMatchesFormat('%aclass="bar"%a', $this->renderer->render($qrCode));
    }
}
