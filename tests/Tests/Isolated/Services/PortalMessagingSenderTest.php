<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Chris Dickman <chrisd@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services;

use OpenEMR\Services\PortalMessagingSender;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PortalMessagingSenderTest extends TestCase
{
    private PortalMessagingSender $sender;

    protected function setUp(): void
    {
        $this->sender = new PortalMessagingSender();
    }

    #[Test]
    public function resolvePrefersStaffIdentityOverPostedValues(): void
    {
        $result = $this->sender->resolve(
            'admin',
            'Admin Person',
            'attacker_user_id',
            'Pretend Sender',
        );

        $this->assertSame(['admin', 'Admin Person'], $result);
    }

    #[Test]
    public function resolveFallsBackToPostedValuesWhenStaffIdentityMissing(): void
    {
        $result = $this->sender->resolve(
            null,
            null,
            'portal_user_42',
            'Patient Name',
        );

        $this->assertSame(['portal_user_42', 'Patient Name'], $result);
    }

    #[Test]
    public function resolveTreatsPartialStaffIdentityAsAbsent(): void
    {
        // Either staff field being null means the request did not come through
        // the staff branch in handle_note.php; the posted values are the
        // authoritative fallback.
        $result = $this->sender->resolve(
            'admin',
            null,
            'posted_id',
            'Posted Name',
        );

        $this->assertSame(['posted_id', 'Posted Name'], $result);
    }

    #[Test]
    public function resolveReturnsEmptyStringsWhenNothingProvided(): void
    {
        // addPnote() / sendMail() declare string parameters, so the resolver
        // never hands a null downstream.
        $result = $this->sender->resolve(null, null, null, null);

        $this->assertSame(['', ''], $result);
    }

    /**
     * @return array<string, array{?string, ?string, ?string, ?string, array{string, string}}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function resolveScenarios(): array
    {
        return [
            'staff both set, posted ignored' => ['admin', 'Admin Person', 'evil', 'Evil', ['admin', 'Admin Person']],
            'staff id only — posted wins'    => ['admin', null, 'p_id', 'p_name', ['p_id', 'p_name']],
            'staff name only — posted wins'  => [null, 'Admin Person', 'p_id', 'p_name', ['p_id', 'p_name']],
            'both null — posted strings'     => [null, null, 'p_id', 'p_name', ['p_id', 'p_name']],
            'both null — posted nulls'       => [null, null, null, null, ['', '']],
            'staff present, posted null'     => ['admin', 'Admin Person', null, null, ['admin', 'Admin Person']],
        ];
    }

    /**
     * @param array{string, string} $expected
     */
    #[Test]
    #[DataProvider('resolveScenarios')]
    public function resolveScenarioTable(
        ?string $staffSenderId,
        ?string $staffSenderName,
        ?string $postedSenderId,
        ?string $postedSenderName,
        array $expected,
    ): void {
        $this->assertSame(
            $expected,
            $this->sender->resolve($staffSenderId, $staffSenderName, $postedSenderId, $postedSenderName),
        );
    }
}
