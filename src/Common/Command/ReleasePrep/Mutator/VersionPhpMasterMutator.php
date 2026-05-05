<?php

/**
 * On master only: bump version.php to the next development version.
 * Increments $v_minor, resets $v_patch to 0, and ensures $v_tag is
 * '-dev' so master keeps marking development builds.
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

final readonly class VersionPhpMasterMutator implements MutatorInterface
{
    private const RELATIVE_PATH = 'version.php';

    public function name(): string
    {
        return 'version.php (master bump)';
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
        );
        $updated = preg_replace(
            '/^(\$v_minor\s*=\s*)\'\d+\';/m',
            "$1'" . $context->minor . "';",
            (string) $updated,
            1,
        );
        $updated = preg_replace(
            '/^(\$v_patch\s*=\s*)\'\d+\';/m',
            "$1'" . $context->patch . "';",
            (string) $updated,
            1,
        );
        $updated = preg_replace(
            '/^(\$v_tag\s*=\s*)\'\';/m',
            "$1'-dev';",
            (string) $updated,
            1,
        );

        if ($updated === $contents) {
            return MutatorResult::noop();
        }
        if (file_put_contents($path, (string) $updated) === false) {
            throw new \RuntimeException('Cannot write ' . $path);
        }
        return new MutatorResult([self::RELATIVE_PATH]);
    }
}
