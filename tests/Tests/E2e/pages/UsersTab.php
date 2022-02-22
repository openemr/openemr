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
        AlertTestElement
    }; 

class UsersTab
{
    public function findCreateUserValidationError($session, $data, $error)
    {
        (new TabTestElement)->open($session, 'Administration', 'Users');
        $session = (new TabTestElement)->focus($session, 'adm');
        (new ButtonTestElement)->clickByText($session, 'Add User');
        $session = (new ModalTestElement)->focus($session, 'modalframe');
        (new FormTestElement)->fill($session, 'new_user', $data);
        (new ButtonTestElement)->clickById($session, 'form_save');
        (new PageTestElement)->refresh($session);
        (new ModalTestElement)->focus($session, 'modalframe');
        (new FormTestElement)->findValidationError($session, $error);
    }

    public function findCreateUserPasswordValidationError($session, $data, $message)
    {
        (new TabTestElement)->open($session, 'Administration', 'Users');
        $session = (new TabTestElement)->focus($session, 'adm');
        (new ButtonTestElement)->clickByText($session, 'Add User');
        $session = (new ModalTestElement)->focus($session, 'modalframe');
        (new FormTestElement)->fill($session, 'new_user', $data);
        (new ButtonTestElement)->clickById($session, 'form_save');
        (new AlertTestElement)->messageIs($session, $message);
    }
}