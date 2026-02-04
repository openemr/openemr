<?php

/**
 * Any model can inherit this class to add some common functionality
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @link      https://www.open-emr.org/wiki/index.php/OEMR_wiki_page OEMR
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Medical Information Integration, LLC
 * @copyright Copyright (c) 2013 OEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace ESign;

abstract class Abstract_Model
{
    private $_args = [];

    public function __construct(?array $args = null)
    {
        if ($args !== null) {
            $this->_args = $args;
        }
    }

    protected function pushArgs($force = false)
    {
        foreach ($this->_args as $key => $value) {
            if ($force) {
                $this->{$key} = $value;
            } else {
                if (property_exists($this, $key)) {
                    $this->{$key} = $value;
                } elseif (property_exists($this, "_" . $key)) {
                    $this->{"_" . $key} = $value;
                }
            }
        }
    }
}
