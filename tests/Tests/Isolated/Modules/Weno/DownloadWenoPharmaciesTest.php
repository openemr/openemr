<?php

/**
 * Tests for the Weno pharmacy directory import helpers.
 *
 * These cover the pure transformation layer that PR #13575 reworked. The
 * import itself needs mysqli and a live Weno endpoint, so what is pinned here
 * is everything that decides what ends up in weno_pharmacy:
 *   - titleCase() must not flatten CVS to Cvs, and must still title-case the
 *     all-caps values Weno actually ships,
 *   - redactUrlQuery() must strip the useremail/data query string before a
 *     transport error reaches weno_download_log,
 *   - buildSyntheticNcpdp() must stay deterministic and exactly 7 chars, since
 *     the pharmacy selector filters on strlen(ncpdp_safe) < 8,
 *   - the collision suffix must keep that width past 99,
 *   - detectFileKind()/detectHeaderOffset() must classify the ZIP, bare CSV and
 *     Weno error-page payloads the download path has to tell apart,
 *   - mapRecordToTableRow() must drop empty rows and normalize the boolish and
 *     date columns.
 *
 * Pure logic only; nothing here touches the database, the network or globals.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'OpenEMR\\Modules\\WenoModule\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }
        $relative = str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        $file = __DIR__
            . '/../../../../../interface/modules/custom_modules/oe-module-weno/src/'
            . $relative;
        if (is_file($file)) {
            require_once $file;
        }
    });
}

namespace OpenEMR\Tests\Isolated\Modules\Weno {

    use OpenEMR\Modules\WenoModule\Services\DownloadWenoPharmacies;
    use PHPUnit\Framework\Attributes\DataProvider;
    use PHPUnit\Framework\Attributes\Group;
    use PHPUnit\Framework\Attributes\Test;
    use PHPUnit\Framework\TestCase;
    use Psr\Log\NullLogger;
    use ReflectionMethod;

    #[Group('isolated')]
    #[Group('weno')]
    class DownloadWenoPharmaciesTest extends TestCase
    {
        private DownloadWenoPharmacies $service;

        /** @var list<string> */
        private array $tempFiles = [];

        protected function setUp(): void
        {
            // The constructor takes an optional logger precisely so the class can
            // be built without ServiceContainer in an isolated run.
            $this->service = new DownloadWenoPharmacies(new NullLogger());
        }

        protected function tearDown(): void
        {
            foreach ($this->tempFiles as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            $this->tempFiles = [];
        }

        /**
         * The transformation helpers are private because nothing outside the
         * import should call them. Reflection keeps them testable without
         * widening the class's public surface.
         */
        private function invoke(string $method, mixed ...$args): mixed
        {
            $ref = new ReflectionMethod(DownloadWenoPharmacies::class, $method);
            $ref->setAccessible(true);

            return $ref->invokeArgs($this->service, $args);
        }

        private function tempFileWith(string $contents): string
        {
            $path = tempnam(sys_get_temp_dir(), 'weno');
            self::assertIsString($path);
            file_put_contents($path, $contents);
            $this->tempFiles[] = $path;

            return $path;
        }

        // ------------------------------------------------------------------
        // titleCase
        // ------------------------------------------------------------------

        /**
         * @return array<string, array{string, string}>
         */
        public static function titleCaseProvider(): array
        {
            return [
                'all caps gets title cased' => ['WALGREENS #05512', 'Walgreens #05512'],
                'known acronym survives' => ['CVS PHARMACY #1234', 'CVS Pharmacy #1234'],
                'entity suffix survives' => ['NORTHWEST FAMILY RX LLC', 'Northwest Family RX LLC'],
                'directional survives' => ['123 NE MAIN ST', '123 NE Main St'],
                'mixed case left alone' => ['McKesson Specialty', 'McKesson Specialty'],
                'already title case left alone' => ['Corner Drug Store', 'Corner Drug Store'],
                'empty stays empty' => ['', ''],
                'whitespace trimmed' => ['  RITE AID  ', 'Rite Aid'],
            ];
        }

        #[Test]
        #[DataProvider('titleCaseProvider')]
        public function titleCasePreservesIntentionalCapitalization(string $input, string $expected): void
        {
            self::assertSame($expected, $this->invoke('titleCase', $input));
        }

        // ------------------------------------------------------------------
        // redactUrlQuery
        // ------------------------------------------------------------------

        #[Test]
        public function redactUrlQueryStripsWenoCredentialsFromTransportErrors(): void
        {
            $message = 'cURL error 28: Operation timed out for '
                . 'https://online.wenoexchange.com/en/EPCS/DownloadPharmacyDirectory'
                . '?useremail=admin%40clinic.example&data=U2FsdGVkX1TOPSECRET';

            $redacted = $this->invoke('redactUrlQuery', $message);

            self::assertIsString($redacted);
            self::assertStringNotContainsString('useremail', $redacted);
            self::assertStringNotContainsString('U2FsdGVkX1TOPSECRET', $redacted);
            self::assertStringContainsString('[redacted]', $redacted);
            // The diagnostic itself has to survive or the log entry is useless.
            self::assertStringContainsString('cURL error 28', $redacted);
            self::assertStringContainsString('DownloadPharmacyDirectory', $redacted);
        }

        #[Test]
        public function redactUrlQueryLeavesMessagesWithoutQueryStringsAlone(): void
        {
            $message = 'Connection refused to https://online.wenoexchange.com/en/EPCS/Download';

            self::assertSame($message, $this->invoke('redactUrlQuery', $message));
        }

        // ------------------------------------------------------------------
        // buildSyntheticNcpdp
        // ------------------------------------------------------------------

        #[Test]
        public function syntheticNcpdpIsSevenCharsAndDeterministic(): void
        {
            $first = $this->invoke('buildSyntheticNcpdp', 'Corner Drug', '1 Main St', 'Fort Myers', 'FL', '33901');
            $second = $this->invoke('buildSyntheticNcpdp', 'Corner Drug', '1 Main St', 'Fort Myers', 'FL', '33901');

            self::assertIsString($first);
            self::assertSame($first, $second, 'Same pharmacy must produce the same key across imports');
            self::assertSame(7, strlen($first), 'Selector filters on strlen(ncpdp_safe) < 8');
            self::assertMatchesRegularExpression('/^\d{7}$/', $first);
        }

        #[Test]
        public function syntheticNcpdpIsCaseInsensitive(): void
        {
            $a = $this->invoke('buildSyntheticNcpdp', 'Corner Drug', '1 Main St', 'Fort Myers', 'FL', '33901');
            $b = $this->invoke('buildSyntheticNcpdp', 'CORNER DRUG', '1 MAIN ST', 'FORT MYERS', 'fl', '33901');

            self::assertSame($a, $b);
        }

        /**
         * Documents a known sharp edge rather than endorsing it: the seed is
         * trimmed as a whole, so padding inside a field shifts the key. The
         * import is only safe because normalizeRecord() trims every value before
         * mapRecordToTableRow() gets there. Changing the formula would re-key
         * every existing row, so this is pinned as-is and tracked separately.
         */
        #[Test]
        public function syntheticNcpdpIsSensitiveToUntrimmedFields(): void
        {
            $clean = $this->invoke('buildSyntheticNcpdp', 'Corner Drug', '1 Main St', 'Fort Myers', 'FL', '33901');
            $padded = $this->invoke('buildSyntheticNcpdp', 'Corner Drug ', '1 Main St', 'Fort Myers', 'FL', '33901');

            self::assertNotSame($clean, $padded);
        }

        #[Test]
        public function normalizeRecordTrimsValuesThatFeedTheSyntheticKey(): void
        {
            $normalized = $this->invoke(
                'normalizeRecord',
                ['Business_Name' => '  Corner Drug  ', 'City' => "Fort Myers\t"],
                ['Business_Name', 'City'],
                ['Business_Name', 'City']
            );

            self::assertIsArray($normalized);
            self::assertSame('Corner Drug', $normalized['Business_Name']);
            self::assertSame('Fort Myers', $normalized['City']);
        }

        #[Test]
        public function syntheticNcpdpDiffersForDifferentPharmacies(): void
        {
            $a = $this->invoke('buildSyntheticNcpdp', 'Corner Drug', '1 Main St', 'Fort Myers', 'FL', '33901');
            $b = $this->invoke('buildSyntheticNcpdp', 'Corner Drug', '2 Main St', 'Fort Myers', 'FL', '33901');

            self::assertNotSame($a, $b);
        }

        // ------------------------------------------------------------------
        // collision suffixing (via mapRecordToTableRow)
        // ------------------------------------------------------------------

        #[Test]
        public function collisionSuffixKeepsTheKeyAtSevenCharacters(): void
        {
            $base = '1234567';
            // Pre-load the taken set so every generated candidate collides and the
            // suffix has to climb past two digits.
            $used = [];
            for ($i = 0; $i <= 120; $i++) {
                $used[substr($base, 0, 5) . str_pad((string) $i, 2, '0', STR_PAD_LEFT)] = true;
                $used[substr($base, 0, 4) . str_pad((string) $i, 3, '0', STR_PAD_LEFT)] = true;
            }
            $used[$base] = true;

            $row = $this->invokeMapRecord(
                ['Business_Name' => 'Corner Drug', 'City' => 'Fort Myers', 'NCPDP_safe' => $base],
                $used
            );

            self::assertIsArray($row);
            self::assertIsString($row['NCPDP_safe']);
            self::assertSame(7, strlen($row['NCPDP_safe']));
            self::assertArrayNotHasKey($row['NCPDP_safe'], $used, 'Must resolve to an unused key');
        }

        /**
         * NCPDP keys are numeric strings, which PHP stores as int array keys, so
         * the taken-set is array-key rather than string keyed.
         *
         * @param array<string, string>    $normalized
         * @param array<array-key, bool>   $usedNcpdp
         * @return array<string, string|null>|null
         */
        private function invokeMapRecord(array $normalized, array &$usedNcpdp): ?array
        {
            $ref = new ReflectionMethod(DownloadWenoPharmacies::class, 'mapRecordToTableRow');
            $ref->setAccessible(true);
            $args = [$normalized, true, &$usedNcpdp];

            /** @var array<string, string|null>|null $result */
            $result = $ref->invokeArgs($this->service, $args);

            return $result;
        }

        // ------------------------------------------------------------------
        // mapRecordToTableRow
        // ------------------------------------------------------------------

        #[Test]
        public function rowsWithNoNameAddressOrCityAreDropped(): void
        {
            $used = [];
            $row = $this->invokeMapRecord(['Business_Name' => '', 'Address_Line_1' => '', 'City' => ''], $used);

            self::assertNull($row);
        }

        #[Test]
        public function mappedRowNormalizesFlagsAndDates(): void
        {
            $used = [];
            $row = $this->invokeMapRecord([
                'Business_Name' => 'CORNER DRUG',
                'Address_Line_1' => '1 MAIN ST',
                'City' => 'FORT MYERS',
                'State' => 'FL',
                'NCPDP_safe' => '1234567',
                'On_WENO' => 'yes',
                'Test_Pharmacy' => 'no',
                'State_Wide_Mail_Order' => 'y',
                'Created' => '03/15/2026 10:00:00 AM',
                'Deleted' => '0000-00-00 00:00:00',
            ], $used);

            self::assertIsArray($row);
            self::assertSame('Corner Drug', $row['Business_Name']);
            self::assertSame('FL', $row['State']);
            self::assertSame('True', $row['On_WENO']);
            self::assertSame('False', $row['Test_Pharmacy']);
            self::assertSame('State', $row['State_Wide_Mail_Order']);
            self::assertSame('2026-03-15 10:00:00', $row['Created']);
            self::assertNull($row['Deleted'], 'Zero dates must become NULL, not 0000-00-00');
        }

        // ------------------------------------------------------------------
        // normalizeDateTime / normalizeBoolish / normalizeMailOrderFlag
        // ------------------------------------------------------------------

        /**
         * @return array<string, array{string, string|null}>
         */
        public static function dateProvider(): array
        {
            return [
                'weno am/pm format' => ['03/15/2026 10:00:00 AM', '2026-03-15 10:00:00'],
                'iso passes through' => ['2026-03-15 10:00:00', '2026-03-15 10:00:00'],
                'zero date is null' => ['0000-00-00', null],
                'zero datetime is null' => ['0000-00-00 00:00:00', null],
                'empty is null' => ['', null],
                'garbage is null' => ['not a date', null],
            ];
        }

        #[Test]
        #[DataProvider('dateProvider')]
        public function dateTimeNormalization(string $input, ?string $expected): void
        {
            self::assertSame($expected, $this->invoke('normalizeDateTime', $input));
        }

        /**
         * @return array<string, array{string, string}>
         */
        public static function mailOrderProvider(): array
        {
            return [
                'yes' => ['yes', 'State'],
                'y' => ['Y', 'State'],
                'true' => ['TRUE', 'State'],
                'state' => ['State', 'State'],
                'mail' => ['mail', 'State'],
                'no' => ['no', 'Local'],
                'empty' => ['', 'Local'],
                'unknown' => ['whatever', 'Local'],
            ];
        }

        #[Test]
        #[DataProvider('mailOrderProvider')]
        public function mailOrderFlagNormalization(string $input, string $expected): void
        {
            self::assertSame($expected, $this->invoke('normalizeMailOrderFlag', $input));
        }

        #[Test]
        public function boolishNormalizationKeepsEmptyEmpty(): void
        {
            self::assertSame('', $this->invoke('normalizeBoolish', '  ', 'True', 'False'));
            self::assertSame('True', $this->invoke('normalizeBoolish', '1', 'True', 'False'));
            self::assertSame('False', $this->invoke('normalizeBoolish', 'N', 'True', 'False'));
        }

        // ------------------------------------------------------------------
        // header handling
        // ------------------------------------------------------------------

        #[Test]
        public function headerNamesAreCanonicalized(): void
        {
            self::assertSame('State_Wide_Mail_Order', $this->invoke('normalizeHeaderName', 'Mail Order'));
            self::assertSame('NCPDP_safe', $this->invoke('normalizeHeaderName', 'ncpdp_safe'));
            self::assertSame('Business_Name', $this->invoke('normalizeHeaderName', 'business_name'));
            // A UTF-8 BOM on the first header would otherwise poison every lookup.
            self::assertSame('Business_Name', $this->invoke('normalizeHeaderName', "\xEF\xBB\xBFbusiness_name"));
        }

        #[Test]
        public function footerRowsAreRecognized(): void
        {
            self::assertTrue($this->invoke('isFooterOrNoiseRow', ['a' => 'Confidential Weno Exchange Inc']));
            self::assertTrue($this->invoke('isFooterOrNoiseRow', ['a' => 'Copyright Weno 2026']));
            self::assertFalse($this->invoke('isFooterOrNoiseRow', ['a' => 'Corner Drug', 'b' => 'Fort Myers']));
        }

        #[Test]
        public function headerOffsetSkipsTheConfidentialityBanner(): void
        {
            $withBanner = $this->tempFileWith(
                "Confidential: Weno Exchange\nBusiness_Name,Address_Line_1,City\nCorner Drug,1 Main St,Fort Myers\n"
            );
            $withoutBanner = $this->tempFileWith(
                "Business_Name,Address_Line_1,City\nCorner Drug,1 Main St,Fort Myers\n"
            );

            self::assertSame(1, $this->invoke('detectHeaderOffset', $withBanner));
            self::assertSame(0, $this->invoke('detectHeaderOffset', $withoutBanner));
        }

        // ------------------------------------------------------------------
        // payload classification
        // ------------------------------------------------------------------

        #[Test]
        public function fileKindDetectsZipPayloads(): void
        {
            $zip = $this->tempFileWith("PK\x03\x04" . str_repeat("\x00", 32));

            self::assertSame('zip', $this->invoke('detectFileKind', $zip));
        }

        #[Test]
        public function fileKindDetectsCsvPayloads(): void
        {
            $csv = $this->tempFileWith("Business_Name,Address_Line_1,City\nCorner Drug,1 Main St,Fort Myers\n");

            self::assertSame('csv', $this->invoke('detectFileKind', $csv));
        }

        #[Test]
        public function fileKindDetectsWenoErrorPages(): void
        {
            $html = $this->tempFileWith("<!DOCTYPE html>\n<html><body>EXCEEDED_DOWNLOAD_LIMITS</body></html>");

            self::assertSame('error_html', $this->invoke('detectFileKind', $html));
        }

        #[Test]
        public function fileKindReportsUnknownForBinaryJunk(): void
        {
            $junk = $this->tempFileWith(random_bytes(64) . "\x00\x01\x02");

            self::assertSame('unknown', $this->invoke('detectFileKind', $junk));
        }

        // ------------------------------------------------------------------
        // retrieveDataFile guard (public, and reachable without the network)
        // ------------------------------------------------------------------

        #[Test]
        public function retrieveDataFileRejectsAnEmptyUrlWithoutTouchingTheNetwork(): void
        {
            $result = $this->service->retrieveDataFile('', sys_get_temp_dir());

            self::assertFalse($result['success']);
            self::assertStringContainsString('missing url', $result['message']);
        }
    }
}
