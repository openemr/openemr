<?php
/**
 *  $Id$
 *
 *  PostCalendar::PostNuke Events Calendar Module
 *  Copyright (C) 2002  The PostCalendar Team
 *  http://postcalendar.tv
 *  
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *  
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 *  To read the license please read the docs/license.txt or visit
 *  http://www.gnu.org/copyleft/gpl.html
 *
 */
function smarty_function_pc_popup($args)
{
	// if we're not using popups just return an empty string
	if(!_SETTING_USE_POPUPS) { return; }
	
	extract($args);

    if (empty($text) && !isset($inarray) && empty($function)) {
        $text = "overlib: attribute 'text' or 'inarray' or 'function' required";
    }

	if (empty($trigger)) { $trigger = "onMouseOver"; }

	echo $trigger.'="return overlib(\''.pc_clean($text).'\'';
    if ($sticky) { echo ",STICKY"; }
    if (!empty($caption)) 		{ echo ",CAPTION,'".pc_clean($caption)."'"; }
    if (!empty($fgcolor)) 		{ echo ",FGCOLOR,'$fgcolor'"; }
    if (!empty($bgcolor)) 		{ echo ",BGCOLOR,'$bgcolor'"; }
    if (!empty($textcolor)) 	{ echo ",TEXTCOLOR,'$textcolor'"; }
    if (!empty($capcolor))  	{ echo ",CAPCOLOR,'$capcolor'"; }
    if (!empty($closecolor)) 	{ echo ",CLOSECOLOR,'$closecolor'"; }
    if (!empty($textfont))  	{ echo ",TEXTFONT,'$textfont'"; }
    if (!empty($captionfont)) 	{ echo ",CAPTIONFONT,'$captionfont'"; }
    if (!empty($closefont)) 	{ echo ",CLOSEFONT,'$closefont'"; }
    if (!empty($textsize))  	{ echo ",TEXTSIZE,$textsize"; }
    if (!empty($captionsize)) 	{ echo ",CAPTIONSIZE,$captionsize"; }
    if (!empty($closesize)) 	{ echo ",CLOSESIZE,$closesize"; }
    if (!empty($width)) 		{ echo ",WIDTH,$width"; }
    if (!empty($height)) 		{ echo ",HEIGHT,$height"; }
    if (!empty($left))  		{ echo ",LEFT"; }
    if (!empty($right)) 		{ echo ",RIGHT"; }
    if (!empty($center)) 		{ echo ",CENTER"; }
    if (!empty($above)) 		{ echo ",ABOVE"; }
    if (!empty($below)) 		{ echo ",BELOW"; }
    if (isset($border)) 		{ echo ",BORDER,$border"; }
    if (isset($offsetx)) 		{ echo ",OFFSETX,$offsetx"; }
    if (isset($offsety)) 		{ echo ",OFFSETY,$offsety"; }
    if (!empty($fgbackground))  { echo ",FGBACKGROUND,'$fgbackground'"; }
    if (!empty($bgbackground))  { echo ",BGBACKGROUND,'$bgbackground'"; }
    if (!empty($closetext)) 	{ echo ",CLOSETEXT,'".pc_clean($closetext)."'"; }
    if (!empty($noclose)) 		{ echo ",NOCLOSE"; }
    if (!empty($status)) 		{ echo ",STATUS,'".pc_clean($status)."'"; }
    if (!empty($autostatus)) 	{ echo ",AUTOSTATUS"; }
    if (!empty($autostatuscap)) { echo ",AUTOSTATUSCAP"; }
    if (isset($inarray)) 		{ echo ",INARRAY,'$inarray'"; }
    if (isset($caparray)) 		{ echo ",CAPARRAY,'$caparray'"; }
    if (!empty($capicon)) 		{ echo ",CAPICON,'$capicon'"; }
    if (!empty($snapx)) 		{ echo ",SNAPX,$snapx"; }
    if (!empty($snapy)) 		{ echo ",SNAPY,$snapy"; }
    if (isset($fixx)) 			{ echo ",FIXX,$fixx"; }
    if (isset($fixy)) 			{ echo ",FIXY,$fixy"; }
    if (!empty($background)) 	{ echo ",BACKGROUND,'$background'"; }
    if (!empty($padx))  		{ echo ",PADX,$padx"; }
    if (!empty($pady))  		{ echo ",PADY,$pady"; }
    if (!empty($fullhtml))  	{ echo ",FULLHTML"; }
    if (!empty($frame)) 		{ echo ",FRAME,'$frame'"; }
    if (isset($timeout)) 		{ echo ",TIMEOUT,$timeout"; }
    if (!empty($function))  	{ echo ",FUNCTION,'$function'"; }
    if (isset($delay))  		{ echo ",DELAY,$delay"; }
    if (!empty($hauto)) 		{ echo ",HAUTO"; }
    if (!empty($vauto)) 		{ echo ",VAUTO"; }
    echo ');" onMouseOut="nd();"';
}
?>
