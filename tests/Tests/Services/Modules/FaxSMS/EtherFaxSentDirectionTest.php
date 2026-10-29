<?php

/**
 * Sent etherFax faxes are stored as outbound and left out of the queue count.
 *
 * insertSentFaxQueue() left the direction column out, so the default
 * 'inbound' was stored for every sent fax, and fetchQueueCount() (the
 * Services badge fallback) counted sent faxes as faxes waiting to be read.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Modules\FaxSMS;

use Composer\Autoload\ClassLoader;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Modules\FaxSMS\Controller\EtherFaxActions;
use OpenEMR\Services\Utils\SQLUpgradeService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

class EtherFaxSentDirectionTest extends TestCase
{
    /** Marks the fixture rows (account), so cleanup removes only those. */
    private const ACCOUNT = 'e2e-test-sent-direction';

    private const MODULE_DIR = '/interface/modules/custom_modules/oe-module-faxsms';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $loaders = ClassLoader::getRegisteredLoaders();
        $loader = reset($loaders);
        if (!$loader instanceof ClassLoader) {
            self::fail('Composer ClassLoader not available to register module autoload prefix.');
        }
        $loader->addPsr4('OpenEMR\\Modules\\FaxSMS\\', self::root() . self::MODULE_DIR . '/src/');

        // The module's tables exist only where it is installed; table.sql is
        // what installing runs, and every statement in it is guarded.
        if (!QueryUtils::existsTable('oe_faxsms_queue')) {
            $install = new SQLUpgradeService();
            $install->setRenderOutputToScreen(false);
            $install->setThrowExceptionOnError(true);
            $install->upgradeFromSqlFile('table.sql', self::root() . self::MODULE_DIR);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanUp();
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
        parent::tearDown();
    }

    public function testSentFaxIsStoredAsOutbound(): void
    {
        $status = new \stdClass();
        $status->JobId = 'sent-job-1';
        $status->FaxResult = 0;

        $id = $this->actions()->insertSentFaxQueue($status, '5551234567', '5557654321', '', 'letter.pdf');

        $row = QueryUtils::querySingleRow('SELECT `direction`, `account` FROM `oe_faxsms_queue` WHERE `id` = ?', [$id]);
        $this->assertIsArray($row);
        $this->assertSame(self::ACCOUNT, $row['account']);
        $this->assertSame('outbound', $row['direction']);
    }

    public function testQueueCountLeavesOutSentFaxes(): void
    {
        $actions = $this->actions();
        $before = $actions->fetchQueueCount();

        $this->insertRow('received-live', 'inbound', 0);
        $this->insertRow('received-filed', 'inbound', 1);
        $this->insertRow('sent-live', 'outbound', 0);

        $this->assertSame($before + 1, $actions->fetchQueueCount());
    }

    public function testUpgradeMarksExistingSentFaxesOutbound(): void
    {
        // As insertSentFaxQueue() used to store them: no direction, so 'inbound'.
        $sent = $this->insertRow('old-sent', 'inbound', 0, '{"JobId":"old-sent","TransactionType":1,"SentOn":"2026-10-14T17:30:05-05:00"}');
        $received = $this->insertRow('old-received', 'inbound', 0, '{"FaxResult":0,"JobId":"old-received","ReceivedOn":"2026-10-14T17:30:05"}');
        $notJson = $this->insertRow('old-not-json', 'inbound', 0, 'not json');

        $upgrade = new SQLUpgradeService();
        $upgrade->setRenderOutputToScreen(false);
        $upgrade->setThrowExceptionOnError(true);
        $upgrade->upgradeFromSqlFile('6_0_1-to-6_0_2_upgrade.sql', self::root() . self::MODULE_DIR . '/sql');
        // Safe to run again.
        $upgrade->upgradeFromSqlFile('6_0_1-to-6_0_2_upgrade.sql', self::root() . self::MODULE_DIR . '/sql');

        $this->assertSame('outbound', $this->direction($sent));
        $this->assertSame('inbound', $this->direction($received));
        $this->assertSame('inbound', $this->direction($notJson));
    }

    private function actions(): EtherFaxActions
    {
        // The constructor authenticates against etherFax; the methods under
        // test only need the account.
        $actions = (new ReflectionClass(EtherFaxActions::class))->newInstanceWithoutConstructor();
        (new ReflectionProperty(EtherFaxActions::class, 'credentials'))->setValue($actions, ['account' => self::ACCOUNT]);
        return $actions;
    }

    private function insertRow(string $jobId, string $direction, int $deleted, ?string $json = null): int
    {
        return (int) QueryUtils::sqlInsert(
            'INSERT INTO `oe_faxsms_queue` (`account`, `job_id`, `receive_date`, `deleted`, `direction`, `details_json`) VALUES (?, ?, NOW(), ?, ?, ?)',
            [self::ACCOUNT, $jobId, $deleted, $direction, $json ?? '{"JobId":"' . $jobId . '"}']
        );
    }

    private function direction(int $id): string
    {
        $row = QueryUtils::querySingleRow('SELECT `direction` FROM `oe_faxsms_queue` WHERE `id` = ?', [$id]);
        $this->assertIsArray($row);
        $this->assertIsString($row['direction']);
        return $row['direction'];
    }

    private function cleanUp(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM `oe_faxsms_queue` WHERE `account` = ?', [self::ACCOUNT]);
    }

    private static function root(): string
    {
        return dirname(__DIR__, 5);
    }
}
