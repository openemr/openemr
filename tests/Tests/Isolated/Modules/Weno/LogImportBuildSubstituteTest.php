<?php

/**
 * The WENO prescription log import keeps the substitution column's meaning.
 *
 * The import used `$line[14] = 'Allowed' ? 1 : 0`, an assignment, so every imported
 * prescription was stored as "substitution allowed". substituteValue() maps the column to
 * the prescriptions.substitute codes (1 allowed, 2 not allowed, 0 not stated).
 *
 * Pure logic only; nothing here touches the database, the network or globals.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace {
    $wenoModuleSrc = dirname(__DIR__, 5) . '/interface/modules/custom_modules/oe-module-weno/src/';
    if (!is_dir($wenoModuleSrc)) {
        throw new RuntimeException(
            'Weno module source not found at ' . $wenoModuleSrc
            . ' - this test moved and the relative path needs updating.'
        );
    }
    spl_autoload_register(static function (string $class) use ($wenoModuleSrc): void {
        $prefix = 'OpenEMR\\Modules\\WenoModule\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }
        $file = $wenoModuleSrc . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    });
}

namespace OpenEMR\Tests\Isolated\Modules\Weno {

    use OpenEMR\Modules\WenoModule\Services\LogImportBuild;
    use PHPUnit\Framework\Attributes\DataProvider;
    use PHPUnit\Framework\Attributes\Group;
    use PHPUnit\Framework\Attributes\Test;
    use PHPUnit\Framework\TestCase;

    #[Group('isolated')]
    #[Group('weno')]
    class LogImportBuildSubstituteTest extends TestCase
    {
        /**
         * @return array<string, array{mixed, int}>
         */
        public static function columns(): array
        {
            return [
                'allowed' => ['Allowed', 1],
                'allowed, other case and spacing' => [' allowed ', 1],
                'not allowed' => ['Not Allowed', 2],
                'dispense as written' => ['DAW', 2],
                'empty' => ['', 0],
                'missing column' => [null, 0],
            ];
        }

        #[Test]
        #[DataProvider('columns')]
        public function substituteFollowsTheLogColumn(mixed $column, int $expected): void
        {
            $this->assertSame($expected, LogImportBuild::substituteValue($column));
        }
    }
}
