<?php
/** **************************************************************************
 *	wmtSoapObjc.module.php
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
 * @subpackage base
 */
require_once($GLOBALS['srcdir'].'/options.inc.php');
class SoapObjcModule extends BaseModule {
	/**
	 * Constructor for the 'module' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @return object instance of module class
	 */
	public function __construct(&$sec_data, &$form_data) {
		if (!$sec_data || !$sec_data['key'])
			throw new \Exception('wmtSoapObjcModule::No module key provided for construct.');
	
		// save data pointers
		$this->sec_data = &$sec_data;
		$this->form_data = &$form_data;
				
		// set defaults
		$this->active = true;
		$this->key = $sec_data['key'];
		$this->title = $sec_data['title'];
		
		return;
	}
	
	
	/**
	 * Display a collapsable section in the form.
	 *
	 */
	public function display($open=false, $bottom=false) {
		if(!isset($use_snippets)) 
			$use_snippets = checkSettingMode('wmt::use_snippets');
		$this->toggle = ($open)? 'block' : 'none'; ?>

		<div class='wmtMainContainer wmtColorMain'>
			<?php Display::chapter($this->title, $this->key, $open); ?>
			<div id='<?php echo $this->key ?>Box' class='wmtCollapseBox wmtColorBox' style='display:<?php echo $this->toggle ?>'>
				<table style="width:100%">
					<tr>
						<td class="wmtLabel" colspan="2">
							<div style="display:inline-block;width:200px">Objective Narrative:</div>
						</td>
						<td>
						<?php 
						if($use_snippets && isSnippetModule('objective_notes')) {
							$field_prefix = '';
							$field_name = 'objective_notes';
							include(FORM_BUTTONS . 'btn_snippets.inc.php');
						}
						?>
						</td>
						<td>
						<?php include(FORM_BUTTONS . 'btn_clear_field.inc.php'); ?>
						</td>
					</tr>
					<tr>
						<td colspan="4">
							<textarea name="objective_notes" id="objective_notes" class="wmtFullInput" rows="4"><?php echo $this->form_data->objective_notes; ?></textarea>
						</td>
					</tr>
				</table>
			</div>
			<?php Display::bottom($this->title, $this->key, $open, $bottom); ?>
		</div>
<?php }
	
	
	/**
	 * Print a collapsable section in the report.
	 *
	 */
	public function report() { 
		$output = false;
		if ($this->form_data->objective_notes) $output = true;
		if (!$output) return; ?>
		
		<div class='wmtPrnMainContainer'>
			<div class='wmtPrnCollapseBar'>
				<span class='wmtPrnChapter'><?php echo $this->title ?></span>
			</div>
			<div class='wmtPrnCollapseBox'>
				<table class='wmtPrnContent' style="margin:6px 0">
					
					<tr>
					<tr>
						<td style='line-height:14px' colspan="2">
							<span class='wmtPrnLabel'>Objective Narrative:</span>
						</td>
					</tr><tr>
						<td style="width:25px"></td><td style='line-height:14px'>
							<span class='wmtPrnBody'><?php echo $this->form_data->objective_notes . "\n"; ?></span>
						</td>
					</tr>

				</table>
			</div> <!-- END COLLAPSE BOX -->
		</div> <!-- END MAIN CONTAINER -->
		
<?php }


	/**
	 * Stores data from a form object into the database.
	 *
	 */
	public function store() {
		$dt = &$_POST;

		// Push data to form
		$this->form_data->objective_notes = strip_tags($dt['objective_notes']);
		
		return;
	}
		
}
?>
