<?php

/**
 * Isolated tests for TemplatePageEvent
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Events\Core;

use OpenEMR\Events\Core\TemplatePageEvent;
use PHPUnit\Framework\TestCase;

class TemplatePageEventTest extends TestCase
{
    public function testConstructorStoresTemplate(): void
    {
        $event = new TemplatePageEvent('login/login.php', [], 'login/layouts/vertical_band.html.twig', ['k' => 'v']);

        $this->assertSame('login/layouts/vertical_band.html.twig', $event->getTwigTemplate());
        $this->assertSame(['k' => 'v'], $event->getTwigVariables());
    }

    public function testConstructorDefaultsToEmptyTemplate(): void
    {
        $event = new TemplatePageEvent('page');

        $this->assertSame('', $event->getTwigTemplate());
    }

    public function testSetTwigTemplateRoundTrip(): void
    {
        $event = new TemplatePageEvent('page');
        $event->setTwigTemplate('login/layouts/vertical_band.html.twig');

        $this->assertSame('login/layouts/vertical_band.html.twig', $event->getTwigTemplate());
    }

    public function testSetTwigTemplateReturnsSelfForChaining(): void
    {
        $event = new TemplatePageEvent('page');
        $this->assertSame($event, $event->setTwigTemplate('any.twig'));
    }
}
