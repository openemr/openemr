<?php

/**
 * Interface for the ESign log
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

require_once $GLOBALS['srcdir'] . '/ESign/ViewableIF.php';
require_once $GLOBALS['srcdir'] . '/ESign/SignableIF.php';

interface LogIF extends ViewableIF
{
    public function isViewable();
    public function render(SignableIF $signable);
    public function getHtml(SignableIF $signable);
}
