<?php

/**
 * Batch Email processor, included from batchcom
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  cfapress
 * @author  Jason 'Toolbox' Oettinger <jason@oettinger.email>
 * @copyright Copyright (c) 2008 cfapress
 * @copyright Copyright (c) 2017 Jason 'Toolbox' Oettinger <jason@oettinger.email>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// create file header.
// menu for fields could be added in the future
require_once("../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

?>
<html>
<head>
    <title><?php echo xlt('Email Notification Report'); ?></title>
    <?php Header::setupHeader(); ?>
</head>
<body class="body_top container">
<header class="row">
    <?php require_once("batch_navigation.php");?>
    <h1 class="col-md-12">
        <a href="batchcom.php"><?php echo xlt('Batch Communication Tool')?></a>
        <small><?php echo xlt('Email Notification Report'); ?></small>
    </h1>
</header>
<main class="row mx-4">
    <ul class="col-md-12">
        <?php
        $email_sender = $_POST['email_sender'];
        $sent_by = $_SESSION['authUserID'];

        while ($row = sqlFetchArray($res)) {
            // prepare text for ***NAME*** tag
            $pt_name = $row['title'] . ' ' . $row['fname'] . ' ' . $row['lname'];
            $pt_email = $row['email'];

            $email_subject = $_POST['email_subject'];
            $email_body = $_POST['email_body'];
            $email_subject = preg_replace('/\*{3}NAME\*{3}/', $pt_name, $email_subject);
            $email_body = preg_replace('/\*{3}NAME\*{3}/', $pt_name, $email_body);

            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "To: $pt_name<" . $pt_email . ">\r\n";
            $headers .= "From: <" . $email_sender . ">\r\n";
            $headers .= "Reply-to: <" . $email_sender . ">\r\n";
            $headers .= "X-Priority: 3\r\n";
            $headers .= "X-Mailer: PHP mailer\r\n";
            if (mail($pt_email, $email_subject, $email_body, $headers)) {
                echo "<li>" . xlt('Email sent to') . ": " . text($pt_name) . " , " . text($pt_email) . "</li>";
            } else {
                $m_error = true;
                $m_error_count++;
            }
        }
        ?>
    </ul>
    <?php
    if ($m_error) {
        echo '<div class="alert alert-danger">' . xlt('Could not send email due to a server problem.') . ' ' . text($m_error_count) . ' ' . xlt('emails not sent') . '</div>';
    }
    ?>
</main>
</body>
</html>
