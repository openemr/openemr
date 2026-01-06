<?php

/**
 * Enables echoing and stringifying objects that implement the
 * ViewableIF interface.
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

require_once $GLOBALS['srcdir'] . '/ESign/Abstract/Model.php';
require_once $GLOBALS['srcdir'] . '/ESign/ViewableIF.php';

class Viewer extends Abstract_Model
{
    public $target;
    public $encounterId;
    public $logId;
    public $formId;
    public $formDir;
    public $signatures;
    public $verified;
    public $form;

    public function __construct(?array $args = null)
    {
        parent::__construct($args);

        // Force the args key => value pairs to be set as properties on the viewer objet
        $this->pushArgs(true);
    }

    protected function setAttributes(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function render(ViewableIF $viewable, ?array $attributes = null)
    {
        if ($attributes) {
            $this->setAttributes($attributes);
        }

        include $viewable->getViewScript();
    }

    public function getHtml(ViewableIF $viewable, ?array $attributes = null)
    {
        ob_start();
        $this->render($viewable, $attributes);
        $buffer = ob_get_clean();
        return $buffer;
    }
}
