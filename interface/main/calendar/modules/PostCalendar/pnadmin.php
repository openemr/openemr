<?php

@define('__POSTCALENDAR__', 'PostCalendar');
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

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Acl\AclExtended;
use OpenEMR\Core\Header;

//=========================================================================
//  Load the API Functions
//=========================================================================
pnModAPILoad(__POSTCALENDAR__, 'admin');

function postcalendar_admin_modifyconfig($msg = '', $showMenu = true)
{
    $output = new pnHTML();

    $output->SetInputMode(_PNH_VERBATIMINPUT);

    $header = "<html><head><title>" . xlt("Calendar") . "</title>";
    $header .= Header::setupHeader('', false)  . '</head><body>';

    $output->Text($header);

    if (!empty($msg)) {
        $output->Text(postcalendar_adminmenu("clearCache"));
        $output -> Text('<div class="alert alert-success mx-1 text-center" role="alert">');
        $output->Text("<b>$msg</b>");
        $output -> Text('</div>');
    } else {
        if ($showMenu) {
            $output->Text(postcalendar_adminmenu(""));
        }
    }

    $output->Text("</body></html>");

    return $output->GetOutput();
}

function postcalendar_admin_categoriesConfirm()
{
    $output = new pnHTML();
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    $header = <<<EOF
	<html>
	<head>
EOF;
    $header .= Header::setupHeader('', false)  . '</head><body><div class="container">';
    $output->Text($header);
    $output->Text(postcalendar_adminmenu("category"));
    list($id, $del, $name, $constantid, $value_cat_type, $desc, $color,
        $event_repeat, $event_repeat_freq,
        $event_repeat_freq_type, $event_repeat_on_num,
        $event_repeat_on_day, $event_repeat_on_freq, $durationh, $durationm,
        $end_date_flag, $end_date_type, $end_date_freq, $end_all_day, $active, $sequence, $aco,
        $newname, $newconstantid, $newdesc, $newcolor, $new_event_repeat, $new_event_repeat_freq,
        $new_event_repeat_freq_type, $new_event_repeat_on_num, $new_event_repeat_on_day,
        $new_event_repeat_on_freq, $new_durationh, $new_durationm, $new_limitid, $new_end_date_flag,
        $new_end_date_type, $new_end_date_freq, $new_end_all_day, $new_value_cat_type, $newactive, $newsequence, $newaco
        ) = pnVarCleanFromInput(
            'id',
            'del',
            'name',
            'constantid',
            'value_cat_type',
            'desc',
            'color',
            'event_repeat',
            'event_repeat_freq',
            'event_repeat_freq_type',
            'event_repeat_on_num',
            'event_repeat_on_day',
            'event_repeat_on_freq',
            'durationh',
            'durationm',
            'end_date_flag',
            'end_date_type',
            'end_date_freq',
            'end_all_day',
            'active',
            'sequence',
            'aco',
            'newname',
            'newconstantid',
            'newdesc',
            'newcolor',
            'newevent_repeat',
            'newevent_repeat_freq',
            'newevent_repeat_freq_type',
            'newevent_repeat_on_num',
            'newevent_repeat_on_day',
            'newevent_repeat_on_freq',
            'newdurationh',
            'newdurationm',
            'newlimitid',
            'newend_date_flag',
            'newend_date_type',
            'newend_date_freq',
            'newend_all_day',
            'newvalue_cat_type',
            'newactive',
            'newsequence',
            'newaco'
        );
    //data validation
    foreach ($name as $i => $item) {
        if (empty($item)) {
            $output->Text(postcalendar_admin_categories($msg, "Category Names must contain a value!"));
            return $output->GetOutput();
        }
        if (empty($constantid[$i])) {
            $output->Text(postcalendar_admin_categories($msg, "Category Identifiers must contain a value!"));
            return $output->GetOutput();
        }
        $tmp = $constantid[$i];
        if (strpos(trim($tmp), ' ')) {
            $output->Text(postcalendar_admin_categories($msg, "Category Identifiers must be one word!"));
            return $output->GetOutput();
        }
        $tmp = $color[$i];
        if (strlen($tmp) != 7 || $tmp[0] != "#") {
            $e = $tmp . " size " . strlen($tmp) . " at 0 " . $tmp[0];
            $output->Text(postcalendar_admin_categories($msg, "You entered an invalid color(USE Pick) $e!"));
            return $output->GetOutput();
        }
    }
    foreach ($durationh as $i => $val) {
        if (
            !is_numeric($durationh[$i]) || !is_numeric($durationm[$i]) ||
            !is_numeric($event_repeat_freq[$i]) ||
            !is_numeric($event_repeat_on_freq[$i]) || !is_numeric($end_date_freq[$i])
        ) {
            $output->Text(postcalendar_admin_categories(
                $msg,
                " Hours, Minutes and recurrence values must be numeric!"
            ));
            return $output->GetOutput();
        }
    }
    if (!empty($newnam)) {
        if (
            !is_numeric($new_durationh) ||
            !is_numeric($new_durationm) ||
            !is_numeric($new_event_repeat_freq) ||
            !is_numeric($new_event_repeat_on_freq) ||
            !is_numeric($new_end_date_freq)
        ) {
            $output->Text(postcalendar_admin_categories($msg, "Hours, Minutes and recurrence values must be numeric!"));
            return $output->GetOutput();
        }
    }
    $new_duration = ($new_durationh * (60 * 60)) + ($new_durationm * 60);
    $event_recurrspec = serialize(compact(
        'event_repeat_freq',
        'event_repeat_freq_type',
        'event_repeat_on_num',
        'event_repeat_on_day',
        'event_repeat_on_freq'
    ));
    $new_event_recurrspec = serialize(compact(
        'new_event_repeat_freq',
        'new_event_repeat_freq_type',
        'new_event_repeat_on_num',
        'new_event_repeat_on_day',
        'new_event_repeat_on_freq'
    ));
    if (is_array($del)) {
        $dels = implode(',', $del);
        $delText = _PC_DELETE_CATS . $dels . '.';
    }
    $output->FormStart(pnModURL(__POSTCALENDAR__, 'admin', 'categoriesUpdate'));
    $output->Text(_PC_ARE_YOU_SURE);
    $output->Linebreak(2);
    // deletions
    if (isset($delText)) {
        $output->FormHidden('dels', $dels);
        $output->Text($delText);
        $output->Linebreak();
    }
    if (!empty($newname)) {
        if (empty($newconstantid)) {
            $output->Text(postcalendar_admin_categories($msg, "Category Identifiers must contain a value!"));
            return $output->GetOutput();
        }
        if (strpos(trim($newconstantid), ' ')) {
            $output->Text(postcalendar_admin_categories($msg, "Category Identifiers must be one word!"));
            return $output->GetOutput();
        }
        $output->FormHidden('newname', $newname);
        $output->FormHidden('newconstantid', $newconstantid);
        $output->FormHidden('newdesc', $newdesc);
        $output->FormHidden('newvalue_cat_type', $new_value_cat_type);
        $output->FormHidden('newcolor', $newcolor);
        $output->FormHidden('newevent_repeat', $new_event_repeat);
        $output->FormHidden('newevent_recurrfreq', $new_event_repeat_freq);
        $output->FormHidden('newevent_recurrspec', $new_event_recurrspec);
        $output->FormHidden('newduration', $new_duration);
        $output->FormHidden('newlimitid', $new_limitid);
        $output->FormHidden('newend_date_flag', $new_end_date_flag);
        $output->FormHidden('newend_date_type', $new_end_date_type);
        $output->FormHidden('newend_date_freq', $new_end_date_freq);
        $output->FormHidden('newend_all_day', $new_end_all_day);
        $output->FormHidden("newactive", $newactive);
        $output->FormHidden("newsequence", $newsequence);
        $output->FormHidden("newaco", $newaco);
        $output->Text(_PC_ADD_CAT . $newname . '.');
        $output->Linebreak();
    }
    $output->Text(_PC_MODIFY_CATS);
    $output->FormHidden('id', serialize($id));
    $output->FormHidden('del', serialize($del));
    $output->FormHidden('name', serialize($name));
    $output->FormHidden('constantid', serialize($constantid));
    $output->FormHidden('desc', serialize($desc));
    $output->FormHidden('value_cat_type', serialize($value_cat_type));
    $output->FormHidden('color', serialize($color));
    $output->FormHidden('event_repeat', serialize($event_repeat));
    $output->FormHidden('event_recurrspec', $event_recurrspec);
    $output->FormHidden('durationh', serialize($durationh));
    $output->FormHidden('durationm', serialize($durationm));
    $output->FormHidden('end_date_flag', serialize($end_date_flag));
    $output->FormHidden('end_date_type', serialize($end_date_type));
    $output->FormHidden('end_date_freq', serialize($end_date_freq));
    $output->FormHidden('end_all_day', serialize($end_all_day));
    $output->FormHidden("active", serialize($active));
    $output->FormHidden("sequence", serialize($sequence));
    $output->FormHidden("aco", serialize($aco));
    $output->Linebreak();
    $output->FormSubmit(_PC_CATS_CONFIRM);
    $output->FormEnd();
    return $output->GetOutput();
}

