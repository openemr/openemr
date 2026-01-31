<?php

use OpenEMR\Common\Session\SessionWrapperFactory;

function doEmailNotificationTask(): void
{
    $scheduled_task_flag = 1;
    $_GET['type'] = 'email';
    $session = SessionWrapperFactory::getInstance()->getActiveSession();
    $_GET['site'] = $session->get('site_id');

    require_once("rc_sms_notification.php");
}

function doSmsNotificationTask(): void
{
    $scheduled_task_flag = 1;
    $_GET['type'] = 'sms';
    $session = SessionWrapperFactory::getInstance()->getActiveSession();
    $_GET['site'] = $session->get('site_id');

    require_once("rc_sms_notification.php");
}
