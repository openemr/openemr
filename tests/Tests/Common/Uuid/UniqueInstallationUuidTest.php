<?php

/**
 * Database tests for the installation UUID: the loaded setting wins, then the stored one, then a new one.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Common\Uuid;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UniqueInstallationUuid;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\TestCase;

final class UniqueInstallationUuidTest extends TestCase
{
    private const SELECT = <<<'SQL'
        SELECT `gl_value` FROM `globals` WHERE `gl_name` = 'unique_installation_id'
        SQL;
    private const UUID4 = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';
    private const UPDATE = <<<'SQL'
        UPDATE `globals` SET `gl_value` = ? WHERE `gl_name` = 'unique_installation_id'
        SQL;

    private string $loadedBefore = '';
    private mixed $storedBefore;

    protected function setUp(): void
    {
        $this->loadedBefore = OEGlobalsBag::getInstance()->getString('unique_installation_id');
        $this->storedBefore = QueryUtils::fetchSingleValue(self::SELECT, 'gl_value');
    }

    protected function tearDown(): void
    {
        OEGlobalsBag::getInstance()->set('unique_installation_id', $this->loadedBefore);
        QueryUtils::sqlStatementThrowException(self::UPDATE, [$this->storedBefore]);
    }

    public function testTheLoadedSettingWins(): void
    {
        OEGlobalsBag::getInstance()->set('unique_installation_id', 'loaded-id');
        QueryUtils::sqlStatementThrowException(self::UPDATE, ['stored-id']);

        $this->assertSame('loaded-id', UniqueInstallationUuid::getUniqueInstallationUuid());
    }

    public function testTheStoredValueIsUsedWhenNoneIsLoaded(): void
    {
        OEGlobalsBag::getInstance()->set('unique_installation_id', '');
        QueryUtils::sqlStatementThrowException(self::UPDATE, ['stored-id']);

        $this->assertSame('stored-id', UniqueInstallationUuid::getUniqueInstallationUuid());
    }

    public function testANewUuidIsStoredWhenThereIsNone(): void
    {
        OEGlobalsBag::getInstance()->set('unique_installation_id', '');
        QueryUtils::sqlStatementThrowException(self::UPDATE, ['']);

        $uuid = UniqueInstallationUuid::getUniqueInstallationUuid();

        $this->assertMatchesRegularExpression(self::UUID4, $uuid);
        $this->assertSame($uuid, QueryUtils::fetchSingleValue(self::SELECT, 'gl_value'));
    }
}
