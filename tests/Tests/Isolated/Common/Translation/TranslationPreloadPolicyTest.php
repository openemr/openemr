<?php

/**
 * Isolated tests for TranslationPreloadPolicy and TranslationPreloadMode.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Translation;

use OpenEMR\Common\Translation\TranslationPreloadMode;
use OpenEMR\Common\Translation\TranslationPreloadPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TranslationPreloadPolicyTest extends TestCase
{
    /** @var list<int> Language ids the policy asked to count */
    private array $counted = [];

    /**
     * @param array<int, int> $definitions Definition count per language id
     */
    private function policy(array $definitions): TranslationPreloadPolicy
    {
        return new TranslationPreloadPolicy(function (int $langId) use ($definitions): int {
            $this->counted[] = $langId;
            return $definitions[$langId] ?? 0;
        });
    }

    /**
     * @return array<string, array{string, TranslationPreloadMode}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function storedValueProvider(): array
    {
        return [
            'old no' => ['0', TranslationPreloadMode::Off],
            'old yes' => ['1', TranslationPreloadMode::Always],
            'auto' => ['auto', TranslationPreloadMode::Auto],
            'unknown' => ['sometimes', TranslationPreloadMode::Off],
            'empty' => ['', TranslationPreloadMode::Off],
        ];
    }

    #[DataProvider('storedValueProvider')]
    public function testModeFromStoredValue(string $stored, TranslationPreloadMode $expected): void
    {
        $this->assertSame($expected, TranslationPreloadMode::fromGlobal($stored));
    }

    public function testOffNeverPreloadsOrCounts(): void
    {
        $decision = $this->policy([1 => 0])->decide(TranslationPreloadMode::Off, 1, null);

        $this->assertFalse($decision->preload);
        $this->assertNull($decision->remember);
        $this->assertSame([], $this->counted);
    }

    public function testAlwaysPreloadsALargeLanguageWithoutCounting(): void
    {
        $decision = $this->policy([34 => 12382])->decide(TranslationPreloadMode::Always, 34, null);

        $this->assertTrue($decision->preload);
        $this->assertNull($decision->remember);
        $this->assertSame([], $this->counted);
    }

    public function testAutoPreloadsEnglishAndRemembersIt(): void
    {
        $decision = $this->policy([1 => 0])->decide(TranslationPreloadMode::Auto, 1, null);

        $this->assertTrue($decision->preload);
        $this->assertSame(['lang_id' => 1, 'preload' => true], $decision->remember);
        $this->assertSame([1], $this->counted);
    }

    public function testAutoPreloadsALanguageAtTheThreshold(): void
    {
        $max = TranslationPreloadPolicy::AUTO_MAX_DEFINITIONS;
        $decision = $this->policy([7 => $max])->decide(TranslationPreloadMode::Auto, 7, null);

        $this->assertTrue($decision->preload);
    }

    public function testAutoSkipsALanguageAboveTheThreshold(): void
    {
        $max = TranslationPreloadPolicy::AUTO_MAX_DEFINITIONS;
        $decision = $this->policy([7 => $max + 1])->decide(TranslationPreloadMode::Auto, 7, null);

        $this->assertFalse($decision->preload);
        $this->assertSame(['lang_id' => 7, 'preload' => false], $decision->remember);
    }

    public function testAutoUsesTheRememberedDecisionWithoutCounting(): void
    {
        $decision = $this->policy([7 => 0])->decide(
            TranslationPreloadMode::Auto,
            7,
            ['lang_id' => 7, 'preload' => false]
        );

        $this->assertFalse($decision->preload);
        $this->assertNull($decision->remember);
        $this->assertSame([], $this->counted);
    }

    public function testAutoRecountsAfterTheLanguageChanges(): void
    {
        $decision = $this->policy([1 => 0, 34 => 12382])->decide(
            TranslationPreloadMode::Auto,
            34,
            ['lang_id' => 1, 'preload' => true]
        );

        $this->assertFalse($decision->preload);
        $this->assertSame(['lang_id' => 34, 'preload' => false], $decision->remember);
        $this->assertSame([34], $this->counted);
    }

    /**
     * @return array<string, array{mixed}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function malformedRememberedProvider(): array
    {
        return [
            'not an array' => ['yes'],
            'string language id' => [['lang_id' => '1', 'preload' => true]],
            'missing preload' => [['lang_id' => 1]],
            'non-bool preload' => [['lang_id' => 1, 'preload' => 1]],
        ];
    }

    #[DataProvider('malformedRememberedProvider')]
    public function testAutoRecountsWhenTheRememberedValueIsMalformed(mixed $remembered): void
    {
        $decision = $this->policy([1 => 0])->decide(TranslationPreloadMode::Auto, 1, $remembered);

        $this->assertTrue($decision->preload);
        $this->assertSame(['lang_id' => 1, 'preload' => true], $decision->remember);
        $this->assertSame([1], $this->counted);
    }
}
