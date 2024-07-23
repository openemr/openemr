<?php

/**
 * add or edit a medical problem.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Thomas Pantelis <tompantelis@gmail.com>
 * @copyright Copyright (c) 2005-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Thomas Pantelis <tompantelis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once '../../globals.php';
require_once $GLOBALS['srcdir'] . '/lists.inc.php';
require_once $GLOBALS['srcdir'] . '/patient.inc.php';
require_once $GLOBALS['srcdir'] . '/options.inc.php';
require_once $GLOBALS['fileroot'] . '/custom/code_types.inc.php';
require_once $GLOBALS['srcdir'] . '/csv_like_join.php';

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\MedicalDevice\MedicalDevice;
use OpenEMR\Services\PatientIssuesService;

// TBD - Resolve functional issues if opener is included in Header
?>
<script src="<?php echo $webroot ?>/interface/main/tabs/js/include_opener.js?v=<?php echo $v_js_includes; ?>"></script>
<script>
    <?php require $GLOBALS['srcdir'] . '/formatting_DateToYYYYMMDD_js.js.php'; ?>
</script>
<?php

if (!empty($_POST['form_save'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    // Following hidden field received in the form will be used to ensure integrity of form values
    // 'issue', 'thispid', 'thisenc'
    $issue = $_POST['issue'];
    $thispid = $_POST['thispid'];
    $thisenc = $_POST['thisenc'];
} else {
    $issue = $_REQUEST['issue'] ?? null;
    $thispid = (int) (empty($_REQUEST['thispid']) ? $pid : $_REQUEST['thispid']);
    // A nonempty thisenc means we are to link the issue to the encounter.
    $thisenc = 0 + (empty($_REQUEST['thisenc']) ? 0 : $_REQUEST['thisenc']);
}
// NOTE: $ISSUE_TYPES is defined in lists.inc.php
if (isset($ISSUE_TYPES['ippf_gcac'])) {
    if ($ISSUE_TYPES['ippf_gcac']) {
        // Similarly for IPPF issues.
        require_once $GLOBALS['srcdir'] . '/ippf_issues.inc.php';
    }
}

$info_msg = "";

// A nonempty thistype is an issue type to be forced for a new issue.
$thistype = empty($_REQUEST['thistype']) ? '' : $_REQUEST['thistype'];

if ($thistype && !$issue && !AclMain::aclCheckIssue($thistype, '', array('write', 'addonly'))) {
    die(xlt("Add is not authorized!"));
}

$tmp = getPatientData($thispid, "squad");
if ($tmp['squad'] && !AclMain::aclCheckCore('squads', $tmp['squad'])) {
    die(xlt("Not authorized for this squad!"));
}

function QuotedOrNull($fld)
{
    return ($fld) ? "'" . add_escape_custom($fld) . "'" : "NULL";
}

function rbinput($name, $value, $desc, $colname)
{
    global $irow;
    $_p = [
        attr($name),
        attr($value),
        ($irow[$colname] == $value) ? " checked" : "",
        text($desc)
    ];
    $str = '<input type="radio" name="%s" value="%s" %s>%s';
    return vsprintf($str, $_p);
}

// Given an issue type as a string, compute its index.
function issueTypeIndex($tstr)
{
    global $ISSUE_TYPES;
    $i = 0;
    foreach ($ISSUE_TYPES as $key => $value) {
        if ($key == $tstr) {
            break;
        }
        ++$i;
    }
    return $i;
}

function ActiveIssueCodeRecycleFn($thispid2, $ISSUE_TYPES2)
{
    ///////////////////////////////////////////////////////////////////////
    // Active Issue Code Recycle Function authored by epsdky (2014-2015) //
    ///////////////////////////////////////////////////////////////////////

    $modeIssueTypes = array();
    $issueTypeIdx2 = array();
    $idx2 = 0;

    foreach ($ISSUE_TYPES2 as $issueTypeX => $isJunk) {
        $modeIssueTypes[$idx2] = $issueTypeX;
        $issueTypeIdx2[$issueTypeX] = $idx2;
        ++$idx2;
    }

    $pe2 = array($thispid2);
    $qs2 = str_repeat('?, ', count($modeIssueTypes) - 1) . '?';
    $sqlParameters2 = array_merge($pe2, $modeIssueTypes);

    $codeList2 = array();

    $issueCodes2 = sqlStatement(
        "SELECT diagnosis FROM lists WHERE pid = ? AND enddate is NULL AND type IN ($qs2)",
        $sqlParameters2
    );

    while ($issueCodesRow2 = sqlFetchArray($issueCodes2)) {
        if ($issueCodesRow2['diagnosis'] != "") {
            $someCodes2 = explode(";", $issueCodesRow2['diagnosis']);
            $codeList2 = array_merge($codeList2, $someCodes2);
        }
    }

    if ($codeList2) {
        $codeList2 = array_unique($codeList2);
        sort($codeList2);
    }

    $memberCodes = array();
    $memberCodes[0] = array();
    $memberCodes[1] = array();
    $memberCodes[2] = array();

    $allowedCodes2 = array();
    $allowedCodes2[0] = collect_codetypes("medical_problem");
    $allowedCodes2[1] = collect_codetypes("diagnosis");
    $allowedCodes2[2] = collect_codetypes("drug");

    // Test membership of codes to each code type set
    foreach ($allowedCodes2 as $akey1 => $allowCodes2) {
        foreach ($codeList2 as $listCode2) {
            list($codeTyX,) = explode(":", $listCode2);

            if (in_array($codeTyX, $allowCodes2)) {
                array_push($memberCodes[$akey1], $listCode2);
            }
        }
    }

    // output sets of display options
    $displayCodeSets[0] = $memberCodes[0]; // medical_problem
    $displayCodeSets[1] = array_merge($memberCodes[1], $memberCodes[2]);  // allergy
    $displayCodeSets[2] = array_merge($memberCodes[2], $memberCodes[1]);  // medication
    $displayCodeSets[3] = $memberCodes[1];  // default

    echo "var listBoxOptionSets = new Array();\n\n";

    foreach ($displayCodeSets as $akey => $displayCodeSet) {
        echo "listBoxOptionSets[" . attr($akey) . "] = new Array();\n";

        if ($displayCodeSet) {
            foreach ($displayCodeSet as $code) {
                $text = getCodeText($code);
                echo "listBoxOptionSets[" .
                    attr($akey) .
                    "][listBoxOptionSets[" .
                    attr($akey) .
                    "].length] = new Option(" .
                    js_escape($text) .
                    ", " . js_escape($code) .
                    ", false, false);\n";
            }
        }
    }

    // map issues to a set of display options
    $modeIndexMapping = array();

    foreach ($modeIssueTypes as $akey2 => $isJunk) {
        $modeIndexMapping[$akey2] = 3;
    }

    if (array_key_exists("medical_problem", $issueTypeIdx2)) {
        $modeIndexMapping[$issueTypeIdx2['medical_problem']] = 0;
    }

    if (array_key_exists("allergy", $issueTypeIdx2)) {
        $modeIndexMapping[$issueTypeIdx2['allergy']] = 1;
    }

    if (array_key_exists("medication", $issueTypeIdx2)) {
        $modeIndexMapping[$issueTypeIdx2['medication']] = 2;
    }

    echo "\nvar listBoxOptions2 = new Array();\n\n";

    foreach ($modeIssueTypes as $akey2 => $isJunk) {
        echo "listBoxOptions2[" . attr($akey2) . "] = listBoxOptionSets[" . attr($modeIndexMapping[$akey2]) . "];\n";
    }

    ///////////////////////////////////////////////////////////////////////
    // End of Active Issue Code Recycle Function main code block         //
    ///////////////////////////////////////////////////////////////////////
}

// If we are saving, then save and close the window.
//
if (!empty($_POST['form_save'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $i = 0;
    $text_type = "unknown";
    foreach ($ISSUE_TYPES as $key => $value) {
        if ($i++ == $_POST['form_type']) {
            $text_type = $key;
        }
    }

    $form_begin = !empty($_POST['form_begin']) ? DateTimeToYYYYMMDDHHMMSS($_POST['form_begin']) : null;
    $form_end   = !empty($_POST['form_end']) ? DateTimeToYYYYMMDDHHMMSS($_POST['form_end']) : null;
    $form_return = !empty($_POST['form_return']) ? DateToYYYYMMDD($_POST['form_return']) : null;

    $form_injury_part = $_POST['form_medical_system'] ?? '';
    $form_injury_type = $_POST['form_medical_type'] ?? '';

    $issueRecord = [
        'type' => $text_type
        ,'begdate' => $form_begin ?? null
        ,'enddate' => $form_end ?? null
        ,'returndate' => $form_return ?? null
        ,'erx_uploaded' => '0'
        ,'id' => $issue ?? null
        ,'pid' => $thispid
    ];
    // TODO: we could simplify this array by just adding 'form_' onto everything
    // but not all of the fields precisely match so that would need to be fixed up
    $issue_form_fields = [
        'title' => 'form_title',
        'udi' => 'form_udi',
        'udi_data' => 'udi_data',
        'comments' => 'form_comments',
        'diagnosis' => 'form_diagnosis',
        'occurrence' => 'form_occur',
        'classification' => 'form_classification',
        'reinjury_id' => 'form_reinjury_id',
        'referredby' => 'form_referredby',
        'injury_grade' => 'form_injury_grade',
        'outcome' => 'form_outcome',
        'destination' => 'form_destination',
        'reaction' => 'form_reaction',
        'verification' => 'form_verification',
        'severity_al' => 'form_severity_id',
        'list_option_id' => 'form_title_id',
        'subtype' => 'form_subtype'
    ];
    foreach ($issue_form_fields as $field => $form_field) {
        if (isset($_POST[$form_field])) {
            $issueRecord[$field] = $_POST[$form_field];
        }
    }

    // now populate medication
    if (isset($_POST['form_medication'])) {
        $issueRecord['medication'] = $_POST['form_medication'];
    }

    $patientIssuesService = new PatientIssuesService();
    if ($issue) {
        $patientIssuesService->updateIssue($issueRecord);
    } else {
        $issueRecord["date"] = date("Y-m-d H:m:s");
        $issueRecord['activity'] = 1;
        $issueRecord['user'] = $_SESSION['authUser'];
        $issueRecord['groupname'] = $_SESSION['authProvider'];
        $patientIssuesService->createIssue($issueRecord);
    }

    // For record/reporting purposes, place entry in lists_touch table.
    setListTouch($thispid, $text_type);

    if ($text_type == 'ippf_gcac') {
        issue_ippf_gcac_save($issue);
    }

    if ($text_type == 'contraceptive') {
        issue_ippf_con_save($issue);
    }

    // If requested, link the issue to a specified encounter.
    if ($thisenc) {
        $sql = "INSERT INTO issue_encounter(pid, list_id, encounter) VALUES (?, ?, ?)";
        sqlStatement($sql, [$thispid, $issue, $thisenc]);
    }

    $tmp_title = $ISSUE_TYPES[$text_type][2] . ": $form_begin " . substr($_POST['form_title'], 0, 40);

    // Close this window and redisplay the updated list of issues.
    //
    echo "<html><body><script>\n";
    if ($info_msg) {
        echo " alert(" . js_escape($info_msg) . ");\n";
    }

    echo " var myboss = opener ? opener : parent;\n";
    echo " if (myboss.refreshIssue) myboss.refreshIssue(" . js_escape($issue) . "," . js_escape($tmp_title) . ");\n";
    echo " else if (myboss.reloadIssues) myboss.reloadIssues();\n";
    echo " else myboss.location.reload();\n";
    echo " dlgclose();\n";

    echo "</script></body></html>\n";
    exit();
}

$irow = array();
if ($issue) {
    $patientIssuesService = new PatientIssuesService();
    $irow = $patientIssuesService->getOneById($issue);
    if (!AclMain::aclCheckIssue($irow['type'], '', 'write')) {
        die(xlt("Edit is not authorized!"));
    }
} elseif ($thistype) {
    $irow['type'] = $thistype;
}

$type_index = 0;

if (!empty($irow['type'])) {
    foreach ($ISSUE_TYPES as $key => $value) {
        if ($key == $irow['type']) {
            break;
        }

        ++$type_index;
    }
}

$code_texts = array();

function getCodeText($code)
{
    global $code_texts;
    if (array_key_exists($code, $code_texts)) {
        return $code_texts[$code];
    }

    $codedesc = lookup_code_descriptions($code);
    $text = $code;
    if ($codedesc) {
        $text .= " (" . $codedesc . ")";
    }

    $code_texts[$code] = $text;
    return $text;
}

?>
<html>
<head>
<?php Header::setupHeader(['common', 'datetime-picker', 'select2']); ?>
<title><?php echo ($issue) ? xlt('Edit Issue') : xlt('Add New Issue'); ?></title>

<style>
    div.section {
        border: 1px solid var(--primary) !important;
        margin: 0 0 0 13px;
        padding: 7px;
    }

    /* Override theme's selected tab top color so it matches tab contents. */
    ul.tabNav li.current a {
        background: var(--white);
    }
