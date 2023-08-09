<?php

namespace OpenEMR\Events\Appointments;

use Symfony\Contracts\EventDispatcher\Event;

class AppointmentDialogCloseEvent extends Event
{
    const EVENT_NAME = 'openemr.appointment.add_edit_event.close.before';

    private $pc_eid;
    private $dialog_action;

    public function __construct()
    {
    }

    public function setAppointmentId($pc_eid)
    {
        $this->pc_eid = $pc_eid;
    }
    public function getAppointmentId()
    {
        return $this->pc_eid;
    }

    public function getDialogAction()
    {
        return $this->dialog_action;
    }

    public function setDialogAction($dialog_action)
    {
        $this->dialog_action = $dialog_action;
    }
}
