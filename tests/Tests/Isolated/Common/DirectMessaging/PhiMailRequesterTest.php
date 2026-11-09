<?php

/**
 * Isolated tests for reading who sends a Direct message from the session.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\DirectMessaging;

use OpenEMR\Common\DirectMessaging\PhiMailRequester;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

final class PhiMailRequesterTest extends TestCase
{
    /**
     * @return array<string, array{mixed, ?int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function userIdProvider(): array
    {
        return [
            'int' => [3, 3],
            'numeric string' => ['3', 3],
            'non-numeric string' => ['three', null],
            'missing' => [null, null],
        ];
    }

    #[DataProvider('userIdProvider')]
    public function testReadsTheSignedInUser(mixed $userId, ?int $expected): void
    {
        $session = new Session(new MockArraySessionStorage());
        $session->set('authUser', 'clinician');
        $session->set('authProvider', 'Default');
        $session->set('authUserID', $userId);

        $requester = PhiMailRequester::fromSession($session);

        self::assertSame(['clinician', 'Default', $expected], [$requester->username, $requester->group, $requester->userId]);
    }

    public function testReadsAnEmptySession(): void
    {
        $requester = PhiMailRequester::fromSession(new Session(new MockArraySessionStorage()));

        self::assertSame([null, null, null], [$requester->username, $requester->group, $requester->userId]);
    }

    public function testActsOnBehalfOfAnotherUserInTheSameGroup(): void
    {
        $requester = (new PhiMailRequester('clinician', 'Default', 3))->onBehalfOf('portal-user', 9);

        self::assertSame(['portal-user', 'Default', 9], [$requester->username, $requester->group, $requester->userId]);
    }
}