</style>

<script>
    var aitypes = new Array(); // issue type attributes
    var aopts = new Array(); // Option objects
    var codeTexts = new Map()
    <?php
    $i = 0;
    foreach ($ISSUE_TYPES as $key => $value) {
        echo " aitypes[" . attr($i) . "] = " . js_escape($value[3]) . ";\n";
        echo " aopts[" . attr($i) . "] = new Array();\n";
        $qry = sqlStatement(
            "SELECT * FROM list_options WHERE list_id = ? AND activity = 1",
            array($key . "_issue_list")
        );
        while ($res = sqlFetchArray($qry)) {
            echo " opt = new Option(" .
                js_escape(xl_list_label(trim($res['title']))) .
                ", " .
                js_escape(trim($res['option_id'])) .
                ", false, false);\n";
            echo " aopts[" . attr($i) . "][aopts[" . attr($i) . "].length] = opt\n";
            if ($res['codes']) {
                $codes = explode(";", $res['codes']);
                foreach ($codes as $code) {
                    $text = getCodeText($code);
                    echo " codeTexts.set(" . js_escape($code) . ", " . js_escape($text) . ");\n";
                }
                echo " opt.setAttribute('codes'," . js_escape(trim($res['codes'])) . ");\n";
            }
        }

        ++$i;
    }

    ///////////
    ActiveIssueCodeRecycleFn($thispid, $ISSUE_TYPES);
    ///////////
    ?>

    <?php require $GLOBALS['srcdir'] . "/restoreSession.php"; ?>

    ///////////////////////////
    function onActiveCodeSelected() {
        var f = document.forms[0];
        var sel = f.form_active_codes.options[f.form_active_codes.selectedIndex];
        addSelectedCode(sel.value, sel.text)
        f.form_active_codes.selectedIndex = -1;
    }
    ///////////////////////////
    //
    // React to selection of an issue type.  This loads the associated
    // shortcuts into the selection list of titles, and determines which
    // rows are displayed or hidden.
    function newtype(index) {
        var f = document.forms[0];
        var theopts = f.form_titles.options;
        theopts.length = 0;
        var i = 0;
        for (i = 0; i < aopts[index].length; ++i) {
            theopts[i] = aopts[index][i];
        }
        document.getElementById('row_titles').style.display = i ? '' : 'none';
        //
        ///////////////////////
        var listBoxOpts2 = f.form_active_codes.options;
        listBoxOpts2.length = 0;
        var ix = 0;
        for (ix = 0; ix < listBoxOptions2[index].length; ++ix) {
            listBoxOpts2[ix] = listBoxOptions2[index][ix];
            listBoxOpts2[ix].title = listBoxOptions2[index][ix].text;
        }
        document.getElementById('row_active_codes').style.display = ix ? '' : 'none';

        //////////////////////
        //
        // Show or hide various rows depending on issue type, except do not
        // hide the comments or referred-by fields if they have data.

        $(function() {
            var comdisp = (aitypes[index] == 1) ? 'none' : '';
            var revdisp = (aitypes[index] == 1) ? '' : 'none';
            var injdisp = (aitypes[index] == 2) ? '' : 'none';
            var nordisp = (aitypes[index] == 0) ? '' : 'none';
            // reaction row should be displayed only for medication allergy.
            var alldisp = (index == <?php echo issueTypeIndex('allergy'); ?>) ? '' : 'none';
            var verificationdisp = (index == <?php echo issueTypeIndex('medical_problem'); ?>) ||
                (index == <?php echo issueTypeIndex('allergy'); ?>) ? '' : 'none';
            document.getElementById('row_enddate').style.display = comdisp;
            // Note that by default all the issues will not show the active row
            //  (which is desired functionality, since then use the end date
            //   to inactivate the item.)
            document.getElementById('row_active').style.display = revdisp;
            document.getElementById('row_selected_codes').style.display = comdisp;
            document.getElementById('row_occurrence').style.display = comdisp;
            document.getElementById('row_classification').style.display = injdisp;
            document.getElementById('row_reinjury_id').style.display = injdisp;
            document.getElementById('row_severity').style.display = alldisp;
            document.getElementById('row_reaction').style.display = alldisp;
            document.getElementById('row_verification').style.display = verificationdisp;
            document.getElementById('row_referredby').style.display = (f.form_referredby.value) ? '' : comdisp;
            //document.getElementById('row_comments'      ).style.display = (f.form_comments.value) ? '' : revdisp;
            document.getElementById('row_referredby').style.display = (f.form_referredby.value) ? '' : comdisp;
        });
        <?php
        if (!empty($ISSUE_TYPES['ippf_gcac']) && empty($_POST['form_save'])) {
            // Generate more of these for gcac and contraceptive fields.
            if (empty($issue) || $irow['type'] == 'ippf_gcac') {
                issue_ippf_gcac_newtype();
            }

            if (empty($issue) || $irow['type'] == 'contraceptive') {
                issue_ippf_con_newtype();
            }
        }
        ?>
    }

    // If a clickoption title is selected, copy it to the title field.
    // If it has a code, add that too.
    function set_text() {
        var f = document.forms[0];
        var sel = f.form_titles.options[f.form_titles.selectedIndex];
        f.form_title.value = sel.text;
        f.form_title_id.value = sel.value;

        f.form_selected_codes.options.length = 0

        var str = sel.getAttribute('codes')
        if (str) {
            var codes = str.split(";")
            for (i = 0; i < codes.length; i++) {
                addSelectedCode(codes[i], codeTexts.has(codes[i]) ? codeTexts.get(codes[i]) : codes[i])
            }
        }
    }

    function closeme() {
        dlgclose();
    }

    // Called when the Active checkbox is clicked.  For consistency we
    // use the existence of an end date to indicate inactivity, even
    // though the simple verion of the form does not show an end date.
    function activeClicked(cb) {
        var f = document.forms[0];
        if (cb.checked) {
            f.form_end.value = '';
        } else {
            var today = new Date();
            f.form_end.value = '' + (today.getYear() + 1900) + '-' +
                (today.getMonth() + 1) + '-' + today.getDate();
        }
    }

    // Called when resolved outcome is chosen and the end date is entered.
    function outcomeClicked(cb) {
        var f = document.forms[0];
        if (cb.value == '1') {
            var today = new Date();
            f.form_end.value = '' + (today.getYear() + 1900) + '-' +
                ("0" + (today.getMonth() + 1)).slice(-2) + '-' + ("0" + today.getDate()).slice(-2);
            f.form_end.focus();
        }
    }

    // This is for callback by the select codes popup.
    // Appends to or erases the current list of diagnoses.
    function OnCodeSelected(codetype, code, selector, codedesc) {
        var codeKey = codetype + ':' + code
        addSelectedCode(codeKey, codeKey + ' (' + codedesc + ')')

        var f = document.forms[0]
        if (f.form_title.value == '') {
            f.form_title.value = codedesc;
        }
    }

    function addSelectedCode(codeKey, codeText) {
        var f = document.forms[0]
        var sel = f.form_selected_codes
        for (i = 0; i < sel.options.length; i++) {
            if (sel.options[i].value == codeKey) {
                return
            }
        }

        var option = document.createElement("option");
        option.value = codeKey
        option.text = codeText
        sel.add(option);

        updateDiagnosisFromSelectedCodes()
    }

    function updateDiagnosisFromSelectedCodes() {
        var f = document.forms[0]
        var diag = ''
        options = f.form_selected_codes.options
        if (options.length > 0) {
            diag = options[0].value
            for (i = 1; i < options.length; i++) {
                diag += ';' + options[i].value;
            }
        }

        f.form_diagnosis.value = diag;
    }

    // This invokes the find-code popup.
    function onAddCode() {
        <?php
        $url = '../encounter/select_codes.php?codetype=';
        if (!empty($irow['type']) && ($irow['type'] == 'medical_problem')) {
            $url .= urlencode(collect_codetypes("medical_problem", "csv"));
        } else {
            $url .= urlencode(collect_codetypes("diagnosis", "csv"));
            $tmp_csv = collect_codetypes("drug", "csv");
            $tmp_csv .= "," . collect_codetypes("clinical_term", "csv");
            $tmp = explode(",", $tmp_csv);
            if (!empty($irow['type']) && ($irow['type'] == 'allergy')) {
                if ($tmp) {
                    foreach ($tmp as $item) {
                        $pos = strpos($url, $item);
                        if ($pos === false) {
                            $item = urlencode($item);
                            $url .= ",$item";
                        }
                    }
                }
            } elseif (!empty($irow['type']) && ($irow['type'] == 'medication')) {
                if ($tmp) {
                    foreach ($tmp as $item) {
                        $pos = strpos($url, $item);
                        if ($pos === false) {
                            $item = urlencode($item);
                            $url .= ",$item&default=$item";
                        }
                    }
                }
            }
        }
        ?>
        dlgopen(<?php echo js_escape($url); ?>, '_blank', 985, 800, '', <?php echo xlj("Select Codes"); ?>);
    }

    function onRemoveCode() {
        var sel = document.forms[0].form_selected_codes
        for (i = 0; i < sel.options.length; i++) {
            if (sel.options[i].selected) {
                sel.remove(i)
                i--
            }
        }

        onCodeSelectionChange()
        updateDiagnosisFromSelectedCodes()
    }

    function onCodeSelectionChange() {
        document.forms[0].rem_selected_code.disabled = document.forms[0].form_selected_codes.selectedIndex == -1
    }

    function processUdiEnter(event) {
        if (event.key == 'Enter') {
            event.preventDefault();
            processUdi(document.getElementById('udi_process_button'));
            return false;
        } {
            return true;
        }
    }

    function processUdi(param) {
        let udi = document.getElementById("form_udi").value;
        if (!udi) {
            alert(<?php echo xlj('UDI field is missing'); ?>);
            document.getElementById('udi_display').innerHTML = <?php echo xlj('A valid UDI has not been processed yet.'); ?>;
            document.getElementById('udi_data').value = '';
            return false;
        }

        originalLabel = param.innerHTML;
        param.innerHTML = "<i class='fa fa-circle-notch fa-spin'></i> " + jsText(<?php echo xlj('Processing'); ?>);

        top.restoreSession();
        let url = '../../../library/ajax/udi.php?udi=' + encodeURIComponent(udi) + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken('udi')); ?>;
        fetch(url, {
            credentials: 'same-origin',
            method: 'GET',
        })
        .then(response => response.json())
        .then(data => {
            if (data.raw_search == null || data.raw_search.udi == null || data.raw_search.udi.udi == null) {
                errorMessage = <?php echo xlj('UDI search failed'); ?>;
                if (data.raw_search != null && data.raw_search.error != null) {
                    errorMessage += ': ' + data.raw_search.error;
                }
                document.getElementById('udi_display').innerHTML = jsText(errorMessage);
                document.getElementById('udi_data').value = '';
            } else {
                let dataJSON = JSON.stringify(data);
                document.getElementById('udi_data').value = dataJSON;
                displayUdi(data);
            }
        })
        .catch(error => console.error(error))

        param.innerHTML = jsText(originalLabel);
    }

    function displayUdi(data) {
        let display = '';
        <?php echo MedicalDevice::fullOutputJavascript('display', 'data', false); ?>
        document.getElementById('udi_display').innerHTML = display;
        document.getElementById('form_title').value = data.standard_elements.deviceName;
    }

    // Check for errors when the form is submitted.
    function validate() {
        var f = document.forms[0];
        var begin_date_val = f.form_begin.value;
        begin_date_val = begin_date_val ? DateToYYYYMMDD_js(begin_date_val) : begin_date_val;
        var end_date_val = f.form_end.value;
        end_date_val = end_date_val ? DateToYYYYMMDD_js(end_date_val) : end_date_val;
        var begin_date = new Date(begin_date_val);
        var end_date = new Date(end_date_val);

        if ((end_date_val) && (begin_date > end_date)) {
            alert(<?php echo xlj('Please Enter End Date greater than Begin Date!'); ?>);
            return false;
        }
        if (!f.form_title.value) {
            alert(<?php echo xlj('Please enter a title!'); ?>);
            return false;
        }
        top.restoreSession();
        return true;
    }

    // Supports customizable forms (currently just for IPPF).
    function divclick(cb, divid) {
        var divstyle = document.getElementById(divid).style;
        if (cb.checked) {
            divstyle.display = 'block';
        } else {
            divstyle.display = 'none';
        }
        return true;
    }

    $(function() {
        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = true; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = true; ?>
            <?php require $GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'; ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma
            ?>
        });
    });
    $('div').hide();