function postcalendar_admin_categoriesUpdate()
{
    $output = new pnHTML();
    $output->SetInputMode(_PNH_VERBATIMINPUT);

    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    list($id,$del,$name,$constantid,$value_cat_type,$desc,$color,
        $event_repeat_array,$event_recurrspec_array,$dels,$durationh,$durationm,
        $end_date_flag,$end_date_type,$end_date_freq,$end_all_day,$active,$sequence,$aco,$newname,$newconstantid,$newdesc,$newcolor,
        $new_event_repeat,$new_event_recurrspec,$new_event_recurrfreq,
        $new_duration,$new_dailylimitid,$new_end_date_flag,$new_end_date_type,
        $new_end_date_freq,$new_end_all_day,$new_value_cat_type,$newactive,$newsequence,$newaco
        ) = pnVarCleanFromInput(
            'id',
            'del',
            'name',
            'constantid',
            'value_cat_type',
            'desc',
            'color',
            'event_repeat',
            'event_recurrspec',
            'dels',
            'durationh',
            'durationm',
            'end_date_flag',
            'end_date_type',
            'end_date_freq',
            'end_all_day',
            'active',
            'sequence',
            'aco',
            'newname',
            'newconstantid',
            'newdesc',
            'newcolor',
            'newevent_repeat',
            'newevent_recurrspec',
            'newevent_recurrfreq',
            'newduration',
            'newlimitid',
            'newend_date_flag',
            'newend_date_type',
            'newend_date_freq',
            'newend_all_day',
            'newvalue_cat_type',
            'newactive',
            'newsequence',
            'newaco'
        );

    $id = unserialize($id, ['allowed_classes' => false]);
    $del = unserialize($del, ['allowed_classes' => false]);
    $name = unserialize($name, ['allowed_classes' => false]);
    $constantid = unserialize($constantid, ['allowed_classes' => false]);
    $value_cat_type = unserialize($value_cat_type, ['allowed_classes' => false]);
    $desc = unserialize($desc, ['allowed_classes' => false]);
    $color = unserialize($color, ['allowed_classes' => false]);
    $event_repeat_array = unserialize($event_repeat_array, ['allowed_classes' => false]);
    $event_recurrspec_array = unserialize($event_recurrspec_array, ['allowed_classes' => false]);
    $durationh = unserialize($durationh, ['allowed_classes' => false]);
    $durationm = unserialize($durationm, ['allowed_classes' => false]);
    $end_date_flag = unserialize($end_date_flag, ['allowed_classes' => false]);
    $end_date_type = unserialize($end_date_type, ['allowed_classes' => false]);
    $end_date_freq = unserialize($end_date_freq, ['allowed_classes' => false]);
    $end_all_day = unserialize($end_all_day, ['allowed_classes' => false]);
    $active = unserialize($active, ['allowed_classes' => false]);
    $sequence = unserialize($sequence, ['allowed_classes' => false]);
    $aco = unserialize($aco, ['allowed_classes' => false]);
    $updates = array();

    if (isset($id)) {
        foreach ($id as $k => $i) {
            $found = false;
            if (!empty($del)) {
                if (count($del)) {
                    foreach ($del as $d) {
                        if ($i == $d) {
                            $found = true;
                            break;
                        }
                    }
                }
            }
            if (!$found) {
                $event_repeat_freq = $event_recurrspec_array['event_repeat_freq'][$i];
                $event_repeat_freq_type = $event_recurrspec_array['event_repeat_freq_type'][$i];
                $event_repeat_on_num = $event_recurrspec_array['event_repeat_on_num'][$i];
                $event_repeat_on_day = $event_recurrspec_array['event_repeat_on_day'][$i];
                $event_repeat_on_freq = $event_recurrspec_array['event_repeat_on_freq'][$i];

                $recurrspec = serialize(compact(
                    'event_repeat_freq',
                    'event_repeat_freq_type',
                    'event_repeat_on_num',
                    'event_repeat_on_day',
                    'event_repeat_on_freq'
                ));

                $dur = ( ($durationh[$i] * (60 * 60)) + ($durationm[$i] * 60));

                $update_sql = "UPDATE $pntable[postcalendar_categories]
		                             SET pc_catname='" . pnVarPrepForStore($name[$k]) . "',
		                                 pc_constant_id='" . trim(pnVarPrepForStore($constantid[$k])) . "',
		                                 pc_catdesc='" . trim(pnVarPrepForStore($desc[$k])) . "',
		                                 pc_cattype='" . trim(pnVarPrepForStore($value_cat_type[$k])) . "',
		                                 pc_catcolor='" . pnVarPrepForStore($color[$k]) . "',
		                                 pc_recurrtype='" . pnVarPrepForStore($event_repeat_array[$i]) . "',
		                                 pc_recurrspec='" . pnVarPrepForStore($recurrspec) . "',
		                                 pc_duration='" . pnVarPrepForStore($dur) . "',
		                                 pc_end_date_flag='" . pnVarPrepForStore($end_date_flag[$i]) . "',
		                             	 pc_end_date_type='" . pnVarPrepForStore($end_date_type[$i]) . "',
		                             	 pc_end_date_freq='" . pnVarPrepForStore($end_date_freq[$i]) . "',
		                             	 pc_end_all_day='" . pnVarPrepForStore($end_all_day[$i]) . "',
		                             	 pc_active ='" . pnVarPrepForStore($active[$i]) . "',
		                             	 pc_seq = '" . pnVarPrepForStore($sequence[$k]) . "',
		                             	 aco_spec = '" . pnVarPrepForStore($aco[$k]) . "'
		                             WHERE pc_catid = '" . pnVarPrepForStore($i) . "'";

                array_push($updates, $update_sql);
                unset($recurrspec);
                unset($dur);
            }
        }
    }


    $delete = "DELETE FROM $pntable[postcalendar_categories] WHERE pc_catid IN ($dels)";
    $e =  $msg = '';
    if (!pnModAPIFunc(__POSTCALENDAR__, 'admin', 'updateCategories', array('updates' => $updates))) {
        $e .= 'UPDATE FAILED';
    }
    if (isset($dels)) {
        if (!pnModAPIFunc(__POSTCALENDAR__, 'admin', 'deleteCategories', array('delete' => $delete))) {
            $e .= 'DELETE FAILED';
        }
    }
    if (isset($newname)) {
        $unpacked = unserialize($new_event_recurrspec, ['allowed_classes' => false]);
        unset($new_event_recurrspec);
        $new_event_recurrspec['event_repeat_freq'] = $unpacked['new_event_repeat_freq'];
        $new_event_recurrspec['event_repeat_freq_type'] = $unpacked['new_event_repeat_freq_type'];
        $new_event_recurrspec['event_repeat_on_num'] = $unpacked['new_event_repeat_on_num'];
        $new_event_recurrspec['event_repeat_on_day'] = $unpacked['new_event_repeat_on_day'];
        $new_event_recurrspec['event_repeat_on_freq'] = $unpacked['new_event_repeat_on_freq'];
        $new_event_recurrspec = serialize($new_event_recurrspec);

        if (
            !pnModAPIFunc(
                __POSTCALENDAR__,
                'admin',
                'addCategories',
                array('name' => $newname,'constantid' => $newconstantid,'desc' => $newdesc,'value_cat_type' => $new_value_cat_type,'color' => $newcolor,'active' => $newactive,'sequence' => $newsequence, 'aco' => $newaco,
                'repeat' => $new_event_repeat,'spec' => $new_event_recurrspec,
                'recurrfreq' => $new_recurrfreq,'duration' => $new_duration,'limitid' => $new_dailylimitid,
                'end_date_flag' => $new_end_date_flag,'end_date_type' => $new_end_date_flag,
                'end_date_freq' => $new_end_date_freq,
                'end_all_day' => $new_end_all_day)
            )
        ) {
            $e .= 'INSERT FAILED';
        }
    }

    if (empty($e)) {
        $msg = 'DONE';
    }
    $output->Text(postcalendar_admin_categories($msg, $e));
    return $output->GetOutput();
}

