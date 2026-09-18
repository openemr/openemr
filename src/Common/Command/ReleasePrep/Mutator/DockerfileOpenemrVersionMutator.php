<?php

/**
 * Rel-side only: change `ARG OPENEMR_VERSION=master` to
 * `ARG OPENEMR_VERSION=<rel-branch>` in `docker/release/Dockerfile`. CI
 * overrides this ARG via `--build-arg`, so the file value is cosmetic for
 * local builds — but keeping it aligned with branch identity makes the
 * Dockerfile self-documenting and matches the long-standing convention
 * across earlier rel branches.
 *
 * Idempotent: if the ARG already names the target rel branch, no-op.
 * If it names some other non-master value (a hand-edit), throws so the
 * branch-cut workflow can surface the unexpected state for review rather
 * than silently overwriting.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command\ReleasePrep\Mutator;

use OpenEMR\Common\Command\ReleasePrep\MutatorContext;
use OpenEMR\Common\Command\ReleasePrep\MutatorInterface;
use OpenEMR\Common\Command\ReleasePrep\MutatorResult;

final readonly class DockerfileOpenemrVersionMutator implements MutatorInterface
{
    private const RELATIVE_PATH = 'docker/release/Dockerfile';

    public function name(): string
    {
        return 'docker/release/Dockerfile (ARG OPENEMR_VERSION → rel branch)';
    }

    public function apply(MutatorContext $context): MutatorResult
    {
        $relBranch = $context->relBranch;
        if ($relBranch === null) {
            throw new \RuntimeException(
                self::class . ' requires --rel-branch to be supplied via MutatorContext',
            );
        }

        $path = $context->projectDir . '/' . self::RELATIVE_PATH;
        $original = file_get_contents($path);
        if ($original === false) {
            throw new \RuntimeException('Cannot read ' . $path);
        }

        // Idempotency: already at target.
        if (preg_match('/^ARG OPENEMR_VERSION=' . preg_quote($relBranch, '/') . '$/m', $original) === 1) {
            return MutatorResult::noop();
        }

        // The only acceptable starting state for branch-cut is the
        // default `master` value. Anything else means somebody made a
        // manual edit; surface that rather than overwrite.
        if (preg_match('/^ARG OPENEMR_VERSION=master$/m', $original) !== 1) {
            // Check whether there's any ARG OPENEMR_VERSION line at all,
            // for a more informative error.
            if (preg_match('/^ARG OPENEMR_VERSION=(.+)$/m', $original, $m) === 1) {
                throw new \RuntimeException(
                    'docker/release/Dockerfile ARG OPENEMR_VERSION is set to "'
                    . $m[1] . '"; expected "master" (default) or "' . $relBranch
                    . '" (already-applied). Refusing to overwrite an unexpected value.',
                );
            }
            throw new \RuntimeException(
                'docker/release/Dockerfile has no ARG OPENEMR_VERSION line to mutate',
            );
        }

        $updated = preg_replace(
            '/^ARG OPENEMR_VERSION=master$/m',
            'ARG OPENEMR_VERSION=' . $relBranch,
            $original,
            1,
        );
        if ($updated === null || $updated === $original) {
            return MutatorResult::noop();
        }
        if (file_put_contents($path, $updated) === false) {
            throw new \RuntimeException('Cannot write ' . $path);
        }
        return new MutatorResult([self::RELATIVE_PATH]);
    }
}
