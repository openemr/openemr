<?php

/**
 * SignableIF Interface represents an object that can be signed, locked
 * and verified
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

require_once $GLOBALS['srcdir'] . '/ESign/VerifiableIF.php';

interface SignableIF extends VerifiableIF
{
    public function getSignatures();
    public function isLocked();
    public function sign($userId, $amendment = null);
}
