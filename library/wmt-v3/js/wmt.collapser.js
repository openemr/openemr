/** **************************************************************************
 *	Copyright (c)2017 - Medical Technology Services (MDTechSvcs.com)
 *
 *	This program is free software: you can redistribute it and/or modify it 
 *	under the terms of the GNU General Public License as published by the Free 
 *	Software Foundation, either version 3 of the License, or (at your option) 
 *	any later version.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT 
 *	ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 
 *	FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for 
 *	more details.
 *
 *	You should have received a copy of the GNU General Public License along with 
 *	this program.  If not, see <http://www.gnu.org/licenses/>.	This program is 
 *	free software; you can redistribute it and/or modify it under the terms of 
 *	the GNU Library General Public License as published by the Free Software 
 *	Foundation; either version 2 of the License, or (at your option) any 
 *	later version.
 *
 *  @package wmt
 *  @subpackage javascript
 *  @version 1.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <info@MDTechSvcs.com>
 * 
 *************************************************************************** */
$(document).ready(function() {
	// this routine opens and closes sections
	$('.wmtCollapseBar, .wmtBottomBar').click(function() {
		if ( $(this).hasClass('wmtNoCollapse') ) return;
		
		var key = $(this).attr('id');
		key = key.replace('BottomBar','');
		key = key.replace('Bar','');
		var id = '#' + key; 
		var toggle = '#tmp_'+key+'_disp_mode';
		if ($(id+'Box').is(':visible')) {
			$(id+'Box').hide();
			$(id+'Bar').addClass("wmtBarClosed");
			$(id+'Bar').children('img').attr("src","../../../images/fill-270.png");
			$(id+'BottomBar').addClass("wmtBarClosed");
			$(id+'BottomBar').children('img').attr("src","../../../images/fill-270.png");
			$(toggle).val('0');
		} else {
			$(toggle).val('1');
			$(id+'Bar').removeClass("wmtBarClosed");
			$(id+'Bar').children('img').attr("src","../../../images/fill-090.png");
			$(id+'BottomBar').removeClass("wmtBarClosed");
			$(id+'BottomBar').children('img').attr("src","../../../images/fill-090.png");
			$(id+'Box').show();
		}
	});
});

function wmtBarOpen(key) {
	// manually open sections
	var toggle = '#tmp_'+key+'_disp_mode';
	var id = '#' + key; 

	$(toggle).val(1);
	$(id+'Bar').removeClass("wmtBarClosed");
	$(id+'Bar').children('img').attr("src","../../../images/fill-090.png");
	$(id+'BottomBar').removeClass("wmtBarClosed");
	$(id+'BottomBar').children('img').attr("src","../../../images/fill-090.png");
	$(id+'Box').show();
}

function wmtBarClose(key) {
	// manually close sections
	var toggle = '#tmp_'+key+'_disp_mode';
	var id = '#' + key; 

	$(id+'Box').hide();
	$(id+'Bar').addClass("wmtBarClosed");
	$(id+'Bar').children('img').attr("src","../../../images/fill-270.png");
	$(id+'BottomBar').addClass("wmtBarClosed");
	$(id+'BottomBar').children('img').attr("src","../../../images/fill-270.png");
	$(toggle).val(0);
}
