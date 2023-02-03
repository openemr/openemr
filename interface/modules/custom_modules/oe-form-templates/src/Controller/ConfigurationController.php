<?php
/**
 *
 */

namespace OpenEMR\Modules\FormTemplates\Controller;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Modules\FormTemplates\Bootstrap;
use OpenEMR\Modules\FormTemplates\Controller\Controller;
use OpenEMR\Modules\FormTemplates\Controller\ControllerInterface;
use OpenEMR\Modules\FormTemplates\Service\FormTemplatesService;
use Symfony\Component\HttpFoundation\Request;

class ConfigurationController extends Controller implements ControllerInterface
{
    private $service;

    public $templateName;

    public function __construct()
    {
        $this->templateName = "configuration/index.html.twig";
        $this->service = new FormTemplatesService();
    }

    public function index()
    {
        $templates = $this->service->getAllTemplates();
        $results['templates'] = $templates;
        return $results;
    }

    public function new()
    {
        $this->templateName = "configuration/detail.html.twig";
        $url = Bootstrap::moduleURL('configuration', 'saveTemplate');
        $forms = $this->service->getRegisteredForms();
        $layouts = $this->service->getLayoutForms();
        return [
            'save_template_url' => $url,
            'registered_forms' => array_merge($forms, $layouts),
        ];
    }

    public function saveTemplate()
    {
        /** @var Request */
        $request = Request::createFromGlobals();

        $fields = $request->request->all();
        $form = [
            'display_name' => $request->request->get('templateName', 'not null'),
            'machine_name' => $request->request->get('templateMachineName', 'default'),
            'method' => $request->request->get('templateMethod', 'POST'),
            'action' => $request->request->get('templateAction', 'some/path/file.php'),
            'active' => $request->request->get('templateActive', '1'),
        ];

        // foreach ($form as $k => $v) {
        //     $request->request->remove($k);
        // }

        $newForm = $this->service->saveNewForm($form);

        $form_data = serialize($fields);
        $request->request->add(['form_data' => $form_data]);

        $form_details = [
            'display_name' => $request->request->get('display_name', ''),
            'machine_name' => $request->request->get('machine_name', ''),
            'form_id' => $newForm,
            'acl' => $request->request->get('acl', ''),
            'beg_effective_date' => $request->request->get('beg_effective_date', ''),
            'end_effective_date' => $request->request->get('end_effective_date', ''),
            'active' => $request->request->get('active', ''),
            'form_data' => $form_data,
        ];

        $this->service->saveNewTemplate($form_details);

    }
}
