<?php

/**
 * Pre-commit hook languages the runner satisfies without a project toolchain.
 *
 * `.github/workflows/pre-commit.yml` runs on a bare runner with no PHP,
 * Composer or Node installed. Hooks written in one of these languages need
 * nothing beyond the runner itself, so they are safe to execute there. Every
 * other language shells out to something the workflow does not install and
 * must appear in that workflow's `SKIP` list instead.
 *
 * Unknown languages classify as toolchain-dependent: assuming a hook needs
 * something we do not have is the safe default, and adding a case here forces
 * the exhaustive match to be revisited.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Ci;

enum SelfContainedHookLanguage: string
{
    case Fail = 'fail';
    case Pygrep = 'pygrep';

    /**
     * Whether a hook declaring this `language` needs a toolchain the
     * pre-commit workflow does not install.
     */
    public static function requiresToolchain(string $language): bool
    {
        return match (self::tryFrom($language)) {
            self::Fail, self::Pygrep => false,
            null => true,
        };
    }
}
