<?php

/**
 * Tests for the 277 claim status indexer, without a database.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Steve Wiig <https://github.com/Steve-Wiig>
 * @copyright Copyright (c) 2026 Steve Wiig
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\EdiHistory;

use OpenEMR\Billing\EdiHistory\ClaimStatusIndexer;
use OpenEMR\Billing\EdiHistory\ClaimStatusIndexStore;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ClaimStatusIndexerTest extends TestCase
{
    private const ISA = 'ISA*00*          *00*          *ZZ*CLEARINGHOUSE  *ZZ*SUBMITTER      '
        . '*260127*0412*^*00501*000000101*0*P*:~';

    private const PAYER = 'NM1*PR*2*EXAMPLE HEALTH PLAN*****PI*54321~TRN*1*888~DTP*050*D8*20260128~';

    private static function transaction(string $controlNumber, string $claims): string
    {
        return 'ST*277*' . $controlNumber . '*005010X214~'
            . 'BHT*0085*08*' . $controlNumber . '*20260127*0412*TH~'
            . 'HL*1**20*1~' . self::PAYER
            . 'HL*2*1*21*1~NM1*41*2*EXAMPLE CLINIC*****46*555~TRN*2*BATCH1~STC*A1:19*20260127*WQ*477~'
            . 'HL*3*2*19*1~NM1*85*2*EXAMPLE CLINIC*****XX*1999999999~TRN*1*PROVIDER1~STC*A1:19*20260127*WQ*477~'
            . $claims
            . 'SE*99*' . $controlNumber . '~';
    }

    private static function interchange(string ...$transactions): string
    {
        return self::ISA
            . 'GS*HN*CLEARINGHOUSE*SUBMITTER*20260127*0412*101*X*005010X214~'
            . implode('', $transactions)
            . 'GE*' . count($transactions) . '*101~IEA*1*000000101~';
    }

    private static function twoClaims(): string
    {
        return self::interchange(self::transaction(
            '0001',
            'HL*4*3*PT~NM1*QC*1*DOE*JANE****MI*MEMBER1~'
            . 'TRN*2*100-200~STC*A1:19*20260128*WQ*257******** Payer has acknowledged receipt.~'
            . 'STC*A2:20:PR*20260128*WQ*257~REF*1K*PAYERCLAIM99~DTP*472*RD8*20260120-20260122~'
            . 'TRN*2*100-201~STC*A7:562:85*20260128*U*220~REF*1K*SECOND~'
        ));
    }

    private function store(): InMemoryClaimStatusStore
    {
        return new InMemoryClaimStatusStore();
    }

    public function testKeepsEveryClaimOfAFileWithSourceAndNumbers(): void
    {
        $store = $this->store();
        $text = self::twoClaims();
        $this->assertTrue((new ClaimStatusIndexer($store))->indexText($text, 'ack.277'));

        $this->assertCount(1, $store->files);
        $claims = $store->files[0];
        $this->assertCount(2, $claims);

        $ack = $claims[0]['ack'];
        $this->assertSame(hash('sha256', $text), $ack['file_sha256']);
        $this->assertSame('ack.277', $ack['file_name']);
        $this->assertSame(0, $ack['block_ordinal']);
        $this->assertSame(1, $claims[1]['ack']['block_ordinal']);
        $this->assertSame('100-200', $ack['claim_reference']);
        $this->assertSame(100, $ack['pid']);
        $this->assertSame(200, $ack['encounter']);
        $this->assertSame('DOE', $ack['patient_last_name']);
        $this->assertSame('JANE', $ack['patient_first_name']);
        $this->assertSame('Payer', $ack['source_kind']);
        $this->assertSame('PR', $ack['source_entity_code']);
        $this->assertSame('EXAMPLE HEALTH PLAN', $ack['source_name']);
        $this->assertSame('PI', $ack['source_id_qualifier']);
        $this->assertSame('54321', $ack['source_id']);
        $this->assertSame('2026-01-28', $ack['source_received_date']);
        $this->assertSame('2026-01-27', $ack['interchange_date']);
        $this->assertSame('000000101', $ack['interchange_control_number']);
        $this->assertSame('0001', $ack['transaction_control_number']);
        $this->assertSame('2026-01-20', $ack['service_date_from']);
        $this->assertSame('2026-01-22', $ack['service_date_to']);

        $this->assertCount(2, $claims[0]['statuses']);
        [$received, $accepted] = $claims[0]['statuses'];
        $this->assertSame(0, $received['position']);
        $this->assertSame('A1', $received['category']);
        $this->assertSame('Payer has acknowledged receipt.', $received['status_text']);
        $this->assertSame('257', $received['amount']);
        $this->assertSame(1, $received['accepted']);
        $this->assertSame(0, $received['rejected']);
        $this->assertSame('PR', $accepted['entity_code']);

        $rejected = $claims[1]['statuses'][0];
        $this->assertSame('A7', $rejected['category']);
        $this->assertSame('85', $rejected['entity_code']);
        $this->assertSame(1, $rejected['rejected']);
        $this->assertSame(0, $rejected['accepted']);

        $this->assertSame([['qualifier' => '1K', 'ref_value' => 'PAYERCLAIM99']], $claims[0]['references']);
        $this->assertSame([['qualifier' => '1K', 'ref_value' => 'SECOND']], $claims[1]['references']);
    }

    public function testServiceLineStatusAndUnrealDateAreKeptAsTheyAre(): void
    {
        $store = $this->store();
        $text = self::interchange(self::transaction(
            '0001',
            'HL*4*3*PT~NM1*QC*1*DOE*JANE****MI*MEMBER1~'
            . 'TRN*2*100-200~STC*A3:21*TODAY*U*257~DTP*472*D8*20260120~'
            . 'SVC*HC:92014*200*0~STC*A7:454*20260128*U*200~'
        ));
        (new ClaimStatusIndexer($store))->indexText($text, 'lines.277');

        [$claim, $line] = $store->files[0][0]['statuses'];
        $this->assertNull($claim['effective_date']);
        $this->assertSame('', $claim['service_line']);
        $this->assertSame('HC:92014', $line['service_line']);
        $this->assertSame('200', $line['amount']);
        $this->assertSame('2026-01-20', $store->files[0][0]['ack']['service_date_from']);
    }

    public function testReferenceThatIsNotPidEncounterLeavesThemZero(): void
    {
        $store = $this->store();
        $text = self::interchange(self::transaction(
            '0001',
            'HL*4*3*PT~NM1*QC*1*DOE*JANE****MI*MEMBER1~'
            . 'TRN*2*ABC-123~STC*A1:19*20260128*WQ*257~'
        ));
        (new ClaimStatusIndexer($store))->indexText($text, 'ref.277');

        $ack = $store->files[0][0]['ack'];
        $this->assertSame('ABC-123', $ack['claim_reference']);
        $this->assertSame(0, $ack['pid']);
        $this->assertSame(0, $ack['encounter']);
    }

    public function testSameContentUnderAnotherNameIsNotIndexedTwice(): void
    {
        $store = $this->store();
        $indexer = new ClaimStatusIndexer($store);
        $text = self::twoClaims();
        $this->assertTrue($indexer->indexText($text, 'ack.277'));
        $this->assertFalse($indexer->indexText($text, 'copy.277'));
        $this->assertCount(1, $store->files);
    }

    public function testTextWithNo277ClaimKeepsNothing(): void
    {
        $store = $this->store();
        $indexer = new ClaimStatusIndexer($store);
        $this->assertFalse($indexer->indexText('This is a letter, not an X12 file.', 'note.txt'));
        $this->assertSame([], $store->files);
    }

    public function testTwoInterchangesKeepFileOrder(): void
    {
        $store = $this->store();
        $first = self::twoClaims();
        $second = strtr($first, ['*' => '|', ':' => '>', '~' => '!']);
        (new ClaimStatusIndexer($store))->indexText($first . "\n" . $second, 'both.277');

        $claims = $store->files[0];
        $this->assertSame([0, 1, 2, 3], array_column(array_column($claims, 'ack'), 'block_ordinal'));
        $this->assertSame('100-200', $claims[2]['ack']['claim_reference']);
    }

    public function testDirectorySweepIndexesNewSkipsKnownAndContinues(): void
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'edih_idx_' . getmypid();
        mkdir($dir);
        mkdir($dir . DIRECTORY_SEPARATOR . 'folder.277');
        try {
            file_put_contents($dir . '/a.277', self::twoClaims());
            file_put_contents($dir . '/dup.277', self::twoClaims());
            file_put_contents($dir . '/note.txt', 'not X12');
            $store = $this->store();
            $summary = (new ClaimStatusIndexer($store))->indexDirectory($dir);
            $this->assertSame(['indexed' => 1, 'skipped' => 2, 'failed' => 0], $summary);
            $this->assertSame('a.277', $store->files[0][0]['ack']['file_name']);
        } finally {
            unlink($dir . '/a.277');
            unlink($dir . '/dup.277');
            unlink($dir . '/note.txt');
            rmdir($dir . DIRECTORY_SEPARATOR . 'folder.277');
            rmdir($dir);
        }
    }

    public function testUnreadableFileThrowsForTheCallerToHandle(): void
    {
        $this->expectException(RuntimeException::class);
        (new ClaimStatusIndexer($this->store()))->indexFile(sys_get_temp_dir() . '/no-such-277-file');
    }
}

final class InMemoryClaimStatusStore implements ClaimStatusIndexStore
{
    /** @var list<list<array{ack: array<string, mixed>, statuses: list<array<string, mixed>>, references: list<array{qualifier: string, ref_value: string}>}>> */
    public array $files = [];

    /** @var array<string, true> */
    private array $seen = [];

    public function isIndexed(string $fileSha256): bool
    {
        return isset($this->seen[$fileSha256]);
    }

    public function recordFile(array $claims): void
    {
        $this->files[] = $claims;
        foreach ($claims as $claim) {
            $this->seen[$claim['ack']['file_sha256']] = true;
        }
    }
}