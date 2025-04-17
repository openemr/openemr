<?php

/**
 * initial_response.php
 *
 * Special script to allow callback from Sphere to avoid cross origin breakage.
 * Csrf security is maintained.
 * Call to top.restoreSession() happens to ensure directed to correct session.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

if (!empty($_GET['revert'])) {
    // processing a void or credit (will go to process_revert_response.php)
    $cancel = '';
    $front = $_GET['front'];
    $csrf = $_GET['csrf_token'];
    $transaction = $_GET;
    $transaction['querystring'] = $_SERVER['QUERY_STRING'];
    $revert = 1;
} elseif (!empty($_GET['cancel']) && ($_GET['cancel'] == 'cancel')) {
    // user cancelled the payment (will go to process_response.php)
    $cancel = 'cancel';
    $front = $_GET['front'];
    $patientIdCc = $_GET['patient_id_cc'];
    $csrf = $_GET['csrf_token'];
    $transaction['ticket'] = $_GET['ticket'];
    $revert = 0;
} elseif (!empty($_GET['transaction'])) {
    // user submitted the payment (will go to process_response.php)
    $cancel = '';
    $transaction = $_GET['transaction'];
    $customFields = json_decode($transaction['transactioncustomfield'], true);
    $front = $customFields[1];
    $patientIdCc = $customFields[2];
    $csrf = $customFields[3];
    unset($transaction['transactioncustomfield']);
    $revert = 0;
} else {
    exit;
}
?>

<html>
    <head>
    </head>
    <body>
        <?php if ($revert === 1) { ?>
            <form id="myForm" method="post" onsubmit="return top.restoreSession()" action="process_revert_response.php?front=<?php echo htmlspecialchars(urlencode($front), ENT_QUOTES); ?>&csrf_token=<?php echo htmlspecialchars(urlencode($csrf), ENT_QUOTES); ?>">
        <?php } else { ?>
            <form id="myForm" method="post" onsubmit="return top.restoreSession()" action="process_response.php?front=<?php echo htmlspecialchars(urlencode($front), ENT_QUOTES); ?>&cancel=<?php echo htmlspecialchars(urlencode($cancel), ENT_QUOTES); ?>&patient_id_cc=<?php echo htmlspecialchars(urlencode($patientIdCc), ENT_QUOTES); ?>&csrf_token=<?php echo htmlspecialchars(urlencode($csrf), ENT_QUOTES); ?>">
        <?php } ?>
        <?php
        foreach ($transaction as $a => $b) {
            echo '<input type="hidden" name="' . htmlspecialchars($a, ENT_QUOTES) . '" value="' . htmlspecialchars($b, ENT_QUOTES) . '">';
        }
        ?>
        </form>
        <script>
            document.getElementById('myForm').submit();
        </script>
    </body>
</html>
