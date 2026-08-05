<?php

/**
 * CAMOS rx_print.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Mark Leeds <drleeds@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2006-2009 Mark Leeds <drleeds@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../../globals.php');

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;

$session = SessionWrapperFactory::getInstance()->getActiveSession();

//practice data
$physician_name = '';
$practice_fname = '';
$practice_lname = '';
$practice_title = '';
$practice_address = '';
$practice_city = '';
$practice_state = '';
$practice_zip  = '';
$practice_phone = '';
$practice_fax = '';
$practice_license = '';
$practice_dea = '';
//patient data
$patient_name = '';
$patient_address = '';
$patient_city = '';
$patient_state = '';
$patient_zip = '';
$patient_phone = '';
$patient_dob = '';
$sigline = [];
$sigline['plain'] =
    "<div class='signature'>"
  . " ______________________________________________<br/>"
  . "</div>\n";
$sigline['embossed'] =
    "<div class='signature'>"
  . " _____________________________________________________<br/>"
#  . "Signature - Valid for three days and in Broward County only."
  . "Signature"
  . "</div>\n";
$sigline['signed'] =
    "<div class='sig'>"
  . "<img src='./sig.jpg'>"
  . "</div>\n";
$siglineValue = match (filter_input(INPUT_GET, 'sigline') ?? 'plain') {
    'embossed' => $sigline['embossed'],
    'signed' => $sigline['signed'],
    default => $sigline['plain'],
};
$query = QueryUtils::sqlStatementThrowException("SELECT fname, lname, street, city, state, postal_code, phone_home, DATE_FORMAT(DOB, '%m/%d/%y') AS DOB FROM patient_data WHERE pid = ?", [$session->get('pid')]);
/** @var array{fname: ?string, lname: ?string, street: ?string, city: ?string, state: ?string, postal_code: ?string, phone_home: ?string, DOB: ?string} $result */
if ($result = QueryUtils::fetchArrayFromResultSet($query)) {
    $patient_name = ($result['fname'] ?? '') . ' ' . ($result['lname'] ?? '');
    $patient_address = $result['street'] ?? '';
    $patient_city = $result['city'] ?? '';
    $patient_state = $result['state'] ?? '';
    $patient_zip = $result['postal_code'] ?? '';
    $patient_phone = $result['phone_home'] ?? '';
    $patient_dob = $result['DOB'] ?? '';
}

//update user information if selected from form
if (filter_input(INPUT_POST, 'update')) { // OPTION update practice inf
    if (!CsrfUtils::verifyCsrfToken(filter_input(INPUT_POST, 'csrf_token_form') ?? '', session: $session)) {
        CsrfUtils::csrfNotVerified();
    }

    QueryUtils::sqlStatementThrowException(
        "UPDATE users SET fname = ?, lname = ?, title = ?, street = ?, city = ?, state = ?, zip = ?, phone = ?, fax = ?, federaldrugid = ? WHERE id = ?",
        [
            filter_input(INPUT_POST, 'practice_fname') ?? '',
            filter_input(INPUT_POST, 'practice_lname') ?? '',
            filter_input(INPUT_POST, 'practice_title') ?? '',
            filter_input(INPUT_POST, 'practice_address') ?? '',
            filter_input(INPUT_POST, 'practice_city') ?? '',
            filter_input(INPUT_POST, 'practice_state') ?? '',
            filter_input(INPUT_POST, 'practice_zip') ?? '',
            filter_input(INPUT_POST, 'practice_phone') ?? '',
            filter_input(INPUT_POST, 'practice_fax') ?? '',
            filter_input(INPUT_POST, 'practice_dea') ?? '',
            $session->get('authUserID'),
        ]
    );
}

