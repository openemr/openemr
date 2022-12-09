<?php
/** **************************************************************************
 *	wmtPediImg.module.php
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
require_once($GLOBALS['srcdir'].'/classes/Document.class.php');
class PediImgModule extends BaseModule {
	/**
	 * Constructor for the 'module' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @return object instance of module class
	 */
	public function __construct(&$sec_data, &$form_data) {
		if (!$sec_data || !$sec_data['key'])
			throw new \Exception('wmtPediImgModule::No module key provided for construct.');
	
		// save data pointers
		$this->sec_data = &$sec_data;
		$this->form_data = &$form_data;
				
		// set defaults
		$this->active = true;
		$this->key = $sec_data['key'];
		$this->title = $sec_data['title'];
		$limit = ($form_data->id)? $form_data->encounter : '';
		
		// load image information
		$sql = "SELECT id AS img_id, type, url, date, foreign_id AS pid, encounter_id AS enc, documentationOf AS img_type, comments AS img_nt ";
		$sql .= "FROM documents WHERE foreign_id = ? AND encounter_id = ? ORDER BY date";
		$binds = array($this->form_data->pid, $form_data->encounter);
		
		// load only linked image information
		if (false && $limit != '') {
			$sql = "SELECT docs.id, docs.type, url, docs.date, foreign_id AS pid, docs.encounter_id AS enc, documentationOf as img_type, comments AS img_nt ";
			$sql .= "FROM documents docs ";
			$sql .= "LEFT JOIN form_wmt_ll wmt ON docs.id = wmt.list_id "; 
			$sql .= "WHERE wmt.pid = ? AND wmt.encounter_id = ? AND wmt.list_type = ? ORDER BY date";
			$binds = array($this->form_data->pid, $limit, 'wmt_img_history');
		}
		
		$iter = 0;
		$this->images = array();
		$res = sqlStatementNoLog($sql, $binds);
		while ($row = sqlFetchArray($res)) {
			$this->images[$iter] = $row;
			$sql = "SELECT COUNT(*) FROM form_wmt_ll WHERE form_wmt_ll.list_type=? AND form_wmt_ll.list_id=?";
			$chk = sqlStatementNoLog($sql, array('wmt_img_history', $this->images[$iter]['id']));
			$num = sqlFetchArray($chk);
			$this->images[$iter]['img_num_links'] = $num['COUNT(*)'];
			$iter++;
		}
		
		return;
	}
	
	
	/**
	 * Display a collapsable section in the form.
	 *
	 */
	public function display($open=false, $bottom=false) {
		$this->toggle = ($open)? 'block' : 'none'; 
		$base_action = $GLOBALS['base_action'];
		$document_url = $GLOBALS['webroot'].'/controller.php?document&retrieve&patient_id='.$this->form_data->pid.'&document_id='; ?>

		<div class='wmtMainContainer wmtColorMain'>
			<?php Display::chapter($this->title, $this->key, $open); ?>
			<div id='<?php echo $this->key ?>Box' class='wmtCollapseBox wmtColorBox' style='display:<?php echo $this->toggle ?>;padding:0 0 10px'>
				<table style="border-collapse:collapse;width:100%">
					<tr class='wmtColorHeader' style='line-height:15px'>
						<td class="wmtLabel wmtC wmtBorder1B" style="width: 90px">Date</td>
						<td class="wmtLabel wmtC wmtBorder1L wmtBorder1B" style="width:20%">Name</td>
						<td class="wmtLabel wmtC wmtBorder1L wmtBorder1B" style="width:20%">Type</td>
						<td class="wmtLabel wmtC wmtBorder1L wmtBorder1B">Notes</td>
						<td class="wmtBorder1L wmtBorder1B" style="width: 115px">&nbsp;</td>
					</tr>

<?php 
	$cnt = 1;
	if (isset($this->images) && (count($this->images) > 0)) {
		foreach($this->images as $image) {
			$name = substr($image['url'], strrpos($image['url'], '/') + 1);
?>
					<tr>
						<td class="wmtBorder1B">
							<input name="img_id_<?php echo $cnt; ?>" id="img_id_<?php echo $cnt; ?>" type="hidden" readonly="readonly" value="<?php echo $image['img_id']; ?>" />
							<input name="img_num_links_<?php echo $cnt; ?>" id="img_num_links_<?php echo $cnt; ?>" type="hidden" tabindex="-1" value="<?php echo $image['img_num_links']; ?>" />
							<?php echo substr($image['date'],0,10); ?>
						</td>
						<td class="wmtBorder1L wmtBorder1B">
							<?php echo $name; ?>
						</td>
						<td class="wmtBorder1L wmtBorder1B">
							<select class="wmtFullInput" name="img_type_<?php echo $cnt; ?>" id="img_type_<?php echo $cnt; ?>" tabindex="-1">
								<?php ListSelAlpha($image['img_type'],'Image_Types'); ?>
							</select>
						</td>
						<td class="wmtBorder1L wmtBorder1B">
							<input class="wmtFullInput" name="img_nt_<?php echo $cnt; ?>" id="img_nt_<?php echo $cnt; ?>" type="text" tabindex="-1" value="<?php echo $image['img_nt']; ?>" />
						</td>
						<td class="wmtBorder1L wmtBorder1B">
							<div style="float: left; padding-left: 2px;">
								<a class="css_button_small" tabindex="-1" href="<?php echo $document_url . $image['img_id'] ?>"><span>View</span></a>
							</div>
							<div style="float: left; padding-left: 2px;">
								<a class="css_button_small" tabindex="-1" onClick="SubmitLinkBuilder('<?php echo $base_action; ?>','<?php echo $wrap_mode; ?>','<?php echo $cnt; ?>','<?php echo $img_id; ?>','updateimg','img_id_','Image');" href="javascript:;"><span>Update</span></a>
							</div>
						&nbsp;</td>
					</tr>
<?php
			$cnt++;
		}
	$cnt--;
	} else { ?> 
					<tr>
						<td class="wmtBorder1B">&nbsp;</td>
						<td class="wmtBorder1L wmtBorder1B wmtLabel">None on File</td>
						<td class="wmtBorder1L wmtBorder1B">&nbsp;</td>
						<td class="wmtBorder1L wmtBorder1B">&nbsp;</td>
						<td class="wmtBorder1L wmtBorder1B">&nbsp;</td>
					</tr>
<?php } ?>

					<tr>
						<td class="wmtColorHeader" colspan="2">
							<a class="css_button" onClick="doRefresh()" href="javascript:;"><span>Save &amp Refresh</span></a>
						</td>
						<td class="wmtColorHeader">
							<div style="float: left; padding-left: 8px;">
								<a class="css_button" onClick="add_item('img_type','Image_Types');" href="javascript:;"><span>Add A Type</span></a>
								<input name="tmp_img_cnt" id="tmp_img_cnt" type="hidden" tabindex="-1" value="1" />
							</div>
						</td>
						<td class="wmtColorHeader">
							<div style="float: right; padding-right: 12px;">
								<a class="css_button" href="javascript:;" onclick="wmtOpen('../../../custom/document_popup.php?pid=<?php echo $this->form_data->pid ?>', '_blank', 800, 600);"><span>View Documents</span></a>
							</div>
						</td>
						<td class="wmtColorHeader">&nbsp;</td>
					</tr>

					<tr>
						<td class="wmtLabel" colspan="5">
							Image Notes:
							<textarea name="fyi_img_nt" id="fyi_img_nt" class="wmtFullInput" rows="4"><?php echo $this->form_data->fyi_img_nt; ?></textarea>
						</td>
					</tr>
				</table>
			</div>
			<?php Display::bottom($this->title, $this->key, $open, $bottom); ?>
		</div>
<?php }
	
	
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
			
		} else if ($form_mode == 'updateimg') {
			$cnt = trim($_GET['itemID']);
			$type = $dt['img_type_'.$cnt];
			$notes = $dt['img_nt_'.$cnt];
			$img_id = $dt['img_id_'.$cnt];
			$sql = "UPDATE documents SET documentationOf = ?, comments = ? WHERE id = ?";
			sqlStatement($sql, array($type, $notes, $img_id));
		}
		
		// refresh image data
		$sql = "SELECT id AS img_id, type, url, date, foreign_id AS pid, encounter_id AS enc, documentationOf AS img_type, comments AS img_nt ";
		$sql .= "FROM documents WHERE foreign_id = ? AND encounter_id = ? ORDER BY date";
		$binds = array($pid, $encounter);
		
			$iter = 0;
		$this->images = array();
		$res = sqlStatementNoLog($sql, $binds);
		while ($row = sqlFetchArray($res)) {
			$this->images[$iter] = $row;
			$sql = "SELECT COUNT(*) FROM form_wmt_ll WHERE form_wmt_ll.list_type=? AND form_wmt_ll.list_id=?";
			$chk = sqlStatementNoLog($sql, array('wmt_img_history', $this->images[$iter]['id']));
			$num = sqlFetchArray($chk);
			$this->images[$iter]['img_num_links'] = $num['COUNT(*)'];
			$iter++;
		}
		
		return 'img';
	}
	
				
/**
	 * Print a collapsable section in the report.
	 *
	 */
	public function report() { 
		$output = false;
		if ($this->form_data->img_notes) $output = true;
		if (!$output) return; ?>
		
		<div class='wmtPrnMainContainer'>
			<div class='wmtPrnCollapseBar'>
				<span class='wmtPrnChapter'><?php echo $this->title ?></span>
			</div>
			<div class='wmtPrnCollapseBox'>
				<table class='wmtPrnContent' style="margin:6px">
					
					<tr>
						<td style="width:25px"></td><td style='line-height:14px'>
							<span class='wmtPrnBody'><?php echo $this->form_data->img_notes . "\n"; ?></span>
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
		$this->form_data->img_notes = strip_tags($dt['img_notes']);
		
		return;
	}
		
}
?>