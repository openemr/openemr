<?php

/** @package Openemr::Controller */

/** import supporting libraries */
require_once("AppBasePortalController.php");

/**
 * DefaultController is the entry point to the application
 *
 * From phreeze package
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 *
 * @package Openemr::Controller
 * @author ClassBuilder
 * @version 1.0
 */
class DefaultController extends AppBasePortalController
{

    /**
     * Override here for any controller-specific functionality
     */
    protected function Init()
    {
        parent::Init();

        // TODO: add controller-wide bootstrap code

        // TODO: if authentiation is required for this entire controller, for example:
        // $this->RequirePermission(SecureApp::$PERMISSION_USER,'SecureApp.LoginForm');
    }

    /**
     * Display the home page for the application
     */
    public function Home()
    {
        $this->Render();
    }

    /**
     * Displayed when an invalid route is specified
     */
    public function Error404()
    {
        $this->Render();
    }

    /**
     * Display a fatal error message
     */
    public function ErrorFatal()
    {
        $this->Render();
    }

    public function ErrorApi404()
    {
        $this->RenderErrorJSON('An unknown API endpoint was requested.');
    }
}
