<?php

/**
 * Tests for linked audit checksums.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Logging\Audit;

use OpenEMR\Common\Logging\Audit\AuditChain;
use OpenEMR\Common\Logging\Audit\AuditChainChecker;
use OpenEMR\Common\Logging\Audit\AuditChainFailure;
use OpenEMR\Common\Logging\Audit\AuditChainKey;
use PHPUnit\Framework\TestCase;

class AuditChainCheckerTest extends TestCase
{
    private const string KEY = 'audit-chain-test-key';

    public function testAnIntactChainHasNoFindings(): void
    {
        $rows = self::linked(['10' => 'aaa', '11' => 'bbb', '12' => 'ccc']);
        $checker = new AuditChainChecker(AuditChain::genesis(), self::KEY);
        foreach ($rows as $row) {
            $checker->add($row['id'], $row['checksum'], $row['chain']);
        }

        self::assertSame([], $checker->failures());
    }

    public function testAMissingMiddleRowBreaksTheNextLink(): void
    {
        $rows = self::linked(['10' => 'aaa', '11' => 'bbb', '12' => 'ccc']);
        unset($rows[1]);

        $checker = new AuditChainChecker(AuditChain::genesis(), self::KEY);
        foreach ($rows as $row) {
            $checker->add($row['id'], $row['checksum'], $row['chain']);
        }

        $failures = $checker->failures();
        self::assertCount(1, $failures);
        self::assertSame(AuditChainFailure::Break, $failures[0]->kind);
        self::assertSame('12', $failures[0]->id);
    }

    public function testARewrittenChecksumBreaksThatRow(): void
    {
        $rows = self::linked(['10' => 'aaa', '11' => 'bbb']);
        $rewritten = $rows[1];
        $rewritten['checksum'] = hash('sha3-512', 'tampered');
        $rows[1] = $rewritten;

        $checker = new AuditChainChecker(AuditChain::genesis(), self::KEY);
        foreach ($rows as $row) {
            $checker->add($row['id'], $row['checksum'], $row['chain']);
        }

        $failures = $checker->failures();
        self::assertCount(1, $failures);
        self::assertSame('11', $failures[0]->id);
    }

    public function testADifferentKeyDoesNotValidateTheChain(): void
    {
        $rows = self::linked(['10' => 'aaa']);
        $checker = new AuditChainChecker(AuditChain::genesis(), 'other-key');
        foreach ($rows as $row) {
            $checker->add($row['id'], $row['checksum'], $row['chain']);
        }

        self::assertSame(AuditChainFailure::Break, $checker->failures()[0]->kind);
    }

    public function testTheAnchorNoticesADeletedTail(): void
    {
        self::assertFalse(AuditChainChecker::anchorDeleted('0', AuditChain::genesis(), null));
        self::assertTrue(AuditChainChecker::anchorDeleted('15', 'abc', null));
        self::assertFalse(AuditChainChecker::anchorDeleted('15', 'abc', 'abc'));
        self::assertTrue(AuditChainChecker::anchorDeleted('15', 'abc', 'abd'));
    }

    public function testTheSiteKeyIsCreatedOnce(): void
    {
        $directory = sys_get_temp_dir() . '/oemr-audit-chain-' . bin2hex(random_bytes(4));
        self::assertTrue(mkdir($directory));
        try {
            $first = AuditChainKey::fromSiteDirectory($directory);
            $second = AuditChainKey::fromSiteDirectory($directory);
            self::assertSame($first->value(), $second->value());
            self::assertSame(64, strlen($first->value()));
            self::assertTrue(ctype_xdigit($first->value()));
        } finally {
            $keyFile = $directory . '/documents/logs_and_misc/audit-chain.key';
            if (is_file($keyFile)) {
                unlink($keyFile);
            }
            @rmdir($directory . '/documents/logs_and_misc');
            @rmdir($directory . '/documents');
            rmdir($directory);
        }
    }

    /**
     * @param array<int|string, string> $checksums
     * @return list<array{id: string, checksum: string, chain: string}>
     */
    private static function linked(array $checksums): array
    {
        $previous = AuditChain::genesis();
        $rows = [];
        foreach ($checksums as $id => $checksum) {
            $logId = (string) $id;
            $chain = AuditChain::link($previous, $logId, $checksum, self::KEY);
            $rows[] = ['id' => $logId, 'checksum' => $checksum, 'chain' => $chain];
            $previous = $chain;
        }

        return $rows;
    }
}
