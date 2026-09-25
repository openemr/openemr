<?php

/**
 * Isolated tests for RoutesExtensionListener.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestControllers\Subscriber;

use OpenEMR\RestControllers\Subscriber\RoutesExtensionListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelEvents;

class RoutesExtensionListenerTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $events = RoutesExtensionListener::getSubscribedEvents();
        $this->assertArrayHasKey(KernelEvents::REQUEST, $events);
        $this->assertSame('onKernelRequest', $events[KernelEvents::REQUEST][0][0]);
        $this->assertSame(40, $events[KernelEvents::REQUEST][0][1]);
    }
}
