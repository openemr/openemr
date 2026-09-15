<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Release;

use OpenEMR\Release\BinaryForgePin;
use OpenEMR\Release\BinaryForgeResolver;
use OpenEMR\Tests\Isolated\Release\Fakes\FakeGitHubApi;
use PHPUnit\Framework\TestCase;

/**
 * Each holding test below is a half-published forge state that a naive
 * "latest release wins" bump would have adopted, and that would then have
 * failed the multi-arch image build. The resolver's contract is that every
 * one of them resolves to the current pin instead.
 */
final class BinaryForgeResolverTest extends TestCase
{
    private const CURRENT = ['8_0_0', '03102026', '8.5'];

    public function testBumpsWhenBothArchesAreFullyPublished(): void
    {
        $resolved = $this->resolve(
            [
                self::linuxRelease('amd64', '8_3_0', '08232026'),
                self::linuxRelease('arm64', '8_3_0', '08232026'),
            ],
            ['/contents/tests?ref=v8_3_0'],
        );

        self::assertSame('8_3_0/08232026', (string)$resolved);
    }

    public function testHoldsWhenOnlyOneArchIsPublished(): void
    {
        $resolved = $this->resolve(
            [self::linuxRelease('amd64', '8_3_0', '08232026')],
            ['/contents/tests?ref=v8_3_0'],
        );

        self::assertSame('8_0_0/03102026', (string)$resolved);
    }

    public function testHoldsWhenAnArchIsMissingAnAsset(): void
    {
        $resolved = $this->resolve(
            [
                self::linuxRelease('amd64', '8_3_0', '08232026'),
                self::linuxRelease('arm64', '8_3_0', '08232026', [
                    'php-fpm-v8_3_0-linux-arm64',
                    'openemr.phar',
                ]),
            ],
            ['/contents/tests?ref=v8_3_0'],
        );

        self::assertSame('8_0_0/03102026', (string)$resolved);
    }

    public function testHoldsWhenTheOpenemrTagHasNoTestsTree(): void
    {
        $resolved = $this->resolve(
            [
                self::linuxRelease('amd64', '8_3_0', '08232026'),
                self::linuxRelease('arm64', '8_3_0', '08232026'),
            ],
            [],
        );

        self::assertSame('8_0_0/03102026', (string)$resolved);
    }

    public function testFallsBackToTheNextNewestFullyPublishedRelease(): void
    {
        $resolved = $this->resolve(
            [
                self::linuxRelease('amd64', '8_4_0', '09152026'),
                self::linuxRelease('amd64', '8_3_0', '08232026'),
                self::linuxRelease('arm64', '8_3_0', '08232026'),
            ],
            ['/contents/tests?ref=v8_4_0', '/contents/tests?ref=v8_3_0'],
        );

        self::assertSame('8_3_0/08232026', (string)$resolved);
    }

    /**
     * The forge re-cut v7_0_4 three times in December 2025, after the
     * v8_0_0 line existed. MMDDYYYY string order puts those rebuilds on
     * top; chronological order correctly leaves them behind the pin.
     */
    public function testNeverDowngradesToARecutOfAnOlderLine(): void
    {
        $forge = new FakeGitHubApi([
            self::linuxRelease('amd64', '7_0_4', '12292025'),
            self::linuxRelease('arm64', '7_0_4', '12292025'),
        ]);
        $openemr = new FakeGitHubApi([], ['/contents/tests?ref=v7_0_4']);

        $resolved = (new BinaryForgeResolver($forge, $openemr))->resolve($this->currentPin());

        self::assertSame('8_0_0/03102026', (string)$resolved);
        self::assertSame([], $openemr->existenceChecks, 'an older release should not cost an API call');
    }

    /**
     * The date-only guard let this through: a fully published v7_0_4
     * re-cut stamped after the current v8_0_0 pin looked newer and would
     * have been proposed as an upgrade.
     */
    public function testNeverDowngradesToARecutStampedAfterTheCurrentPin(): void
    {
        $resolved = $this->resolve(
            [
                self::linuxRelease('amd64', '7_0_4', '09012026'),
                self::linuxRelease('arm64', '7_0_4', '09012026'),
            ],
            ['/contents/tests?ref=v7_0_4'],
        );

        self::assertSame('8_0_0/03102026', (string)$resolved);
    }

    public function testPrefersTheHigherVersionOverTheLaterBuildDate(): void
    {
        $resolved = $this->resolve(
            [
                self::linuxRelease('amd64', '8_1_0', '09012026'),
                self::linuxRelease('arm64', '8_1_0', '09012026'),
                self::linuxRelease('amd64', '8_3_0', '08232026'),
                self::linuxRelease('arm64', '8_3_0', '08232026'),
            ],
            ['/contents/tests?ref=v8_1_0', '/contents/tests?ref=v8_3_0'],
        );

        self::assertSame('8_3_0/08232026', (string)$resolved);
    }

    public function testAdoptsALaterRebuildOfTheCurrentVersion(): void
    {
        $forge = new FakeGitHubApi([
            self::linuxRelease('amd64', '8_0_0', '09012026'),
            self::linuxRelease('arm64', '8_0_0', '09012026'),
        ]);
        $openemr = new FakeGitHubApi([], ['/contents/tests?ref=v8_0_0']);

        $resolved = (new BinaryForgeResolver($forge, $openemr))->resolve($this->currentPin());

        self::assertSame('8_0_0/09012026', (string)$resolved);
    }

