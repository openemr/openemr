<?php
/**
 * Patient Tracker (Patient Flow Board)
 *
 * This program displays the information entered in the Calendar program ,
 * allowing the user to change status and view those changed here and in the Calendar
 * Will allow the collection of length of time spent in each status
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Terry Hill <terry@lilysystems.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015-2017 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once "../globals.php";
require_once "$srcdir/patient.inc";
require_once "$srcdir/options.inc.php";
require_once "$srcdir/patient_tracker.inc.php";
require_once "$srcdir/user.inc";
require_once "$srcdir/MedEx/API.php";

// mdsupport - user_settings prefix
$uspfx = substr( __FILE__, strlen( $webserver_root ) ) . '.';
$setting_new_window = prevSetting( $uspfx, 'setting_new_window', 'setting_new_window', ' ' );
$setting_bootstrap_submenu = prevSetting( $webserver_root, 'setting_bootstrap_submenu', 'setting_bootstrap_submenu', ' ' );
if (($_POST['setting_new_window'])||($_POST['setting_bootstrap_submenu'])) { 
  // These settings are not part of the form. We only ever change them via ajax so exit now.
  // Currently only the flow boad and recall board point here to alter setting_bootstrap_menu.
  exit();  
}

if ( !is_null( $_POST['form_provider'] ) ) {
  $provider = $_POST['form_provider'];
} else if ( $_SESSION['userauthorized'] ) {
    $provider = $_SESSION['authUserID'];
} else if ( $_POST['form_provider'] == 'all' ) {
  $provider = null;
} else  { 
  $provider = null;
}

if ( $_POST['saveCALLback'] =="Save" ) {
  $sqlINSERT = "INSERT INTO medex_outgoing (msg_pc_eid,campaign_uid,msg_type,msg_reply,msg_extra_text)
                  VALUES
                (?,?,'NOTES','CALLED',?)";
  sqlQuery( $sqlINSERT, array( $_POST['pc_eid'], $_POST['campaign_uid'], $_POST['txtCALLback'] ) );
}

$facility  = !is_null( $_POST['form_facility'] ) ? $_POST['form_facility'] : null;

$form_apptstatus = !is_null( $_POST['form_apptstatus'] ) ? $_POST['form_apptstatus'] : null;

$form_apptcat=null;
if ( isset( $_POST['form_apptcat'] ) ) {
  if ( $form_apptcat!="all" ) {
    $form_apptcat=intval( $_POST['form_apptcat'] );
  }
}

$from_date = !is_null( $_REQUEST['datepicker1'] ) ? date( 'Y-m-d', strtotime( $_REQUEST['datepicker1'] ) ) : date( 'Y-m-d', strtotime( '-1 days' ) );

if ( substr( $GLOBALS['ptkr_end_date'], 0, 1 ) == 'Y' ) {
  $ptkr_time = substr( $GLOBALS['ptkr_end_date'], 1, 1 );
  $ptkr_future_time = mktime( 0, 0, 0, date( 'm' ), date( 'd' ), date( 'Y' )+$ptkr_time );
} elseif ( substr( $GLOBALS['ptkr_end_date'], 0, 1 ) == 'M' ) {
  $ptkr_time = substr( $GLOBALS['ptkr_end_date'], 1, 1 );
  $ptkr_future_time = mktime( 0, 0, 0, date( 'm' )+$ptkr_time, date( 'd' ), date( 'Y' ) );
} elseif ( substr( $GLOBALS['ptkr_end_date'], 0, 1 ) == 'D' ) {
  $ptkr_time = substr( $GLOBALS['ptkr_end_date'], 1, 1 );
  $ptkr_future_time = mktime( 0, 0, 0, date( 'm' ), date( 'd' )+$ptkr_time, date( 'Y' ) );
}

$to_date = date( 'Y-m-d', $ptkr_future_time );
$to_date = !is_null( $_REQUEST['datepicker2'] ) ? date( 'Y-m-d', strtotime( $_REQUEST['datepicker2'] ) ) : $to_date;
$form_patient_name = !is_null( $_POST['form_patient_name'] ) ? $_POST['form_patient_name'] : null;
$form_patient_id = !is_null( $_POST['form_patient_id'] ) ? $_POST['form_patient_id'] : null;

$appointments = array();
$appointments = fetch_Patient_Tracker_Events( $from_date, $to_date, $provider, $facility, $form_apptstatus, $form_apptcat, $form_patient_name, $form_patient_id );
$appointments = sortAppointments( $appointments, 'date', 'time' );
//grouping of the count of every status
$appointments_status = getApptStatus( $appointments );

$disp_from_date = oeFormatShortDate( $from_date );
$disp_to_date = oeFormatShortDate( $to_date );

$lres = sqlStatement( "SELECT option_id, title FROM list_options WHERE list_id = ? AND activity=1", array( 'apptstat' ) );
while ( $lrow = sqlFetchArray( $lres ) ) {
  // if exists, remove the legend character
  if ( $lrow['title'][1] == ' ' ) {
    $splitTitle = explode( ' ', $lrow['title'] );
    array_shift( $splitTitle );
    $title = implode( ' ', $splitTitle );
  } else {
    $title = $lrow['title'];
  }

  $statuses_list[$lrow['option_id']] = $title;
}


$MedEx = new MedExApi\MedEx( 'MedExBank.com' );
if ( $GLOBALS['medex_enable'] == '1' ) {
  $query2 = "SELECT * from medex_icons";
  $iconed = sqlStatement( $query2 );
  while ( $icon = sqlFetchArray( $iconed ) ) {
    $icons[$icon['msg_type']][$icon['msg_status']]['html'] = $icon['i_html'];
  }

  $logged_in = $MedEx->login();
  $sql = "SELECT * from medex_prefs LIMIT 1";
  $preferences = sqlStatement( $sql );
  $prefs = sqlFetchArray( $preferences );
  if ( $logged_in ) {
    $results  = $MedEx->campaign->events( $logged_in['token'] );
    foreach ( $results['events'] as $event ) {
      if ( $event['M_group'] != 'REMINDER' ) {
        continue;
      }
      $icon = $icons[$event['M_type']]['SCHEDULED']['html'];
      if ( $event['E_timing'] =='1' ) {
        $action = "before";
      }
      if ( $event['E_timing'] =='2' ) {
        $action = "before (PM)";
      }
      if ( $event['E_timing'] =='3' ) {
        $action = "after";
      }
      if ( $event['E_timing'] =='4' ) {
        $action = "after (PM)";
      }
      $days = ($event['E_fire_time']=='1') ? "day" : "days";
      $current_events .=  $icon." &nbsp; ".(int)$event['E_fire_time']." ".$days." ".xlt( $action )."<br />";
    }
  } else {
    $current_events = $icons['SMS']['FAILED']['html']." Currently off-line";
  }
}
if ( $GLOBALS['pat_trkr_timer'] == '0' ) {
  // if the screen is not set up for auto refresh, use standard page call
  $action_page = "patient_tracker.php";
} else {
  // if the screen is set up for auto refresh, this will allow it to be closed by auto logoff
  $action_page = "patient_tracker.php?skip_timeout_reset=1";
}

//ob_start();

//we need to respect facility and provider requests if submitted.
// 1.Retrieve everything for a given date range.
// 2.Refine results by facility and provider using jquery on cached results
//   ie. further requests to view facility/provider within page can be done fast through javascript, no page reload needed.
if ( !$_REQUEST['facility_selector'] ) {
  $_REQUEST['facility_selector'] = 'all';
}
if ( !$_REQUEST['provider_selector'] ) {
  $_REQUEST['provider_selector'] = 'all';
}

if ( $GLOBALS['pat_trkr_timer'] == '0' ) {
  // if the screen is not set up for auto refresh, use standard page call
  $action_page = "patient_tracker.php";
}
else {
  // if the screen is set up for auto refresh, this will allow it to be closed by auto logoff
  $action_page = "patient_tracker.php?skip_timeout_reset=1";
}
if (!$_REQUEST['flb_table']) {
  ?><html>
  <head>
    <title><?php echo xlt( 'Flow Board' ); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/jquery-ui-1-11-4/themes/excite-bike/jquery-ui.css">
    <?php if ( $_SESSION['language_direction'] == 'rtl' ) { ?>
    <link href="<?php echo $GLOBALS['assets_static_relative']; ?>/bootstrap-rtl-3-3-4/dist/css/bootstrap-rtl.min.css" rel="stylesheet" type="text/css" />
    <?php } ?>
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/bootstrap-3-3-4/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/font-awesome-4-6-3/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo $GLOBALS['assets_static_relative'] ?>/pure-0-5-0/pure-min.css">
    <link rel="stylesheet" href="<?php echo $GLOBALS['web_root']; ?>/library/css/bootstrap_navbar.css?v=<?php echo $v_js_includes; ?>" type="text/css">
    <link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

    <link rel="shortcut icon" href="<?php echo $webroot; ?>/sites/default/favicon.ico" />

    <script type="text/javascript" src="<?php echo $GLOBALS['web_root']; ?>/library/dialog.js?v=<?php echo $v_js_includes; ?>"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['web_root']; ?>/library/textformat.js?v=<?php echo $v_js_includes; ?>"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative'] ?>/jquery-min-3-1-1/index.js"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative'] ?>/jquery-ui-1-12-1/jquery-ui.min.js"></script>
    <script src="<?php echo $GLOBALS['assets_static_relative'] ?>/bootstrap-3-3-4/dist/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?php echo $GLOBALS['assets_static_relative'] ?>/moment-2-13-0/moment.js"></script>
    <script>
        var xljs1 = '<?php echo xl( 'Preferences updated successfully' ); ?>';
        var xljs_NOTE = '<?php echo xl( 'NOTE' ); ?>';
        var xljs_PthsApSched = '<?php echo xl( 'This patient already has an appointment scheduled for' ); ?>';
        var xljs_PlsDecRecDate = '<?php echo xl( 'Please decide on a Recall Date' ); ?>';
        <?php

        if ( $GLOBALS['date_display_format']=='0' ) {
          $date_format = 'yy-m-d';

        } elseif ( $GLOBALS['date_display_format']=='1' ) {
          $date_format = 'mm/dd/yy';

        } elseif ( $GLOBALS['date_display_format']=='2' ) {
          $date_format = 'dd/mm/yy';
        }
        ?>
        var xljs_dateFormat = '<?php echo $date_format; ?>';
    </script>
    <script type="text/javascript" src="<?php echo $GLOBALS['web_root']; ?>/interface/main/messages/js/reminder_appts.js?v=<?php echo $v_js_includes; ?>"></script>
    <script type="text/javascript">
        <?php require_once "$srcdir/restoreSession.php"; ?>
    </script>
    <script type="text/javascript" src="<?php echo $webroot ?>/interface/main/tabs/js/include_opener.js">
    </script>
    <script type="text/javascript">

    </script>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="MedEx Bank">
    <meta name="author" content="OpenEMR: MedExBank">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
      label { font-weight:400;}
      select { width:150px;}
      .nodisplay { display:none; }
      .btn {
        border: solid black 0.5pt;
        box-shadow: 3px 3px 3px #7b777760;
      }
      .Xui-tooltip-content {
        font-size:8pt;
        font-family:Calibri;
        background-color:#fff;
        border-radius:5px;
        padding:3px;
        color:#000;
        width:200px;
        white-space: pre-line;
      }
      .dialogIframe {
        border:none;
      }
      .scheduled {
        background-color:white;
        color:black;
        padding:5px;
      }
      .divTable {
        display: table;
        font-size: 1.0em;
        background: white;
        border: 1px solid #000;
        box-shadow: 0px 0px 9px #C0C0C0;
        border-radius: 8px;
        padding: 10px;
        width:100%;
        margin:0px auto;
      }
      .title {
        font-family: Georgia, serif;
        font-weight: bold;
        padding: 3px 10px;
        text-transform: uppercase;
        line-height: 1.5em;
        color: #455832;
        border-bottom: 2px solid #455832;
        margin: 0em auto;
        width: 70%;
      }
    </style>
  <?php echo myLocalJS(); ?>
   
    <script>
  
  </script>


  </head>

  <body class="body_top">
    <?php
  if ( ( $GLOBALS['medex_enable'] == '1' )&&( empty( $_REQUEST['nomenu'] ) ) ) {
      $MedEx->display->navigation($logged_in);
  }
  ?>
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-12">
        <div class="showRFlow" id="show_flows" style="text-align:center;margin:20 auto;">
          <?php

    $fac_sql = sqlStatement( "SELECT * FROM facility ORDER BY id" );
    while ( $fac = sqlFetchArray( $fac_sql ) ) {
      $true = ($fac['id'] == $_POST['form_facility']) ? "selected=true" : '';
      $select_facs .= "<option value=".attr( $fac['id'] )." ".$true.">".text( $fac['name'] )."</option>\n";
      $count_facs++;
    }
    $prov_sql = sqlStatement( "SELECT * FROM users WHERE authorized != 0 AND active = 1 ORDER BY lname, fname" );
    while ( $prov = sqlFetchArray( $prov_sql ) ) {
      $prov_name = $prov['fname']." ".$prov['lname'];
      if ( !empty( $prov['suffix'] ) ) {
        $prov_name .= ', '.text( $prov['suffix'] );
      }
      $true = ($prov['id'] == $_POST['form_provider']) ? "selected=true" : '';
      $select_provs .="<option value=".attr( $prov['id'] )." ".$true.">".text( $prov_name )."</option>\n";
      $count_provs++;
    }

    ?>
          <div class="title"><?php echo xlt( 'Flow Board' ); ?></div>
          <div name="div_response" id="div_response nodisplay"></div>
          <?php
      if ( $GLOBALS['medex_enable'] == '1' ) {
        $col_width="3";
      } else {
        $col_width="4";
        $last_col_width="nodisplay";
      }
      ?>
      <br />
      <form name="flb" id="flb" method="post">
        <div class=" text-center row divTable" style="width: 75%;padding: 10px 10px 0px;margin: 10px auto;">
              <div class="col-sm-<?php echo $col_width; ?> text-center" style="margin-top:15px;">
                <?php 
                  //$xl_All = xla('Appt{{abbreviation ofr Appointment}} Status: All');
                  //generate_form_field(array('data_type'=>1,'field_id'=>'apptstatus','list_id'=>'apptstat','empty_title'=>$xl_All),$_POST['form_apptstatus']);
                  //remove and replace Matrix code to add bootstrap class form-group and padding
                
                ?>
                <select id="form_apptcat" name="form_apptcat" class="form-group">
                  <?php
                  $categories=fetchAppointmentCategories();
                  echo "<option value='ALL'>".xlt("Categories")."</option>";
                  while($cat=sqlFetchArray($categories))
                  {
                      echo "<option value='".attr($cat['id'])."'";
                      if($cat['id']==$_POST['form_apptcat'])
                      {
                          echo " selected='true' ";
                      }
                      echo    ">".text(xl_appt_category($cat['category']))."</option>";
                  }
                  ?>
                </select>
                
                <select id="form_apptstatus" name="form_apptstatus" class="form-group">
                  <option value=''><?php echo xlt( "Appt Status: All" ); ?></option>

                  <?php
                  $apptstats=sqlStatement( "SELECT * FROM list_options WHERE list_id = 'apptstat' AND activity = 1 order by seq" );
                  while ( $apptstat=sqlFetchArray( $apptstats ) ) {
                    echo "<option value='".attr( $apptstat['option_id'] )."'";
                    if ( $apptstat['option_id']==$_POST['form_apptstatus'] ) {
                      echo " selected='true' ";
                    }
                    echo    ">".xlt( $apptstat['title'] ) ."</option>";
                  }
                  ?>
                </select>

                <input type="text" style="max-width:200px;" placeholder="<?php echo attr('Patient Name'); ?>" class="form-control input-sm" id="form_patient_name" name="form_patient_name" value="<?php echo ( $form_patient_name ) ? attr( $form_patient_name ) : ""; ?>">
             

              </div>
              <div class="col-sm-<?php echo $col_width; ?> text-center" style="margin-top:15px;">
                <select class="form-group" id="form_facility" name="form_facility" style="<?php
                  if ( $count_facs <'1' ) {
                    echo "display:none;";
                  }
                  ?>">
                  <option value="" selected><?php echo xlt( 'All Facilities' ); ?></option>
                  <?php  echo $select_facs;  ?>
                </select>

                <select class="form-group" id="form_provider" name="form_provider" style="<?php if ( $count_provs <'1' ) {
                  echo "display:none;"; } ?>">
                  <option value="" selected><?php echo xlt( 'All Providers' ); ?></option>
                  <?php echo $select_provs; ?>
                </select>

                <input placeholder="<?php echo attr('Patient ID'); ?>"  style="max-width:200px;" class="form-control input-sm" type="text" id="form_patient_id" name="form_patient_id" value="<?php echo ( $form_patient_id ) ? attr( $form_patient_id ) : ""; ?>">
              </div>
              <div class="col-sm-<?php echo $col_width; ?>">
                <div style="margin: 0px auto;" class="input-append">
                    <table class="table-hover table-condensed" style="margin:0px auto;">
                      <tr><td class="text-right" style="vertical-align:bottom;">
                        <label for="flow_from"><?php echo xlt('From'); ?>:</label></td><td>
                        <input type="date" id="datepicker1" name="datepicker1"
                                data-format="<?php echo $date_format; ?>"
                                class="form-control datepicker input-sm" value="<?php echo attr( $disp_from_date ); ?>" style="max-width:140px;min-width:85px;text-align: center;">
                      </td></tr>
                      <tr><td class="text-right" style="vertical-align:bottom;">
                        <label for="flow_to">&nbsp;&nbsp;<?php echo xlt('To'); ?>:</label></td><td>
                        <input type="date" id="datepicker2" name="datepicker2"
                                data-format="<?php echo $date_format; ?>"
                                class="form-control datepicker input-sm" value="<?php echo attr( $disp_to_date ); ?>" style="max-width:140px;min-width:85px;text-align: center;">
                      </td></tr>
                      <tr><td class="text-center" colspan="2">
                        <input href="#" class="css_button btn ui-buttons ui-widget ui-corner-all news" type="submit" id="filter_submit" value="<?php echo xla( 'Filter' ); ?>">
                      </td></tr>
                    </table>
                </div>
              </div>
              <div class="col-sm-<?php echo $col_width." ".$last_col_width; ?> text-center" >
                <div class="text-center" style="margin: 0 auto;">
                  <b><u>MedEx <?php echo xlt( 'Reminders' ); ?></u></b><br />
                  <div class="text-left" style="width: 70%;margin: 5px auto;">
                    <?php echo $current_events; ?>
                  </div>
                </div>
              </div>
              <div name="message" id="message" class="warning"></div>
              </div>
        </div>        
      </form>    
      <div class="row divTable">
        <div class="col-sm-12 text-center small" style='margin:5px;'>
            <?php
              $statuses_output =  "<span style='margin:0px 10px;'><em>".xlt( 'Total patients' )  . ':</em> <span class="badge">' . text( $appointments_status['count_all'] )."</span></span>";
              unset( $appointments_status['count_all'] );
              foreach ( $appointments_status as $status_symbol => $count ) {
                $statuses_output .= " | <span style='margin:0px 10px;'><em>" . text( xl_list_label( $statuses_list[$status_symbol] ) )  .":</em> <span class='badge'>" . $count."</span></span>";
              }
              echo $statuses_output;
            ?>
            <span class="pull-right">
              <a id='setting_cog'><i class="fa fa-cog fa-2x fa-fw">&nbsp;</i></a>
              
              <label for='setting_new_window' id='settings'>
                <input type='checkbox' name='setting_new_window' id='setting_new_window' value='<?php echo $setting_new_window; ?>' <?php echo $setting_new_window; ?> />
                <?php echo xlt( 'Open Patient in New Window' ); ?>
              </label>
              <a id='refreshme'><i class="fa fa-refresh fa-2x fa-fw">&nbsp;</i></a>
            </span>
        </div>
      
    
        <div class="col-sm-12 textclear" id="flb_table" name="flb_table">
        <?php 
  }
    //end of if !$_REQUEST['flb_table']
    ?>
    <table class="table table-responsive table-condensed table-hover table-bordered">
          <thead>
            <tr bgcolor="#cccff" class="small bold  text-center">
              <?php if ( $GLOBALS['ptkr_show_pid'] ) { ?>
              <td class="dehead text-center">
                <?php  echo xlt( 'PID' ); ?>
              </td>
              <?php } ?>
              <td class="dehead text-center" max-width="150px">
                <?php  echo xlt( 'Patient' ); ?>
              </td>
              <?php if ( $GLOBALS['ptkr_visit_reason'] ) { ?>
              <td class="dehead hidden-xs text-center">
                <?php  echo xlt( 'Reason' ); ?>
              </td>
              <?php } ?>
              <?php if ( $GLOBALS['ptkr_show_encounter'] ) { ?>
              <td class="dehead text-center">
                <?php  echo xlt( 'Encounter' ); ?>
              </td>
              <?php } ?>
              <td class="dehead hidden-xs text-center">
                <?php  echo xlt( 'Exam Room #' ); ?>
              </td>
              <td class="dehead visible-xs text-center">
                <?php  echo xlt( 'Room' ); ?>
              </td>

              <?php if ( $GLOBALS['ptkr_date_range'] ) { ?>
              <td class="dehead hidden-xs hidden-sm text-center">
                <?php  echo xlt( 'Appt Date' ); ?>
              </td>
              <?php } ?>
              <td class="dehead text-center">
                <?php  echo xlt( 'Appt Time' ); ?>
              </td>
              <td class="dehead hidden-xs hidden-sm text-center">
                <?php  echo xlt( 'Arrive Time' ); ?>
              </td>
              <td class="dehead visible-xs visible-sm text-center">
                <?php  echo xlt( 'Arrival' ); ?>
              </td>
              <td class="dehead hidden-xs hidden-sm text-center">
                <?php  echo xlt( 'Appt Status' ); ?>
              </td>
              <td class="dehead hidden-xs text-center">
                <?php  echo xlt( 'Current Status' ); ?>
              </td>
              <td class="dehead visible-xs text-center">
                <?php  echo xlt( 'Current' ); ?>
              </td>
              <td class="dehead hidden-xs hidden-sm text-center" max-width="150px">
                <?php  echo xlt( 'Visit Type' ); ?>
              </td>
              <?php if ( count( $chk_prov ) > 1 ) { ?>
              <td class="dehead text-center">
                <?php  echo xlt( 'Provider' ); ?>
              </td>
              <?php } ?>
              <td class="dehead text-center">
                <?php  echo xlt( 'Total Time' ); ?>
              </td>
              <td class="dehead  hidden-xs hidden-sm text-center">
                <?php  echo xlt( 'Check Out Time' ); ?>
              </td>
              <td class="dehead  visible-sm text-center">
                <?php  echo xlt( 'Out Time' ); ?>
              </td>
              <td class="dehead hidden-xs hidden-sm text-center">
                <?php  echo xlt( 'Updated By' ); ?>
              </td>
              <?php if ( $GLOBALS['drug_screen'] ) { ?>
              <td class="dehead center">
                <?php  echo xlt( 'Random Drug Screen' ); ?>
              </td>
              <td class="dehead center">
                <?php  echo xlt( 'Drug Screen Completed' ); ?>
              </td>
              <?php } ?>
            </tr>
          </thead>
          <tbody>
            <?php
            $prev_appt_date_time = "";
            foreach ( $appointments as $appointment ) {
              if ( empty( $room ) && ( $logged_in ) ) {
                //they are not here yet, display MedEx Reminder info
                //one icon per type of response.
                //If there was a SMS dialog, display it as a mouseover/title
                //Display date received also as mouseover title.
                $other_title = '';
                $title = '';
                $icon2_here ='';
                $icon_CALL = '';
                $icon_4_CALL = '';
                $appt['stage'] ='';
                $icon_here = array();
                $prog_text='';
                $CALLED='';
                $FINAL='';
                $icon_CALL = '';

                // Collect appt date and set up squashed date for use below
                $date_appt = $appointment['pc_eventDate'];
                $date_squash = str_replace( "-", "", $date_appt );

                $query = "Select * from medex_outgoing where msg_pc_eid =? order by msg_date";
                $myMedEx = sqlStatement( $query, array( $appointment['eid'] ) );
                /**
                 * Each row for this pc_eid in the medex_outgoing table represents an event.
                 * Every event is recorded in $prog_text.
                 * A modality is represented by an icon (eg mail icon, phone icon, text icon).
                 * The state of the Modality is represented by the color of the icon:
                 *      CONFIRMED       =   green
                 *      READ            =   blue
                 *      FAILED          =   pink
                 *      SENT/in process =   yellow
                 *      SCHEDULED       =   white
                 * Icons are displayed in their highest state.
                 */
                $FINAL='';
                while ( $row = sqlFetchArray( $myMedEx ) ) {
                  // Need to convert $row['msg_date'] to localtime (stored as GMT & then oeFormatShortDate it.
                  // I believe there is a new GLOBAL for server timezone???  If so, it will be easy.
                  // If not we need to import it from Medex through medex_preferences.  It should really be in openEMR though.
                  // Delete when we figure this out.
                  $other_title = '';
                  if ( !empty( $row['msg_extra_text'] ) ) {
                    $local = attr( $row['msg_extra_text'] )." |";
                  }
                  $prog_text .= oeFormatShortDate( $row['msg_date'] )." :: ".attr( $row['msg_type'] )." : ".attr( $row['msg_reply'] )." | ".$local." |";

                  if ( $row['msg_reply'] == 'Other' ) {
                    $other_title .= $row['msg_extra_text']."\n";
                    $icon_extra .= str_replace( "EXTRA",
                      oeFormatShortDate( $row['msg_date'] )."\n".xla( 'Patient Message' ).":\n".attr( $row['msg_extra_text'] )."\n",
                      $icons[$row['msg_type']]['EXTRA']['html'] );
                    continue;
                  }
                  elseif ( $row['msg_reply'] == "FAILED" ) {
                    $appointment[$row['msg_type']]['stage'] = "FAILED";
                    $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['FAILED']['html'];
                  }
                  elseif ( ( $row['msg_reply'] == "CONFIRMED" )||( $FINAL ) ) {
                    $appointment[$row['msg_type']]['stage'] = "CONFIRMED";
                    $FINAL = $icons[$row['msg_type']]['CONFIRMED']['html'];
                    $icon_here[$row['msg_type']] = $FINAL;
                    continue;
                  }
                  elseif ( $row['msg_type']=="NOTES" ) {
                    $CALLED="1";
                    $FINAL = $icons['NOTES']['CALLED']['html'];
                    $FINAL = str_replace( "Call Back: COMPLETED", oeFormatShortDate( $row['msg_date'] )." :: ".xla( 'Callback Performed' )." | ".xla( 'NOTES' ).": ".$row['msg_extra_text']." | ", $FINAL );
                    $icon_CALL = $icon_4_call;
                    continue;
                  }
                  elseif ( ( $row['msg_reply'] == "READ" )||( $appointment[$row['msg_type']]['stage']=="READ" ) ) {
                    $appointment[$row['msg_type']]['stage'] = "READ";
                    $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['READ']['html'];
                  }

                  elseif ( ( $row['msg_reply'] == "SENT" )||( $appointment[$row['msg_type']]['stage']=="SENT" ) ) {
                    $appointment[$row['msg_type']]['stage'] = "SENT";
                    $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['SENT']['html'];
                  }
                  elseif ( ( $row['msg_reply'] == "To Send" )||( empty( $appointment['stage'] ) ) ) {
                    if ( ( $appointment[$row['msg_type']]['stage']!="CONFIRMED" )&&
                      ( $appointment[$row['msg_type']]['stage']!="READ" )&&
                      ( $appointment[$row['msg_type']]['stage']!="SENT" )&&
                      ( $appointment[$row['msg_type']]['stage']!="FAILED" ) ) {
                      $appointment[$row['msg_type']]['stage']   = "QUEUED";
                      $icon_here[$row['msg_type']] = $icons[$row['msg_type']]['SCHEDULED']['html'];
                    }
                  }
                  //these are additional icons if present
                  if ( ( $row['msg_reply'] == "CALL" )&&( !$CALLED ) ) {
                    $icon_here ='';
                    $icon_4_CALL = $icons[$row['msg_type']]['CALL']['html'];
                    $icon_CALL = "<span onclick=\"doCALLback('".$date_squash."','".$appointment['eid']."','".$appointment['pc_cattype']."')\">".$icon_4_CALL."</span>
                                <span class='hidden' name='progCALLback_".$appointment['eid']."' id='progCALLback_".$appointment['eid']."'>
                                  <form  method='post'>
                                    <h4>Call Back Notes:</h4>
                                    <input type='hidden' name='pc_eid' id='pc_eid' value='".$appointment['eid']."'>
                                    <input type='hidden' name='campaign_uid' id='campaign_uid' value='".$row['campaign_uid']."'>
                                    <textarea name='txtCALLback' id='txtCALLback' rows=6 cols=20></textarea>
                                    <input type='submit' name='saveCALLback' id='saveCALLback' value='Save'>
                                  </form>
                                </span>
                                  ";
                  } elseif ( $row['msg_reply'] == "STOP" ) {
                    $icon2_here .= $icons[$row['msg_type']]['STOP']['html'];
                  } elseif ( $row['msg_reply'] == "Other" ) {
                    $icon2_here .= $icons[$row['msg_type']]['Other']['html'];
                  } elseif ( $row['msg_reply'] == "CALLED" ) {
                    $icon2_here .= $icons[$row['msg_type']]['CALLED']['html'];
                  }
                }
                //if pc_apptstatus == '-', update it now to=status
                if ( !empty( $other_title ) ) {
                  $appointment['messages']= $icon2_here.$icon_extra;
                }
              }

              // Collect variables and do some processing
              $chk_prov = array();  // list of providers with appointments

              $chk_prov[$appointment['uprovider_id']] = $appointment['ulname'] . ', ' . $appointment['ufname'] . ' ' . $appointment['umname'];
              $docname  = $chk_prov[$appointment['uprovider_id']];
              if ( strlen( $docname )<= 3 ) continue;
              $ptname = $appointment['lname'] . ', ' . $appointment['fname'] . ' ' . $appointment['mname'];
              $ptname_short = $appointment['fname'][0]." ".substr($appointment['lname'], 0, 10)."...";
              $appt_enc = $appointment['encounter'];
              $appt_eid = ( !empty( $appointment['eid'] ) ) ? $appointment['eid'] : $appointment['pc_eid'];
              $appt_pid = ( !empty( $appointment['pid'] ) ) ? $appointment['pid'] : $appointment['pc_pid'];
              if ( $appt_pid ==0 ) continue; // skip when $appt_pid = 0, since this means it is not a patient specific appt slot
              $status = ( !empty( $appointment['status'] )&&( !is_numeric( $appointment['status'] ) ) ) ?  $appointment['status'] : $appointment['pc_apptstatus'];
              $appt_room = ( !empty( $appointment['room'] ) ) ? $appointment['room'] : $appointment['pc_room'];
              $appt_time = ( !empty( $appointment['appttime'] ) ) ? $appointment['appttime'] : $appointment['pc_startTime'];
              $appt_date_time = $date_appt .' '. $appt_time;  // used to find flag double booked
              $tracker_id = $appointment['id'];
              // reason for visit
              if ( $GLOBALS['ptkr_visit_reason'] ) {
                $reason_visit = $appointment['pc_hometext'];
              }
              $newarrive = collect_checkin( $tracker_id );
              $newend = collect_checkout( $tracker_id );
              $colorevents = ( collectApptStatusSettings( $status ) );
              $bgcolor = $colorevents['color'];
              $statalert = $colorevents['time_alert'];
              // process the time to allow items with a check out status to be displayed
              if ( is_checkout( $status ) && ( ( $GLOBALS['checkout_roll_off'] > 0 ) && strlen( $form_apptstatus ) != 1 )  ) {
                $to_time = strtotime( $newend );
                $from_time = strtotime( $datetime );
                $display_check_out = round( abs( $from_time - $to_time ) / 60, 0 );
                if ( $display_check_out >= $GLOBALS['checkout_roll_off'] ) continue;
              }
                
              echo '<tr bgcolor="'.$bgcolor.'" 
                        class="apptstat_'.attr($appointment['status']).' 
                              facstat_'.attr($appointment['pc_facility']).' 
                              facility_'.attr($recall['r_facility']).' 
                              provider_'.attr($appointment['r_provider']).' text-small "
                              id="pid_'.attr($recall['pid']).'"
                              bgcolor="'.attr($bgcolor).'" >'; 
              if ( $GLOBALS['ptkr_show_pid'] ) { 
                ?>
                <td class="detail" align="center">
                  <?php echo text( $appt_pid ) ?>
                </td>
                  <?php 
              } 
              ?>
                          <td class="detail text-center hidden-xs hidden-sm">
                            <a href="#" onclick="return topatient('<?php echo attr( $appt_pid );?>','<?php echo attr( $appt_enc );?>')" >
                                <?php echo text( $ptname ); ?></a>
                          </td>
                          <td class="detail text-center visible-xs visible-sm" style"white-space: normal;">
                            <a href="#" onclick="return topatient('<?php echo attr( $appt_pid );?>','<?php echo attr( $appt_enc );?>')" >
                                <?php echo text( $ptname_short ); ?></a>
                          </td>
                            
                            <!-- reason -->
                            <?php if ( $GLOBALS['ptkr_visit_reason'] ) { ?>
                          <td class="detail hidden-xs" align="center">
                                <?php echo text( $reason_visit ) ?>
                          </td>
                            <?php } ?>
                            <?php if ( $GLOBALS['ptkr_show_encounter'] ) { ?>
                          <td class="detail" align="center">
                                <?php if ( $appt_enc != 0 ) {
                                echo text( $appt_enc );} ?>
                          </td>
                            <?php } ?>
                          <td class="detail" align="center">
                                <?php echo getListItemTitle( 'patient_flow_board_rooms', $appt_room );?>
                          </td>
                            <?php if ( $GLOBALS['ptkr_date_range'] ) { ?>
                          <td class="detail hidden-xs hidden-sm" align="center">
                                <?php echo oeFormatShortDate( $date_appt ); ?>
                          </td>
                            <?php } ?>
                          <td class="detail" align="center">
                            <?php echo oeFormatTime( $appt_time ) ?>
                          </td>
                          <td class="detail text-center">
                              <?php
                              if ( $newarrive ) {
                                echo oeFormatTime( $newarrive );
                              }
                              ?>
                          </td>
                          <td class="detail hidden-xs hidden-sm text-center small">
                            <?php if ( empty( $tracker_id ) ) { //for appt not yet with tracker id and for recurring appt ?>
                            <a onclick="return calendarpopup(<?php echo attr( $appt_eid ).",".attr( $date_squash ); // calls popup for add edit calendar event?>)">
                              <?php } else { ?>
                            <a onclick="return bpopup(<?php echo attr( $tracker_id ); // calls popup for patient tracker status?>)">
                              <?php }
                                echo text( getListItemTitle( "apptstat", $status ) ); // drop down list for appointment status
                              ?>
                            </a>
                          </td>

                            <?php
                              //time in current status
                              $to_time = strtotime( date( "Y-m-d H:i:s" ) );
                              $yestime = '0';
                              if ( strtotime( $newend ) != '' ) {
                                $from_time = strtotime( $newarrive );
                                $to_time = strtotime( $newend );
                                $yestime = '0';
                              } else {
                                $from_time = strtotime( $appointment['start_datetime'] );
                                $yestime = '1';
                              }

                              $timecheck = round( abs( $to_time - $from_time ) / 60, 0 );
                              if ( $timecheck >= $statalert && ( $statalert > '0' ) ) { // Determine if the time in status limit has been reached.
                                echo "<td class='text-center  js-blink-infinite small small'>  "; // and if so blink
                              } else {
                                echo "<td class='detail text-center'> "; // and if not do not blink
                              }
                              if ( ( $yestime == '1' ) && ( $timecheck >=1 ) && ( strtotime( $newarrive )!= '' ) ) {
                                echo text( $timecheck . ' ' .( $timecheck >=2 ? xl( 'minutes' ): xl( 'minute' ) ) );
                              } else if ( $icon_here||$icon2_here||$icon_CALL ) {
                                  echo  "<span style='font-size:0.7em;' onclick='return calendarpopup(". attr( $appt_eid ).",".attr( $date_squash ).")'>". implode( $icon_here ).$icon2_here."</span> ".$icon_CALL;
                                } else if ( $logged_in ) {
                                  $pat = $MedEx->display->possibleModalities($appointment);
                                  echo "<span style='font-size:0.7em;'>".$pat['SMS'].$pat['AVM'].$pat['EMAIL']."</span>";
                              }
                              //end time in current status
                              echo "</td>";
                              ?>
                        
                        <td class="detail hidden-xs hidden-sm" align="center">
                          <?php echo text( xl_appt_category( $appointment['pc_title'] ) ) ?>
                        </td>
                          <?php
                            if ( count( $chk_prov ) > 1 ) { ?>
                        <td class="detail text-center">
                              <?php echo text( $docname ); ?>
                        </td>
                          <?php
                            } ?>
                        <td class="detail text-center">
                          <?php
                            // total time in practice
                            if ( strtotime( $newend ) != '' ) {
                              $from_time = strtotime( $newarrive );
                              $to_time = strtotime( $newend );
                            } else {
                              $from_time = strtotime( $newarrive );
                              $to_time = strtotime( date( "Y-m-d H:i:s" ) );
                            }
                            $timecheck2 = round( abs( $to_time - $from_time ) / 60, 0 );
                            if ( strtotime( $newarrive ) != '' && ( $timecheck2 >=1 ) ) {
                              echo text( $timecheck2 . ' ' .( $timecheck2 >=2 ? xl( 'minutes' ): xl( 'minute' ) ) );
                            }
                            // end total time in practice
                            echo text( $appointment['pc_time'] ); ?>
                        </td>
                        <td class="detail hidden-xs text-center">
                          <?php
                            if ( $prog_text >'' ) {
                              echo  '
                                      <span  class="btn btn-primary" data-toggle="tooltip" title="'.$prog_text.'" style="padding:5px;" onclick="SMS_bot(\''.attr( $appointment['pc_eid'] ).'\')">
                                        
                                          <i class="fa fa-list-alt fa-inverse"></i>
                                      </span>
                                    
                                    <div class="jqui" style="display:none;">'. $prog_text .'</div>
                                    ';
                            } else if ( strtotime( $newend ) != '' ) {
                                echo oeFormatTime( $newend ) ;
                              }
                          ?>
                        </td>
                        <td class="detail hidden-xs hidden-sm" align="center">
                          <?php echo text( $appointment['user'] ) ?>
                        </td>
                          <?php 
                          if ( $GLOBALS['drug_screen'] ) { 
                                  if ( strtotime( $newarrive ) != '' ) { ?>
                                    <td class="detail" align="center">
                                            <?php 
                                              if ( text( $appointment['random_drug_test'] ) == '1' ) {
                                                    echo xl( 'Yes' );
                                                  } else {
                                                    echo xl( 'No' ); 
                                                  } ?>
                                    </td>
                                      <?php 
                                  }  ?>
                                  <?php 
                                  if ( strtotime( $newarrive ) != '' && $appointment['random_drug_test'] == '1' ) { ?>
                                  <td class="detail" align="center">
                                    <?php if ( strtotime( $newend ) != '' ) { // the following block allows the check box for drug screens to be disabled once the status is check out ?>
                                    <input type=checkbox  disabled='disable' class="drug_screen_completed" id="<?php echo htmlspecialchars( $appointment['pt_tracker_id'], ENT_NOQUOTES ) ?>"  <?php if ( $appointment['drug_screen_completed'] == "1" ) {
                                    echo "checked";}?>>
                                      <?php } else { ?>
                                      <input type=checkbox  class="drug_screen_completed" id='<?php echo htmlspecialchars( $appointment['pt_tracker_id'], ENT_NOQUOTES ) ?>' name="drug_screen_completed" <?php if ( $appointment['drug_screen_completed'] == "1" ) {
                                        echo "checked";}?>>
                                        <?php } ?>
                                      </td>
                                      <?php } else {
                                          echo "  <td>"; 
                                        }  
                          } ?>
                      </tr>
                      <?php
            } //end foreach
            ?>
          </tbody>
    </table>
    <?php 
  if (!$_REQUEST['flb_table']) { ?>
          </div>
        </div>
      </div>
    </div><?php //end container ?>
              <!-- form used to open a new top level window when a patient row is clicked -->
              <form name='fnew' method='post' target='_blank' action='../main/main_screen.php?auth=login&site=<?php echo attr( $_SESSION['site_id'] ); ?>'>
                <input type='hidden' name='patientID'      value='0' />
                <input type='hidden' name='encounterID'    value='0' />
              </form>


              <?php
              echo "</body></html>";
  } //end of second !$_REQUEST['flb_table']

