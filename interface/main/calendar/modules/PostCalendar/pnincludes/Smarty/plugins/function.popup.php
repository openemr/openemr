<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     popup
 * Purpose:  make text pop up in windows via overlib
 * -------------------------------------------------------------
 */
function smarty_function_popup($params, &$smarty)
{
    extract($params);

    if (empty($text) && !isset($inarray) && empty($function)) {
        $smarty->trigger_error("overlib: attribute 'text' or 'inarray' or 'function' required");
        return false;
    }

    if (empty($trigger)) { $trigger = "onMouseOver"; }

    echo $trigger.'="return overlib(\''.str_replace("'","\'",$text).'\'';
    if ($sticky) { echo ",STICKY"; }
    if (!empty($caption)) { echo ",CAPTION,'".str_replace("'","\'",$caption)."'"; }
    if (!empty($fgcolor)) { echo ",FGCOLOR,'$fgcolor'"; }
    if (!empty($bgcolor)) { echo ",BGCOLOR,'$bgcolor'"; }
    if (!empty($textcolor)) { echo ",TEXTCOLOR,'$textcolor'"; }
    if (!empty($capcolor)) { echo ",CAPCOLOR,'$capcolor'"; }
    if (!empty($closecolor)) { echo ",CLOSECOLOR,'$closecolor'"; }
    if (!empty($textfont)) { echo ",TEXTFONT,'$textfont'"; }
    if (!empty($captionfont)) { echo ",CAPTIONFONT,'$captionfont'"; }
    if (!empty($closefont)) { echo ",CLOSEFONT,'$closefont'"; }
    if (!empty($textsize)) { echo ",TEXTSIZE,$textsize"; }
    if (!empty($captionsize)) { echo ",CAPTIONSIZE,$captionsize"; }
    if (!empty($closesize)) { echo ",CLOSESIZE,$closesize"; }
    if (!empty($width)) { echo ",WIDTH,$width"; }
    if (!empty($height)) { echo ",HEIGHT,$height"; }
    if (!empty($left)) { echo ",LEFT"; }
    if (!empty($right)) { echo ",RIGHT"; }
    if (!empty($center)) { echo ",CENTER"; }
    if (!empty($above)) { echo ",ABOVE"; }
    if (!empty($below)) { echo ",BELOW"; }
    if (isset($border)) { echo ",BORDER,$border"; }
    if (isset($offsetx)) { echo ",OFFSETX,$offsetx"; }
    if (isset($offsety)) { echo ",OFFSETY,$offsety"; }
    if (!empty($fgbackground)) { echo ",FGBACKGROUND,'$fgbackground'"; }
    if (!empty($bgbackground)) { echo ",BGBACKGROUND,'$bgbackground'"; }
    if (!empty($closetext)) { echo ",CLOSETEXT,'".str_replace("'","\'",$closetext)."'"; }
    if (!empty($noclose)) { echo ",NOCLOSE"; }
    if (!empty($status)) { echo ",STATUS,'".str_replace("'","\'",$status)."'"; }
    if (!empty($autostatus)) { echo ",AUTOSTATUS"; }
    if (!empty($autostatuscap)) { echo ",AUTOSTATUSCAP"; }
    if (isset($inarray)) { echo ",INARRAY,'$inarray'"; }
    if (isset($caparray)) { echo ",CAPARRAY,'$caparray'"; }
    if (!empty($capicon)) { echo ",CAPICON,'$capicon'"; }
    if (!empty($snapx)) { echo ",SNAPX,$snapx"; }
    if (!empty($snapy)) { echo ",SNAPY,$snapy"; }
    if (isset($fixx)) { echo ",FIXX,$fixx"; }
    if (isset($fixy)) { echo ",FIXY,$fixy"; }
    if (!empty($background)) { echo ",BACKGROUND,'$background'"; }
    if (!empty($padx)) { echo ",PADX,$padx"; }
    if (!empty($pady)) { echo ",PADY,$pady"; }
    if (!empty($fullhtml)) { echo ",FULLHTML"; }
    if (!empty($frame)) { echo ",FRAME,'$frame'"; }
    if (isset($timeout)) { echo ",TIMEOUT,$timeout"; }
    if (!empty($function)) { echo ",FUNCTION,'$function'"; }
    if (isset($delay)) { echo ",DELAY,$delay"; }
    if (!empty($hauto)) { echo ",HAUTO"; }
    if (!empty($vauto)) { echo ",VAUTO"; }
    echo ');" onMouseOut="nd();"';
}

/* vim: set expandtab: */

?>
