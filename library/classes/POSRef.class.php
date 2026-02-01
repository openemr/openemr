<?php

/** Copyright (C) 2016 Sherwin Gaddis <sherwingaddis@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author Sherwin Gaddis <sherwingaddis@gmail.com>
 * @link    https://www.open-emr.org
 *
 */

use OpenEMR\Common\Enum\PlaceOfServiceEnum;

class POSRef
{
    public $pos_ref;

    public function __construct($state = "")
    {
        $this->pos_ref = [];
        $this->pos_ref = POSRef::init_pos();
        $this->pos_ref = array_merge($this->pos_ref, $this->state_overides($state));
    }

    public function init_pos()
    {
        $pos = [];

        // Iterate through all PlaceOfServiceEnum cases and build the array
        // AI Generated
        foreach (PlaceOfServiceEnum::cases() as $posEnum) {
            $pos[] = [
                "code" => $posEnum->value,
                 "title" => $posEnum->getTranslatedTitle(),
                "description" => $posEnum->getDescription(),
                "enum" => $posEnum  // Include the enum for easy access to untranslated values
            ];
        }
        // End AI Generated

        return $pos;
    }
    public function state_overides($state): array
    {
        $pos = [];
        switch (strtoupper((string) $state)) {
            case "CA":
                break;
            default:
                break;
        }

        return $pos;
    }

    public function get_pos_ref(): array
    {
        return $this->pos_ref;
    }

    /**
     * Get PlaceOfServiceEnum by code
     * @param string $code The place of service code (e.g., "01", "11")
     * @return PlaceOfServiceEnum|null
     */
    public static function getEnumByCode(string $code): ?PlaceOfServiceEnum
    {
        return PlaceOfServiceEnum::fromCode($code);
    }

    /**
     * Get untranslated name for a code (useful for FHIR API)
     * @param string $code The place of service code
     * @return string|null
     */
    public static function getUntranslatedName(string $code): ?string
    {
        $enum = self::getEnumByCode($code);
        return $enum?->getName();
    }

    /**
     * Get description for a code (useful for FHIR API)
     * @param string $code The place of service code
     * @return string|null
     */
    public static function getDescription(string $code): ?string
    {
        $enum = self::getEnumByCode($code);
        return $enum?->getDescription();
    }
}
