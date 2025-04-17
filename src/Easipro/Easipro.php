<?php

/**
 * Easipro utility class
 *
 * For licensing options for this feature, please contact api@assessmentcenter.net
 *
 * Can get developer sandbox credentials for server https://www.assessmentcenter.net/ac_api by
 *  getting an account at https://www.assessmentcenter.net
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Shiqiang Tao <StrongTSQ@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Shiqiang Tao <StrongTSQ@gmail.com>
 * @copyright Copyright (c) 2020 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Easipro;

use MyMailer;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Http\oeHttp;

class Easipro
{
    // Package authentication
    private static function packageAuth()
    {
        return base64_encode($GLOBALS['easipro_name'] . ":" . (new CryptoGen())->decryptStandard($GLOBALS['easipro_pass']));
    }

    // Collect list of forms (returns json)
    public static function listForms()
    {
        $response = oeHttp::usingHeaders(['Authorization' => 'Basic ' . self::packageAuth()])->get($GLOBALS['easipro_server'] . '/2014-01/Forms/.json');
        $data = $response->body();
        return $data;
    }

    // Order form (returns json)
    public static function orderForm($form_oid)
    {
        $response = oeHttp::usingHeaders(['Authorization' => 'Basic ' . self::packageAuth()])->get($GLOBALS['easipro_server'] . '/2014-01/Assessments/' . $form_oid . '.json');
        $data = $response->body();
        return $data;
    }

    // Start assessment (returns json)
    public static function startAssessment($assessment_oid)
    {
        $response = oeHttp::usingHeaders(['Authorization' => 'Basic ' . self::packageAuth()])->get($GLOBALS['easipro_server'] . '/2014-01/Participants/' . $assessment_oid . '.json');
        $data = $response->body();
        return $data;
    }

    // Select response during assessment (returns json)
    public static function selectResponse($assessment_oid, $itemresponse_oid, $response)
    {
        $query = ['ItemResponseOID' => $itemresponse_oid, 'Response' => $response];
        //$query = "ItemResponseOID=" . $itemresponse_oid . "&Response=" . $response;
        $response = oeHttp::usingHeaders(['Authorization' => 'Basic ' . self::packageAuth()])->get($GLOBALS['easipro_server'] . '/2014-01/Participants/' . $assessment_oid . '.json', $query);
        $data = $response->body();
        return $data;
    }

    // Collect results after completing assessment (returns json)
    public static function collectResults($assessment_oid)
    {
        $response = oeHttp::usingHeaders(['Authorization' => 'Basic ' . self::packageAuth()])->get($GLOBALS['easipro_server'] . '/2014-01/Results/' . $assessment_oid . '.json');
        $data = $response->body();
        return $data;
    }

    // Obtain assessments that are assigned to a patient (returns array)
    public static function assessmentsForPatient($patient_id)
    {
        $res = sqlStatement("SELECT * FROM `pro_assessments` WHERE `patient_id`=?", [$patient_id]);
        for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
            $returnval[$iter] = $row;
        }
        return $returnval;
    }

    // Request assessment and send notification
    public static function requestAssessment($patient_id, $user_id, $form_oid, $form_name, $expiration, $assessment_oid, $status)
    {
        // store request
        sqlStatement(
            "INSERT INTO `pro_assessments` (`form_oid`, `form_name`, `user_id`, `deadline`, `patient_id`, `assessment_oid`, `status`, `created_at`) VALUES(?, ?, ?, ?, ?, ?, ?, NOW())",
            [$form_oid, $form_name, $user_id, $expiration, $patient_id, $assessment_oid, $status]
        );

        // email request
        $patientData = sqlQuery("SELECT `fname`, `lname`, `email` FROM `patient_data` WHERE `pid`=?", [$patient_id]);
        $pt_name = $patientData['fname'] . ' ' . $patientData['lname'];
        $pt_email = $patientData['email'];
        $email_subject = 'New assessment request';
        $email_sender = $GLOBALS['patient_reminder_sender_email'];
        $message = '<html><body>';
        $message .= '<table style="border-radius:4px;border:1px #dceaf5 solid" align="center" border="0" cellpadding="0" cellspacing="0">';
        $message .= '<tbody><tr><td>';
        $message .= '<table style="line-height:25px" align="center" border="0" cellpadding="10" cellspacing="0">';
        $message .= '<tbody><tr>';
        $message .= '<td style="color:#444444;border-collapse:collapse;font-size:11pt;font-family:proxima_nova,\'Open Sans\',\'Lucida Grande\',\'Segoe UI\',Arial,Verdana,\'Lucida Sans Unicode\',Tahoma,\'Sans Serif\';max-width:700px" align="left" valign="top" width="700">';
        $message .= 'Dear ' . text($pt_name) . ', <br><br>Your provider has ordered a assessment for you: <b>';
        $message .= text($form_name);
        $message .= '</b><br><b>Your assessment will close after ';
        $message .= text(oeFormatDateTime($expiration));
        $message .= ' ,</b> so please log in and complete it before then.';
        $message .= '<center>Go to: ' . text($GLOBALS['portal_onsite_two_address']) . '</center><br>Thanks.';
        $message .= '</td></tr></tbody></table>';
        $message .= '</td></tr></tbody></table>';
        $message .= '</body></html>';
        $mail = new MyMailer();
        $mail->AddReplyTo($email_sender, $email_sender);
        $mail->SetFrom($email_sender, $email_sender);
        $mail->AddAddress($pt_email, $pt_name);
        $mail->Subject = $email_subject;
        $mail->MsgHTML($message);
        $mail->IsHTML(true);
        $mail->AltBody = $message;
        if ($mail->Send()) {
            return true;
        } else {
            $email_status = $mail->ErrorInfo;
            error_log("EMAIL ERROR: " . errorLogEscape($email_status), 0);
            return false;
        }
    }

    // Record assessment result and send notification
    public static function recordResult($patient_id, $score, $assessment_oid, $std_err)
    {
        // process score
        $score = ((float) $score) * 10 + 50;

        // store result
        sqlStatement(
            "UPDATE `pro_assessments` SET `status`='completed', `score`=?, `error`=?, `updated_at`=NOW() WHERE `patient_id`=? AND `assessment_oid`=?",
            [$score, $std_err, $patient_id, $assessment_oid]
        );

        // email provider
        $assessmentData = sqlQuery("SELECT `user_id`, `patient_id` FROM `pro_assessments` WHERE `assessment_oid`=?", [$assessment_oid]);
        $patientData = sqlQuery("SELECT `fname`, `lname` FROM `patient_data` WHERE `pid`=?", [$assessmentData['patient_id']]);
        $userData = sqlQuery("SELECT `fname`, `lname`, `email` FROM `users` WHERE `id`=?", [$assessmentData['user_id']]);
        $pt_name = $patientData['fname'] . ' ' . $patientData['lname'];
        $user_name = $userData['fname'] . ' ' . $userData['lname'];
        $user_email = $userData['email'];
        $email_subject = 'Patient completed a measurement';
        $email_sender = $GLOBALS['patient_reminder_sender_email'];
        $message = '<html><body>';
        $message .= '<table style="border-radius:4px;border:1px #dceaf5 solid" align="center" border="0" cellpadding="0" cellspacing="0">';
        $message .= '<tbody><tr><td>';
        $message .= '<table style="line-height:25px" align="center" border="0" cellpadding="10" cellspacing="0">';
        $message .= '<tbody><tr>';
        $message .= '<td style="color:#444444;border-collapse:collapse;font-size:11pt;font-family:proxima_nova,\'Open Sans\',\'Lucida Grande\',\'Segoe UI\',Arial,Verdana,\'Lucida Sans Unicode\',Tahoma,\'Sans Serif\';max-width:700px" align="left" valign="top" width="700">';
        $message .= 'Dear ' . text($user_name) . ', <br><br>Your patient ' . text($pt_name) . ' completed a measurement: <b>';
        $message .= text($row['form_name']);
        $message .= '</b><br>';
        $message .= 'Please log in to OpenEMR and review it.';
        $message .= '<center> Thanks';
        $message .= '</td></tr></tbody></table>';
        $message .= '</td></tr></tbody></table>';
        $message .= '</body></html>';
        $mail = new MyMailer();
        $mail->AddReplyTo($email_sender, $email_sender);
        $mail->SetFrom($email_sender, $email_sender);
        $mail->AddAddress($user_email, $user_name);
        $mail->Subject = $email_subject;
        $mail->MsgHTML($message);
        $mail->IsHTML(true);
        $mail->AltBody = $message;
        if ($mail->Send()) {
            return true;
        } else {
            $email_status = $mail->ErrorInfo;
            error_log("EMAIL ERROR: " . errorLogEscape($email_status), 0);
            return false;
        }
    }
}
