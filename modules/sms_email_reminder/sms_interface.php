<?php

interface sms_interface
{
    /**
     * @param string $phoneNo
     * @param string $sender
     * @param string $message
     * @return mixed
     */
    public function send($phoneNo, $sender, $message);
}
