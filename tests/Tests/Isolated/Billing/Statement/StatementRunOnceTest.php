<?php

/**
 * A statement run is done once: a refresh or resubmit of the same form is refused.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\Statement;

use OpenEMR\Billing\Statement\StatementRunOnce;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class StatementRunOnceTest extends TestCase
{
    public function testARunIdIsHonoredOnce(): void
    {
        $once = self::runOnce(new Session(new MockArraySessionStorage()));
        $first = StatementRunOnce::newId();
        $second = StatementRunOnce::newId();

        $this->assertNotSame($first, $second);
        $this->assertTrue($once->claim($first));
        $this->assertFalse($once->claim($first), 'a refresh resubmits the same run id');
        $this->assertTrue($once->claim($second), 'the next listing carries a new run id');
    }

    public function testOnlyTheLatestRunIdsAreKept(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $once = self::runOnce($session);
        $first = StatementRunOnce::newId();
        $once->claim($first);
        for ($i = 0; $i < 50; $i++) {
            $once->claim(StatementRunOnce::newId());
        }

        $done = $session->get('statement_runs_done');
        $this->assertIsArray($done);
        $this->assertCount(50, $done);
        $this->assertNotContains($first, $done);
    }

    private static function runOnce(Session $session): StatementRunOnce
    {
        return new StatementRunOnce($session, static function (array $ids) use ($session): void {
            $session->set('statement_runs_done', $ids);
        });
    }
}
