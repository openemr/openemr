<?php

/**
 * CAMOS note generator.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Mark Leeds <drleeds@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2006-2009 Mark Leeds <drleeds@gmail.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$depth = '../../../';
require_once($depth . 'interface/globals.php');
require_once("content_parser.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;

$session = SessionWrapperFactory::getInstance()->getActiveSession();
?>
<?php
if (!(filter_input(INPUT_POST, 'submit_pdf') || filter_input(INPUT_POST, 'submit_html')) && (filter_input(INPUT_GET, 'pid') && filter_input(INPUT_GET, 'encounter'))) {
    ?>
<html>
<head>
<title>
    <?php echo xlt('Print Notes'); ?>
</title>
</head>
<body>
    <?php echo xlt('Choose print format for this encounter report.'); ?><br /><br />
<form method=post name=choose_patients>
<input type="hidden" name="csrf_token_form" value="<?php echo CsrfUtils::collectCsrfToken(session: $session); ?>" />
<input type='submit' name='submit_pdf' value='<?php echo xla('Print (PDF)'); ?>'>
<input type='submit' name='submit_html' value='<?php echo xla('Print (HTML)'); ?>'>
</form>
</body>
</html>
    <?php
    exit;
}

if (!filter_input(INPUT_POST, 'submit_pdf') && !filter_input(INPUT_POST, 'submit_html') && !(filter_input(INPUT_GET, 'pid') && filter_input(INPUT_GET, 'encounter'))) {
    ?>
<html>
<head>

<title>
    <?php echo xlt('Print Notes'); ?>
</title>

    <?php Header::setupHeader('datetime-picker'); ?>

<script>
$(function () {
    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = false; ?>
        <?php require(OEGlobalsBag::getInstance()->getSrcDir() . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
});
</script>

</head>

<body>

<form method=post name=choose_patients>
<input type="hidden" name="csrf_token_form" value="<?php echo CsrfUtils::collectCsrfToken(session: $session); ?>" />

<table>
<tr><td>
<span class='text'><?php echo xlt('Start (yyyy-mm-dd): ') ?></span>
</td><td>
<input type='text' size='10' name='start' id='start' value='<?php echo ($postStart = filter_input(INPUT_POST, 'start')) ? attr($postStart) : date('Y-m-d') ?>'
class='datepicker'
title='<?php echo xla('yyyy-mm-dd last date of this event'); ?>' />
</td></tr>
<tr><td>
<span class='text'><?php echo xlt('End (yyyy-mm-dd): ') ?></span>
</td><td>
<input type='text' size='10' name='end' id='end' value ='<?php echo ($postEnd = filter_input(INPUT_POST, 'end')) ? attr($postEnd) : date('Y-m-d') ?>'
class='datepicker'
title='<?php echo xla('yyyy-mm-dd last date of this event'); ?>' />
</td></tr>
<tr><td></td><td></td></tr>
<tr><td><?php echo xlt('Last Name'); ?>: </td><td>
<input type='text' name='lname'/>
</td></tr>
<tr><td><?php echo xlt('First Name'); ?>: </td><td>
<input type='text' name='fname'/>
</td></tr>
<tr><td>
<input type='submit' name='submit_pdf' value='<?php echo xla('Print (PDF)'); ?>'>
<input type='submit' name='submit_html' value='<?php echo xla('Print (HTML)'); ?>'>
</td><td>
</td></tr>
</table>
</form>
</body>
</html>
    <?php
}

if (filter_input(INPUT_POST, 'submit_pdf') || filter_input(INPUT_POST, 'submit_html') || (filter_input(INPUT_GET, 'pid') && filter_input(INPUT_GET, 'encounter'))) {
    if (!CsrfUtils::verifyCsrfToken(filter_input(INPUT_POST, 'csrf_token_form') ?: '', session: $session)) {
        CsrfUtils::csrfNotVerified();
    }

    // note we are trimming variables before sending through this function
    $output = getFormData(trim(filter_input(INPUT_POST, 'start') ?: ''), trim(filter_input(INPUT_POST, 'end') ?: ''), trim(filter_input(INPUT_POST, 'lname') ?: ''), trim(filter_input(INPUT_POST, 'fname') ?: ''));
    ksort($output);

    if (filter_input(INPUT_POST, 'submit_html')) { //print as html
        ?>
        <html>
        <head>
        <style>
        body {
     font-family: sans-serif;
     font-weight: normal;
     font-size: 8pt;
     background: white;
     color: black;
    }
    .paddingdiv {
     width: 524pt;
     padding: 0pt;
    }
    .navigate {
     margin-top: 2.5em;
    }
    @media print {
     .navigate {
      display: none;
     }
    }
    div.page {
     page-break-after: always;
     padding: 0pt;
     margin-top: 50pt;
    }
    span.heading {
     font-weight: bold;
     font-size: 130%;
    }
    </style>
    <title><?php echo xl('Patient Notes'); ?></title>
    </head>
        <body>
    <div class='paddingdiv'>
        <?php
        foreach ($output as $dailynote) {
            foreach ($dailynote as $note_id => $notecontents) {
                $noteIdStr = (string) $note_id;
                preg_match('/(\d+)_(\d+)/', $noteIdStr, $matches); //the unique note id contains the pid and encounter
                $pid = (int) ($matches[1] ?? 0);
                $enc = (int) ($matches[2] ?? 0);

                //new page code here
                print "<DIV class='page'>";

                print xlt("Date") . ": " . text($notecontents['date']) . "<br/>";
                print xlt("Name") . ": " . text($notecontents['name']) . "<br/>";
                    print xlt("DOB") . ": " . text($notecontents['dob']) . "<br/>";
                    print xlt("Claim") . "# " . text($notecontents['pubpid']) . "<br/>";

                print "<br/>";
                print xlt("Chief Complaint") . ": " . text($notecontents['reason']) . "<br/>";
                if ($notecontents['vitals']) {
                    print "<br/>";
                    print text($notecontents['vitals']) . "<br/>";
                }

                if (count($notecontents['exam']) > 0) {
                    print "<br/>";
                    print "<span class='heading'>" . xlt("Progress Notes") . "</span><br/>";
                    print "<br/>";
                    foreach ($notecontents['exam'] as $examnote) {
                        print nl2br(text(replace($pid, $enc, $examnote))) . "<br/>";
                    }
                }

                if (count($notecontents['prescriptions']) > 0) {
                    print "<br/>";
                    print "<span class='heading'>" . xlt("Prescriptions") . "</span><br/>";
                    print "<br/>";
                    foreach ($notecontents['prescriptions'] as $rx) {
                        print nl2br(text(replace($pid, $enc, $rx))) . "<br/>";
                    }
                }

                if (count($notecontents['other']) > 0) {
                    print "<br/>";
                    print "<span class='heading'>" . xlt("Other") . "</span><br/>";
                    print "<br/>";
                    foreach ($notecontents['other'] as $other => $othercat) {
                        print nl2br(text($other)) . "<br/>";
                        foreach ($othercat as $items) {
                            print nl2br(text(replace($pid, $enc, $items))) . "<br/>";
                        }
                    }
                }

                if (count($notecontents['billing']) > 0) {
                    $tmp = [];
                    foreach ($notecontents['billing'] as $code) {
                        $tmp[$code] = ($tmp[$code] ?? 0) + 1;
                    }

                    print "<br/>";
                    print "<span class='heading'>" . xlt("Coding") . "</span><br/>";
                    print "<br/>";
                    foreach ($tmp as $code => $val) {
                        print nl2br(text($code)) . "<br/>";
                    }
                }

                if (count($notecontents['calories']) > 0) {
                    $sum = 0;
                    print "<br/>";
                    print "<span class='heading'>" . xlt("Calories") . "</span><br/>";
                    print "<br/>";
                    foreach ($notecontents['calories'] as $value) {
                        /** @var array{subcategory: string, item: string, content: string, date: string} $value */
                        print text($value['content']) . ' - ' . text($value['item']) . ' - ' . text($value['date']) . "<br/>";
                        $sum += (int) $value['content'];
                    }

                    print "--------" . "<br/>";
                    print text((string) $sum) . "<br/>";
                }

                print "<br/>";
                print "<br/>";
                print "<span class='heading'>" . xlt("Digitally Signed") . "</span><br/>";

                $query = QueryUtils::sqlStatementThrowException(
                    "SELECT t2.id, t2.fname, t2.lname, t2.title
                        FROM forms AS t1
                        JOIN users AS t2 ON (t1.user LIKE t2.username)
                        WHERE t1.pid = ?
                            AND t1.encounter = ?",
                    [$pid, $enc]
                );
                $name = '';
                $user_id = 0;
                /** @var array{id: int, fname: string, lname: string, title: string}|false $results */
                $results = QueryUtils::fetchArrayFromResultSet($query);
                if ($results) {
                    $name = $results['fname'] . " " . $results['lname'] . ", " . $results['title'];
                    $user_id = $results['id'];
                }

                $path = OEGlobalsBag::getInstance()->getProjectDir() . "/interface/forms/CAMOS";
                $safeName = convert_safe_file_dir_name($user_id);
                if (is_string($safeName) && file_exists($path . "/sig" . $safeName . ".jpg")) {
                //show the image here
                }

                print "<span class='heading'>" . text($name) . "</span><br/>";
                    print "</DIV>"; //end of last page
            }
        }
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
    } else { // print as pdf
        $pdf = new Cezpdf();
        $pdf->selectFont('Helvetica');
        $pdf->ezSetCmMargins(3, 1, 1, 1);
        $first = 1;
        foreach ($output as $dailynote) {
            foreach ($dailynote as $note_id => $notecontents) {
                $noteIdStr = (string) $note_id;
                preg_match('/(\d+)_(\d+)/', $noteIdStr, $matches); //the unique note id contains the pid and encounter
                $pid = (int) ($matches[1] ?? 0);
                $enc = (int) ($matches[2] ?? 0);
                if (!$first) { //generate a new page each time except first iteration when nothing has been printed yet
                    $pdf->ezNewPage();
                } else {
                    $first = 0;
                }

                $pdf->ezText(xl("Date") . ": " . $notecontents['date'], 8);
                $pdf->ezText(xl("Name") . ": " . $notecontents['name'], 8);
                        $pdf->ezText(xl("DOB") . ": " . $notecontents['dob'], 8);
                $pdf->ezText(xl("Claim") . "# " . $notecontents['pubpid'], 8);

                $pdf->ezText("", 8);
                $pdf->ezText(xl("Chief Complaint") . ": " . $notecontents['reason'], 8);
                if ($notecontents['vitals']) {
                    $pdf->ezText("", 8);
                    $pdf->ezText($notecontents['vitals'], 8);
                }

                if (count($notecontents['exam']) > 0) {
                    $pdf->ezText("", 8);
                    $pdf->ezText(xl("Progress Notes"), 12);
                    $pdf->ezText("", 8);
                    foreach ($notecontents['exam'] as $examnote) {
                        $pdf->ezText(replace($pid, $enc, $examnote));
                    }
                }

                if (count($notecontents['prescriptions']) > 0) {
                    $pdf->ezText("", 8);
                    $pdf->ezText(xl("Prescriptions"), 12);
                    $pdf->ezText("", 8);
                    foreach ($notecontents['prescriptions'] as $rx) {
                        $pdf->ezText(replace($pid, $enc, $rx));
                    }
                }

                if (count($notecontents['other']) > 0) {
                    $pdf->ezText("", 8);
                    $pdf->ezText(xl("Other"), 12);
                    $pdf->ezText("", 8);
                    foreach ($notecontents['other'] as $other => $othercat) {
                        $pdf->ezText($other, 8);
                        foreach ($othercat as $items) {
                            $pdf->ezText(replace($pid, $enc, $items), 8);
                        }
                    }
                }

                if (count($notecontents['billing']) > 0) {
                    $tmp = [];
                    foreach ($notecontents['billing'] as $code) {
                        $tmp[$code] = ($tmp[$code] ?? 0) + 1;
                    }

                    $pdf->ezText("", 8);
                    $pdf->ezText(xl("Coding"), 12);
                    $pdf->ezText("", 8);
                    foreach ($tmp as $code => $val) {
                        $pdf->ezText($code, 8);
                    }
                }

                if (count($notecontents['calories']) > 0) {
                    $sum = 0;
                    $pdf->ezText("", 8);
                    $pdf->ezText(xl("Calories"), 12);
                    $pdf->ezText("", 8);
                    foreach ($notecontents['calories'] as $value) {
                        /** @var array{subcategory: string, item: string, content: string, date: string} $value */
                        $pdf->ezText($value['content'] . ' - ' . $value['item'] . ' - ' . $value['date'], 8);
                        $sum += (int) $value['content'];
                    }

                    $pdf->ezText("--------", 8);
                    $pdf->ezText((string) $sum, 8);
                }

                $pdf->ezText("", 12);
                $pdf->ezText("", 12);
                $pdf->ezText(xl("Digitally Signed"), 12);

                $query = QueryUtils::sqlStatementThrowException(
                    "SELECT t2.id, t2.fname, t2.lname, t2.title
                        FROM forms AS t1
                        JOIN users AS t2 ON (t1.user LIKE t2.username)
                        WHERE t1.pid = ?
                            AND t1.encounter = ?",
                    [$pid, $enc]
                );
                $name = '';
                $user_id = 0;
                /** @var array{id: int, fname: string, lname: string, title: string}|false $results */
                $results = QueryUtils::fetchArrayFromResultSet($query);
                if ($results) {
                        $name = $results['fname'] . " " . $results['lname'] . ", " . $results['title'];
                        $user_id = $results['id'];
                }

                $path = OEGlobalsBag::getInstance()->getProjectDir() . "/interface/forms/CAMOS";
                $safeName = convert_safe_file_dir_name($user_id);
                if (is_string($safeName) && file_exists($path . "/sig" . $safeName . ".jpg")) {
                        $pdf->ezImage($path . "/sig" . $safeName . ".jpg", 0.0, 72.0, '', 'left', '');
                }

                $pdf->ezText($name, 12);
            }
        }

        $pdf->ezStream();
    }
}

