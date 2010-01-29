<?php
include_once("../../globals.php");
include_once("$srcdir/transactions.inc");
?>
<html>
<head>
<?php html_header_show();?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" type="text/css" href="../../../library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/js/jquery.1.3.2.js"></script>
<script type="text/javascript" src="../../../library/js/common.js"></script>
<script type="text/javascript" src="../../../library/js/fancybox/jquery.fancybox-1.2.6.js"></script>

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
    <table>
    <tr>
        <td>
            <span class="title"><?php xl('Patient Transactions','e'); ?></span>&nbsp;</td>
        <td>
            <!-- Define CSS Buttons -->
            <a href="add_transaction.php"  <?php if (!$GLOBALS['concurrent_layout']) echo "target='Main'"; ?> class="css_button" onclick="top.restoreSession()">
            <span><?php xl('Add','e'); ?></span></a>
        </td>
        <td>
            <a href="print_referral.php" <?php if (!$GLOBALS['concurrent_layout']) echo "target='Main'"; ?> onclick="top.restoreSession()" class="css_button" >
            <span><?php xl('Print Blank Referral Form','e'); ?></span></a>
        </td>
    </tr>
    </table>

    <div style='margin-left:10px' class='text'>
    <?php if ($result = getTransByPid($pid)) { ?>
        <div id='transactions_div'></div>
    <?php } else { ?>
        <!-- English until parameterized translations are supported -->
        <span class="text">Currently there are no transcations. Please <a href='add_transaction.php'>click here</a> to add one.</span>
    <?php } ?>
    </div>
</body>
</html>