/**
* Creates a new category
*/
function postcalendar_admin_categories($msg = '', $e = '', $args = array())
{
    extract($args);
    unset($args);

    $output = new pnHTML();
    $output->SetInputMode(_PNH_VERBATIMINPUT);
    // set up Smarty
    $tpl = new pcSmarty();
    $tpl->caching = false;

    $template_name = pnModGetVar(__POSTCALENDAR__, 'pcTemplate');

    if (!isset($template_name)) {
        $template_name = 'default';
    }

    $output->Text(postcalendar_adminmenu("category"));

    if (!empty($e)) {
        $output -> Text('<div class="alert alert-danger mx-1" role="alert">');
        $output->Text('<span class="text-center font-weight-bold">' . text($e) . '</span>');
        $output -> Text('</div><br />');
    }

    if (!empty($msg)) {
        $output -> Text('<div class="alert alert-success mx-1" role="alert">');
        $output->Text('<span class="text-center font-weight-bold">' . text($msg) . '</span>');
        $output -> Text('</div><br />');
    }

    //=================================================================
    //  Setup the correct config file path for the templates
    //=================================================================
    $modinfo = pnModGetInfo(pnModGetIDFromName(__POSTCALENDAR__));
    $modir = pnVarPrepForOS($modinfo['directory']);
    $modname = $modinfo['displayname'];
    $all_categories = pnModAPIFunc(__POSTCALENDAR__, 'user', 'getCategories');
    //print_r($all_categories);
    unset($modinfo);
    $tpl->config_dir = "modules/$modir/pntemplates/$template_name/config/";

    //=================================================================
    //  PARSE MAIN
    //=================================================================

    // create translations if applicable
    if (($GLOBALS['translate_appt_categories']) && ($_SESSION['language_choice'] > 1)) {
        $sizeAllCat = count($all_categories);
        for ($m = 0; $m < $sizeAllCat; $m++) {
            $tempCategory = $all_categories[$m]["name"];
            $tempDescription = $all_categories[$m]["desc"];
            $all_categories[$m]["nameTranslate"] = xl($tempCategory);
            $all_categories[$m]["descTranslate"] = xl($tempDescription);
        }
    }
    $tpl->assign('globals', $GLOBALS);

    $tpl->assign_by_ref('TPL_NAME', $template_name);
    $tpl->assign('FUNCTION', pnVarCleanFromInput('func'));
    $tpl->assign_by_ref('ModuleName', $modname);
    $tpl->assign_by_ref('ModuleDirectory', $modir);
    $tpl->assign_by_ref('all_categories', $all_categories);

    $tpl->assign('pcDir', $modir);
    $tpl->assign('action', pnModURL(__POSTCALENDAR__, 'admin', 'categoriesConfirm'));
    $tpl->assign('adminmenu', postcalendar_adminmenu("category"));
    $tpl->assign('BGCOLOR2', $GLOBALS['style']['BGCOLOR2']);
    $tpl->assign('css_header', $GLOBALS['css_header']);
    $tpl->assign('_PC_REP_CAT_TITLE_S', _PC_REP_CAT_TITLE_S);
    $tpl->assign('_PC_NEW_CAT_TITLE_S', _PC_NEW_CAT_TITLE_S);
    $tpl->assign('_PC_CAT_NAME', _PC_CAT_NAME);
    $tpl->assign('_PC_CAT_CONSTANT_ID', _PC_CAT_CONSTANT_ID);
    $tpl->assign('_PC_CAT_TYPE', _PC_CAT_TYPE);
    $tpl->assign('_PC_CAT_NAME_XL', _PC_CAT_NAME_XL);
    $tpl->assign('_PC_CAT_DESC', _PC_CAT_DESC);
    $tpl->assign('_PC_CAT_DESC_XL', _PC_CAT_DESC_XL);
    $tpl->assign('_PC_CAT_COLOR', _PC_CAT_COLOR);
    $tpl->assign('_PC_CAT_DELETE', _PC_CAT_DELETE);
    $tpl->assign('_PC_CAT_DUR', _PC_CAT_DUR);
    $tpl->assign('_PC_COLOR_PICK_TITLE', _PC_COLOR_PICK_TITLE);
    $tpl->assign('_EDIT_PC_CONFIG_CATDETAILS', _EDIT_PC_CONFIG_CATDETAILS);
    $tpl->assign("_PC_ACTIVE", _PC_ACTIVE);
    $tpl->assign("_PC_SEQ", _PC_SEQ);
    $tpl->assign("_ACO", _ACO);
    //=================================================================
    //  Repeating Information
    //=================================================================
    $tpl->assign('RepeatingHeader', _PC_REPEATING_HEADER);
    $tpl->assign('NoRepeatTitle', _PC_NO_REPEAT);
    $tpl->assign('RepeatTitle', _PC_REPEAT);
    $tpl->assign('RepeatOnTitle', _PC_REPEAT_ON);
    $tpl->assign('OfTheMonthTitle', _PC_OF_THE_MONTH);
    $tpl->assign('EndDateTitle', _PC_END_DATE);
    $tpl->assign('NoEndDateTitle', _PC_NO_END);
    $tpl->assign('REP_CAT_TITLE', _PC_REP_CAT_TITLE);
    $tpl->assign('NEW_CAT_TITLE', _PC_NEW_CAT_TITLE);
    $tpl->assign('InputNoRepeat', 'event_repeat');
    $tpl->assign('ValueNoRepeat', '0');
    $tpl->assign('SelectedNoRepeat', (int) $event_repeat == 0 ? 'checked' : '');
    $tpl->assign('InputRepeat', 'event_repeat');
    $tpl->assign('ValueRepeat', '1');
    $tpl->assign('SelectedRepeat', (int) $event_repeat == 1 ? 'checked' : '');


    unset($in);
    $in = array(_PC_EVERY,_PC_EVERY_OTHER,_PC_EVERY_THIRD,_PC_EVERY_FOURTH);
    $keys = array(REPEAT_EVERY,REPEAT_EVERY_OTHER,REPEAT_EVERY_THIRD,REPEAT_EVERY_FOURTH);
    $repeat_freq = array();
    foreach ($in as $k => $v) {
        array_push($repeat_freq, array('value' => $keys[$k],
                                      'selected' => ($keys[$k] == $event_repeat_freq ? 'selected' : ''),
                                      'name' => $v));
    }
    $tpl->assign('InputRepeatFreq', 'event_repeat_freq');
    if (empty($event_repeat_freq) || $event_repeat_freq < 1) {
        $event_repeat_freq = 1;
    }
    $tpl->assign('InputRepeatFreqVal', $event_repeat_freq);
    $tpl->assign('repeat_freq', $event_repeat_freq);
    unset($in);

    $in = array(_PC_EVERY_DAY,_PC_EVERY_WORKDAY,_PC_EVERY_WEEK,_PC_EVERY_MONTH,_PC_EVERY_YEAR);
    $keys = array(REPEAT_EVERY_DAY,REPEAT_EVERY_WORK_DAY,REPEAT_EVERY_WEEK,REPEAT_EVERY_MONTH,REPEAT_EVERY_YEAR);
    $repeat_freq_type = array();
    foreach ($in as $k => $v) {
        array_push($repeat_freq_type, array('value' => $keys[$k],
                                           'selected' => ($keys[$k] == $event_repeat_freq_type ? 'selected' : ''),
                                           'name' => $v));
    }
    $tpl->assign('InputRepeatFreqType', 'event_repeat_freq_type');
    $tpl->assign('InuptRepeatFreq', '' . 'event_repeat_freq');
    $tpl->assign('repeat_freq_type', $repeat_freq_type);

    $tpl->assign('InputRepeatOn', 'event_repeat');
    $tpl->assign('ValueRepeatOn', '2');
    $tpl->assign('SelectedRepeatOn', (int) $event_repeat == 2 ? 'checked' : '');

    // All Day START
    $tpl->assign('InputAllDay', 'end_all_day');
    $tpl->assign('ValueAllDay', '1');
    $tpl->assign('ValueAllDayNo', '0');
    $tpl->assign('ALL_DAY_CAT_TITLE', _PC_ALL_DAY_CAT_TITLE);
    $tpl->assign('ALL_DAY_CAT_YES', _PC_ALL_DAY_CAT_YES);
    $tpl->assign('ALL_DAY_CAT_NO', _PC_ALL_DAY_CAT_NO);

    //ALL Day End
    // End date gather date start

    $tpl->assign('InputEndDateFreq', 'end_date_freq');
    $tpl->assign('InputEndOn', 'end_date_flag');
    $tpl->assign('InputEndDateFreqType', 'end_date_type');
    $tpl->assign('ValueNoEnd', '0');
    $tpl->assign('ValueEnd', '1');

    if (empty($end_date_type)) {
        $end_date_type = array();
    }
    foreach ($in as $k => $v) {
        array_push($end_date_type, array('value' => $keys[$k],
                                           'selected' => ($keys[$k] == $end_date_type ? 'selected' : ''),
                                           'name' => $v));
    }
    unset($in);


    // End date gather date end


    unset($in);
    $in = array(_PC_EVERY_1ST,_PC_EVERY_2ND,_PC_EVERY_3RD,_PC_EVERY_4TH,_PC_EVERY_LAST);
    $keys = array(REPEAT_ON_1ST,REPEAT_ON_2ND,REPEAT_ON_3RD,REPEAT_ON_4TH,REPEAT_ON_LAST);
    $repeat_on_num = array();
    foreach ($in as $k => $v) {
        array_push($repeat_on_num, array('value' => $keys[$k],
                                        'selected' => ($keys[$k] == $event_repeat_on_num ? 'selected' : ''),
                                        'name' => $v));
    }
    $tpl->assign('InputRepeatOnNum', 'event_repeat_on_num');
    $tpl->assign('repeat_on_num', $repeat_on_num);

    unset($in);
    $in = array(_PC_EVERY_SUN,_PC_EVERY_MON,_PC_EVERY_TUE,_PC_EVERY_WED,_PC_EVERY_THU,_PC_EVERY_FRI,_PC_EVERY_SAT);
    $keys = array(REPEAT_ON_SUN,REPEAT_ON_MON,REPEAT_ON_TUE,REPEAT_ON_WED,REPEAT_ON_THU,REPEAT_ON_FRI,REPEAT_ON_SAT);
    $repeat_on_day = array();
    foreach ($in as $k => $v) {
        array_push($repeat_on_day, array('value' => $keys[$k],
                                        'selected' => ($keys[$k] == $event_repeat_on_day ? 'selected' : ''),
                                        'name' => $v));
    }
    $tpl->assign('InputRepeatOnDay', 'event_repeat_on_day');
    $tpl->assign('repeat_on_day', $repeat_on_day);

    unset($in);
    $in = array(_PC_CAT_PATIENT,_PC_CAT_PROVIDER,_PC_CAT_CLINIC,_PC_CAT_THERAPY_GROUP);
    $keys = array(TYPE_ON_PATIENT,TYPE_ON_PROVIDER,TYPE_ON_CLINIC,TYPE_ON_THERAPY_GROUP);
    $cat_type = array();
    foreach ($in as $k => $v) {
        array_push($cat_type, array('value' => $keys[$k],
                                        'selected' => ($keys[$k] == $value_cat_type ? 'selected' : ''),
                                        'name' => $v));
    }
    $tpl->assign('InputCatType', 'value_cat_type');
    $tpl->assign('cat_type', $cat_type);

    unset($in);
    $in = array(_PC_OF_EVERY_MONTH,_PC_OF_EVERY_2MONTH,_PC_OF_EVERY_3MONTH,_PC_OF_EVERY_4MONTH,_PC_OF_EVERY_6MONTH,_PC_OF_EVERY_YEAR);
    $keys = array(REPEAT_ON_MONTH,REPEAT_ON_2MONTH,REPEAT_ON_3MONTH,REPEAT_ON_4MONTH,REPEAT_ON_6MONTH,REPEAT_ON_YEAR);
    $repeat_on_freq = array();
    foreach ($in as $k => $v) {
        array_push($repeat_on_freq, array('value' => $keys[$k],
                                         'selected' => ($keys[$k] == $event_repeat_on_freq ? 'selected' : ''),
                                         'name' => $v));
    }
    $tpl->assign('InputRepeatOnFreq', 'event_repeat_on_freq');
    if (empty($event_repeat_on_freq) || $event_repeat_on_freq < 1) {
        $event_repeat_on_freq = 1;
    }
    $tpl->assign('InputRepeatOnFreqVal', $event_repeat_on_freq);
    $tpl->assign('repeat_on_freq', $repeat_on_freq);
    $tpl->assign('MonthsTitle', _PC_MONTHS);
    $tpl->assign('DurationHourTitle', _PC_DURATION_HOUR);
    $tpl->assign('DurationMinTitle', _PC_DURATION_MIN);
    $tpl->assign('InputDurationHour', "durationh");
    $tpl->assign('InputDurationMin', "durationm");
    $tpl->assign('ActiveTitleYes', xl('Yes'));
    $tpl->assign('ActiveTitleNo', xl('No'));

    // Added ACO for each category
    $tpl->assign('InputACO', 'aco');
    $acoList = AclExtended::genAcoArray();
    $tpl->assign('ACO_List', $acoList);

    $output->SetOutputMode(_PNH_RETURNOUTPUT);
    $output->SetOutputMode(_PNH_KEEPOUTPUT);

    $form_hidden = "<input type=\"hidden\" name=\"is_update\" value=\"" . attr($is_update) . "\" />";
    $form_hidden .= "<input type=\"hidden\" name=\"pc_event_id\" value=\"" . attr($pc_event_id) . "\" />";
    if (isset($data_loaded)) {
        $form_hidden .= "<input type=\"hidden\" name=\"data_loaded\" value=\"" . attr($data_loaded) . "\" />";
        $tpl->assign('FormHidden', $form_hidden);
    }
    $form_submit = '<input type=hidden name="form_action" value="commit"/>
				   ' . text($authkey) . '<input class="btn btn-primary" type="submit" name="submit" value="' . xla('Save') . '">';
    $tpl->assign('FormSubmit', $form_submit);

    $output->Text($tpl->fetch($template_name . '/admin/submit_category.html'));
    $output->Text(postcalendar_footer());
    return $output->GetOutput();
}