/**
 * @return array<string, array<string, array{name: string, date: string, pubpid: string, dob: string, reason: string, vitals: string, exam: list<string>, prescriptions: list<string>, other: array<string, list<string>>, billing: list<string>, calories: list<array{subcategory: string, item: string, content: string, date: string}>}>>
 */
function getFormData(string $start_date, string $end_date, string $lname, string $fname): array
{
 //dates in sql format

        // All 4 parameters have previously been trimmed

    $binds = [];
    $where_clauses = [];
    $getPid = filter_input(INPUT_GET, 'pid', FILTER_VALIDATE_INT);
    $getEncounter = filter_input(INPUT_GET, 'encounter', FILTER_VALIDATE_INT);
    if ($getPid !== null && $getPid !== false && $getEncounter !== null && $getEncounter !== false) {
        $where_clauses[] = "t2.pid = ?";
        $binds[] = $getPid;
        $where_clauses[] = "t2.encounter = ?";
        $binds[] = $getEncounter;
    } else {
        $where_clauses[] = "DATE(t2.date) >= ?";
        $binds[] = $start_date;
        $where_clauses[] = "DATE(t2.date) <= ?";
        $binds[] = $end_date;
        if ($lname || $fname) {
            $where_clauses[] = "t3.lname LIKE ?";
            $binds[] = '%' . $lname . '%';
            $where_clauses[] = "t3.fname LIKE ?";
            $binds[] = '%' . $fname . '%';
        }
    }

    $dates = [];
    $query1 = QueryUtils::sqlStatementThrowException(
        "SELECT t1.form_id, t1.form_name, t1.pid,
                DATE_FORMAT(t2.date, '%m-%d-%Y') AS date,
                DATE_FORMAT(t2.date, '%Y%m%d') AS datekey,
                t3.lname, t3.fname, t3.pubpid,
                DATE_FORMAT(t3.DOB, '%m-%d-%Y') AS dob,
                t2.encounter AS enc,
                t2.reason
            FROM forms AS t1
            JOIN form_encounter AS t2
                ON (t1.pid = t2.pid AND t1.encounter = t2.encounter)
            JOIN patient_data AS t3
                ON (t1.pid = t3.pid)
            WHERE " . implode(" AND ", $where_clauses) . "
            ORDER BY date, pid",
        $binds
    );
    while ($results1 = QueryUtils::fetchArrayFromResultSet($query1)) {
        /** @var array{form_id: int, form_name: ?string, pid: int, date: string, datekey: string, lname: string, fname: string, pubpid: string, dob: string, enc: int, reason: ?string} $results1 */
        $datekey = $results1['datekey'];
        $pidEnc = $results1['pid'] . '_' . $results1['enc'];
        if (!isset($dates[$datekey])) {
            $dates[$datekey] = [];
        }

        if (!isset($dates[$datekey][$pidEnc])) {
            $dates[$datekey][$pidEnc] = [];
            $dates[$datekey][$pidEnc]['name'] = $results1['fname'] . ' ' . $results1['lname'];
            $dates[$datekey][$pidEnc]['date'] = $results1['date'];
            $dates[$datekey][$pidEnc]['pubpid'] = $results1['pubpid'];
            $dates[$datekey][$pidEnc]['dob'] = $results1['dob'];
            $dates[$datekey][$pidEnc]['vitals'] = '';
            $dates[$datekey][$pidEnc]['reason'] = $results1['reason'] ?? '';
            $dates[$datekey][$pidEnc]['exam'] = [];
            $dates[$datekey][$pidEnc]['prescriptions'] = [];
            $dates[$datekey][$pidEnc]['other'] = [];
            $dates[$datekey][$pidEnc]['billing'] = [];
            $dates[$datekey][$pidEnc]['calories'] = [];
        }

        // get ICD10 codes for this encounter
        $query2 = QueryUtils::sqlStatementThrowException(
            "SELECT *
                FROM billing
                WHERE encounter = ?
                    AND pid = ?
                    AND code_type LIKE 'ICD10'
                    AND activity = 1",
            [$results1['enc'], $results1['pid']]
        );
        /** @var array{code: string, code_text: string}|false $results2 */
        while ($results2 = QueryUtils::fetchArrayFromResultSet($query2)) {
            array_push(
                $dates[$datekey][$pidEnc]['billing'],
                $results2['code'] . ' ' . $results2['code_text']
            );
        }

        if (strtolower($results1['form_name'] ?? '') == 'vitals') { // deal with Vitals
            $query2 = QueryUtils::sqlStatementThrowException(
                "SELECT * FROM form_vitals WHERE id = ?",
                [$results1['form_id']]
            );
            /** @var array{height: ?string, weight: ?string, BMI: ?string, temperature: ?string, bps: ?string, bpd: ?string, pulse: ?string, respiration: ?string, oxygen_saturation: ?string}|false $results2 */
            if ($results2 = QueryUtils::fetchArrayFromResultSet($query2)) {
                $dates[$datekey][$pidEnc]['vitals'] = formatVitals($results2);
            }
        }

        if (str_starts_with(strtolower($results1['form_name'] ?? ''), 'camos')) { // deal with camos
            // escape_table_name() on a literal handles case-insensitive table name matching.
            $query2 = QueryUtils::sqlStatementThrowException(
                "SELECT category, subcategory, item, content,
                        DATE_FORMAT(date, '%h:%i %p') AS date
                    FROM " . escape_table_name("form_CAMOS") . "
                    WHERE id = ?",
                [$results1['form_id']]
            );
            /** @var array{category: string, subcategory: string, item: string, content: ?string, date: string}|false $results2 */
            if ($results2 = QueryUtils::fetchArrayFromResultSet($query2)) {
                if ($results2['category'] == 'exam') {
                    array_push($dates[$datekey][$pidEnc]['exam'], ($results2['content'] ?? ''));
                } elseif ($results2['category'] == 'prescriptions') {
                    array_push($dates[$datekey][$pidEnc]['prescriptions'], (string) preg_replace("/\n+/", ' ', ($results2['content'] ?? '')));
                } elseif ($results2['category'] == 'communications') {
                    //do nothing
                } elseif ($results2['category'] == 'calorie intake') {
                    $values = ['subcategory' => $results2['subcategory'],
                        'item' => $results2['item'],
                        'content' => ($results2['content'] ?? ''),
                        'date' => $results2['date']];
                    array_push($dates[$datekey][$pidEnc]['calories'], $values);
                } else {
                    if (!isset($dates[$datekey][$pidEnc]['other'][$results2['category']])) {
                        $dates[$datekey][$pidEnc]['other'][$results2['category']] = [];
                    }

                    array_push(
                        $dates[$datekey][$pidEnc]['other'][$results2['category']],
                        (string) preg_replace(["/\n+/","/patientname/i"], [' ',$results1['fname'] . ' ' . $results1['lname']], ($results2['content'] ?? ''))
                    );
                }
            }
        }
    }

    return $dates;
}
/**
 * @param array{height: ?string, weight: ?string, BMI: ?string, temperature: ?string, bps: ?string, bpd: ?string, pulse: ?string, respiration: ?string, oxygen_saturation: ?string} $raw
 */
