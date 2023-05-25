<?php

/**
 * emailServiceRun function (used by background service)
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2023 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function emailServiceRun(): void
{
    // collect the queue
    $res = sqlStatement("SELECT `id`, `sender`, `recipient`, `subject`, `body` FROM `email_queue` WHERE `sent` = 0");

    // send emails in the queue (to avoid race conditions, sent flag is rechecked before sending the email and then quickly set before proceeding to send the email)
    while ($ret = sqlFetchArray($res)) {
        $sql = sqlQuery("SELECT `sent` FROM `email_queue` WHERE `id` = ?", [$ret['id']]);
        if ($sql['sent'] == 1) {
            // Sent, so skip
        } else {
            // Not sent, so set the sent flag, and then send the email
            sqlStatement("UPDATE `email_queue` SET `sent` = 1 WHERE `id` = ?", [$ret['id']]);

            $mail = new MyMailer();
            $email_subject = $ret['subject'];
            $email_sender = $ret['sender'];
            $email_address = $ret['recipient'];
            $message = $ret['body'];
            $mail->AddReplyTo($email_sender, $email_sender);
            $mail->SetFrom($email_sender, $email_sender);
            $mail->AddAddress($email_address);
            $mail->Subject = $email_subject;
            $mail->MsgHTML("<html><body><div class='wrapper'>" . text($message) . "</div></body></html>");
            $mail->AltBody = $message;
            $mail->IsHTML(true);
            if (!$mail->Send()) {
                sqlStatement("UPDATE `email_queue` SET `error` = 1, `error_message`= ? WHERE `id` = ?", [$mail->ErrorInfo, $ret['id']]);
                error_log("Failed to send email notification through Mymailer emailServiceRun with error " . errorLogEscape($mail->ErrorInfo));
            }
        }
    }
}
