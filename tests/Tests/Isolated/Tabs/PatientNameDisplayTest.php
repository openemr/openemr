<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Tabs;

use OpenEMR\Tabs\PatientNameDisplay;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class PatientNameDisplayTest extends TestCase
{
    /**
     * @return array<string, array{string, PatientNameDisplay}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function settingProvider(): array
    {
        return [
            'button' => ['btn', PatientNameDisplay::Button],
            'text' => ['text', PatientNameDisplay::Text],
            'large text' => ['text-large', PatientNameDisplay::LargeText],
            'empty falls back to text' => ['', PatientNameDisplay::Text],
            'unknown falls back to text' => ['huge', PatientNameDisplay::Text],
        ];
    }

    #[DataProvider('settingProvider')]
    public function testFromSetting(string $setting, PatientNameDisplay $expected): void
    {
        $this->assertSame($expected, PatientNameDisplay::fromSetting($setting));
    }

    public function testButton(): void
    {
        $display = PatientNameDisplay::Button;
        $this->assertSame('div', $display->wrapperElement());
        $this->assertSame('btn-group btn-group-sm mb-2', $display->wrapperClass());
        $this->assertSame('btn btn-sm btn-secondary', $display->nameClass());
        $this->assertSame('btn btn-sm btn-secondary', $display->linkClass());
        $this->assertSame('text-muted', $display->iconClass());
        $this->assertSame('span', $display->pubpidElement());
        $this->assertSame('', $display->closeElement());
        $this->assertTrue($display->isButton());
    }

    public function testText(): void
    {
        $display = PatientNameDisplay::Text;
        $this->assertSame('div', $display->wrapperElement());
        $this->assertSame('d-inline', $display->wrapperClass());
        $this->assertSame('', $display->nameClass());
        $this->assertSame('text-muted', $display->linkClass());
        $this->assertSame('text-muted', $display->iconClass());
        $this->assertSame('span', $display->pubpidElement());
        $this->assertSame('span', $display->closeElement());
        $this->assertFalse($display->isButton());
    }

    public function testLargeText(): void
    {
        $display = PatientNameDisplay::LargeText;
        $this->assertSame('h3', $display->wrapperElement());
        $this->assertSame('d-inline', $display->wrapperClass());
        $this->assertSame('', $display->nameClass());
        $this->assertSame('text-muted', $display->linkClass());
        $this->assertSame('text-muted fa-xs', $display->iconClass());
        $this->assertSame('small', $display->pubpidElement());
        $this->assertSame('small', $display->closeElement());
        $this->assertFalse($display->isButton());
    }
}
