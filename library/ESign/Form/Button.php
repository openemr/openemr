<?php

/**
 * Form implementation of ButtonIF interface, which is used to
 * display a button that triggers esign behavior.
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
require_once $GLOBALS['srcdir'] . '/ESign/Viewer.php';

class Form_Button implements ButtonIF
{
    private $_viewer = null;

    public function __construct($formId, $formDir, $encounterId)
    {
        // Configure the viewer so it has access to these variables
        $this->_viewer = new Viewer();
        $this->_viewer->formId = $formId;
        $this->_viewer->formDir = $formDir;
        $this->_viewer->encounterId = $encounterId;
        $this->_viewer->target = "_parent";
    }

    public function isViewable()
    {
        return $GLOBALS['esign_individual'];
    }

    public function getViewScript()
    {
        return $GLOBALS['srcdir'] . '/ESign/views/form/esign_button.php';
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
