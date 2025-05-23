<?php

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 * dispatchPatientDocumentEvent() version for smarty templates
 *
 * Copyright (C) 2019 Brady Miller <brady.g.miller@gmail.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 */

use OpenEMR\Events\PatientDocuments\PatientDocumentEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Smarty {dispatchPatientDocumentEvent} function plugin
 *
 * Type:     function<br />
 * Name:     dispatchPatientDocumentEvent<br />
 * Purpose:  Event listener for PatientDocumentEvent<br />
 *
 * Examples:
 *
 * {dispatchPatientDocumentEvent event="javascript_ready_fax_dialog"}
 * {dispatchPatientDocumentEvent event="actions_render_fax_anchor"}
 *
 * @param array
 * @param Smarty
 */


function smarty_function_dispatchPatientDocumentEvent($params, &$smarty)
{
    if (empty($params['event'])) {
        trigger_error("dispatchPatientDocumentEvent: missing 'event' parameter", E_USER_WARNING);
        return;
    } else {
        $event = $params['event'];
    }

    $eventDispatcher = $GLOBALS['kernel']->getEventDispatcher();
    if ($event == "javascript_ready_fax_dialog") {
        $eventDispatcher->dispatch(new GenericEvent(), PatientDocumentEvent::JAVASCRIPT_READY_FAX_DIALOG);
    } elseif ($event == "actions_render_fax_anchor") {
        $eventDispatcher->dispatch(new GenericEvent(), PatientDocumentEvent::ACTIONS_RENDER_FAX_ANCHOR);
    } else {
        trigger_error("dispatchPatientDocumentEvent: invalid 'event' parameter", E_USER_WARNING);
        return;
    }
}
