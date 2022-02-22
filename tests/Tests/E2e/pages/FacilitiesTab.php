<?php

declare(strict_types=1);

namespace OpenEMR\Tests\E2e\pages;
use OpenEMR\Tests\E2e\ui\
    {
        TabTestElement, 
        ButtonTestElement, 
        FormTestElement,
        ModalTestElement,
        PageTestElement,
    }; 

class FacilitiesTab
{
    public function findCreateFacilityValidationError($session, $data, $error)
    {
        (new TabTestElement)->open($session, 'Administration', 'Clinic', 'Facilities');
        $session = (new TabTestElement)->focus($session, 'adm');
        (new ButtonTestElement)->clickByText($session, 'Add Facility');      
        $session = (new ModalTestElement)->focus($session, 'modalframe');
        (new FormTestElement)->fill($session, 'facility-add', $data);
        (new ButtonTestElement)->clickById($session, 'form_save');
        (new PageTestElement)->refresh($session);
        (new ModalTestElement)->focus($session, 'modalframe');
        (new FormTestElement)->findValidationError($session, $error);
    }
}