<?php

/**
 * This file is part of OpenEMR.
 *
 * @link https://github.com/openemr/openemr/tree/master
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Globals;

/**
 * Represents a global setting
 *
 * @package OpenEMR\Services
 * @subpackage Globals
 * @author Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2019 Ken Chapple <ken@mi-squared.com>
 */
class GlobalSetting
{
    protected $label = null;
    protected $dataType = null;
    protected $default = null;
    protected $description = null;
    protected $isUserSetting = false;

    public function __construct($label, $dataType, $default, $description, $isUserSetting = false)
    {
        $this->label = $label;
        $this->dataType = $dataType;
        $this->default = $default;
        $this->description = $description;
        $this->isUserSetting = $isUserSetting;
    }

    public function format()
    {
        return [
            $this->label,
            $this->dataType,
            $this->default,
            $this->description
        ];
    }

    public function isUserSetting()
    {
        return $this->isUserSetting;
    }
}
