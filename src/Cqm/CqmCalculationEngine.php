<?php

/**
 * Which engine calculates eCQMs: the Node.js cqm-execution service, the PHP
 * port, or the service with the PHP port run alongside it and its
 * differences logged (shadow).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm;

enum CqmCalculationEngine: string
{
    case Node = 'node';
    case Shadow = 'shadow';
    case Php = 'php';

    /** The engine a global setting names; the Node service for anything unknown. */
    public static function fromSetting(string $value): self
    {
        return self::tryFrom($value) ?? self::Node;
    }
}
