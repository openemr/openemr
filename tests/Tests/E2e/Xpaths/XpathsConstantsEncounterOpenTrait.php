<?php

/**
 * XpathsConstantsEncounterOpenTrait class
 *
 *
 * TODO when no longer supporting PHP 8.1
 * THIS class is needed since unable to add constants directly to the EncounterOpenTrait in PHP 8.1
 *
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\Xpaths;

class XpathsConstantsEncounterOpenTrait
{
    public const SELECT_ENCOUNTER_BUTTON_ENCOUNTEROPEN_TRAIT = '//button[@id="pastEncounters"]';

    public const SELECT_A_ENCOUNTER_ENCOUNTEROPEN_TRAIT = '//ul[contains(@class, "dropdown-menu")]/li[1]/a[1]/span[text()="Office Visit"]';
}
