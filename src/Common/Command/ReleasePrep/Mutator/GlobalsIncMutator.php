<?php

/**
 * Switch the `allow_debug_language` default to '0' in
 * library/globals.inc.php. The wiki release checklist requires this so
 * production releases don't ship with the dummy/debug language enabled.
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

final readonly class GlobalsIncMutator implements MutatorInterface
{
    private const RELATIVE_PATH = 'library/globals.inc.php';

    public function name(): string
    {
        return 'library/globals.inc.php (allow_debug_language → 0)';
    }

    public function apply(MutatorContext $context): MutatorResult
    {
        $path = $context->projectDir . '/' . self::RELATIVE_PATH;
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new \RuntimeException('Cannot read ' . $path);
        }

        // The block reads:
        //
        //     'allow_debug_language' => [
        //         xl('Allow Debugging Language'),
        //         'bool',                           // data type
        //         '1',                              // default = true during development...
        //
        // Match the entire block so the replacement is anchored on the
        // 'allow_debug_language' key, then swap '1' → '0' on the third
        // line of that block.
        $pattern =
            '/(\'allow_debug_language\'\s*=>\s*\[\s*'
            . 'xl\([^)]*\),\s*'
            . "'bool',\s*\/\/\s*data type\s*"
            . ")'1'(,\s*\/\/\s*default = true)/";
        $updated = preg_replace($pattern, "$1'0'$2", $contents, 1, $count);
        if ($updated === null) {
            throw new \RuntimeException('preg_replace failed for globals.inc.php');
        }
        if ($count === 0) {
            // Already at 0, or the structure changed. Verify it's at 0,
            // not changed.
            if (preg_match('/\'allow_debug_language\'\s*=>\s*\[\s*xl\([^)]*\),\s*\'bool\',[^\']*\'0\'/', $contents) !== 1) {
                throw new \RuntimeException(
                    "Expected 'allow_debug_language' default to be '1' or '0' in globals.inc.php; structure changed",
                );
            }
            return MutatorResult::noop();
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
