<?php
/**
 * =======================================
 * OpenEMR FS Trait
 * =======================================
 * Trait to abstract away the pushd/popd filesystem behavior to support walking into a directory structure on a given class.
 *
 * Other common filesystem functionality could be added here as a common spot to manage filesystem manipulation.
 *
 * ============================================================================
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Luis M. Santos, MD <lsantos@medicalmasses.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Console\Command\Traits;

use RuntimeException;

trait CommandLineFSTrait
{
    protected array $cwd = [];

    public function pushd(string $path): string
    {
        $full_path = realpath($path);
        if (is_dir($full_path)) {
            $this->cwd[] = realpath(getcwd());
            chdir(realpath($full_path));
            return $full_path;
        } else {
            throw new RuntimeException("$full_path is not a directory");
        }
    }

    public function popd()
    {
        if (!empty($this->cwd)) {
            $previous_cwd = array_pop($this->cwd);
            chdir($previous_cwd);
        }
    }
}