    public function testIsPublishedConfirmsAPinThatIsFullyOnTheForge(): void
    {
        $forge = new FakeGitHubApi([
            self::linuxRelease('amd64', '8_3_0', '08232026'),
            self::linuxRelease('arm64', '8_3_0', '08232026'),
        ]);
        $openemr = new FakeGitHubApi([], ['/contents/tests?ref=v8_3_0']);

        $resolver = new BinaryForgeResolver($forge, $openemr);

        self::assertTrue($resolver->isPublished(new BinaryForgePin('8_3_0', '08232026', '8.5')));
    }

    /**
     * The shape the `--verify` guard exists for: a version and a date that
     * each came from a real release, paired into one that never existed.
     */
    public function testIsPublishedRejectsAPinStitchedFromTwoReleases(): void
    {
        $forge = new FakeGitHubApi([
            self::linuxRelease('amd64', '8_3_0', '08232026'),
            self::linuxRelease('arm64', '8_3_0', '08232026'),
            self::linuxRelease('amd64', '8_4_0', '09152026'),
            self::linuxRelease('arm64', '8_4_0', '09152026'),
        ]);
        $openemr = new FakeGitHubApi([], ['/contents/tests?ref=v8_3_0', '/contents/tests?ref=v8_4_0']);

        $resolver = new BinaryForgeResolver($forge, $openemr);

        self::assertFalse($resolver->isPublished(new BinaryForgePin('8_3_0', '09152026', '8.5')));
    }

    public function testIsPublishedRejectsAPinMissingAnArch(): void
    {
        $forge = new FakeGitHubApi([self::linuxRelease('amd64', '8_3_0', '08232026')]);
        $openemr = new FakeGitHubApi([], ['/contents/tests?ref=v8_3_0']);

        $resolver = new BinaryForgeResolver($forge, $openemr);

        self::assertFalse($resolver->isPublished(new BinaryForgePin('8_3_0', '08232026', '8.5')));
    }

    public function testIgnoresOtherPhpSelectors(): void
    {
        $php84 = new BinaryForgePin('8_3_0', '08232026', '8.4');
        $resolved = $this->resolve(
            [
                self::linuxRelease('amd64', '8_3_0', '08232026', $php84->requiredAssets('amd64'), false, $php84),
                self::linuxRelease('arm64', '8_3_0', '08232026', $php84->requiredAssets('arm64'), false, $php84),
            ],
            ['/contents/tests?ref=v8_3_0'],
        );

        self::assertSame('8_0_0/03102026', (string)$resolved);
    }

    public function testIgnoresNonLinuxPlatforms(): void
    {
        $resolved = $this->resolve(
            [
                [
                    'tag_name' => 'mac_os-php85-openemr-v8_3_0-arm64-08232026',
                    'draft' => false,
                    'assets' => [['name' => 'openemr.phar']],
                ],
                [
                    'tag_name' => 'freebsd15.1-php85-openemr-v8_3_0-arm64-08232026',
                    'draft' => false,
                    'assets' => [['name' => 'openemr.phar']],
                ],
            ],
            ['/contents/tests?ref=v8_3_0'],
        );

        self::assertSame('8_0_0/03102026', (string)$resolved);
    }

    public function testIgnoresDraftReleases(): void
    {
        $resolved = $this->resolve(
            [
                self::linuxRelease('amd64', '8_3_0', '08232026', null, true),
                self::linuxRelease('arm64', '8_3_0', '08232026', null, true),
            ],
            ['/contents/tests?ref=v8_3_0'],
        );

        self::assertSame('8_0_0/03102026', (string)$resolved);
    }

    public function testHoldsWhenTheForgeHasNoReleases(): void
    {
        self::assertSame('8_0_0/03102026', (string)$this->resolve([], []));
    }

    public function testChecksTheTestsTreeAtTheMatchingOpenemrTag(): void
    {
        $forge = new FakeGitHubApi([
            self::linuxRelease('amd64', '8_3_0', '08232026'),
            self::linuxRelease('arm64', '8_3_0', '08232026'),
        ]);
        $openemr = new FakeGitHubApi([], ['/contents/tests?ref=v8_3_0']);

        (new BinaryForgeResolver($forge, $openemr))->resolve($this->currentPin());

        self::assertSame(['/contents/tests?ref=v8_3_0'], $openemr->existenceChecks);
    }

    /**
     * @param list<array<string, mixed>> $releases
     * @param list<string>               $existingTestTrees
     */
    private function resolve(array $releases, array $existingTestTrees): BinaryForgePin
    {
        $resolver = new BinaryForgeResolver(
            new FakeGitHubApi($releases),
            new FakeGitHubApi([], $existingTestTrees),
        );

        return $resolver->resolve($this->currentPin());
    }

    private function currentPin(): BinaryForgePin
    {
        return new BinaryForgePin(...self::CURRENT);
    }

    /**
     * @param list<string>|null $assets defaults to everything the Dockerfile downloads
     * @return array<string, mixed>
     */
    private static function linuxRelease(
        string $arch,
        string $version,
        string $date,
        ?array $assets = null,
        bool $draft = false,
        ?BinaryForgePin $pin = null,
    ): array {
        $pin ??= new BinaryForgePin($version, $date, '8.5');

        return [
            'tag_name' => $pin->forgeTag($arch),
            'draft' => $draft,
            'assets' => array_map(
                static fn(string $name): array => ['name' => $name],
                $assets ?? $pin->requiredAssets($arch),
            ),
        ];
    }
}