//get user information
$query = QueryUtils::sqlStatementThrowException("SELECT * FROM users WHERE id = ?", [$session->get('authUserID')]);
/** @var array{fname: ?string, lname: ?string, title: ?string, street: ?string, city: ?string, state: ?string, zip: ?string, phone: ?string, fax: ?string, federaldrugid: ?string} $userRow */
if ($userRow = QueryUtils::fetchArrayFromResultSet($query)) {
    $physician_name = ($userRow['fname'] ?? '') . ' ' . ($userRow['lname'] ?? '') . ', ' . ($userRow['title'] ?? '');
    $practice_fname = $userRow['fname'] ?? '';
    $practice_lname = $userRow['lname'] ?? '';
    $practice_title = $userRow['title'] ?? '';
    $practice_address = $userRow['street'] ?? '';
    $practice_city = $userRow['city'] ?? '';
    $practice_state = $userRow['state'] ?? '';
    $practice_zip  = $userRow['zip'] ?? '';
    $practice_phone = $userRow['phone'] ?? '';
    $practice_fax = $userRow['fax'] ?? '';
    $practice_dea = $userRow['federaldrugid'] ?? '';
}

// Cache escaped table name to avoid repeated SHOW TABLES lookups.
// escape_table_name() on a literal handles case-insensitive matching
// on MySQL installs where the actual table case differs from the code.
$tbl_camos = escape_table_name("form_CAMOS");