function formatVitals(array $raw): string
{
 //pass raw vitals array, format and return as string
    $height = '';
    $weight = '';
    $bmi = '';
    $temp = '';
    $bp = '';
    $pulse = '';
    $respiration = '';
    $oxygen_saturation = '';
    if ($raw['height'] && $raw['height'] > 0) {
        $height = xl("HT") . ": " . $raw['height'] . " ";
    }

    if ($raw['weight'] && $raw['weight'] > 0) {
        $weight = xl("WT") . ": " . $raw['weight'] . " ";
    }

    if ($raw['BMI'] && $raw['BMI'] > 0) {
        $bmi = xl("BMI") . ": " . $raw['BMI'] . " ";
    }

    if ($raw['temperature'] && $raw['temperature'] > 0) {
        $temp = xl("Temp") . ": " . $raw['temperature'] . " ";
    }

    if ($raw['bps'] && $raw['bpd'] && $raw['bps'] > 0 && $raw['bpd'] > 0) {
        $bp = xl("BP") . ": " . $raw['bps'] . "/" . $raw['bpd'] . " ";
    }

    if ($raw['pulse'] && $raw['pulse'] > 0) {
        $pulse = xl("Pulse") . ": " . $raw['pulse'] . " ";
    }

    if ($raw['respiration'] && $raw['respiration'] > 0) {
        $respiration = xl("Respiration") . ": " . $raw['respiration'] . " ";
    }

    if ($raw['oxygen_saturation'] && $raw['oxygen_saturation'] > 0) {
        $oxygen_saturation = xl("O2 Sat") . ": " . $raw['oxygen_saturation'] . "% ";
    }

    $ret = $height . $weight . $bmi . $temp . $bp .
        $pulse . $respiration . $oxygen_saturation;
    if ($ret != '') {
        $ret = xl("Vital Signs") . ": " . $ret;
    }

    return $ret;
}
