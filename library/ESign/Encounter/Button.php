<?php

/**
 * Implementation of ButtonIF for encounter module
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

require_once $GLOBALS['srcdir'] . '/ESign/ButtonIF.php';
require_once $GLOBALS['srcdir'] . '/ESign/ViewableIF.php';

class Encounter_Button implements ButtonIF
{
    private $_viewer = null;

    public function __construct($encounterId)
    {
        $this->_viewer = new Viewer();
        $this->_viewer->target = "_parent";
        $this->_viewer->encounterId = $encounterId;
    }

    public function isViewable()
    {
        return $GLOBALS['esign_all'];
    }

    public function getViewScript()
    {
        return $GLOBALS['srcdir'] . '/ESign/views/encounter/esign_button.php';
    }

    public function render(?SignableIF $signable = null)
    {
        return $this->_viewer->render($this);
    }

    public function getHtml(?SignableIF $signable = null)
    {
        return $this->_viewer->getHtml($this);
    }
}
