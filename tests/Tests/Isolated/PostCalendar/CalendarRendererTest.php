<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PostCalendar;

use OpenEMR\PostCalendar\CalendarRenderer;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

#[Group('isolated')]
#[Group('postcalendar')]
final class CalendarRendererTest extends TestCase
{
    /**
     * @param  array<string, string> $templates
     */
    private function buildRenderer(array $templates = []): CalendarRenderer
    {
        return new CalendarRenderer(new Environment(new ArrayLoader($templates)));
    }

    public function testAssignThenGetVarRoundTripsValue(): void
    {
        $renderer = $this->buildRenderer();
        $renderer->assign('foo', 42);
        self::assertSame(42, $renderer->getVar('foo'));
    }

    public function testGetVarReturnsNullForUnknownKey(): void
    {
        self::assertNull($this->buildRenderer()->getVar('missing'));
    }

    public function testAssignAcceptsAssociativeArray(): void
    {
        $renderer = $this->buildRenderer();
        $renderer->assign(['a' => 1, 'b' => 'two', 'c' => [3]]);

        self::assertSame(1, $renderer->getVar('a'));
        self::assertSame('two', $renderer->getVar('b'));
        self::assertSame([3], $renderer->getVar('c'));
    }

    public function testAssignIgnoresEmptyStringKey(): void
    {
        $renderer = $this->buildRenderer();
        $renderer->assign('', 'discarded');
        self::assertNull($renderer->getVar(''));
    }

    public function testAssignArrayFormSkipsEmptyStringKey(): void
    {
        $renderer = $this->buildRenderer();
        $renderer->assign(['' => 'discarded', 'kept' => 'value']);

        self::assertNull($renderer->getVar(''));
        self::assertSame('value', $renderer->getVar('kept'));
    }

    public function testAssignByRefMirrorsAssign(): void
    {
        $renderer = $this->buildRenderer();
        $value = 'initial';
        $renderer->assign_by_ref('shared', $value);

        // The wrapper copies the value at call-time; mutating $value after
        // assignment doesn't affect what the renderer holds, because Twig
        // copies values when rendering anyway.
        $value = 'mutated';
        self::assertSame('initial', $renderer->getVar('shared'));
    }

    public function testAssignOverwritesPreviousValueForSameKey(): void
    {
        $renderer = $this->buildRenderer();
        $renderer->assign('k', 'first');
        $renderer->assign('k', 'second');
        self::assertSame('second', $renderer->getVar('k'));
    }

    public function testRenderInterpolatesAssignedVariablesIntoTemplate(): void
    {
        $renderer = $this->buildRenderer([
            'greeting.twig' => 'Hello, {{ name }}!',
        ]);
        $renderer->assign('name', 'World');

        self::assertSame('Hello, World!', $renderer->render('greeting.twig'));
    }

    public function testRenderUsesNamedTemplateNotMostRecentlyAssigned(): void
    {
        $renderer = $this->buildRenderer([
            'a.twig' => 'A says {{ msg }}',
            'b.twig' => 'B says {{ msg }}',
        ]);
        $renderer->assign('msg', 'hi');

        self::assertSame('A says hi', $renderer->render('a.twig'));
        self::assertSame('B says hi', $renderer->render('b.twig'));
    }

    public function testRenderReceivesAccumulatedAssignmentsAcrossCalls(): void
    {
        $renderer = $this->buildRenderer([
            't.twig' => '{{ a }}/{{ b }}/{{ c }}',
        ]);
        $renderer->assign('a', '1');
        $renderer->assign(['b' => '2', 'c' => '3']);

        self::assertSame('1/2/3', $renderer->render('t.twig'));
    }
}
