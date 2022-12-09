<?php
/** **************************************************************************
 *	HPI.MODULE.PHP
 *
 *	Copyright (c)2017 - Medical Technology Services <MDTechSvcs.com>
 *
 *	This program is free software: you can redistribute it and/or modify it under the 
 *  terms of the GNU General Public License as published by the Free Software Foundation, 
 *  either version 3 of the License, or (at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT ANY
 *	WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 *  PARTICULAR PURPOSE. DISTRIBUTOR IS NOT LIABLE TO USER FOR ANY DAMAGES, INCLUDING 
 *  COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL DAMAGES, 
 *  CONNECTED WITH OR RESULTING FROM THIS AGREEMENT OR USE OF THIS SOFTWARE.
 *
 *	See the GNU General Public License <http://www.gnu.org/licenses/> for more details.
 *
 *  @package wmt
 *  @subpackage modules
 *  @version 2.0.0
 *  @category Module Base Class
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ******************************************************************************************** */

/**
 * All new classes are defined in the WMT namespace
 */
namespace wmt;

/**
 * Provides standardized processing for many forms.
 *
 * @package wmt
 * @subpackage modules
 */
class HPIModule extends BaseModule {
	/**
	 * Constructor for the 'module' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @return object instance of form class
	 */
	public function __construct(&$sec_data, &$form_data) {
		if (!$sec_data || !$sec_data['key'])
			throw new \Exception('wmtHPIModule::No module key provided for construct.');
	
		// save data pointers
		$this->sec_data = &$sec_data;
		$this->form_data = &$form_data;
				
		// set defaults
		$this->active = true;
		$this->key = $sec_data['key'];
		$this->title = $sec_data['title'];
			
		// retrieve cc/hpi data
		$limit = ($form_data->id)? $form_data->encounter : '';
		$this->allergies = GetList($form_data->pid, 'allergy', $limit);

		return;
	}
	
	
	/**
	 * Display a collapsable section in the form.
	 *
	 * @param boolean $toggle - true section open, false section collapsed
	 */
	public function display($open=false, $bottom=false) {
		$this->toggle = ($open)? 'block' : 'none';
		
		echo "<div class='wmtMainContainer wmtMainColor'>\n";
		Display::chapter($this->title, $this->key, $open);
		echo "<div id='".$this->key."Box' class='wmtCollapseBox wmtColorBox' style='padding:0 10px 10px 10px;display:".$this->toggle.";'>\n";
		
		// CONTENT GOES HERE !!!
		$field_prefix = 'fyi_';
		$pid = $this->form_data->pid;
		$base_action = $GLOBALS['base_action'];
		$dt['fyi_cc'] = $this->form_data->cc_notes;
		$dt['fyi_hpi'] = $this->form_data->hpi_notes; 
		$dt['fyi_rec_review'] = $this->form_data->rec_review; 
		$frmdir = $this->form_data->form_name; ?>
		
		<script>

			// This invokes the find-code popup.
			function get_hpi() {
				dlgopen('../../../custom/hpi_choice_popup.php', '_blank', 800, 400);
			}

			// This is for callback by the HPI look-up  popup.
			function set_hpi(hpiValue) {
				var f = document.forms[0];
				var decodedHpi = window.atob(hpiValue);
				if (decodedHpi) {
					f.elements['fyi_hpi'].value = decodedHpi;
				}
			}

		</script>
<?php 
		include($GLOBALS['srcdir'] . '/wmt-v2/form_modules/cc_module.inc.php');
		include($GLOBALS['srcdir'] . '/wmt-v2/form_modules/hpi_module.inc.php');
		
		echo "</div>\n";
		Display::bottom($this->title, $this->key, $open, $bottom);
		echo "</div>\n";
	}
	
	
	/**
	 * Print a collapsable section in the report.
	 *
	 */
	public function report() { 
		$dt['fyi_cc'] = $this->form_data->cc_notes;
		$dt['fyi_hpi'] = $this->form_data->hpi_notes;
		$dt['fyi_rec_review'] = $this->form_data->rec_review;
		
		$output = false;
		if ($this->form_data->cc_notes) $output = true;
		else if ($this->form_data->hpi_notes) $output = true;
		else if ($this->form_data->rec_review) $output = true;
		
		if (!$output) return; ?>
		
		<div class='wmtPrnMainContainer'>
			<div class='wmtPrnCollapseBar'>
				<span class='wmtPrnChapter'><?php echo $this->title ?></span>
			</div>
			<div class='wmtPrnCollapseBox'>
				<table class='wmtPrnContent'>
					
					<tr>
						<td style='line-height:14px' colspan="2">
							<span class='wmtPrnLabel'>Chief Complaint:
							</span>
						</td>
					</tr><tr>
						<td style="width:25px"></td><td style='line-height:14px'>
							<span class='wmtPrnBody'><?php echo $this->form_data->cc_notes . "\n"; ?></span>
						</td>
					</tr>
			
					<tr>
						<td style='line-height:14px;padding-top:10px' colspan="2">
							<span class='wmtPrnLabel'>History of Present Illness:</span>
						</td>
					</tr><tr>
						<td style="width:25px"></td><td style='line-height:14px'>
							<span class='wmtPrnBody'><?php echo $this->form_data->hpi_notes . "\n"; ?></span>
						</td>
					</tr>
<?php if ($this->form_data->rec_review) { ?>
					<tr>
						<td style='line-height:14px;padding-top:10px' colspan="2">
							<span class='wmtPrnLabel'>
								 Medical Records Reviewed <i style='margin-left:10px' class='fa fa-fw fa-check'></i>
							</span>
						</td>
					</tr>
<?php } ?>
				</table>
			</div> <!-- END COLLAPSE BOX -->
		</div> <!-- END MAIN CONTAINER -->
<?php 		
	}
	

