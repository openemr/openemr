<style>
    a.active {
        padding: 8px 25px !important;
        top: -2px;
        position: relative;
        border-radius: 5px 5px 0 0 !important;
    }
</style>
<div  class="patient-pills">
    <?php 
    $query_str   = explode('&',$_SERVER['QUERY_STRING']);
    $pill_str    = $query_str[0];
    ?>
<table cellspacing='0' cellpadding='0' border='0'>
 <tr>
  <td class="small" colspan='4'>
    <span class="css_button_link">
        <a href="../summary/demographics.php?home" onclick='top.restoreSession()' class="<?php echo ($pill_str=='home')?'active':'no'; ?>">
        <?php echo htmlspecialchars(xl('Patient'),ENT_NOQUOTES);?></a>
        
        <span class="css_button_separator">|</span>
        
        <a href="../history/history.php?history" onclick='top.restoreSession()' class="<?php echo ($pill_str=='history')?'active':'no'; ?>">
        <?php echo htmlspecialchars(xl('History'),ENT_NOQUOTES); ?></a>
        
        <span class="css_button_separator">|</span>
        
        <?php //note that we have temporarily removed report screen from the modal view ?>
        <a href="../report/patient_report.php?report" onclick='top.restoreSession()' class="<?php echo ($pill_str=='report')?'active':'no'; ?>">
        <?php echo htmlspecialchars(xl('Report'),ENT_NOQUOTES); ?></a>
        
        <span class="css_button_separator">|</span>
        
        <?php //note that we have temporarily removed document screen from the modal view ?>
        <a href="../../../controller.php?document&list&patient_id=<?php echo $pid;?>&documents" onclick='top.restoreSession()' class="<?php echo ($pill_str=='documents')?'active':'no'; ?>">
        <?php echo htmlspecialchars(xl('Documents'),ENT_NOQUOTES); ?></a>
        
        <span class="css_button_separator">|</span>
        
        <a href="../transaction/transactions.php?transactions" onclick='top.restoreSession()' class="<?php echo ($pill_str=='transactions')?'active':'no'; ?>">
        <?php echo htmlspecialchars(xl('Transactions'),ENT_NOQUOTES); ?></a>
        
        <span class="css_button_separator">|</span>
        <a href="../summary/stats_full.php?active=all&active=Issues" onclick='top.restoreSession()' class="<?php echo ($_REQUEST['active']=='Issues')?'active':'no'; ?>">
        <?php echo htmlspecialchars(xl('Issues'),ENT_NOQUOTES); ?></a>
        
        <span class="css_button_separator">|</span>
        
        <a href="../../reports/pat_ledger.php?form=1&patient_id=<?php echo attr($pid);?>&active=ladger" onclick='top.restoreSession()' class="<?php echo ($_REQUEST['active']=='ladger')?'active':'no'; ?>">
        <?php echo xlt('Ledger'); ?></a>
        
    </span>

<!-- DISPLAYING HOOKS STARTS HERE -->
<?php
	$module_query = sqlStatement("SELECT msh.*,ms.menu_name,ms.path,m.mod_ui_name,m.type FROM modules_hooks_settings AS msh
					LEFT OUTER JOIN modules_settings AS ms ON obj_name=enabled_hooks AND ms.mod_id=msh.mod_id
					LEFT OUTER JOIN modules AS m ON m.mod_id=ms.mod_id 
					WHERE fld_type=3 AND mod_active=1 AND sql_run=1 AND attached_to='demographics' ORDER BY mod_id");
	$DivId = 'mod_installer';
	if (sqlNumRows($module_query)) {
		$jid 	= 0;
		$modid 	= '';
		while ($modulerow = sqlFetchArray($module_query)) {
			$DivId 		= 'mod_'.$modulerow['mod_id'];
			$new_category 	= $modulerow['mod_ui_name'];
			$modulePath 	= "";
			$added      	= "";
			if($modulerow['type'] == 0) {
				$modulePath 	= $GLOBALS['customModDir'];
				$added		= "";
			}
			else{ 	
				$added		= "index";
				$modulePath 	= $GLOBALS['zendModDir'];
			}
			$relative_link 	= "../../modules/".$modulePath."/".$modulerow['path'];
			$nickname 	= $modulerow['menu_name'] ? $modulerow['menu_name'] : 'Noname';
			$jid++;
			$modid = $modulerow['mod_id'];			
			?>
			|
			<a href="<?php echo $relative_link; ?>" onclick='top.restoreSession()'>
			<?php echo htmlspecialchars($nickname,ENT_NOQUOTES); ?></a>
		<?php	
		}
	}
	?>
<!-- DISPLAYING HOOKS ENDS HERE -->

  </td>
 </tr>
 
</table> 
</div>    