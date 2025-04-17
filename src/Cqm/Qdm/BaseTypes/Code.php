<?php
/**
 * @package OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Cqm\Qdm\BaseTypes;

class Code extends AbstractType
{
    public $code;
    public $system;
    public $display = null; // Not required
    public $version = null; // Not required
    public $_type = "QDM::Code";
}
