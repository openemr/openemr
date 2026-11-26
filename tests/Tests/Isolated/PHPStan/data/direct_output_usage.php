<?php

/**
 * Fixture for ForbiddenDirectOutputRuleTest.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\PHPStan\data;

/**
 * @param resource $handle
 */
function render(string $name, string $path, $handle): string
{
    echo $name;
    echo $name, $path;
    print $name;
    $printed = $name !== '' && print $name;
    printf('%s', $name);
    \vprintf('%s', [$name]);
    readfile($path);
    fpassthru($handle);
    $callback = static function () use ($name): void {
        echo $name;
    };
    $callback();

    // Returning a string is fine; so is a method that shares a name with a forbidden function.
    $printer = new class {
        public function printf(string $format): string
        {
            return $format;
        }
    };
    return sprintf('%s', $name) . $printer->printf($name) . var_export($printed, true);
}

?>
<div>
    <?= render('a', 'b', STDIN) ?>

</div>
<?php

exit;

?>
<p>unreachable but still output</p>
<?php echo 'after exit';
