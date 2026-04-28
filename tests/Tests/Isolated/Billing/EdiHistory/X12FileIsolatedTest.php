<?php

/**
 * Isolated tests for the OpenEMR\Billing\EdiHistory\X12File parser.
 *
 * Builds a synthetic, minimal X12 270 (eligibility inquiry) file in a
 * temp directory and verifies that the parser exposes the expected
 * envelope, delimiter, and segment data through its public accessors.
 *
 * The tests stay synthetic to keep PHI out of the repository and to
 * avoid coupling to any specific real-world payer dialect.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\EdiHistory;

use OpenEMR\Billing\EdiHistory\X12File;
use PHPUnit\Framework\TestCase;

// X12File renders error messages through the global text() helper that
// normally lives in library/htmlspecialchars.inc.php. Stub it here so the
// isolated test does not require the legacy bootstrap. The stub is invoked
// by tests that exercise error paths (e.g. invalid file path), but function
// declarations themselves never register as covered lines, so ignore it.
// @codeCoverageIgnoreStart
if (!function_exists('text')) {
    function text(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_NOQUOTES);
    }
}
// @codeCoverageIgnoreEnd

class X12FileIsolatedTest extends TestCase
{
    private string $fixturePath;
    private string $fixtureText;

    protected function setUp(): void
    {
        $this->fixtureText = $this->build270();
        $this->fixturePath = tempnam(sys_get_temp_dir(), 'x12file_test_');
        file_put_contents($this->fixturePath, $this->fixtureText);
    }

    private function build270(): string
    {
        $isa = 'ISA*00*          *00*          '
             . '*ZZ*SENDER         *ZZ*RECEIVER       '
             . '*240101*1200*^*00501*000000001*0*T*:~';
        $body = 'GS*HS*SENDER*RECEIVER*20240101*1200*1*X*005010X279A1~'
              . 'ST*270*0001*005010X279A1~'
              . 'BHT*0022*13*REF1*20240101*1200~'
              . 'HL*1**20*1~'
              . 'NM1*PR*2*INSCO*****PI*12345~'
              . 'HL*2*1*21*1~'
              . 'NM1*1P*2*PROV*****XX*1234567890~'
              . 'HL*3*2*22*0~'
              . 'TRN*1*REF*1234567890~'
              . 'NM1*IL*1*DOE*JOHN****MI*123456789~'
              . 'DMG*D8*19800101*M~'
              . 'DTP*291*D8*20240101~'
              . 'EQ*30~'
              . 'SE*12*0001~'
              . 'GE*1*1~'
              . 'IEA*1*000000001~';

        return $isa . $body;
    }

    protected function tearDown(): void
    {
        if (is_file($this->fixturePath)) {
            unlink($this->fixturePath);
        }
    }

    public function testEmptyConstructorProducesUsableObject(): void
    {
        $x = new X12File();
        $this->assertSame('', $x->edih_filepath());
        $this->assertSame('', $x->edih_filename());
        $this->assertFalse($x->edih_valid());
        $this->assertFalse($x->edih_isx12());
        $this->assertSame([], $x->edih_segments());
        $this->assertSame([], $x->edih_envelopes());
    }

    public function testInvalidFilePathReportsMessageAndStaysInvalid(): void
    {
        $x = new X12File('/nonexistent/path/should/not/resolve.x12');
        $this->assertFalse($x->edih_valid());
        $this->assertStringContainsString(
            'invalid file path',
            $x->edih_message(),
        );
    }

    public function testFixtureIsRecognisedAsValidX12(): void
    {
        $x = new X12File($this->fixturePath);
        $this->assertTrue($x->edih_valid(), 'fixture should pass scan');
        $this->assertTrue($x->edih_isx12(), 'starts with ISA');
        $this->assertTrue($x->edih_hasGS(), 'has GS envelope');
        $this->assertTrue($x->edih_hasST(), 'has ST envelope');
        $this->assertSame(basename($this->fixturePath), $x->edih_filename());
        $this->assertSame($this->fixturePath, $x->edih_filepath());
        $this->assertSame('00501', $x->edih_version());
    }

    public function testDelimitersDetectedFromIsaSegment(): void
    {
        $x = new X12File($this->fixturePath);
        $delim = $x->edih_delimiters();
        self::assertIsArray($delim);
        $this->assertSame('*', $delim['e'] ?? null, 'element separator');
        $this->assertSame('~', $delim['t'] ?? null, 'segment terminator');
        $this->assertSame(':', $delim['s'] ?? null, 'sub-element separator');
    }

    public function testTransactionTypeReturnsGsFunctionalIdCode(): void
    {
        $x = new X12File($this->fixturePath);
        // edih_type() returns the GS01 functional identifier code itself
        // ('HS' for a 270 inquiry); the gstype_ar lookup table maps it to
        // the transaction-set number elsewhere.
        $this->assertSame('HS', $x->edih_type());
    }

    public function testSegmentsAndEnvelopesPopulatedAfterParsing(): void
    {
        $x = new X12File($this->fixturePath);
        $segments = $x->edih_segments();
        $this->assertNotEmpty($segments);

        $envelopes = $x->edih_envelopes();
        $this->assertNotEmpty($envelopes, 'envelope summary should be populated');
    }

    public function testTextNotRetainedByDefault(): void
    {
        $x = new X12File($this->fixturePath);
        $this->assertSame('', $x->edih_text(), 'default $text=false should drop file body');
        $this->assertGreaterThan(0, $x->edih_length(), 'length recorded even when text dropped');
    }

    public function testTextRetainedWhenRequested(): void
    {
        $x = new X12File($this->fixturePath, true, true);
        $text = $x->edih_text();
        self::assertIsString($text);
        $this->assertNotSame('', $text);
        $this->assertSame(strlen($text), $x->edih_length());
    }

    public function testNoMkSegsConstructorPathPopulatesTypeFromText(): void
    {
        // mk_segs=false skips segment building and instead derives type by
        // scanning the file body for GS segments via edih_x12_type($text).
        $x = new X12File($this->fixturePath, false, false);
        $this->assertTrue($x->edih_valid());
        $this->assertSame([], $x->edih_segments(), 'no segments built');
        $this->assertSame('HS', $x->edih_type(), 'type derived from GS scan of text');
    }

    public function testEdihGsTypeMapsKnownGs01Codes(): void
    {
        $x = new X12File();
        $this->assertSame('837', $x->edih_gs_type('HC'));
        $this->assertSame('835', $x->edih_gs_type('HP'));
        $this->assertSame('270', $x->edih_gs_type('HS'));
        $this->assertSame('999', $x->edih_gs_type('FA'));
        // case-insensitive lookup
        $this->assertSame('837', $x->edih_gs_type('hc'));
    }

    public function testEdihGsTypeReturnsFalseForUnknownCode(): void
    {
        $x = new X12File();
        $this->assertFalse($x->edih_gs_type('XX'));
    }

    public function testEdihMessageReturnsEmptyStringWhenNoMessages(): void
    {
        $x = new X12File();
        $this->assertSame('', $x->edih_message());
    }

    public function testEdihMessageRendersAccumulatedMessagesAsHtml(): void
    {
        $x = new X12File('/nonexistent/path/file.x12');
        $html = $x->edih_message();
        $this->assertStringContainsString('<p>', $html);
        $this->assertStringContainsString('<br />', $html);
        $this->assertStringContainsString('invalid file path', $html);
    }

    public function testScanReturnsEmptyOnZeroLengthInput(): void
    {
        $x = new X12File();
        $this->assertSame('', $x->edih_x12_scan(''));
        $this->assertStringContainsString('zero length', $x->edih_message());
    }

    public function testScanStripsInternalNewlinesBeforeProcessing(): void
    {
        // Internal PHP_EOL inside an otherwise-valid X12 file should be
        // removed before mime/regex checks; the file should still scan as
        // a valid x12 with ISA/GS/ST envelopes ('ovigs').
        $x = new X12File();
        $multiline = 'ISA*00*          *00*          '
            . '*ZZ*SENDER         *ZZ*RECEIVER       '
            . '*240101*1200*^*00501*000000001*0*T*:~' . PHP_EOL
            . 'GS*HS*S*R*20240101*1200*1*X*005010X279A1~' . PHP_EOL
            . 'ST*270*0001*005010X279A1~' . PHP_EOL
            . 'SE*1*0001~GE*1*1~IEA*1*000000001~';
        $this->assertSame('ovigs', $x->edih_x12_scan($multiline));
    }

    public function testScanRejectsBinaryContentViaMimeCheck(): void
    {
        // PNG signature is unmistakably binary; finfo classifies it as
        // image/png, which fails the text/plain;us-ascii requirement.
        $x = new X12File();
        $png = "\x89PNG\r\n\x1a\nbinary garbage and more padding to be safe";
        $this->assertSame('', $x->edih_x12_scan($png));
        $this->assertStringContainsString('invalid mime info', $x->edih_message());
    }

    public function testScanRejectsSuspectCharacterPatterns(): void
    {
        // The suspect-pattern regex catches ${ along with <?, <%, <asp, etc.
        // Plain ASCII text containing the marker reliably trips the regex
        // after passing the mime check.
        $x = new X12File();
        $payload = 'plain ascii text with marker ${VAR} embedded inside';
        $this->assertSame('', $x->edih_x12_scan($payload));
        $this->assertStringContainsString('suspect characters', $x->edih_message());
    }

    public function testTypeFromTextArgumentOnConstructedObject(): void
    {
        // Calling edih_x12_type() with explicit text after construction
        // exercises the !$this->constructing branch and the GS preg_match
        // path.
        $x = new X12File($this->fixturePath);
        $this->assertSame('HS', $x->edih_x12_type($this->fixtureText));
    }

    public function testTypeReturnsFalseWhenTextHasNoGsSegment(): void
    {
        // A scan-valid ISA/ST file with no recognized GS functional ID
        // should fall through to "error in identifying type" → false.
        $x = new X12File();
        $noGs = 'ISA*00*          *00*          '
            . '*ZZ*SENDER         *ZZ*RECEIVER       '
            . '*240101*1200*^*00501*000000001*0*T*:~'
            . 'ST*270*0001*005010X279A1~SE*1*0001~IEA*1*000000001~';
        // edih_x12_type's PHPDoc claims @return string but the
        // implementation returns false on this error path; assert via the
        // accumulated message which is the observable contract.
        $x->edih_x12_type($noGs);
        $this->assertStringContainsString('error in identifying type', $x->edih_message());
    }

    public function testDelimitersRejectsTooShortIsaString(): void
    {
        $x = new X12File();
        $this->assertSame([], $x->edih_x12_delimiters('ISA*too*short'));
        $this->assertStringContainsString('too short', $x->edih_message());
    }

    public function testDelimitersRejectsNonIsaPrefix(): void
    {
        // 106+ char string that does not begin with ISA.
        $x = new X12File();
        $padded = str_repeat('X', 106);
        $this->assertSame([], $x->edih_x12_delimiters($padded));
        $this->assertStringContainsString('does not begin with ISA', $x->edih_message());
    }

    public function testDelimitersRejectsTruncatedIsaWithTooFewElements(): void
    {
        // Starts with ISA, length >= 106, but does not contain 16
        // element separators, so the parser bails on "too few elements".
        $x = new X12File();
        $truncated = 'ISA*00*' . str_repeat('A', 110);
        $this->assertSame([], $x->edih_x12_delimiters($truncated));
        $this->assertStringContainsString('too few elements', $x->edih_message());
    }

    public function testDelimitersHandleNon5010IsaWithoutRepetitionSeparator(): void
    {
        // A 4010 ISA segment puts no repetition separator at element 11; the
        // delim_ct == 12 branch resets $dr to '' when ISA12 lacks '501'.
        $isa4010 = 'ISA*00*          *00*          '
            . '*ZZ*SENDER         *ZZ*RECEIVER       '
            . '*240101*1200*U*00401*000000001*0*T*:~';
        $x = new X12File();
        $delim = $x->edih_x12_delimiters($isa4010);
        $this->assertSame('*', $delim['e'] ?? null);
        $this->assertSame('~', $delim['t'] ?? null);
        $this->assertSame(':', $delim['s'] ?? null);
        $this->assertSame('', $delim['r'] ?? null, '4010 has no repetition separator');
    }

    public function testEnvelopesWarnOnUnknownGsFunctionalIdCode(): void
    {
        // Build a fixture whose GS01 (ZZ) is not in the gstype_ar map.
        $isa = 'ISA*00*          *00*          '
            . '*ZZ*SENDER         *ZZ*RECEIVER       '
            . '*240101*1200*^*00501*000000001*0*T*:~';
        $body = 'GS*ZZ*SENDER*RECEIVER*20240101*1200*1*X*005010~'
            . 'ST*999*0001~SE*1*0001~GE*1*1~IEA*1*000000001~';
        $path = tempnam(sys_get_temp_dir(), 'x12file_unknown_gs_');
        file_put_contents($path, $isa . $body);
        try {
            $x = new X12File($path);
            $this->assertStringContainsString('Unknown GS type', $x->edih_message());
        } finally {
            unlink($path);
        }
    }
}
