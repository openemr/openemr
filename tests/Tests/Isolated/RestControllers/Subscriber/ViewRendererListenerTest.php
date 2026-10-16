<?php

/**
 * Isolated tests for REST controller response rendering.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestControllers\Subscriber;

use OpenEMR\RestControllers\Subscriber\ViewRendererListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ViewRendererListenerTest extends TestCase
{
    protected function tearDown(): void
    {
        http_response_code(200);
    }

    public function testJsonResponsePreservesControllerStatusCode(): void
    {
        foreach ([400, 404] as $statusCode) {
            http_response_code($statusCode);
            $event = new ViewEvent(
                $this->createMock(HttpKernelInterface::class),
                Request::create('/api/drug/not-found'),
                HttpKernelInterface::MAIN_REQUEST,
                ['data' => []]
            );

            (new ViewRendererListener())->onViewEvent($event);

            self::assertSame($statusCode, $event->getResponse()?->getStatusCode());
        }
    }

    public function testJsonResponseDefaultsToOkWhenNoStatusWasSet(): void
    {
        http_response_code(200);
        $event = new ViewEvent(
            $this->createMock(HttpKernelInterface::class),
            Request::create('/api/drug'),
            HttpKernelInterface::MAIN_REQUEST,
            ['data' => []]
        );

        (new ViewRendererListener())->onViewEvent($event);

        self::assertSame(200, $event->getResponse()?->getStatusCode());
    }
}
