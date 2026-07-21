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

use OpenEMR\Release\PullRequestTarget;
use OpenEMR\Release\RoleLabel;
use PHPUnit\Framework\TestCase;

final class PullRequestTargetTest extends TestCase
{
    public function testForReleaseProducesConductorDocsFinalizeInOrder(): void
    {
        $targets = PullRequestTarget::forRelease('8.1.0', 'rel-810');

        self::assertCount(3, $targets);
        self::assertSame(RoleLabel::Conductor, $targets[0]->roleLabel);
        self::assertSame('openemr/openemr', $targets[0]->repo);
        self::assertSame('release-prep/rel-810', $targets[0]->branch);
        self::assertSame('rel-810', $targets[0]->expectedBase);
        self::assertSame(1, $targets[0]->mergeOrder);

        self::assertSame(RoleLabel::Docs, $targets[1]->roleLabel);
        self::assertSame('openemr/website-openemr', $targets[1]->repo);
        self::assertSame('release-docs/8.1.0', $targets[1]->branch);
        self::assertSame('master', $targets[1]->expectedBase);
        self::assertSame(2, $targets[1]->mergeOrder);

        self::assertSame(RoleLabel::Finalize, $targets[2]->roleLabel);
        self::assertSame('openemr/openemr', $targets[2]->repo);
        self::assertSame('release-finalize/rel-810', $targets[2]->branch);
        self::assertSame('master', $targets[2]->expectedBase);
        self::assertSame(3, $targets[2]->mergeOrder);
    }
}
