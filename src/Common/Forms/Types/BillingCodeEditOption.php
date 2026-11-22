<?php

/**
 * Layout edit options that change how a billing-code field renders
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Forms\Types;

use OpenEMR\Common\Layouts\LayoutsUtils;

/**
 * The same letters mean other things on other data types, so these cases
 * name what each option does on a billing-code field only.
 */
enum BillingCodeEditOption: string
{
    case MultipleCodes = 'E';
    case ShowDescriptions = '2';
    case DescriptionIsDefault = 'T';
    case ReadOnly = '0';

    /**
     * @return list<self> the cases set in a layout row's edit_options value
     */
    public static function enabledIn(?string $editOptions): array
    {
        return array_values(array_filter(
            self::cases(),
            static fn (self $option): bool => LayoutsUtils::isOption($editOptions, $option->value),
        ));
    }
}