if (filter_input(INPUT_POST, 'print_pdf') || filter_input(INPUT_POST, 'print_html')) {
    if (!CsrfUtils::verifyCsrfToken(filter_input(INPUT_POST, 'csrf_token_form') ?? '', session: $session)) {
        CsrfUtils::csrfNotVerified();
    }
    $camos_content = [];
    $postData = filter_input_array(INPUT_POST) ?: [];
    foreach ($postData as $key => $val) {
        if (str_starts_with((string) $key, 'ch_')) {
            $chId = filter_var(substr((string) $key, 3), FILTER_VALIDATE_INT);
            if ($chId === false) {
                continue;
            }
            $query = QueryUtils::sqlStatementThrowException("SELECT content FROM $tbl_camos WHERE id = ? AND pid = ?", [$chId, $session->get('pid')]);
            /** @var array{content: ?string} $result */
            if ($result = QueryUtils::fetchArrayFromResultSet($query)) {
                $raw_content = $result['content'] ?? '';
                if (filter_input(INPUT_POST, 'print_html')) { //do this change to formatting only for html output
                            $content = preg_replace('|\n|', '<br/>', text($raw_content)) ?? '';
                            $content = preg_replace('|<br/><br/>|', '<br/>', $content) ?? '';
                } else {
                        $content = $raw_content;
                }

                  array_push($camos_content, $content);
            }
        }

        if (str_starts_with((string) $key, 'chrx_')) {
            $rx = new Prescription(substr((string) $key, 5));
            //$content = $rx->drug.' '.$rx->form.' '.$rx->dosage;
            $content = ''
            . text($rx->drug) . ' '
            . text($rx->size)
            . text($rx->unit_array[$rx->unit] ?? '') . '<br/>'
            . text($rx->quantity) . ' '
            . text($rx->form_array[$rx->form] ?? '') . '<br/>'
            . text($rx->dosage) . ' '
            . text($rx->form_array[$rx->form] ?? '') . ' '
            . text($rx->route_array[$rx->route] ?? '') . ' '
            . text($rx->interval_array[$rx->interval] ?? '') . '<br/>'
            . text($rx->note) . '<br/>'
            . 'refills:' . text((string) $rx->refills);
      //      . $rx->substitute_array[$rx->substitute]. ''
      //      . $rx->per_refill . '';
            array_push($camos_content, $content);
        }
    }

    if (!filter_input(INPUT_GET, 'letterhead')) { //OPTION print a prescription with css formatting
        ?>
  <html>
  <head>
<title>
        <?php echo xlt('CAMOS'); ?>
</title>
<link rel="stylesheet" href="./rx.css" />
</head>
<body onload='init()'>
<img src='./hline.jpg' id='hline'>
<img src='./vline.jpg' id='vline'>
        <?php
        if ($camos_content[0]) { //decide if we are printing this rx
            ?>
            <?php
            function topHeaderRx(string $physician_name, string $practice_address, string $practice_city, string $practice_state, string $practice_zip, string $practice_phone, string $practice_fax, string $practice_dea): void
            {
                print text($physician_name) . "<br/>\n";
                print text($practice_address) . "<br/>\n";
                print text($practice_city) . ", ";
                print text($practice_state) . " ";
                print text($practice_zip) . "<br/>\n";
                print xlt('Voice') . ': ' . text($practice_phone) . ' / ' . xlt('Fax') . ': ' . text($practice_fax) . "<br/>\n";
                print xlt('DEA') . ': ' . text($practice_dea);
            }
            function bottomHeaderRx(string $patient_name, string $patient_address, string $patient_city, string $patient_state, string $patient_zip, string $patient_phone, string $patient_dob): void
            {
                print "<span class='mytagname'> " . xlt('Name') . ":</span>\n";
                print "<span class='mydata'> " . text($patient_name) . " </span>\n";
                print "<span class='mytagname'> " . xlt('Address') . ": </span>\n";
                print "<span class='mydata'> " . text($patient_address) . ", " . text($patient_city) . ", " .
                text($patient_state) . " " . text($patient_zip) . " </span><br/>\n";
                print "<span class='mytagname'>" . xlt('Phone') . ":</span>\n";
                print "<span class='mydata'>" . text($patient_phone) . "</span>\n";
                print "<span class='mytagname'>" . xlt('DOB') . ":</span>\n";
                print "<span class='mydata'> " . text($patient_dob) . " </span>\n";
                print "<span class='mytagname'>" . xlt('Date') . ":</span>\n";
                print "<span class='mydata'>" . date("F d, Y") . "</span><br/><br/>\n";
                print "<div class='symbol'>" . xlt('Rx') . "</div><br/>\n";
            }
            ?>
<div id='rx1'  class='rx' >
<div class='topheader'>
            <?php
            topHeaderRx($physician_name, $practice_address, $practice_city, $practice_state, $practice_zip, $practice_phone, $practice_fax, $practice_dea);
            ?>
    </div>
    <hr/>
  <div class='bottomheader'>
            <?php
            bottomHeaderRx($patient_name, $patient_address, $patient_city, $patient_state, $patient_zip, $patient_phone, $patient_dob);
            ?>
  </div>
  <div class='content'>
            <?php
              print $camos_content[0];
            ?>
  </div>
            <?php echo $siglineValue ?>
</div> <!-- end of rx block -->
            <?php
        } else { // end of deciding if we are printing the above rx block
            print "<img src='./xout.jpg' id='rx1'>\n";
        }
        ?>
        <?php

        if ($camos_content[1]) { //decide if we are printing this rx
            ?>
<div id='rx2'  class='rx' >
<div class='topheader'>
            <?php

            topHeaderRx($physician_name, $practice_address, $practice_city, $practice_state, $practice_zip, $practice_phone, $practice_fax, $practice_dea);
            ?>
  </div>
    <hr/>
  <div class='bottomheader'>
            <?php

            bottomHeaderRx($patient_name, $patient_address, $patient_city, $patient_state, $patient_zip, $patient_phone, $patient_dob);
            ?>
  </div>
  <div class='content'>
            <?php

                print $camos_content[1];
            ?>
  </div>
            <?php echo $siglineValue ?>
</div> <!-- end of rx block -->
            <?php
        } else { // end of deciding if we are printing the above rx block
            print "<img src='./xout.jpg' id='rx2'>\n";
        }
        ?>
        <?php

        if ($camos_content[2]) { //decide if we are printing this rx
            ?>
<div id='rx3'  class='rx' >
<div class='topheader'>
            <?php

            topHeaderRx($physician_name, $practice_address, $practice_city, $practice_state, $practice_zip, $practice_phone, $practice_fax, $practice_dea);
            ?>
  </div>
    <hr/>
  <div class='bottomheader'>
            <?php

            bottomHeaderRx($patient_name, $patient_address, $patient_city, $patient_state, $patient_zip, $patient_phone, $patient_dob);
            ?>
  </div>
  <div class='content'>
            <?php

              print $camos_content[2];
            ?>
  </div>
            <?php echo $siglineValue ?>
</div> <!-- end of rx block -->
            <?php
        } else { // end of deciding if we are printing the above rx block
            print "<img src='./xout.jpg' id='rx3'>\n";
        }
        ?>
        <?php

        if ($camos_content[3]) { //decide if we are printing this rx
            ?>
<div id='rx4'  class='rx' >
<div class='topheader'>
            <?php

            topHeaderRx($physician_name, $practice_address, $practice_city, $practice_state, $practice_zip, $practice_phone, $practice_fax, $practice_dea);
            ?>
  </div>
    <hr/>
  <div class='bottomheader'>
            <?php

            bottomHeaderRx($patient_name, $patient_address, $patient_city, $patient_state, $patient_zip, $patient_phone, $patient_dob);
            ?>
  </div>
  <div class='content'>
            <?php

              print $camos_content[3];
            ?>
  </div>
            <?php echo $siglineValue ?>
</div> <!-- end of rx block -->
            <?php
        } else { // end of deciding if we are printing the above rx block
            print "<img src='./xout.jpg' id='rx4'>\n";
        }
        ?>
</body>
</html>
        <?php
    } elseif (filter_input(INPUT_GET, 'letterhead')) { // end of printing to rx not letterhead. OPTION print to letterhead
        $firstContent = $camos_content[0] ?? '';
        $content = preg_replace('/PATIENTNAME/i', $patient_name, $firstContent) ?? $firstContent;
        if (filter_input(INPUT_POST, 'print_html')) { // print letterhead to html
            ?>
        <html>
        <head>
        <style>
        body {
     font-family: sans-serif;
     font-weight: normal;
     font-size: 12pt;
     background: white;
     color: black;
    }
    .paddingdiv {
     width: 524pt;
     padding: 0pt;
     margin-top: 50pt;
    }
    .navigate {
     margin-top: 2.5em;
    }
    @media print {
     .navigate {
      display: none;
     }
    }
    </style>
      <title><?php echo xlt('Letter'); ?></title>
    </head>
        <body>
    <div class='paddingdiv'>
            <?php
    //bold
            print "<div style='font-weight:bold;'>";
            print text($physician_name) . "<br/>\n";
            print text($practice_address) . "<br/>\n";
            print text($practice_city) . ', ' . text($practice_state) . ' ' . text($practice_zip) . "<br/>\n";
            print text($practice_phone) . ' (' . xlt('Voice') . ')' . "<br/>\n";
            print text($practice_phone) . ' (' . xlt('Fax') . ')' . "<br/>\n";
            print "<br/>\n";
            print date("l, F jS, Y") . "<br/>\n";
            print "<br/>\n";
            print "</div>";
        //not bold
            print "<div style='font-size:90%;'>";
            print $content;
            print "</div>";
        //bold
            print "<div style='font-weight:bold;'>";
            print "<br/>\n";
            print "<br/>\n";
            if (filter_input(INPUT_GET, 'signer') == 'patient') {
                print "__________________________________________________________________________________" . "<br/>\n";
                print xlt("Print name, sign and date.") . "<br/>\n";
            } elseif (filter_input(INPUT_GET, 'signer') == 'doctor') {
                print xlt('Sincerely,') . "<br/>\n";
                print "<br/>\n";
                print "<br/>\n";
                print text($physician_name) . "<br/>\n";
            }

            print "</div>";
            ?>
        <script>
        var win = top.printLogPrint ? top : opener.top;
        win.printLogPrint(window);
        </script>
    </div>
        </body>
        </html>
            <?php
            exit;
        } else { //print letterhead to pdf
            $pdf = new Cezpdf();
            $pdf->selectFont('Times-Bold');
            $pdf->ezSetCmMargins(3, 1, 1, 1);
            $pdf->ezText($physician_name, 12);
            $pdf->ezText($practice_address, 12);
            $pdf->ezText($practice_city . ', ' . $practice_state . ' ' . $practice_zip, 12);
            $pdf->ezText($practice_phone . ' (' . xl('Voice') . ')', 12);
            $pdf->ezText($practice_phone . ' (' . xl('Fax') . ')', 12);
            $pdf->ezText('', 12);
            $pdf->ezText(date("l, F jS, Y"), 12);
            $pdf->ezText('', 12);
            $pdf->selectFont('Helvetica');
            $pdf->ezText($content, 10);
            $pdf->selectFont('Times-Bold');
            $pdf->ezText('', 12);
            $pdf->ezText('', 12);
            if (filter_input(INPUT_GET, 'signer') == 'patient') {
                $pdf->ezText("__________________________________________________________________________________", 12);
                $pdf->ezText(xl("Print name, sign and date."), 12);
            } elseif (filter_input(INPUT_GET, 'signer') == 'doctor') {
                $pdf->ezText(xl('Sincerely,'), 12);
                $pdf->ezText('', 12);
                $pdf->ezText('', 12);
                $pdf->ezText($physician_name, 12);
            }

            $pdf->ezStream();
        } // end of html vs pdf print
    }
} else { // end of if print. OPTION selection of what to print
    ?>
<html>
<head>
<title>
    <?php echo xlt('CAMOS'); ?>
</title>
<script>
//below init function just to demonstrate how to do it.
//now need to create 'cycle' function triggered by button to go by fours
//through selected types of subcategories.
//this is to be very very cool.
function init() {}
function checkall(){
var f = document.forms[0];
var x = f.elements.length;
var i;
for(i=0;i<x;i++) {
  if (f.elements[i].type == 'checkbox') {
    f.elements[i].checked = true;
  }
}
}
function uncheckall(){
var f = document.forms[0];
var x = f.elements.length;
var i;
for(i=0;i<x;i++) {
  if (f.elements[i].type == 'checkbox') {
    f.elements[i].checked = false;
  }
}
}
function cycle() {
var log = document.getElementById('log');
var cboxes = document.getElementById('checkboxes');
var cb = cboxes.getElementsByTagName('div');
if (cycle_engine(cb,0) == 0) {cycle_engine(cb,1);}
}
function cycle_engine(cb,seed) {
//seed determines if we should turn on up to first 4
var count_turnon = 0;
var count_turnoff = 0;
for (var i=0;i<cb.length;i++) {
  cbc = cb[i].childNodes;
  if (cbc[2].innerHTML == 'prescriptions') {
    if (cbc[1].checked == true) {
      cbc[1].checked = false;
      count_turnoff++;
    } else {
      if ((count_turnoff > 0 || seed == 1) && count_turnon < 4) {
        cbc[1].checked = true;
        count_turnon++;
      }
    }
  }
}
return count_turnoff;
}

</script>
<link rel="stylesheet" href="./rx.css" />
</head>
<h1><?php echo xlt('Select CAMOS Entries for Printing'); ?></h1>
<form method=POST name='pick_items' target=_new>
<input type="hidden" name="csrf_token_form" value="<?php echo CsrfUtils::collectCsrfToken(session: $session); ?>" />
<input type=button name=cyclerx value='<?php echo xla('Cycle'); ?>' onClick='cycle()'><br/>
<input type='button' value='<?php echo xla('Select All'); ?>' onClick='checkall()'>
<input type='button' value='<?php echo xla('Unselect All'); ?>' onClick='uncheckall()'>

    <?php if (filter_input(INPUT_GET, 'letterhead')) { ?>
<input type=submit name='print_pdf' value='<?php echo xla('Print (PDF)'); ?>'>
<?php } ?>

<input type=submit name='print_html' value='<?php echo xla('Print (HTML)'); ?>'>
    <?php

//check if an encounter is set
    if ($session->get('encounter') == null) {
        $query = QueryUtils::sqlStatementThrowException("SELECT x.id AS id, x.category, x.subcategory, x.item FROM $tbl_camos AS x JOIN forms AS y ON (x.id = y.form_id) WHERE y.pid = ? AND y.form_name LIKE 'CAMOS%' AND x.activity = 1", [$session->get('pid')]);
    } else {
        $query = QueryUtils::sqlStatementThrowException("SELECT x.id AS id, x.category, x.subcategory, x.item FROM $tbl_camos AS x JOIN forms AS y ON (x.id = y.form_id) WHERE y.encounter = ? AND y.pid = ? AND y.form_name LIKE 'CAMOS%' AND x.activity = 1", [$session->get('encounter'), $session->get('pid')]);
    }

    $results = [];
    echo "<div id='checkboxes'>\n";
    $count = 0;
    /** @var array{id: int, category: ?string, subcategory: ?string, item: ?string} $camosRow */
    while ($camosRow = QueryUtils::fetchArrayFromResultSet($query)) {
        $checked = '';
        $category = $camosRow['category'] ?? '';
        if ($category === 'prescriptions' && $count < 4) {
            $count++;
            $checked = 'checked';
        }

        echo "<div>\n";
        echo "<input type=checkbox name='ch_" . attr("{$camosRow['id']}") . "' $checked><span>" .
        text($category) . '</span>:' . text($camosRow['subcategory'] ?? '') . ':' . text($camosRow['item'] ?? '') . "<br/>\n";
        echo "</div>\n";
    }

    echo "</div>\n";
    echo "<div id='log'>\n";//temp for debugging
    echo "</div>\n";
//create Prescription object for the purpose of drawing data from the Prescription
//table for those who wish to do so
    $rxarray = Prescription::prescriptions_factory($session->get('pid'));
//now give a choice of drugs from the Prescription table
    foreach ($rxarray as $val) {
        echo "<input type=checkbox name='chrx_" . attr((string) $val->id) . "'>" .
        text($val->drug) . ':' . text($val->start_date) . "<br/>\n";
    }
    ?>

    <?php if (filter_input(INPUT_GET, 'letterhead')) { ?>
<input type=submit name='print_pdf' value='<?php echo xla('Print (PDF)'); ?>'>
<?php } ?>

<input type=submit name='print_html' value='<?php echo xla('Print (HTML)'); ?>'>
</form>
<h1><?php echo xlt('Update User Information'); ?></h1>
<form method=POST name='pick_items'>
<input type="hidden" name="csrf_token_form" value="<?php echo CsrfUtils::collectCsrfToken(session: $session); ?>" />
<table>
<tr>
<td> <?php echo xlt('First Name'); ?>: </td>
<td> <input type=text name=practice_fname value ='<?php echo attr($practice_fname); ?>'> </td>
</tr>
<tr>
<td> <?php echo xlt('Last Name'); ?>: </td>
<td> <input type=text name=practice_lname value ='<?php echo attr($practice_lname); ?>'> </td>
</tr>
<tr>
<td> <?php echo xlt('Title'); ?>: </td>
<td> <input type=text name=practice_title value ='<?php echo attr($practice_title); ?>'> </td>
</tr>
<tr>
<td> <?php echo xlt('Street Address'); ?>: </td>
<td> <input type=text name=practice_address value ='<?php echo attr($practice_address); ?>'> </td>
</tr>
<tr>
<td> <?php echo xlt('City'); ?>: </td>
<td> <input type=text name=practice_city value ='<?php echo attr($practice_city); ?>'> </td>
</tr>
<tr>
<td> <?php echo xlt('State'); ?>: </td>
<td> <input type=text name=practice_state value ='<?php echo attr($practice_state); ?>'> </td>
</tr>
<tr>
<td> <?php echo xlt('Zip'); ?>: </td>
<td> <input type=text name=practice_zip value ='<?php echo attr($practice_zip); ?>'> </td>
</tr>
<tr>
<td> <?php echo xlt('Phone'); ?>: </td>
<td> <input type=text name=practice_phone value ='<?php echo attr($practice_phone); ?>'> </td>
</tr>
<tr>
<td> <?php echo xlt('Fax'); ?>: </td>
<td> <input type=text name=practice_fax value ='<?php echo attr($practice_fax); ?>'> </td>
</tr>
<tr>
<td> <?php echo xlt('DEA'); ?>: </td>
<td> <input type=text name=practice_dea value ='<?php echo attr($practice_dea); ?>'> </td>
</tr>
</table>
<input type=submit name=update value='<?php echo xla('Update'); ?>'>
</form>
    <?php
} //end of else statement
?>
</body>
</html>
