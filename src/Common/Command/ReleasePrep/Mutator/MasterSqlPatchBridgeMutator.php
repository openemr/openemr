<?php

/**
 * Master-side patch-prep: rename the long-lived bridge upgrade file so
 * its "from" anchor reflects the just-shipped patch.
 *
 * Each minor line on master carries a single sql/<X_Y_(P-1)>-to-<X_(Y+1)_0>_upgrade.sql
 * file that accumulates SQL during the dev cycle. When a rel branch
 * ships a new patch (e.g., rel-810 going 8.1.0 → 8.1.1-dev), the bridge's
 * "from" anchor must advance to match: the file's contents are preserved
 * (they accumulate dev-cycle SQL) but the filename changes so callers
 * applying the upgrade catalog know the new patch is the floor.
 *
 * Concrete example, target patch 8.1.1 on rel-810 (so the next minor
 * is 8.2.0):
 *   sql/8_1_0-to-8_2_0_upgrade.sql  →  sql/8_1_1-to-8_2_0_upgrade.sql
 *
 * Idempotency:
 *   - new exists, old gone        → no-op (already renamed on a prior run)
 *   - new exists, old also exists → error (ambiguous; two parallel bridges)
 *   - old exists, new missing     → rename (the canonical path)
 *   - neither exists              → error (something is structurally wrong;
 *                                   refuse to invent a bridge file silently)
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

final readonly class MasterSqlPatchBridgeMutator implements MutatorInterface
{
    public function name(): string
    {
        return 'sql/<prev-patch>-to-<next-minor>_upgrade.sql → sql/<new-patch>-to-<next-minor>_upgrade.sql (rename)';
    }

    public function apply(MutatorContext $context): MutatorResult
    {
        $fromVersion = $context->fromVersion;
        if ($fromVersion === null) {
            throw new \RuntimeException(
                self::class . ' requires --prev-version (MutatorContext::$fromVersion) to compute the prior bridge filename',
            );
        }

        $sqlDir = $context->projectDir . '/sql';
        // The bridge anchors on (target_major, target_minor + 1, 0); the
        // master-side patch-prep is happening because a rel branch
        // shipped a patch. The bridge points "down" the minor ladder.
        $nextMinorAnchor = sprintf('%d_%d_0', $context->major, $context->minor + 1);
        $prevPatchSeg = str_replace('.', '_', $fromVersion);
        $newPatchSeg = sprintf('%d_%d_%d', $context->major, $context->minor, $context->patch);

        $oldRelPath = sprintf('sql/%s-to-%s_upgrade.sql', $prevPatchSeg, $nextMinorAnchor);
        $newRelPath = sprintf('sql/%s-to-%s_upgrade.sql', $newPatchSeg, $nextMinorAnchor);
        $oldAbs = $context->projectDir . '/' . $oldRelPath;
        $newAbs = $context->projectDir . '/' . $newRelPath;

        $oldExists = is_file($oldAbs);
        $newExists = is_file($newAbs);

        if ($newExists && $oldExists) {
            throw new \RuntimeException(
                sprintf(
                    'Both old (%s) and new (%s) bridge files exist; ambiguous state — resolve manually before re-running.',
                    $oldRelPath,
                    $newRelPath,
                ),
            );
        }
        if ($newExists) {
            // !$oldExists by the guard above. Already renamed on a prior
            // run; clean no-op.
            return MutatorResult::noop();
        }
        if (!$oldExists) {
            // !$newExists by the guard above. Refuse to invent a bridge.
            throw new \RuntimeException(
                sprintf(
                    'Neither bridge file exists (looked for %s and %s); refusing to invent one. '
                    . 'A bridge file from the previous patch should be present on master before patch-prep runs.',
                    $oldRelPath,
                    $newRelPath,
                ),
            );
        }

        // oldExists && !newExists → rename.
        if (!rename($oldAbs, $newAbs)) {
            throw new \RuntimeException(
                sprintf('Failed to rename %s → %s', $oldRelPath, $newRelPath),
            );
        }

        // Report both paths so the conductor PR summary reflects the move.
        return new MutatorResult([$oldRelPath, $newRelPath]);
    }
}
