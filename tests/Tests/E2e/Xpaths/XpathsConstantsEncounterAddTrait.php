<?php

/**
 * XpathsConstantsEncounterAddTrait class
 *
 *
 * TODO when no longer supporting PHP 8.1
 * THIS class is needed since unable to add constants directly to the EncounterAddTrait in PHP 8.1
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

class XpathsConstantsEncounterAddTrait
{
    public const CREATE_ENCOUNTER_BUTTON_ENCOUNTERADD_TRAIT = '//a[@title="New Encounter"]';

    public const SAVE_ENCOUNTER_BUTTON_ENCOUNTERADD_TRAIT = "//button[@id='saveEncounter']";
}
