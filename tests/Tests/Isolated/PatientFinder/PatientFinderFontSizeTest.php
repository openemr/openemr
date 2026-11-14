<?php

/**
 * Isolated PatientFinderFontSize Test
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PatientFinder;

use OpenEMR\Services\PatientFinder\PatientFinderFontSize;
use PHPUnit\Framework\TestCase;

class PatientFinderFontSizeTest extends TestCase
{
    public function testStoredSizeIsRead(): void
    {
        $this->assertSame(PatientFinderFontSize::Large, PatientFinderFontSize::fromSetting('large'));
        $this->assertSame(PatientFinderFontSize::ExtraLarge, PatientFinderFontSize::fromSetting('x-large'));
    }

    public function testMissingOrUnknownSizeIsNormal(): void
    {
        $this->assertSame(PatientFinderFontSize::Normal, PatientFinderFontSize::fromSetting(null));
        $this->assertSame(PatientFinderFontSize::Normal, PatientFinderFontSize::fromSetting('huge'));
    }

    /**
     * The Font Size menu offers every size, smallest first, each with a name.
     */
    public function testMenuListsEverySizeInOrder(): void
    {
        // xl() returns its input when translation is disabled, so no database is needed.
        $GLOBALS['disable_translation'] = true;
        try {
            $menu = PatientFinderFontSize::menu();
        } finally {
            unset($GLOBALS['disable_translation']);
        }

        $this->assertSame(
            ['small' => 'Small', 'normal' => 'Normal', 'large' => 'Large', 'x-large' => 'Extra Large'],
            $menu
        );
    }
}
