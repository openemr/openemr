<?php

/**
 * FactoryIF interface represents an object that is capable
 * of creating a complete ESign object. Used by the Api class
 * to assemble the ESign object.
 *
 * @see \Esign\Api::createESign( FactoryIF $factory )
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @link      https://www.open-emr.org/wiki/index.php/OEMR_wiki_page OEMR
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Medical Information Integration, LLC
 * @copyright Copyright (c) 2013 OEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace ESign;

interface FactoryIF
{
    /**
     * Returns an instance of ConfigurationIF
     */
    public function createConfiguration();

    /**
     * Returns an instance of SignableIF
     */
    public function createSignable();

    /**
     * Returns an instance of ButtonIF
     */
    public function createButton();

    /**
     * Returns an instance of LogIF
     */
    public function createLog();
}
