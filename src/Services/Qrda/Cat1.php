<?php


namespace OpenEMR\Services\Qrda;


use OpenEMR\Cqm\Qdm\Patient;

class Cat1
{
    protected $templatePath =
        __DIR__ . DIRECTORY_SEPARATOR .
        'qrda-export' . DIRECTORY_SEPARATOR .
        'catI-r5' . DIRECTORY_SEPARATOR;

    protected $patient;
    protected $mustache;

    public function __construct(Patient $patient)
    {
        $this->patient = $patient;
        $this->mustache = new \Mustache_Engine(array('entity_flags' => ENT_QUOTES));
    }

    protected function template()
    {
        return $this->templatePath . DIRECTORY_SEPARATOR . 'qrda_r5.mustache';
    }

    public function render()
    {
        $xml = $this->mustache->render(
            $this->template(),
            $this->patient
        );

        return $xml;
    }

    public function generateXmlDoc()
    {
        // TODO
        $this->render();
    }

    public function patient_characteristic_birthdate()
    {
        return $this->patient->get_data_elements('patient_characteristic', 'birthdate');
    }
}
