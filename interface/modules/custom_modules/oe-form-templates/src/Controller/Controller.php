<?php
/**
 *
 */

namespace OpenEMR\Modules\FormTemplates\Controller;

class Controller
{

    /**
     * The template name to render
     */
    public $templateName;

    /**
     * The $GLOBALS supervar, encapsulated in the class
     */
    public $globals;

    public function __construct()
    {
        $this->globals = $GLOBALS;
    }

    public function getTemplateName()
    {
        return $this->templateName;
    }
}