//$content = ob_get_clean();
//echo $content;

exit;

function myLocalJS() {
  ?>
  <script language="JavaScript">
    function refreshMe(){
        var posting = $.post( '../patient_tracker/patient_tracker.php', { 
          flb_table         :  '1',
          datepicker1       : $("#datepicker1").val(),
          datepicker2       : $("#datepicker2").val(),
          form_facility     : $("#form_facility").val(),
          form_provider     : $("#form_provider").val(),
          form_apptstatus   : $("#form_apptstatus").val(),
          form_patient_name : $("#form_patient_naem").val(),
          form_patient_id   : $("#form_patient_id").val(),
          form_apptcat      : $("#form_apptcat").val()
        }).done(
          function( data ) {
            $( "#flb_table" ).html( data );
          });
    }
    // popup for patient tracker status
    function bpopup(tkid) {
     top.restoreSession();
     dlgopen('../patient_tracker/patient_tracker_status.php?tracker_id=' + tkid, '_blank', 500, 250);
     return false;
    }
    // popup for calendar add edit
    function calendarpopup(eid,date_squash) {
     top.restoreSession()
     dlgopen('../main/calendar/add_edit_event.php?eid=' + eid + '&date=' + date_squash, '_blank', 775, 500);
     return false;
    }
    // used to display the patient demographic and encounter screens
    function topatient(newpid, enc) {
      if ($('#setting_new_window').val() =='checked') {
        openNewTopWindow(newpid,enc);
      }
      else {
        top.restoreSession();
        if (enc > 0) {
          top.RTop.location= "<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/demographics.php?set_pid=" + newpid + "&set_encounterid=" + enc;
        }
        else {
          top.RTop.location = "<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/demographics.php?set_pid=" + newpid;
        }
      }
    }
    function doCALLback(eventdate,eid,pccattype) {
      $("#progCALLback_"+eid).parent().removeClass('js-blink-infinite').css('animation-name','none');
      $("#progCALLback_"+eid).removeClass("hidden");
      auto_refresh = '';
    }
    // opens the demographic and encounter screens in a new window
    function openNewTopWindow(newpid,newencounterid) {
      document.fnew.patientID.value = newpid;
      document.fnew.encounterID.value = newencounterid;
      top.restoreSession();
      document.fnew.submit();
    }
    function SMS_bot(eid) {
      top.restoreSession();
      //dlgopen('../main/messages/messages.php?nomenu=1&go=SMS_bot&pc_eid=' + eid,'_blank', 340,<?php echo $height; ?>);
      var from = '<?php echo attr( $from_date ); ?>';
      var to = '<?php echo attr( $to_date ); ?>';
      var oefrom = '<?php echo attr( oeFormatShortDate( $from_date ) ); ?>';
      var oeto = '<?php echo attr( oeFormatShortDate( $to_date ) ); ?>';
      window.open('../main/messages/messages.php?nomenu=1&go=SMS_bot&pc_eid=' + eid+'&to='+to+'&from='+from+'&oeto='+oeto+'&oefrom='+oefrom,'SMS_bot', 'width=370,height=600,resizable=0');
      return false;
    }

    $(document).ready(function() {
      $( "#datepicker1" ).datepicker({
                                   beforeShow: function() {
                                   setTimeout(function(){
                                              $('.ui-datepicker').css('z-index', 99999999999999);
                                              }, 0);
                                   },
                                   changeYear: true,
                                   defaultDate: "-1d",
                                   showButtonPanel: true,
                                   dateFormat: xljs_dateFormat,
                                   onSelect: function(dateText, inst) {
                                   $('#'+inst.id).attr('value',dateText);
                                   }
                                   });
        $( "#datepicker2" ).datepicker({
                                   beforeShow: function() {
                                   setTimeout(function(){
                                              $('.ui-datepicker').css('z-index', 99999999999999);
                                              }, 0);
                                   },
                                   changeYear: true,
                                   defaultDate: "+2d",
                                   showButtonPanel: true,
                                   dateFormat: xljs_dateFormat,
                                   onSelect: function(dateText, inst) {
                                   $('#'+inst.id).attr('value',dateText);
                                   }
                                   }); 
       
        <?php
        if ( $GLOBALS['pat_trkr_timer'] != '0' ) { 
            ?>
          var reftime="<?php echo attr( $GLOBALS['pat_trkr_timer'] ); ?>";
          var parsetime=reftime.split(":");
          parsetime=(parsetime[0]*60)+(parsetime[1]*1)*1000;
          var auto_refresh = setInterval(function(){
            refreshMe() // this will run after every parsetime seconds
            }, parsetime);
          <?php 
        } 
        ?>

      $('#settings').css("display","none");
      $('.js-blink-infinite').each(function() {
        // set up blinking text
        var elem = $(this);
        setInterval(function() {
          if (elem.css('visibility') == 'hidden') {
            elem.css('visibility', 'visible');
          } else {
            elem.css('visibility', 'hidden');
          }
        }, 500);
      });
      // toggle of the check box status for drug screen completed and ajax call to update the database
      $(".drug_screen_completed").change(function() {
        top.restoreSession();
        if (this.checked) {
          testcomplete_toggle="true";
        } else {
          testcomplete_toggle="false";
        }
        $.post( "../../library/ajax/drug_screen_completed.php", {
          trackerid: this.id,
          testcomplete: testcomplete_toggle
        });
      });

       
      // mdsupport - Immediately post changes to setting_new_window
      $('#setting_new_window').click(function () {
        $('#setting_new_window').val(this.checked ? 'checked' : ' ');
        $.post( "<?php echo basename( __FILE__ ) ?>", {
          'setting_new_window' : $('#setting_new_window').val(),
          success: function (data) {}
        });
      });

      $('#setting_cog').click(function () {
          $(this).css("display","none");
          $('#settings').css("display","inline");
      });

      $('#refreshme').click(function () {
          refreshMe();
      });

      $('#filter_submit').click(function (e) {
        e.preventDefault;
        refreshMe();
      });
    });

  </script>
  <?php
}

?>
