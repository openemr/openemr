<?php

/**
 * FactoryIF interface represents an object that is capable
 * of creating a complete ESign object. Used by the Api class
 * to assemble the ESign object.
 *
 * @see \Esign\Api::createESign( FactoryIF $factory )
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

interface FactoryIF
{
    public function createConfiguration(): ConfigurationIF;

    public function createSignable(): SignableIF;

    public function createButton(): ButtonIF;

    public function createLog(): LogIF;
}
