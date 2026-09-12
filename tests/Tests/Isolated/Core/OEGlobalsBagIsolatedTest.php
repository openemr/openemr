<?php

/**
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Core;

use ErrorException;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

#[Group('isolated')]
#[Group('core')]
class OEGlobalsBagIsolatedTest extends TestCase
{
    protected function tearDown(): void
    {
        ServiceContainer::reset();
    }

    /**
     * A global that cannot be read as its declared type must not take the installation down.
     *
     * interface/globals.php calls getInt('user_php_debug', 0) during bootstrap. ParameterBag
     * throws UnexpectedValueException when the stored value fails the filter, which fatals every
     * page -- including Administration > Globals, the screen needed to fix it. `gl_value` is
     * `varchar(255) NOT NULL DEFAULT ''` and an empty string fails FILTER_VALIDATE_INT, so an
     * integer global present with no value is enough to trigger it.
     *
     * @param scalar $stored
     */
    #[DataProvider('unusableIntValueProvider')]
    public function testGetIntFallsBackInsteadOfThrowing(mixed $stored, string $case): void
    {
        $bag = new OEGlobalsBag(['user_php_debug' => $stored]);

        $this->assertSame(0, $bag->getInt('user_php_debug', 0), $case);
        $this->assertSame(7, $bag->getInt('user_php_debug', 7), $case . ' (caller default is honoured)');
    }

    /**
     * @return array<string, array{mixed, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unusableIntValueProvider(): array
    {
        return [
            'empty string (the gl_value column default)' => ['', 'empty string'],
            'whitespace only' => ['   ', 'whitespace'],
            'non-numeric text' => ['off', 'non-numeric'],
            'partially numeric' => ['2abc', 'partially numeric'],
        ];
    }

    public function testGetIntStillReturnsValidStoredValues(): void
    {
        $bag = new OEGlobalsBag(['user_php_debug' => '2']);

        $this->assertSame(2, $bag->getInt('user_php_debug', 0));
    }

    public function testGetBooleanFallsBackOnAnUnusableValue(): void
    {
        $bag = new OEGlobalsBag(['some_flag' => 'maybe']);

        $this->assertFalse($bag->getBoolean('some_flag'));
        $this->assertTrue($bag->getBoolean('some_flag', true), 'caller default is honoured');
    }

    public function testGetBooleanStillReturnsValidStoredValues(): void
    {
        $bag = new OEGlobalsBag(['on_flag' => '1', 'off_flag' => '0']);

        $this->assertTrue($bag->getBoolean('on_flag'));
        $this->assertFalse($bag->getBoolean('off_flag', true));
    }

    public function testGetEnumFallsBackOnAnUnusableValue(): void
    {
        $bag = new OEGlobalsBag(['suit' => 'not-a-case']);

        $this->assertNull($bag->getEnum('suit', OEGlobalsBagTestSuit::class));
        $this->assertSame(
            OEGlobalsBagTestSuit::Hearts,
            $bag->getEnum('suit', OEGlobalsBagTestSuit::class, OEGlobalsBagTestSuit::Hearts)
        );
    }

    public function testGetEnumStillReturnsValidStoredValues(): void
    {
        $bag = new OEGlobalsBag(['suit' => 'spades']);

        $this->assertSame(OEGlobalsBagTestSuit::Spades, $bag->getEnum('suit', OEGlobalsBagTestSuit::class));
    }

    /**
     * The substitution must be recorded rather than passing silently.
     */
    public function testAnUnusableValueIsReported(): void
    {
        $logger = new class extends AbstractLogger {
            /** @var list<array{level: mixed, message: string|\Stringable, context: array<mixed>}> */
            public array $records = [];

            /** @param array<mixed> $context */
            public function log($level, string|\Stringable $message, array $context = []): void
            {
                $this->records[] = ['level' => $level, 'message' => $message, 'context' => $context];
            }
        };
        ServiceContainer::override(LoggerInterface::class, $logger);

        (new OEGlobalsBag(['user_php_debug' => 'off']))->getInt('user_php_debug', 0);

        $this->assertCount(1, $logger->records);
        $record = $logger->records[0];
        $this->assertSame(LogLevel::WARNING, $record['level']);
        $this->assertStringContainsString('Administration > Globals', (string) $record['message']);
        $this->assertSame('user_php_debug', $record['context']['global'] ?? null);
        $this->assertSame('off', $record['context']['stored'] ?? null);
        $this->assertSame(0, $record['context']['default'] ?? null);
    }

    public function testGlobalsBagInit(): void
    {
        $key = 'dummy-key';
        $value = 'dummy-value';
        $values = [$key => $value];

        $bag = new OEGlobalsBag($values);
        $this->assertTrue($bag->has($key));
        $this->assertSame($value, $bag->get($key));

        $this->assertArrayNotHasKey($key, $GLOBALS);
    }

    public function testGlobalsBagPushesIntoGlobalsOnSet(): void
    {
        $key = 'dummy-key';
        $value = 'dummy-value';

        $globalsBag = new OEGlobalsBag([]);
        $this->assertFalse($globalsBag->has($key));
        $this->assertArrayNotHasKey($key, $GLOBALS);

        $globalsBag->set($key, $value);
        $this->assertTrue($globalsBag->has($key));
        $this->assertSame($value, $globalsBag->get($key));

        $this->assertArrayHasKey($key, $GLOBALS);
        $this->assertSame($value, $GLOBALS[$key]);
    }

    /**
     * Keep in sync with OEGlobalsBag::DEPRECATED_KEYS.
     *
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function deprecatedKeysProvider(): array
    {
        $keys = [
            'unit_test_placeholder',
        ];
        // Format to DataProvider
        return array_combine($keys, array_map(fn($k): array => [$k], $keys));
    }

    #[DataProvider('deprecatedKeysProvider')]
    public function testGetDeprecatedKeyTriggersWarning(string $key): void
    {
        $bag = new OEGlobalsBag([$key => 'test-value']);
        $this->expectException(ErrorException::class);
        $bag->get($key);
    }

    #[DataProvider('deprecatedKeysProvider')]
    public function testHasDeprecatedKeyTriggersWarning(string $key): void
    {
        $bag = new OEGlobalsBag([$key => 'test-value']);
        $this->expectException(ErrorException::class);
        $bag->has($key);
    }
}

/**
 * Backed enum fixture for the getEnum() cases above.
 */
enum OEGlobalsBagTestSuit: string
{
    case Hearts = 'hearts';
    case Spades = 'spades';
}
