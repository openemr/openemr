<?php

/**
 * SignatureIF interface
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

interface SignatureIF extends VerifiableIF
{
    const ESIGN_NOLOCK = 0;
    const ESIGN_LOCK = 1;

    public function getId();
    public function getUid();
    public function getFirstName();
    public function getLastName();
    public function getValedictory();
    public function getDatetime();
    public function isLock();
    public function getAmendment();
}
