<?php



 require_once("../../globals.php");
 require_once("$srcdir/patient.inc");
 require_once("history.inc.php");
 require_once("$srcdir/options.inc.php");
 require_once("$srcdir/acl.inc");
 require_once("$srcdir/options.js.php");

?>
<html>
<head>
    <?php
    require_once "{$GLOBALS['srcdir']}/templates/standard_header_template.php";
    ?>
<script type="text/javascript" src="../../../library/js/common.js"></script>

<script type="text/javascript">
$(document).ready(function(){
    tabbify();
});
</script>
</head>
<body class="body_top">

<?php
 if (acl_check('patients','med')) {
  $tmp = getPatientData($pid, "squad");
  if ($tmp['squad'] && ! acl_check('squads', $tmp['squad'])) {
   echo "<p>(".htmlspecialchars(xl('History not authorized'),ENT_NOQUOTES).")</p>\n";
   echo "</body>\n</html>\n";
   exit();
  }
 }
 else {
  echo "<p>(".htmlspecialchars(xl('History not authorized'),ENT_NOQUOTES).")</p>\n";
  echo "</body>\n</html>\n";
  exit();
 }

 $result = getHistoryData($pid);
 if (!is_array($result)) {
  newHistoryData($pid);
  $result = getHistoryData($pid);
 }
?>

<?php if (acl_check('patients','med','',array('write','addonly') )) { ?>
<div class="page-header">
    <h1><?php echo htmlspecialchars(getPatientName($pid), ENT_NOQUOTES);?> <small><?php echo xlt("History & Lifestyle");?></small></h1>
</div>
<div>
<div class="btn-group">
    <a href="../summary/demographics.php" class="btn btn-default btn-back" onclick="top.restoreSession()">
        <?php echo htmlspecialchars(xl('Back To Patient'),ENT_NOQUOTES);?>
    </a>
    <a href="history_full.php" class="btn btn-default btn-edit" onclick="top.restoreSession()">
        <?php echo htmlspecialchars(xl("Edit"),ENT_NOQUOTES);?>
    </a>
</div>
</div>
<br/>
<?php } ?>

<div style='float:none; margin-top: 10px; margin-right:20px'>
    <table>
    <tr>
        <td>
            <!-- Demographics -->
            <div id="HIS">
                <ul class="tabNav">
                   <?php display_layout_tabs('HIS', $result, $result2); ?>
                </ul>
                <div class="tabContainer">
                   <?php display_layout_tabs_data('HIS', $result, $result2); ?>
                </div>
            </div>
        </td>
    </tr>
    </table>
</div>

<script language='JavaScript'>
    // Array of skip conditions for the checkSkipConditions() function.
    var skipArray = [
        <?php echo $condition_str; ?>
    ];
    checkSkipConditions();
</script>

</body>
</html>
