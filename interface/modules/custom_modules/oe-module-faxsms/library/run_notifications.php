<?php

function doEmailNotificationTask(): void
{
    $scheduled_task_flag = 1;
    $_GET['type'] = 'email';
    $_GET['site'] = $_SESSION['site_id'];

    require_once("rc_sms_notification.php");
}

function doSmsNotificationTask(): void
{
    $scheduled_task_flag = 1;
    $_GET['type'] = 'sms';
    $_GET['site'] = $_SESSION['site_id'];

    require_once("rc_sms_notification.php");
}
