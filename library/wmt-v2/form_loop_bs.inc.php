<?php

$draw_display = TRUE;
foreach($modules as $module) {
	// GENDER FILTER, FIELD PREFIX AND ALTERNATE ARE ALL IN CODES AS
	// alternate|prefix|gender|field name|button 1|button 2
	$field_prefix = $field_name = $button1 = $button2 = '';
	unset($chp_options);
	$chp_options = array();
	if($module['codes'] != '') $chp_options = explode('|', $module['codes']);
	$cnt = 0;
	while($cnt < 10) {
		if(!isset($chp_options[$cnt])) $chp_options[$cnt] = '';
		$cnt++;
	}

	if($chp_options[0] != '') $field_name = $module['option_id'];
	if($chp_options[1] != '') $field_prefix = $chp_options[1];
	if($chp_options[2] != '' && $chp_options[2] != $pat_sex) continue;
	if($chp_options[3] != '') $button1 = $chp_options[3];
	if($chp_options[4] != '') $button2 = $chp_options[4];

	if($button1) $button1 = FORM_BUTTONS . 'btn_'.$button1.'.inc.php';
	if($button2) $button2 = FORM_BUTTONS . 'btn_'.$button2.'.inc.php';

	$display_toggle = 'tmp_'.$module['option_id'].'_disp_mode';
	$use_bottom_bar = false;
	if(strpos($module['option_id'], 'ros') !== false) $use_bottom_bar = 1; 
	if(strpos($module['option_id'], 'exam') !== false) $use_bottom_bar = 1;

	//generateChapter($module['title'], $module['option_id'], $dt[$display_toggle], 'wmtCollapseBar', 'wmtChapter', true, $use_bottom_bar);
	?>
	<div id="case_accordion_<?php echo $module['option_id']; ?>" class="accordion mb-2">
	  	<div class="card">
		    <div class="card-header d-flex align-items-center" id="<?php echo "header_".$module['option_id']; ?>" data-toggle="collapse" data-target="#<?php echo "section_".$module['option_id']; ?>" aria-expanded="true" aria-controls="<?php echo "section_".$module['option_id']; ?>">
		      <h6 class="mb-0 d-inline-block mr-auto"><?php echo xl($module['title']); ?></h6>
		    </div>

		    <div id="<?php echo "section_".$module['option_id']; ?>" class="collapse show " aria-labelledby="<?php echo "header_".$module['option_id']; ?>" data-parent="#case_accordion_<?php echo $module['option_id']; ?>">
		      <div class="card-body">
		        <?php
		        $this_module = $module['option_id'];
				$target_container = $module['option_id'] . 'Box';
				if($chp_options[0]) $this_module = $chp_options[0];
				if($chp_options[0] != '') {
					if(is_file($GLOBALS['srcdir'].'/wmt-v2/' . $this_module . '_bs.inc.php')) {
						include($GLOBALS['srcdir'].'/wmt-v2/' . $this_module . '_bs.inc.php');
					} else {
				 		include($GLOBALS['srcdir'].'/wmt-v2/' . $this_module . '.inc.php');
				 	}
				} else {
					// Is there a form specific module?
					if(is_file('./modules/' . $this_module . '_module.inc.php')) {
						include('./modules/' . $this_module . '_module.inc.php');
					} else {
						if(is_file(FORM_MODULES .  $this_module . '_module_bs.inc.php')) {
							include(FORM_MODULES .  $this_module . '_module_bs.inc.php');
						} else {
							include(FORM_MODULES .  $this_module . '_module.inc.php');
						}
					}
				}


				if($use_bottom_bar) {
					$use_bottom_bar = 2;
					//generateChapter($module['title'], $module['option_id'], $dt[$display_toggle], 'wmtCollapseBar wmtBottomBar', 'wmtChapter', true, $use_bottom_bar);
				}

				generateHiddenInput($display_toggle, $dt[$display_toggle]);
				
		        ?>
		      </div>
		    </div>
		</div>
	</div>
	<?php
} 
?>
