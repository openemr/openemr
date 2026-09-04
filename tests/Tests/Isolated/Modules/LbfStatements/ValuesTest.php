<?php

/**
 * Values and Identifiers tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace {
    $moduleSrc = dirname(__DIR__, 5) . '/interface/modules/custom_modules/oe-module-lbf-statements/src/';
    if (!is_dir($moduleSrc)) {
        throw new RuntimeException('LBF statements module source not found at ' . $moduleSrc);
    }
    spl_autoload_register(static function (string $class) use ($moduleSrc): void {
        $prefix = 'OpenEMR\\Modules\\LbfStatements\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }
        $file = $moduleSrc . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    });
}

namespace OpenEMR\Tests\Isolated\Modules\LbfStatements {

    use OpenEMR\Modules\LbfStatements\Identifiers;
    use OpenEMR\Modules\LbfStatements\StatementParagraph;
    use OpenEMR\Modules\LbfStatements\Values;
    use PHPUnit\Framework\TestCase;

    final class ValuesTest extends TestCase
    {
        /**
         * Cast null, strings, numbers, and booleans to strings.
         */
        public function testAsStringCastsScalars(): void
        {
            $this->assertSame('', Values::asString(null));
            $this->assertSame('fallback', Values::asString(null, 'fallback'));
            $this->assertSame('keep', Values::asString('keep'));
            $this->assertSame('3', Values::asString(3));
            $this->assertSame('1.5', Values::asString(1.5));
            $this->assertSame('1', Values::asString(true));
            $this->assertSame('0', Values::asString(false));
            $this->assertSame('', Values::asString(['nope']));
        }

        /**
         * Cast numeric scalars to integers and reject junk.
         */
        public function testAsIntCastsScalars(): void
        {
            $this->assertSame(4, Values::asInt(4));
            $this->assertSame(12, Values::asInt('12'));
            $this->assertSame(3, Values::asInt(3.9));
            $this->assertSame(0, Values::asInt('nope'));
            $this->assertSame(9, Values::asInt(false, 9));
        }

        /**
         * Parse boolean flags and optional floats.
         */
        public function testAsBoolAndFloat(): void
        {
            $this->assertTrue(Values::asBool(true));
            $this->assertTrue(Values::asBool(1));
            $this->assertTrue(Values::asBool('1'));
            $this->assertFalse(Values::asBool(0));
            $this->assertFalse(Values::asBool('0'));
            $this->assertNull(Values::asFloatOrNull(null));
            $this->assertSame(1.5, Values::asFloatOrNull(1.5));
            $this->assertSame(2.0, Values::asFloatOrNull(2));
            $this->assertSame(3.25, Values::asFloatOrNull(' 3.25 '));
            $this->assertNull(Values::asFloatOrNull(''));
            $this->assertNull(Values::asFloatOrNull('abc'));
            $this->assertNull(Values::asFloatOrNull([]));
        }

        /**
         * Keep string-keyed SQL rows and typed column helpers.
         */
        public function testAssocRowAndRowHelpers(): void
        {
            $this->assertNull(Values::assocRow('nope'));
            $this->assertSame(['a' => 1], Values::assocRow([0 => 'skip', 'a' => 1]));
            $row = ['n' => '7', 's' => 'hi'];
            $this->assertSame('hi', Values::rowString($row, 's'));
            $this->assertSame('', Values::rowString($row, 'missing'));
            $this->assertSame(7, Values::rowInt($row, 'n'));
            $this->assertSame(0, Values::rowInt($row, 'missing'));
        }

        /**
         * Accept layout ids and reject identifiers with spaces.
         */
        public function testIdentifiers(): void
        {
            $this->assertSame('LBFecho', Identifiers::assertFieldId('LBFecho'));
            $this->assertSame('stmt_paragraph', Identifiers::assertFieldId('stmt_paragraph'));
            $this->expectException(\InvalidArgumentException::class);
            Identifiers::assertFieldId('bad id');
        }

        /**
         * Allow spaces and plus in stored list option ids.
         */
        public function testUnsafeOptionId(): void
        {
            $this->assertTrue(Identifiers::isSafeStoredOptionId('mild + dilated'));
            $this->assertFalse(Identifiers::isSafeStoredOptionId(''));
            $this->assertFalse(Identifiers::isSafeStoredOptionId('../x'));
            $this->assertFalse(Identifiers::isSafeStoredOptionId('a/b'));
            $this->assertFalse(Identifiers::isSafeStoredOptionId('a\\b'));
        }

        /**
         * Join action sentences and add a missing period.
         */
        public function testParagraphJoinsSentences(): void
        {
            $this->assertSame('', StatementParagraph::fromActions([['sentence' => '  ']]));
            $this->assertSame(
                'One. Two!',
                StatementParagraph::fromActions([
                    ['sentence' => 'One'],
                    ['sentence' => 'Two!'],
                ])
            );
        }
    }
}
