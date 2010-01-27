<?php
// Copyright (C) 2010 Open Support LLC
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once ($GLOBALS['srcdir'] . "/classes/class.phpmailer.php");

// Add these two lines to Authenticate in phpmailer.php, lines 633-634
// Customized for Web hosts that don't require SMTP authentication
// if ($SMTP_Auth=="No") { $connection = true; }
// Also, remove "25" in line 185 and change $Port to $this->Port on line 612 so that it can read Admin's setting

class MyMailer extends PHPMailer
{
    var $Mailer;
    var $SMTPAuth;
    var $Host;
    var $Username;
    var $Password;
    var $Port;
    var $CharSet;

    function MyMailer()
    {
        $this->emailMethod();
    }
    
    function emailMethod()
    {
        global $EMAIL_METHOD, $HTML_CHARSET;
        $this->CharSet = $HTML_CHARSET;
        switch($EMAIL_METHOD)
        {
            case "PHPMAIL" :
            {
                $this->Mailer = "mail";
            }
            break;
            case "SMTP" :
            {
				global $SMTP_Auth, $SMTP_HOST, $SMTP_USER, $SMTP_PASS, $SMTP_PORT;
                $this->Mailer = "smtp";
                $this->SMTPAuth = $SMTP_Auth;
                $this->Host = $SMTP_HOST;
                $this->Username = $SMTP_USER;
                $this->Password = $SMTP_PASS;
                $this->Port = $SMTP_PORT;
            }
            break;
            case "SENDMAIL" :
            {
                $this->Mailer = "sendmail";
            }
            break;
        }
    }
}

?>
