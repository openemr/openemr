<?php

/**
 * Isolated test that GetServiceOtherCounts() leaves the user's selected
 * Fax/SMS module in the session alone.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\FaxSMS;

use Composer\Autoload\ClassLoader;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class ServiceOtherCountsSessionTest extends TestCase
{
    /**
     * The badge poll calls GetServiceOtherCounts() on every open window, so a
     * session write there replaces the module the user picked in the Fax/SMS
     * screens. An unknown vendor key makes client construction throw, which
     * stops the poll short of the real clients (their constructors exit on
     * the module ACL check) while still running everything before it.
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testDoesNotChangeSelectedModuleType(): void
    {
        $loaders = ClassLoader::getRegisteredLoaders();
        $loader = reset($loaders);
        self::assertInstanceOf(ClassLoader::class, $loader);
        $loader->addPsr4(
            'OpenEMR\\Modules\\FaxSMS\\',
            dirname(__DIR__, 5) . '/interface/modules/custom_modules/oe-module-faxsms/src/'
        );
        require_once __DIR__ . '/../../../../../library/translation.inc.php';
        require_once __DIR__ . '/../../../../../library/dated_reminder_functions.php';

        $session = new Session(new MockArraySessionStorage());
        $session->set('oefax_current_module_type', 'fax');
        SessionWrapperFactory::getInstance()->setActiveSession($session);

        $globals = OEGlobalsBag::getInstance();
        $globals->set('disable_translation', true);
        $globals->set('oefax_enable_fax', 0);
        $globals->set('oefax_enable_sms', 'not-a-vendor');

        try {
            GetServiceOtherCounts();
            self::fail('Expected the unknown SMS vendor to be rejected.');
        } catch (RuntimeException) {
        }

        self::assertSame('fax', $session->get('oefax_current_module_type'));
    }
}
