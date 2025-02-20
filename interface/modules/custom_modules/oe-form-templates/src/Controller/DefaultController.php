<?php
/**
 *
 */

namespace OpenEMR\Modules\FormTemplates\Controller;

use OpenEMR\Modules\FormTemplates\Bootstrap;
use OpenEMR\Modules\FormTemplates\Controller\Controller;
use OpenEMR\Modules\FormTemplates\Controller\ControllerInterface;
use stdClass;

class DefaultController extends Controller implements ControllerInterface
{
    private $service;

    public function __construct()
    {
        $this->templateName = "admin/index.html.twig";
    }

    public function index()
    {
        return [];
    }
}
