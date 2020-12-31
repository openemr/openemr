<?php

// check acl

// three actions

// list
// edit/:id
// edit/:id/enable
// edit/:id/disable

// Router
// checkSecurity
// route($_REQUEST['action'])

// listAction
// editAction
// enableAction
// disableAction

$router = new SMARTAppRouter();
try {
    $router->dispatch($_REQUEST['action'], $_REQUEST);
}
catch (CsrfInvalidException $exception) {
    CsrfUtils::csrfNotVerified();
}
catch (AccessDeniedException $exception) {
    SystemLogger::instance()->critical($exception->getMessage(), ["trace" => $exception->getTraceAsString()]);
    die();
}

use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfInvalidException;
use OpenEMR\Common\Csrf\CsrfUtils;

class SMARTAppRouter {
    public function __construct()
    {
    }

    public function checkSecurity($request) {
        $csrfToken = $this->getCSRFToken($request);
        if (CsrfUtils::verifyCsrfToken($csrfToken)) {
            throw new CsrfInvalidException(xlt('Authentication Error'));
        }
        if (!AclMain::aclCheckCore('admin', 'super')) {
            throw new AccessDeniedException('admin', 'super');
        }
    }

    private function getCSRFToken($request) {
        return '';
    }

    // quick little dispatch router... for the 4 different types of actions that can occur here.
    public function dispatch($action, $request) {
        $this->checkSecurity($request);

        if (empty($action)) {
            return $this->listAction($request);
        }

        $parts = explode("/", $action);
        if ($parts[0] == 'list') {
            $this->listAction($request);
        } else if ($parts[0] == 'edit' && count($parts) > 1) {
            $clientId = $parts[1];
            if (count($parts) < 2) {
                return $this->editAction($clientId, $request);
            } else if ($parts[2] == 'enable') {
                return $this->enableAction($clientId, $request);
            } else if ($parts[2] == 'disable') {
                return $this->disableAction($clientId, $request);
            } else {
                return $this->notFoundAction($request);
            }
        } else {
            return $this->notFoundAction($request);
        }
    }

    public function listAction($request) {
        echo "list";
    }

    public function editAction($clientId, $request) {
        echo "edit $clientId";
    }

    public function disableAction($clientId, $request) {
        echo "disable $clientId";
    }

    public function enableAction($clientId, $request) {
        echo "enable $clientId";
    }

    public function notFoundAction($request) {
        http_response_code(404);
        // could return a 404 page here, but for now we will just skip it.
    }
}
