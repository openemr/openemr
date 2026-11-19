<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Http;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Http\HttpSessionFactory;
use OpenEMR\Common\Session\Storage\Handler\StoreFailureStrictSessionHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\Proxy\SessionHandlerProxy;

class HttpSessionFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
    }

    public function testNativeSessionsReportStoreFailures(): void
    {
        $factory = new HttpSessionFactory(
            HttpRestRequest::createFromGlobals(),
            '',
            HttpSessionFactory::SESSION_TYPE_CORE,
            true,
        );
        $factory->createSession();

        $storage = $factory->getLastCreatedStorage();
        self::assertInstanceOf(NativeSessionStorage::class, $storage);

        $proxy = $storage->getSaveHandler();
        self::assertInstanceOf(SessionHandlerProxy::class, $proxy);

        self::assertInstanceOf(StoreFailureStrictSessionHandler::class, $proxy->getHandler());
    }
}
