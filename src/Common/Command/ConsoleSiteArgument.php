<?php

/**
 * Reads the --site option from bin/console's arguments, before the
 * application bootstraps the site.
 *
 * Commands declare --site as a normal Symfony option, so Symfony accepts both
 * `--site=<site>` and `--site <site>`, and keeps the last one when it's
 * repeated. bin/console must select the same site in every case; otherwise a
 * command could bootstrap one site while its own --site option names another.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Command;

final class ConsoleSiteArgument
{
    private const DEFAULT_SITE = 'default';

    /**
     * @param list<string> $argv the console's arguments, as in $argv
     * @return string the requested site (the last one, if repeated), or
     *                `default` when none is given
     */
    public static function fromArgv(array $argv): string
    {
        $site = self::DEFAULT_SITE;
        $count = count($argv);
        for ($i = 0; $i < $count; $i++) {
            $arg = $argv[$i];
            // Arguments after `--` are positional, never options.
            if ($arg === '--') {
                break;
            }
            if (str_starts_with($arg, '--site=')) {
                $site = self::orDefault(substr($arg, strlen('--site=')));
            } elseif ($arg === '--site') {
                $site = self::orDefault($argv[$i + 1] ?? '');
                $i++; // the value isn't an option
            }
        }
        return $site;
    }

    private static function orDefault(string $site): string
    {
        return $site === '' ? self::DEFAULT_SITE : $site;
    }
}
