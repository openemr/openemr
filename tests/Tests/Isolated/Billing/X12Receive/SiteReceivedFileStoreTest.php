<?php

/**
 * Isolated tests for SiteReceivedFileStore, against a temporary site folder.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\X12Receive;

use OpenEMR\Billing\EraFilenameValidator;
use OpenEMR\Billing\X12Receive\ReceivedStatus;
use OpenEMR\Billing\X12Receive\SiteReceivedFileStore;
use OpenEMR\Billing\X12Receive\X12FileType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SiteReceivedFileStoreTest extends TestCase
{
    private string $site;

    protected function setUp(): void
    {
        $this->site = sys_get_temp_dir() . '/x12receive_' . bin2hex(random_bytes(6));
        mkdir($this->site . '/documents/era', 0700, true);
    }

    protected function tearDown(): void
    {
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->site, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            if (!$item instanceof \SplFileInfo) {
                continue;
            }
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                chmod($item->getPathname(), 0600);
                unlink($item->getPathname());
            }
        }
        rmdir($this->site);
    }

    private function era(?string $payerRef = 'REF*2U*60054~'): string
    {
        return implode("\n", array_filter([
            'ISA*00*          *00*          *ZZ*PAYER          *ZZ*RECEIVER       *260101*1200*^*00501*000000042*0*P*:~',
            'GS*HP*PAYER*RECEIVER*20260101*1200*1*X*005010X221A1~',
            'ST*835*0001~',
            'BPR*I*0*C*NON************20260101~',
            'TRN*1*12345*1234567890~',
            'DTM*405*20260101~',
            'N1*PR*TEST PAYER~',
            $payerRef,
            'N1*PE*TEST PROVIDER*XX*1234567890~',
            'LX*1~',
            'CLP*CLAIM001*1*100.00*0*0*MC*12345~',
            'NM1*QC*1*DOE*JOHN****MI*123456789~',
            'SVC*HC:99213*100.00*0~',
            'DTM*472*20260101~',
            'CAS*OA*96*100.00~',
            'SE*' . ($payerRef === null ? 13 : 14) . '*0001~',
            'GE*1*1~',
            'IEA*1*000000042~',
        ], static fn(?string $segment): bool => $segment !== null));
    }

    private function localFile(string $content): string
    {
        $path = $this->site . '/download_' . bin2hex(random_bytes(4));
        file_put_contents($path, $content);
        return $path;
    }

    public function testRemittanceIsStoredUnderTheUploadNameWithItsEdiHistoryCopy(): void
    {
        $store = new SiteReceivedFileStore($this->site);

        $outcome = $store->store(X12FileType::Remittance835, $this->localFile($this->era()), 'whatever.RMT');

        $this->assertSame(ReceivedStatus::Stored, $outcome->status);
        $this->assertSame('20260101_42_60054.edi', $outcome->storedName);
        $this->assertSame($this->era(), file_get_contents($this->site . '/documents/era/20260101_42_60054.edi'));
        $this->assertSame($this->era(), file_get_contents($this->site . '/documents/edi/history/f835/20260101_42_60054.835'));
    }

    public function testRemittanceAlreadyOnTheSiteIsReportedAsDuplicate(): void
    {
        file_put_contents($this->site . '/documents/era/20260101_42_60054.edi', $this->era());
        $store = new SiteReceivedFileStore($this->site);

        $outcome = $store->store(X12FileType::Remittance835, $this->localFile($this->era()), 'whatever.RMT');

        $this->assertSame(ReceivedStatus::Duplicate, $outcome->status);
        $this->assertFileDoesNotExist($this->site . '/documents/edi/history/f835/20260101_42_60054.835');
    }

    public function testDifferentRemittanceOfANameAlreadyStoredForPostingIsRejectedAndTheStoredOneKept(): void
    {
        file_put_contents($this->site . '/documents/era/20260101_42_60054.edi', 'uploaded by the biller');
        $store = new SiteReceivedFileStore($this->site);

        $outcome = $store->store(X12FileType::Remittance835, $this->localFile($this->era()), 'whatever.RMT');

        $this->assertSame(ReceivedStatus::Rejected, $outcome->status);
        $this->assertSame('A different 835 of this name is already stored for posting', $outcome->message);
        $this->assertSame('uploaded by the biller', file_get_contents($this->site . '/documents/era/20260101_42_60054.edi'));
        $this->assertFileDoesNotExist($this->site . '/documents/edi/history/f835/20260101_42_60054.835');
    }

    public function testNothingIsLeftInTheStagingFolder(): void
    {
        $store = new SiteReceivedFileStore($this->site);

        $store->store(X12FileType::Remittance835, $this->localFile($this->era()), 'whatever.RMT');
        $store->store(X12FileType::ClaimStatus277, $this->localFile('277 body'), 'a.277');

        $this->assertSame([], glob($this->site . '/documents/temp/*'));
    }

    public function testRemittanceWhoseEdiHistoryCopyFailsIsNotStoredAndTheRetryStoresBoth(): void
    {
        file_put_contents($this->site . '/documents/edi', 'a file where the folder should be');
        $store = new SiteReceivedFileStore($this->site);

        $first = @$store->store(X12FileType::Remittance835, $this->localFile($this->era()), 'day1.RMT');

        $this->assertSame(ReceivedStatus::Failed, $first->status);
        $this->assertFileDoesNotExist($this->site . '/documents/era/20260101_42_60054.edi');

        unlink($this->site . '/documents/edi');
        $second = $store->store(X12FileType::Remittance835, $this->localFile($this->era()), 'day1.RMT');

        $this->assertSame(ReceivedStatus::Stored, $second->status);
        $this->assertFileExists($this->site . '/documents/era/20260101_42_60054.edi');
        $this->assertFileExists($this->site . '/documents/edi/history/f835/20260101_42_60054.835');
    }

    public function testRemittanceIsStoredWhenEdiHistoryAlreadyHoldsTheSameFile(): void
    {
        mkdir($this->site . '/documents/edi/history/f835', 0700, true);
        file_put_contents($this->site . '/documents/edi/history/f835/20260101_42_60054.835', $this->era());
        $store = new SiteReceivedFileStore($this->site);

        $outcome = $store->store(X12FileType::Remittance835, $this->localFile($this->era()), 'day1.RMT');

        $this->assertSame(ReceivedStatus::Stored, $outcome->status);
        $this->assertFileExists($this->site . '/documents/era/20260101_42_60054.edi');
    }

    public function testRemittanceIsRejectedWhenEdiHistoryHoldsADifferentFileOfItsName(): void
    {
        mkdir($this->site . '/documents/edi/history/f835', 0700, true);
        file_put_contents($this->site . '/documents/edi/history/f835/20260101_42_60054.835', 'another remittance');
        $store = new SiteReceivedFileStore($this->site);

        $outcome = $store->store(X12FileType::Remittance835, $this->localFile($this->era()), 'day1.RMT');

        $this->assertSame(ReceivedStatus::Rejected, $outcome->status);
        $this->assertFileDoesNotExist($this->site . '/documents/era/20260101_42_60054.edi');
        $this->assertSame('another remittance', file_get_contents($this->site . '/documents/edi/history/f835/20260101_42_60054.835'));
    }

    public function testRemittanceIsLeftToRetryWhenTheEdiHistoryCopyCannotBeRead(): void
    {
        mkdir($this->site . '/documents/edi/history/f835', 0700, true);
        $historyCopy = $this->site . '/documents/edi/history/f835/20260101_42_60054.835';
        file_put_contents($historyCopy, $this->era());
        chmod($historyCopy, 0000);
        if (is_readable($historyCopy)) {
            $this->markTestSkipped('Running as a user that can read a mode 0000 file.');
        }
        $store = new SiteReceivedFileStore($this->site);

        $outcome = $store->store(X12FileType::Remittance835, $this->localFile($this->era()), 'day1.RMT');

        $this->assertSame(ReceivedStatus::Failed, $outcome->status);
        $this->assertFileDoesNotExist($this->site . '/documents/era/20260101_42_60054.edi');
    }

    public function testEraNameFallsBackToTheSenderWhenThePayerIdIsAllZeros(): void
    {
        $path = $this->localFile($this->era('REF*2U*000~'));

        $this->assertSame('20260101_42_PAYER', SiteReceivedFileStore::eraName($path));
    }

    /**
     * The name the ERA upload builds (eob_search_era_callback() in sl_eob_search.php), copied
     * here because that callback cannot be called from a test. A change to the service fails
     * the comparison; a change to the upload does not, so keep this copy in step by hand.
     */
    private static function uploadName(string $gsDate, string $controlNumber, string $payerId, string $sender): string
    {
        return $gsDate . '_' . ltrim($controlNumber, '0') . '_' . ltrim($payerId ?: $sender, '0');
    }

    /**
     * @return array<string, array{?string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function payerIdProvider(): array
    {
        return [
            'a payer id' => ['REF*2U*60054~', '60054'],
            'a payer id with leading zeros' => ['REF*2U*0060054~', '0060054'],
            'payer id "0"' => ['REF*2U*0~', '0'],
            'no payer id' => [null, ''],
        ];
    }

    #[DataProvider('payerIdProvider')]
    public function testEraNameMatchesTheUploadForEveryPayerIdTheUploadCanName(?string $payerRef, string $payerId): void
    {
        $path = $this->localFile($this->era($payerRef));

        $this->assertSame(self::uploadName('20260101', '000000042', $payerId, 'PAYER'), SiteReceivedFileStore::eraName($path));
    }

    public function testEraNameDiffersFromTheUploadOnlyWhereTheUploadsNameIsUnusable(): void
    {
        $path = $this->localFile($this->era('REF*2U*000~'));
        $uploadName = self::uploadName('20260101', '000000042', '000', 'PAYER');

        $this->assertNull(EraFilenameValidator::sanitize($uploadName), 'the upload trims "000" to nothing');
        $this->assertSame('20260101_42_PAYER', SiteReceivedFileStore::eraName($path));
    }

    public function testRemittanceThatCannotBeParsedIsRejected(): void
    {
        $store = new SiteReceivedFileStore($this->site);

        $outcome = $store->store(X12FileType::Remittance835, $this->localFile('ISA*no terminator and nothing else'), 'bad.RMT');

        $this->assertSame(ReceivedStatus::Rejected, $outcome->status);
        $this->assertSame([], glob($this->site . '/documents/era/*'));
    }

    public function testEraNameFallsBackToTheSenderWhenThePayerHasNoId(): void
    {
        $path = $this->localFile($this->era(null));

        $this->assertSame('20260101_42_PAYER', SiteReceivedFileStore::eraName($path));
    }

    public function testClaimStatusAndAcknowledgmentGoToTheirEdiHistoryFolders(): void
    {
        $store = new SiteReceivedFileStore($this->site);

        $status = $store->store(X12FileType::ClaimStatus277, $this->localFile('277 body'), 'claim status 101.277');
        $ack = $store->store(X12FileType::Acknowledgment999, $this->localFile('999 body'), 'ack.999');

        $this->assertSame(ReceivedStatus::Stored, $status->status);
        $this->assertSame('claim_status_101.277', $status->storedName);
        $this->assertSame('277 body', file_get_contents($this->site . '/documents/edi/history/f277/claim_status_101.277'));
        $this->assertSame(ReceivedStatus::Stored, $ack->status);
        $this->assertSame('999 body', file_get_contents($this->site . '/documents/edi/history/f997/ack.999'));
    }

    public function testSameFileAgainIsADuplicate(): void
    {
        $store = new SiteReceivedFileStore($this->site);
        $store->store(X12FileType::ClaimStatus277, $this->localFile('first'), 'same.277');

        $outcome = $store->store(X12FileType::ClaimStatus277, $this->localFile('first'), 'same.277');

        $this->assertSame(ReceivedStatus::Duplicate, $outcome->status);
        $this->assertCount(1, (array) glob($this->site . '/documents/edi/history/f277/*'));
    }

    public function testDifferentFileOfATakenNameIsStoredBesideTheFirstNotDropped(): void
    {
        $store = new SiteReceivedFileStore($this->site);
        $store->store(X12FileType::ClaimStatus277, $this->localFile('first'), 'same.277');

        $outcome = $store->store(X12FileType::ClaimStatus277, $this->localFile('second'), 'same.277');
        $again = $store->store(X12FileType::ClaimStatus277, $this->localFile('second'), 'same.277');

        $this->assertSame(ReceivedStatus::Stored, $outcome->status);
        $this->assertMatchesRegularExpression('/^same-[0-9a-f]{8}\.277$/', $outcome->storedName);
        $this->assertSame('first', file_get_contents($this->site . '/documents/edi/history/f277/same.277'));
        $this->assertSame('second', file_get_contents($this->site . '/documents/edi/history/f277/' . $outcome->storedName));
        $this->assertSame(ReceivedStatus::Duplicate, $again->status);
    }

    public function testUnusableRemoteNamesAreRejected(): void
    {
        $store = new SiteReceivedFileStore($this->site);

        $this->assertSame(ReceivedStatus::Rejected, $store->store(X12FileType::ClaimStatus277, $this->localFile('x'), '...')->status);
        $this->assertNull(SiteReceivedFileStore::safeName(''));
        $this->assertNull(SiteReceivedFileStore::safeName('.hidden'));
        $this->assertSame('a_b_c.277', SiteReceivedFileStore::safeName('a/b c.277'));
    }

    public function testFolderThatCannotBeCreatedIsAFailureNotAnError(): void
    {
        file_put_contents($this->site . '/documents/edi', 'a file where the folder should be');
        $store = new SiteReceivedFileStore($this->site);

        $outcome = @$store->store(X12FileType::ClaimStatus277, $this->localFile('x'), 'a.277');

        $this->assertSame(ReceivedStatus::Failed, $outcome->status);
    }
}
