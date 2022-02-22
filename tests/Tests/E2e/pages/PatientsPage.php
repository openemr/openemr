<?php

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\pages;
use OpenEMR\Tests\E2e\ui\
    {
        TabTestElement, 
        ButtonTestElement, 
        FormTestElement,
        PageTestElement,
}; 

class PatientsPage
{

    public function findCreatePatientValidationError($session, $data, $error)
    {
        (new TabTestElement)->open($session, 'Patient/Client', 'Patients');
        $session = (new TabTestElement)->focus($session, 'fin');
        (new ButtonTestElement)->clickById($session, 'create_patient_btn1');
        (new PageTestElement)->focusDefault($session);
        $session = (new TabTestElement)->focus($session, 'pat');
        (new FormTestElement)->fill($session, 'DEM', $data);
        (new ButtonTestElement)->clickById($session, 'create');
        (new FormTestElement)->findValidationError($session, $error);
    }
}