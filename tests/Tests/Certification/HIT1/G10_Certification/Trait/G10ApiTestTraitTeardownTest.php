<?php

/**
 * The G10 tests set US Core's max supported profile version to 3.1.1 for their run, and
 * their teardown puts back the value the site had.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Certification\HIT1\G10_Certification\Trait;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\Globals\GlobalConnectorsEnum;
use OpenEMR\Tests\Certification\HIT1\G10_Certification\SinglePatient311APITest;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionProperty;

class G10ApiTestTraitTeardownTest extends TestCase
{
    private string $original;

    protected function setUp(): void
    {
        $this->original = $this->profileVersion();
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException('UPDATE `globals` SET `gl_value` = ? WHERE `gl_name` = ?', [$this->original, self::settingName()]);
    }

    public function testTeardownRestoresTheProfileVersionTheSetupChanged(): void
    {
        // A value other than the 3.1.1 the setup writes, so a teardown that restores nothing shows.
        QueryUtils::sqlStatementThrowException('UPDATE `globals` SET `gl_value` = ? WHERE `gl_name` = ?', ['8.0.0', self::settingName()]);
        // A G10 test class, without the Inferno session setupG10Test() starts: it saves the site's value
        // as setupG10Test() does, then sets the profile version for the run.
        (new ReflectionProperty(SinglePatient311APITest::class, 'previousProfileValue'))->setValue(null, $this->profileVersion());
        (new ReflectionMethod(SinglePatient311APITest::class, 'setupG10ProfileGlobals'))->invoke(null);
        $this->assertSame('3.1.1', $this->profileVersion());

        SinglePatient311APITest::teardownG10Test();

        $this->assertSame('8.0.0', $this->profileVersion());
    }

    private function profileVersion(): string
    {
        $value = QueryUtils::fetchSingleValue('SELECT `gl_value` FROM `globals` WHERE `gl_name` = ?', 'gl_value', [self::settingName()]);
        $this->assertIsString($value, 'the profile version setting exists');
        return $value;
    }

    private static function settingName(): string
    {
        return GlobalConnectorsEnum::FHIR_US_CORE_MAX_SUPPORTED_PROFILE_VERSION->value;
    }
}