/**
 * Main administration menu
 */
function postcalendar_adminmenu($menuItem)
{
    global $bgcolor1, $bgcolor2;

    @define('_AM_VAL', 1);
    @define('_PM_VAL', 2);

    @define('_EVENT_APPROVED', 1);
    @define('_EVENT_QUEUED', 0);
    @define('_EVENT_HIDDEN', -1);

    $categoryURL  = pnModURL(__POSTCALENDAR__, 'admin', 'categories');
    $cacheURL     = pnModURL(__POSTCALENDAR__, 'admin', 'clearCache');
    $systemURL    = pnModURL(__POSTCALENDAR__, 'admin', 'testSystem');

    $categoryText = text(_EDIT_PC_CONFIG_CATEGORIES);
    $cacheText    = text(_PC_CLEAR_CACHE);
    $systemText   = text(_PC_TEST_SYSTEM);

    $output = " <div class='container mt-3 mb-3'><ul class='nav nav-pills'>";

    if ($menuItem === "clearCache") {
        $output .= <<<EOF
<li class="nav-item">
    <a class="nav-link active" href="$cacheURL">$cacheText</a>
</li>
<li class="nav-item">
    <a class="nav-link" href="$categoryURL">$categoryText</a>
</li>
EOF;
    } elseif ($menuItem === "testSystem") {
        $output .= <<<EOF
<li class="nav-item">
    <a class="nav-link" href="$cacheURL">$cacheText</a>
</li>
<li class="nav-item">
    <a class="nav-link" href="$categoryURL">$categoryText</a>
</li>
EOF;
    } elseif ($menuItem === "category") {
        $output .= <<<EOF
<li class="nav-item">
    <a class="nav-link" href="$cacheURL">$cacheText</a>
</li>
<li class="nav-item">
    <a class="nav-link active" href="$categoryURL">$categoryText</a>
</li>
EOF;
    } else {
        $output .= <<<EOF
<li class="nav-item">
    <a class="nav-link" href="$cacheURL">$cacheText</a>
</li>
<li class="nav-item">
    <a class="nav-link" href="$categoryURL">$categoryText</a>
</li>
EOF;
    }
    $output .= "</ul></div>";
    // Return the output that has been generated by this function
    return $output;
}

