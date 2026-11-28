<?php

/**
 * Isolated FlowBoardChangeEndpoint Test
 *
 * Reading the Flow Board dropdown's POST and answering with JSON.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    David Eschelbacher <psoas@tampabay.rr.com>
 * @copyright Copyright (c) 2026 David Eschelbacher <psoas@tampabay.rr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Appointment\FlowBoard;

use DateTimeImmutable;
use Lcobucci\Clock\FrozenClock;
use OpenEMR\Appointment\FlowBoard\FlowBoardChange;
use OpenEMR\Appointment\FlowBoard\FlowBoardChangeEndpoint;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class FlowBoardChangeEndpointTest extends TestCase
{
    /**
     * @param array<string, string> $body
     * @return array{int, mixed}
     */
    private static function respond(FakeFlowBoardStore $store, array $body, string $method = 'POST'): array
    {
        $endpoint = new FlowBoardChangeEndpoint(
            new FlowBoardChange($store, true, new FrozenClock(new DateTimeImmutable('2026-11-28 12:00:00')))
        );
        $response = $endpoint->respond(Request::create('/', $method, $body), 'admin');
        $content = $response->getContent();
        return [$response->getStatusCode(), json_decode(is_string($content) ? $content : '', true)];
    }

    public function testASavedChange(): void
    {
        $store = new FakeFlowBoardStore(FakeFlowBoardStore::appointmentOn('2026-11-30'));

        $this->assertSame([200, ['saved' => true]], self::respond($store, ['eid' => '768', 'status' => '@', 'room' => '2']));
        $this->assertSame([['eventId' => 768, 'user' => 'admin', 'status' => '@', 'room' => '2', 'encounter' => 0]], $store->saved);
    }

    /**
     * @return array<string, array{array<string, string>, string, int, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function failureProvider(): array
    {
        return [
            'not a POST' => [['eid' => '768', 'status' => '@'], 'GET', 405, 'method'],
            'no event' => [['status' => '@'], 'POST', 400, 'request'],
            'event not a number' => [['eid' => '7x', 'status' => '@'], 'POST', 400, 'request'],
            'nothing to change' => [['eid' => '768'], 'POST', 400, 'request'],
            'empty status' => [['eid' => '768', 'status' => ''], 'POST', 400, 'request'],
            'no such appointment' => [['eid' => '5', 'status' => '@'], 'POST', 404, 'not_found'],
            'unknown status' => [['eid' => '768', 'status' => 'zz'], 'POST', 400, 'status'],
            'unknown room' => [['eid' => '768', 'room' => '9'], 'POST', 400, 'room'],
        ];
    }

    /**
     * @param array<string, string> $body
     */
    #[DataProvider('failureProvider')]
    public function testFailures(array $body, string $method, int $status, string $reason): void
    {
        $store = new FakeFlowBoardStore(FakeFlowBoardStore::appointmentOn('2026-11-30'));

        $this->assertSame([$status, ['saved' => false, 'reason' => $reason]], self::respond($store, $body, $method));
        $this->assertSame([], $store->saved);
    }

    public function testARepeatingAppointmentIsAConflict(): void
    {
        $store = new FakeFlowBoardStore(FakeFlowBoardStore::appointmentOn('2026-11-30', repeating: true));

        $this->assertSame([409, ['saved' => false, 'reason' => 'repeating']], self::respond($store, ['eid' => '768', 'status' => '@']));
    }
}