	/**
	 * Update data from a form object into the database.
	 *
	 * @return int $id identifier for object
	 */
	public function update($form_mode=false) {
		$pid = &$this->form_data->pid;
		$encounter = &$this->form_data->encounter;
		$dt = &$_POST;

		if ($form_mode === false) {
			return;
			
		} else if ($form_mode == 'all') {
			$all_id = AddAllergy($pid, $dt['all_begdate'], $dt['all_title'], $dt['all_comm'],$dt['all_react']);
			if ($all_id) { LinkListEntry($pid, $all_id, $encounter, 'allergy'); }
			
			$dt['all_begdate']='';
			$dt['all_title']='';
			$dt['all_comm']='';
			$dt['all_react']='';
			
			$form_focus='all_begdate';
		
		} else if ($form_mode == 'updateall') {
			$cnt = trim($_GET['itemID']);
			UpdateAllergy($pid, $dt['all_id_'.$cnt], $dt['all_comments_'.$cnt]);
			$form_focus = 'all_comments_'.$cnt;
		
		} else if ($form_mode == 'unlinkall') {
			$cnt = trim($_GET['itemID']);
			UnlinkListEntry($pid, $dt['all_id_'.$cnt], $encounter, 'allergy');
		
		} else if ($form_mode == 'unlinkallall') {
			$max = 0;
			if (isset($dt['tmp_allergy_cnt'])) { $max= $dt['tmp_allergy_cnt']; }
			$cnt = 1;
			while ($cnt <= $max) {
				UnLinkListEntry($pid, $dt['all_id_'.$cnt], $encounter, 'allergy');
				$cnt++;
			}
		}
		
		// refresh allergy data
		$limit = ($this->form_data->id)? $encounter : '';
		$this->allergies = GetList($pid, 'allergy', $limit);
		
		return $form_focus;
	}

	
	/**
	 * Stores data from a form object into the database.
	 *
	 * @return int $id identifier for object
	 */
	public function store() {
		$id = &$this->form_data->id;
		$pid = &$this->form_data->pid;
		$encounter = &$this->form_data->encounter;
		$dt = &$_POST;

		// Push data to form
		$this->form_data->cc_notes = strip_tags($dt['fyi_cc']);
		$this->form_data->hpi_notes = strip_tags($dt['fyi_hpi']);
		$this->form_data->rec_review = strip_tags($dt['fyi_rec_review']);
		
	}
		
}

?>