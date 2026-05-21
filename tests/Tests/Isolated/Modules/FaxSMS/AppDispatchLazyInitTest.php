<?php

/**
 * Regression test for FaxSMS AppDispatch typed-session-property fatal.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\FaxSMS;

use Composer\Autoload\ClassLoader;
use OpenEMR\Modules\FaxSMS\Controller\AppDispatch;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * The custom_modules/oe-module-faxsms module is not registered in the root
 * composer.json autoload map. At runtime the module manager registers its
 * PSR-4 prefix dynamically when the module is enabled in the database. The
 * isolated test suite has no database, so we register the prefix ourselves
 * before referencing any module class.
 */
final class AppDispatchLazyInitTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $loaders = ClassLoader::getRegisteredLoaders();
        $loader = reset($loaders);
        if (!$loader instanceof ClassLoader) {
            self::fail('Composer ClassLoader not available to register module autoload prefix.');
        }
        $loader->addPsr4(
            'OpenEMR\\Modules\\FaxSMS\\',
            dirname(__DIR__, 5) . '/interface/modules/custom_modules/oe-module-faxsms/src/'
        );
    }

    /**
     * Reproduces the issue #12208 call shape: a service-client constructor
     * touches getSession() before parent::__construct() has run. Prior to
     * the fix, $_session was a readonly typed property assigned only inside
     * AppDispatch::__construct(), so this access fatalled with
     * "Typed property AppDispatch::$_session must not be accessed before
     * initialization".
     */
    public function testGetSessionBeforeParentConstructorDoesNotFatal(): void
    {
        $subject = new class extends AppDispatch {
            public mixed $sessionResult = 'sentinel';

            public function __construct()
            {
                // Touch the session before parent::__construct() — this is the
                // bug path. Do not call parent::__construct() at all: it runs
                // dispatchActions() and render(), which exit().
                $this->sessionResult = $this->getSession('authUserID');
            }

            public function authenticate(): string|int|bool
            {
                return false;
            }

            public function sendFax(): string|bool
            {
                return false;
            }

            public function sendSMS(): mixed
            {
                return null;
            }

            public function sendEmail(): mixed
            {
                return null;
            }

            public function fetchReminderCount(): string|bool
            {
                return false;
            }
        };

        // Reaching this line at all proves the fatal is gone.
        self::assertNotSame('sentinel', $subject->sessionResult);
    }

    public function testGetSessionWithoutParamReturnsSessionInterface(): void
    {
        $subject = new class extends AppDispatch {
            public mixed $session;

            public function __construct()
            {
                $this->session = $this->getSession();
            }

            public function authenticate(): string|int|bool
            {
                return false;
            }

            public function sendFax(): string|bool
            {
                return false;
            }

            public function sendSMS(): mixed
            {
                return null;
            }

            public function sendEmail(): mixed
            {
                return null;
            }

            public function fetchReminderCount(): string|bool
            {
                return false;
            }
        };

        self::assertInstanceOf(SessionInterface::class, $subject->session);
    }
}