function postcalendar_admin_clearCache()
{
    $tpl = new pcSmarty();
    //fmg: check that both subdirs to be cleared first exist and are writeable
    $spec_err = '';

    if (!file_exists($tpl->compile_dir)) {
        $spec_err .= "Error: folder '" . text($tpl->compile_dir) . "' doesn't exist!<br />";
    } elseif (!is_writeable($tpl->compile_dir)) {
        $spec_err .= "Error: folder '" . text($tpl->compile_dir) . "' not writeable!<br />";
    }

    //note: we don't abort on error... like before.
    $tpl->clear_all_cache();
    $tpl->clear_compiled_tpl();

    return postcalendar_admin_modifyconfig('<div class="text-center">' . $spec_err . text(_PC_CACHE_CLEARED) . '</div>');
}

function postcalendar_admin_testSystem()
{
    $modinfo = pnModGetInfo(pnModGetIDFromName(__POSTCALENDAR__));
    $pcDir = pnVarPrepForOS($modinfo['directory']);
    $version = $modinfo['version'];
    unset($modinfo);

    $tpl = new pcSmarty();
    $infos = array();

    $__SERVER =& $_SERVER;
    $__ENV    =& $_ENV;

    if (defined('_PN_VERSION_NUM')) {
        $pnVersion = _PN_VERSION_NUM;
    } else {
        $pnVersion = pnConfigGetVar('Version_Num');
    }

    array_push($infos, array('CMS Version', $pnVersion));
    array_push($infos, array('Sitename', pnConfigGetVar('sitename')));
    array_push($infos, array('url', pnGetBaseURL()));
    array_push($infos, array('PHP Version', phpversion()));
    if ((bool) ini_get('safe_mode')) {
        $safe_mode = "On";
    } else {
        $safe_mode = "Off";
    }
    array_push($infos, array('PHP safe_mode', $safe_mode));
    if ((bool) ini_get('safe_mode_gid')) {
        $safe_mode_gid = "On";
    } else {
        $safe_mode_gid = "Off";
    }
    array_push($infos, array('PHP safe_mode_gid', $safe_mode_gid));
    $base_dir = ini_get('open_basedir');
    if (!empty($base_dir)) {
        $open_basedir = "$base_dir";
    } else {
        $open_basedir = "NULL";
    }
    array_push($infos, array('PHP open_basedir', $open_basedir));
    array_push($infos, array('SAPI', php_sapi_name()));
    array_push($infos, array('OS', php_uname()));
    array_push($infos, array('WebServer', $__SERVER['SERVER_SOFTWARE']));
    array_push($infos, array('Module dir', "modules/$pcDir"));

    $modversion = array();
    include  "modules/$pcDir/pnversion.php";

    $error = '';
    if ($modversion['version'] != $version) {
        $error  = '<br /><div class="text-danger">';
        $error .= "new version $modversion[version] installed but not updated!";
        $error .= '</div>';
    }
    array_push($infos, array('Module version', $version . " $error"));
    array_push($infos, array('smarty version', $tpl->_version));
    array_push($infos, array('smarty location',  SMARTY_DIR));
    array_push($infos, array('smarty template dir', $tpl->template_dir));

    $info = $tpl->compile_dir;
    $error = '';
    if (!file_exists($tpl->compile_dir)) {
        $error .= " compile dir doesn't exist! [" . text($tpl->compile_dir) . "]<br />";
    } else {
        // dir exists -> check if it's writeable
        if (!is_writeable($tpl->compile_dir)) {
            $error .= " compile dir not writeable! [" . text($tpl->compile_dir) . "]<br />";
        }
    }
    if (strlen($error) > 0) {
        $info .= "<br /><div class='text-danger'>$error</div>";
    }
    array_push($infos, array('smarty compile dir',  $info));

    if (AclMain::aclCheckCore('admin', 'super')) {
        $header = "<head><title>" . xlt("Diagnostics") . "</title></head><body>";
        $output = $header;
        $output .= '<div class="container mt-3"><div class="row"><div class="col-sm-12"><div class="clearfix">';
        $output .= '<h2>' . xlt('Diagnostics') . '</h2>';
        $output .= '</div></div></div>';
        $output .= '<div class="table-responsive"><table class="table table-bordered table-striped"><thead>';
        $output .= '<tr><th>' . xlt('Name') . '</th><th>' . xlt('Value') . '</th></tr></thead>';
        foreach ($infos as $info) {
            $output .= '<tr><td><b>' . pnVarPrepHTMLDisplay($info[0]) . '</b></td>';
            $output .= '<td>' . pnVarPrepHTMLDisplay($info[1]) . '</td></tr>';
        }
        $output .= '</table></div></div>';
        $output .= '<br /><br />';
        $output .= postcalendar_admin_modifyconfig('', false);
        $output .= "</body></html>";
        return $output;
    } else {
        die(xlt("Not Authorized"));
    }
}
