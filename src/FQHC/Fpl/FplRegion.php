<?php

/**
 * Federal Poverty Level guideline region.
 *
 * HRSA requires using the poverty guideline for the health center's location.
 * The three regions have different annual thresholds (the contiguous 48 states
 * + DC, Alaska, and Hawaii). Backed because it is persisted and seeded as data.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\FQHC\Fpl;

enum FplRegion: string
{
    case Contiguous = 'contiguous';
    case Alaska = 'alaska';
    case Hawaii = 'hawaii';

    public function label(): string
    {
        return match ($this) {
            self::Contiguous => '48 contiguous states & DC',
            self::Alaska => 'Alaska',
            self::Hawaii => 'Hawaii',
        };
    }
}
