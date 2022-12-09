<?php 
/** **************************************************************************
 *	wmtDisplay.class.php
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
 *  @subpackage healer
 *  @version 2.0.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ******************************************************************************************** */

/**
 * All new classes are defined in the WMT namespace
 */
namespace wmt;

/**
 * Provides standardized processing for browser utilities.
 *
 * @package wmt
 * @subpackage display
 */
class Report {

	/** Generate the header for a section of the report
	 * 
	 *  @param string $title
	 */
	public static function chapter($title='') { 
?>
		<div class="wmtPrnCollapseBar" style="text-align:center">
			<span class="wmtPrnChapter"><?php echo $title ?></span>
		</div>
<?php 
	}
	
	public static function bottom($title='', $bar_id='', $toggle=false, $bottom=false, $collapsible=true, $class='wmtBottomBar') {
		if (!$bottom) return;
		
		// old version support
		if ($toggle == 'open') $toggle = true;
		if ($toggle == 'none') $toggle = false;
		
		$arrow = ($toggle)? 'fill-090.png' : 'fill-270.png';
		?>
			<div class="<?php echo $class ?> wmtColorBar <?php if (!$toggle) echo 'wmtBarClosed' ?> <?php if (!$collapsible) echo 'wmtNoCollapse'?>" id="<?php echo $bar_id ?>BottomBar" style="text-align:center">
				<?php if ($collapsible) { ?><img class="wmtCollapseArrow" id="<?php echo $bar_id ?>ImageL" src="<?php echo $GLOBALS['webroot'];?>/library/wmt/<?php echo ($arrow) ?>" title="Show/Hide" style="float:left" /><?php } ?>
				<span class="wmtCollapseTitle"><?php echo $title ?></span>
				<?php if ($collapsible) { ?><img class="wmtCollapseArrow" id="<?php echo $bar_id ?>ImageR" src="<?php echo $GLOBALS['webroot'];?>/library/wmt/<?php echo ($arrow) ?>" title="Show/Hide"  style="float:right"/><?php } ?>
			</div>
	<?php 
	}
		
}
?>