</script>
</head>
<body>
    <div class="container-fluid mt-3">
        <ul class="tabNav">
            <li class='current'><a href='#'><?php echo xlt('Issue'); ?></a></li>
            <?php
            // Build html tab data for each visit form linked to this issue.
            $tabcontents = '';
            if ($issue) {
                $vres = sqlStatement(
                    "SELECT f.id, f.encounter, f.form_name, f.form_id, f.formdir, fe.date " .
                        "FROM forms AS f, form_encounter AS fe WHERE " .
                        "f.pid = ? AND f.issue_id = ? AND f.deleted = 0 AND " .
                        "fe.pid = f.pid and fe.encounter = f.encounter " .
                        "ORDER BY fe.date DESC, f.id DESC",
                    array($thispid, $issue)
                );
                while ($vrow = sqlFetchArray($vres)) {
                    $formdir = $vrow['formdir'];
                    $formid  = $vrow['form_id'];
                    $visitid = $vrow['encounter'];
                    echo " <li><a href='#'>" . text(oeFormatShortDate(substr($vrow['date'], 0, 10))) . ' ' .
                        text($vrow['form_name']) . "</a></li>\n";
                    $tabcontents .= "<div class='tab' style='height:90%;width:98%;'>\n";
                    $tabcontents .= "<iframe frameborder='0' class='h-100 w-100' " .
                        "src='../../forms/LBF/new.php?formname=" . attr_url($formdir) . "&id=" . attr_url($formid) . "&visitid=" . attr_url($visitid) . "&from_issue_form=1'" .
                        ">Oops</iframe>\n";
                    $tabcontents .= "</div>\n";
                }
            }
            ?>
        </ul>
        <div class="tabContainer">
            <div class='tab current h-auto'>
                <form name='theform' method="post" onsubmit='return validate()'>
                    <div class="container-fluid">
                        <div class="row">
                            <div class='col-sm-6'>
                                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                                <?php
                                // action setting not required in html5.  By default form will submit to itself.
                                // Provide key values previously passed as part of action string.
                                foreach (array('issue' => $issue, 'thispid' => $thispid, 'thisenc' => $thisenc) as $fldName => $fldVal) {
                                    printf('<input name="%s" type="hidden" value="%s"/>%s', attr($fldName), attr($fldVal), PHP_EOL);
                                }
                                ?>
                                    <label><?php echo xlt('Type'); ?>:</label>
                                    <?php
                                    $index = 0;
                                    foreach ($ISSUE_TYPES as $key => $value) {
                                        if ($issue || $thistype) {
                                            if ($index == $type_index) {
                                                echo text($value[1]);
                                                echo "<input type='hidden' name='form_type' value='" . attr($index) . "' />\n";
                                            }
                                        } else {
                                            $checked = ($index == $type_index) ? " checked" : '';
                                            $disabled = (!AclMain::aclCheckIssue($key, '', ['write', 'addonly'])) ? " disabled" : '';
                                            $str = '<input type="radio" name="form_type" value="%s" onclick="newtype(%s)" %s %s>';
                                            if ($key != 'medical_device') { /* rm/brady millr - medical device does not work correctly */
                                                echo vsprintf($str, [attr($index), attr_js($index), $checked, $disabled]);
                                                echo text($value[1] . " "); /*rm - display issue type name */
                                            }
                                        }

                                        ++$index;
                                    }
                                    ?>
                            </div>
                            <div class="col-md-6 d-flex justify-content-end">
                                <div class="form-check" id='row_active'>
                                    <input type="checkbox" class="form-check-input" name="form_active" id="form_active" value='1' <?php echo (!empty($irow['enddate'])) ? "" : "checked"; ?> onclick='activeClicked(this);' title='<?php echo xla('Indicates if this issue is currently active'); ?>'>
                                    <label class="form-check-label" for="form_active"><?php echo xlt('Active{{Issue}}'); ?></label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col" id='row_titles'>
                                <label for="form_titles" class=""><?php echo xlt('Select from list or type your own in Title'); ?></label>
                                <select name='form_titles' id='form_titles' class="form-control select2" multiple onchange='set_text()'><option></option></select>
                            </div>
                            <div class="form-group col">
                                <label for="title_diagnosis"><?php echo xlt('Title'); ?>:</label>
                                <div class="input-group">
                                    <input type='text' class="form-control" name='form_title' id='form_title' value='<?php echo attr($irow['title'] ?? '') ?>' />
                                    <div class="input-group-append">
                                        <button type="button" id="btnCodeSearch" class="btn btn-outline-secondary" onclick="onAddCode()" title="<?php echo xla("Select a Code"); ?>"><i class="fa fa-sm fa-magnifying-glass"></i></button>
                                    </div>
                                </div>
                                <input type='hidden' name='form_title_id' value='<?php echo attr($irow['list_option_id'] ?? '') ?>'>
                            </div>
                            <div class="form-group col-sm-12 col-md-3">
                                <label for="form_begin"><?php echo xlt('Begin Date and Time'); ?>:</label>
                                <input type='text' class='datepicker form-control' name='form_begin' id='form_begin' value='<?php echo attr(trim(oeFormatDateTime($irow['begdate'] ?? ''))) ?>' title='<?php echo xla('yyyy-mm-dd HH:MM date of onset, surgery or start of medication'); ?>' />
                            </div>
                            <div class="form-group col-sm-12 col-md-3" id='row_enddate'>
                                <label for="form_begin"><?php echo xlt('End Date and Time'); ?>:</label>
                                <input type='text' class='datepicker form-control' placeholder="<?php echo xlt('leave blank if still active'); ?>" name='form_end' id='form_end' value='<?php echo attr(trim(oeFormatDateTime($irow['enddate'] ?? ''))) ?>' title='<?php echo xla('yyyy-mm-dd HH:MM date of recovery or end of medication'); ?>' />
                            </div>
                        </div>
                        <div class="row">
                            <!-- Reaction For Medication Allergy -->
                            <div class="form-group col" id='row_reaction'>
                                <label for="form_reaction"><?php echo xlt('Reaction'); ?>:</label>
                                <?php
                                echo generate_select_list('form_reaction', 'reaction', ($irow['reaction'] ?? null), '', '', '', '');
                                ?>
                            </div>
                            <div class="form-group col" id='row_severity'>
                                <label for="form_severity_id"><?php echo xlt('Severity'); ?>:</label>
                                <?php
                                $severity = $irow['severity_al'] ?? null;
                                generate_form_field(array('data_type' => 1, 'field_id' => 'severity_id', 'list_id' => 'severity_ccda', 'empty_title' => 'SKIP'), $severity);
                                ?>
                            </div>
                            <!-- End of reaction -->
                        </div>
                        <?php if ($thistype == 'medical_device' || (!empty($irow['type']) && $irow['type'] == 'medical_device')) : ?>
                        <div class="row">
                            <div class="form-group col-12">
                                <label class="col-form-label" for="form_udi"><?php echo xlt('UDI{{Unique Device Identifier}}'); ?>:</label>
                                <div class="input-group">
                                    <input type='text' class="form-control" name='form_udi' id='form_udi' onkeydown="processUdiEnter(event)" value='<?php echo attr($irow['udi'] ?? '') ?>' />
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-secondary btn-sm" id='udi_process_button' style="margin-right:5px;" onclick='processUdi(this)'><?php echo (!empty($irow['udi_data'])) ? xlt('Re-Process UDI') : xlt('Process UDI'); ?></button>
                                    </div>
                                </div>
                                <div id="udi_display" class="alert alert-info m-2" role="alert">
                                    <?php echo (!empty($irow['udi_data'])) ? (new MedicalDevice($irow['udi_idata']))->fullOutputHtml(false) : xlt('A valid UDI has not been processed yet'); ?>
                                </div>
                                <input type='hidden' name='udi_data' id='udi_data' value='<?php echo attr($irow['udi_data'] ?? '') ?>' />
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="row">
                            <?php if (($irow['type'] ?? '') == 'medication') : ?>
                                <!-- any medication specific issue information goes here -->
                                <?php include "add_edit_issue_medication_fragment.php"; ?>
                            <?php endif; ?>
                        </div>
                        <div class="row">
                            <div class="form-group col-12" id='row_comments'>
                                <label class="col-form-label" for="form_comments"><?php echo xlt('Comments'); ?>:</label>
                                <textarea class="form-control" name='form_comments' id='form_comments' rows="2" id='form_comments'><?php echo text($irow['comments'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <div id="expanded_options" class="collapse">
                            <div class="row">
                                <div class="form-group col-sm-12 col-md-6" id='row_active_codes'>
                                    <label for="form_active_codes" class="col-form-label"><?php echo xlt('Active Issue Codes'); ?>:</label>
                                    <select name='form_active_codes' id='form_active_codes' class= "form-control" size='4'
                                        onchange="onActiveCodeSelected()"></select>
                                </div>
                                <div class="form-group col-sm-12 col-md-6" id='row_selected_codes'>
                                    <label for="form_selected_codes" class="col-form-label"><?php echo xlt('Coding'); ?>:</label>
                                    <select name='form_selected_codes' id='form_selected_codes' class= "form-control" multiple size='4'
                                        onchange="onCodeSelectionChange()">
                                    <?php
                                    if (!empty($irow['diagnosis'])) {
                                        $codes = explode(";", $irow['diagnosis']);
                                        foreach ($codes as $code) {
                                            echo "<option value='" . attr($code) . "'>" . text(getCodeText($code)) . "</option>\n";
                                        }
                                    }
                                    ?>
                                    </select>
                                    <div class="btn-group mt-1">
                                        <button type="button" class="btn btn-secondary btn-sm" onclick='onAddCode()'><?php echo xlt('Add');?></button>
                                        <button type="button" id="rem_selected_code" class="btn btn-secondary btn-sm" onclick='onRemoveCode()'><?php echo xlt('Remove');?></button>
                                    </div>
                                    <input type='hidden' class="form-control" name='form_diagnosis' id='form_diagnosis'
                                        value='<?php echo attr($irow['diagnosis'] ?? '') ?>' onclick='onAddCode()'
                                        title='<?php echo xla('Click to select or change coding'); ?>' readonly />
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-sm-12 col-md-4" id='row_occurrence'>
                                    <label for="form_occur"><?php echo xlt('Occurrence'); ?>:</label>
                                    <?php
                                    // Modified 6/2009 by BM to incorporate the occurrence items into the list_options listings
                                    generate_form_field(array('data_type' => 1, 'field_id' => 'occur', 'list_id' => 'occurrence', 'empty_title' => 'SKIP'), ($irow['occurrence'] ?? null));
                                    ?>
                                </div>
                                <div class="form-group col-sm-12 col-md-4 <?php echo ($GLOBALS['ippf_specific']) ? 'd-none' : '';?>">
                                    <label for="form_outcome"><?php echo xlt('Outcome'); ?>:</label>
                                    <?php
                                    echo generate_select_list('form_outcome', 'outcome', ($irow['outcome'] ?? null), '', '', '', 'outcomeClicked(this);');
                                    ?>
                                </div>
                                <div class="form-group col-sm-12 col-md-4" id='row_subtype'>
                                    <label for="form_subtype"><?php echo xlt('Classification Type'); ?>:</label>
                                    <?php
                                    echo generate_select_list('form_subtype', 'issue_subtypes', ($irow['subtype'] ?? null), '', 'NA', '', '');
                                    ?>
                                    <div class="form-group" id='row_classification'>
                                        <label for="form_classification"><?php echo xlt('Classification'); ?>:</label>
                                        <select name='form_classification' id='form_classification' class='form-control'>
                                            <?php
                                            foreach ($ISSUE_CLASSIFICATIONS as $key => $value) {
                                                echo "   <option value='" . attr($key) . "'";
                                                if (!empty($irow['classification']) && ($key == $irow['classification'])) {
                                                    echo " selected";
                                                }
                                                echo ">" . text($value) . "\n";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <!-- Verification Status for Medication Allergy -->
                                <div class="form-group col-sm-12 col-md-4" id='row_verification'>
                                    <label class="col-form-label" for="form_verification"><?php echo xlt('Verification Status'); ?>:</label>
                                    <?php
                                    $codeListName = ($thistype == 'medical_problem') ? 'condition-verification' : 'allergyintolerance-verification';
                                    echo generate_select_list('form_verification', $codeListName, ($irow['verification'] ?? null), '', '', '', '');
                                    ?>
                                </div>
                                <!-- End of Verification Status -->
                                <div class="form-group col-sm-12 col-md-4" id='row_referredby'>
                                    <label class="col-form-label" for="form_referredby"><?php echo xlt('Referred by'); ?>:</label>
                                    <input type='text' name='form_referredby' id='form_referredby' class='form-control' value='<?php echo attr($irow['referredby'] ?? '') ?>' title='<?php echo xla('Referring physician and practice'); ?>' />
                                </div>
                                <div class="form-group col-sm-12 col-md-4 <?php echo ($GLOBALS['ippf_specific']) ? 'd-none' : ''; ?>">
                                    <label class="col-form-label" for="form_destination"><?php echo xlt('Destination'); ?>:</label>
                                    <?php if (true) { ?>
                                        <input type='text' class='form-control' name='form_destination' id='form_destination' value='<?php echo attr($irow['destination'] ?? '') ?>' style='width:100%' title='GP, Secondary care specialist, etc.' />
                                    <?php } else { // leave this here for now, please -- Rod
                                        ?>
                                        <?php echo rbinput('form_destination', '1', 'GP', 'destination') ?>&nbsp;
                                        <?php echo rbinput('form_destination', '2', 'Secondary care spec', 'destination') ?>&nbsp;
                                        <?php echo rbinput('form_destination', '3', 'GP via physio', 'destination') ?>&nbsp;
                                        <?php echo rbinput('form_destination', '4', 'GP via podiatry', 'destination') ?>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col" id='row_returndate'>
                                    <input type='hidden' name='form_return' id='form_return' />
                                    <input type='hidden' name='row_reinjury_id' id='row_reinjury_id' />
                                    <img id='img_return' />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col d-flex justify-content-end">
                                <button type="button" class="btn btn-text mr-3" data-toggle="collapse" data-target="#expanded_options" aria-expanded="false" aria-controls="expanded_options"><?php echo xlt("Show More Fields"); ?>&nbsp;<i class="fa fa-angles-down"></i></button>
                                <div class="btn-group" role="group">
                                    <button type='submit' name='form_save' value="<?php echo xla('Save'); ?>" class="btn btn-primary btn-save"><?php echo xlt('Save'); ?></button>
                                    <button type="button" class="btn btn-secondary btn-cancel" onclick='closeme();'><?php echo xlt('Cancel'); ?></button>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <?php
                            if (!empty($ISSUE_TYPES['ippf_gcac'])) {
                                if (empty($issue) || $irow['type'] == 'ippf_gcac') {
                                    issue_ippf_gcac_form($issue, $thispid);
                                }

                                if (empty($issue) || $irow['type'] == 'contraceptive') {
                                    issue_ippf_con_form($issue, $thispid);
                                }
                            }
                            ?>
                        </div>
                    </div>
                </form>
            </div>
            <?php echo $tabcontents; ?>
        </div>
    </div>
</div>

<script>
    newtype(<?php echo js_escape($type_index); ?>);
    // Set up the tabbed UI.
    tabbify();

    function toggleBtnExpOpts() {
        let btnExpOpts = document.querySelector('button[data-target="#expanded_options"]');
        let isOpen = btnExpOpts.toggleAttribute('data-open');
        let txtShowHide = isOpen ? <?php echo xlj("Hide More Fields"); ?> : <?php echo xlj("Show More Fields"); ?>;
        let iconShowHide = isOpen ? "fa-angles-up" : "fa-angles-down";
        btnExpOpts.innerHTML = `${txtShowHide}&nbsp;<i class='fa ${iconShowHide}'></i>`;
    }

    $(function() {
        // Include bs3 / bs4 classes here.  Keep html tags functional.
        $('table').addClass('table table-sm');
        $('.select2').select2({theme: 'bootstrap4'});
        $('button[data-target="#expanded_options"]').on('click', () => {toggleBtnExpOpts()});

        onCodeSelectionChange()
    });
</script>
<?php validateUsingPageRules($_SERVER['PHP_SELF']); ?>
</body>
</html>
