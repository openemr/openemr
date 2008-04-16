<?php
/** 
 * VEKTIS
 *
 * @author Cristian NAVALICI
 * @version 1.0 feb 2008
 *
 */

require_once("../globals.php");
require_once("$srcdir/acl.inc");
?>

<html>
<head><title>Vektis Generation</title>
<link rel=stylesheet href='<?php echo $css_header ?>' type='text/css'>
</head>

<body <?php echo $top_bg_line;?>>

<a href="<?php echo $_SERVER['HTTP_REFERER'];?>" target="Main">Terug</a>

<?php
    $vkready = vk_vektis_ready();
    if ( $vkready ) {
        vk_init(); 
        vk_generate_preamble(); 
        foreach ( $vkready as $vk ) {
            $_SESSION['vk_dbcs'][] = $vk['ax_id'];
            vk_main($vk['ax_id']);
        }
         //vk_generate_comment(); 
        vk_generate_closing(); 
        vk_display_file();
        vk_last();

        global $totalclaim;
        $total_sum_euro = $totalclaim / 100;
        echo "<br />TOTAL AMOUNT: $total_sum_euro &euro; <br>"; // display grand total
    } else {
        echo '<p>Nothing to generate.</p>';
    }
?>

<?php if ( $vkready ) { ?>
<form method="post" action="vk_save.php">
    <p>Are these results ok? Press the commit button if you want to save them to the database.</p>
    <input type="submit" onClick="return confirm('Are you sure?');" value="Commit to Database" name="comdb"/>
</form>
<?php } ?>

</body>
</html>