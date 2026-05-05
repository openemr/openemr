<?php

/**
 * Strip the `-dev` suffix from $v_tag in version.php for a release-branch
 * push. The -dev tag distinguishes development builds from cut releases;
 * removing it is the load-bearing version-string change at release time.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command\ReleasePrep\Mutator;

use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use OpenEMR\Common\Command\ReleasePrep\MutatorInterface;
use OpenEMR\Common\Command\ReleasePrep\MutatorResult;

final readonly class VersionPhpMutator implements MutatorInterface
{
    private const RELATIVE_PATH = 'version.php';

    public function name(): string
    {
        return 'version.php (strip -dev)';
    }

    public function apply(MutatorContext $context): MutatorResult
    {
        $path = $context->projectDir . '/' . self::RELATIVE_PATH;
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new \RuntimeException('Cannot read ' . $path);
        }

        $updated = preg_replace(
            '/^(\$v_major\s*=\s*)\'\d+\';/m',
            "$1'" . $context->major . "';",
            $contents,
            1,
            $majorReplaced,
        );
        $updated = preg_replace(
            '/^(\$v_minor\s*=\s*)\'\d+\';/m',
            "$1'" . $context->minor . "';",
            (string) $updated,
            1,
            $minorReplaced,
        );
        $updated = preg_replace(
            '/^(\$v_patch\s*=\s*)\'\d+\';/m',
            "$1'" . $context->patch . "';",
            (string) $updated,
            1,
            $patchReplaced,
        );
        $updated = preg_replace(
            '/^(\$v_tag\s*=\s*)\'-?dev\';/m',
            "$1'';",
            (string) $updated,
            1,
            $tagReplaced,
        );
        // Tolerate $v_tag already at '' (idempotence).
        if ($majorReplaced === 0 || $minorReplaced === 0 || $patchReplaced === 0) {
            throw new \RuntimeException(
                'version.php did not contain expected $v_major / $v_minor / $v_patch lines',
            );
        }

        if ($updated === $contents) {
            return MutatorResult::noop();
        }
        if (file_put_contents($path, $updated) === false) {
            throw new \RuntimeException('Cannot write ' . $path);
        }
        return new MutatorResult([self::RELATIVE_PATH]);
    }
}
