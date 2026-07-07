<?php

/**
 * Application configuration
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 Eric Stern
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

return [
    // Important: this must be a closure rather than a static definition so
    // that a compiled container is guaranteed to align with the runtime.
    'installRoot' => fn () => dirname(__DIR__),

    // Mirrors the semantic value of `interface/globals.php`'s $web_root
    // For now, only works in non-directory installations; see #12690.
    'webRoot' => '',
];
