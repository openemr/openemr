<?php



include_once("../../globals.php");
include_once("$srcdir/transactions.inc");
?>
<html>
<head>
    <?php
    require_once "{$GLOBALS['srcdir']}/templates/standard_header_template.php";
    ?>

<script type="text/javascript" src="../../../library/js/common.js"></script>

<script type="text/javascript">
    function toggle( target, div ) {
        $mode = $(target).find(".indicator").text();
        if ( $mode == "collapse" ) {
            $(target).find(".indicator").text( "expand" );
            $(div).hide();
        } else {
            $(target).find(".indicator").text( "collapse" );
            $(div).show();
        }
    }

    $(document).ready(function(){

        $("#transactions_view").click( function() {
            toggle( $(this), "#transactions_div" );
        });

        // load transaction divs
        $("#transactions_div").load("transactions_full.php");
    });
</script>
</head>

<body class="body_top">
    <div class="page-header">
        <h1><?php echo xl('Patient Transactions');?></h1>
    </div>
    <div class="btn-group">
        <a href="add_transaction.php" class="btn btn-default btn-add" onclick="top.restoreSession()">
            <?php echo htmlspecialchars( xl('Add'), ENT_NOQUOTES); ?></a>
        <a href="print_referral.php" onclick="top.restoreSession()" class="btn btn-print btn-default" >
            <?php echo htmlspecialchars( xl('View Blank Referral Form'), ENT_NOQUOTES); ?></a>
    </div>

    <div class='text'>
    <?php if ($result = getTransByPid($pid)) { ?>
        <div id='transactions_div'></div>
    <?php } else { ?>
        <span class="text"><?php echo htmlspecialchars( xl('There are no transactions on file for this patient.'), ENT_NOQUOTES); ?></span>
    <?php } ?>
    </div>
</body>
</html>
