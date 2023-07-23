<?php

/**
 * This is the place to put JavaScript functions that are needed to support
 * options.inc.php. Include this in the <head> section of relevant modules.
 * It's a .php module so that translation can be supported.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2014-2021 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Csrf\CsrfUtils;

?>
<script>

    // JavaScript support for date types when the A or B edit option is used.
    // Called to recompute displayed age dynamically when the corresponding date is
    // changed. Must generate the same age formats as the oeFormatAge() function.
    //
    function updateAgeString(fieldid, asof, format, description) {
    var datefld = document.getElementById('form_' + fieldid);
    var f = datefld.form;
    var age = '';
    var date1 = new Date(datefld.value);
    var date2 = asof ? new Date(asof) : new Date();
    if (format == 3) {
    // Gestational age.
    var msecs = date2.getTime() - date1.getTime();
    var days  = Math.round(msecs / (24 * 60 * 60 * 1000));
    var weeks = Math.floor(days / 7);
    days = days % 7;
    if (description == '') description = <?php echo xlj('Gest age') ?>;
    age = description + ' ' +
    weeks + (weeks == 1 ? ' ' + <?php echo xlj('week') ?> : ' ' + <?php echo xlj('weeks') ?>) + ' ' +
    days  + (days  == 1 ? ' ' + <?php echo xlj('day') ?> : ' ' + <?php echo xlj('days') ?>);
}
    else {
    // Years or months.
    var dayDiff   = date2.getDate()     - date1.getDate();
    var monthDiff = date2.getMonth()    - date1.getMonth();
    var yearDiff  = date2.getFullYear() - date1.getFullYear();
    var ageInMonths = yearDiff * 12 + monthDiff;
    if (dayDiff < 0) --ageInMonths;
    if (format == 1 || (format == 0 && ageInMonths >= 24)) {
    age = yearDiff;
    if (monthDiff < 0 || (monthDiff == 0 && dayDiff < 0)) --age;
    age = '' + age;
}
    else {
    age = '' + ageInMonths;
    if (format == 0) {
    age = age + ' ' + (ageInMonths == 1 ? <?php echo xlj('month') ?> : <?php echo xlj('months') ?>);
}
}
    if (description == '') description = <?php echo xlj('Age') ?>;
    if (age != '') age = description + ' ' + age;
}
    document.getElementById('span_' + fieldid).innerHTML = age;
}

    // Function to support datatype 46 - single-selection list with comment support
    function processCommentField(fieldId) {
    if (document.getElementById("form_" + fieldId) != null) {
    if (document.getElementById("form_" + fieldId).options[document.getElementById("form_" + fieldId).selectedIndex].value.match(/^comment_/)) {
    if (document.getElementById("form_text_" + fieldId).style.display == "none") {
    document.getElementById("form_text_" + fieldId).style.display = "inline-block";
}
} else {
    document.getElementById("form_text_" + fieldId).value = "";
    document.getElementById("form_text_" + fieldId).style.display = "none";
}
}
}

    function myHideOrShow(elem, hide) {
    // elem is a td or bootstrap column div.
    for (var inp = elem.firstChild; inp; inp = inp.nextSibling) {
    if (inp.style) {
    inp.style.display = hide ? 'none' : '';
} else {
    // This must be a text node with no tag, so hide/show the parent elem instead.
    elem.style.display = hide ? 'none' : '';
}
}
}

    // Function to show or hide form fields (and their labels) depending on "skip conditions"
    // defined in the layout.
    //
    var cskerror = false; // to avoid repeating error messages
    function checkSkipConditions() {
    var myerror = cskerror;
    var prevandor = '';
    var prevcond = false;
    if (typeof skipArray === 'undefined') {
    return '';
}
    for (var i = 0; i < skipArray.length; ++i) {
    var target   = skipArray[i].target;
    var id       = skipArray[i].id;
    var itemid   = skipArray[i].itemid;
    var operator = skipArray[i].operator;
    var value    = skipArray[i].value;
    var is_radio = false;
    var action   = skipArray[i].action;
    var tofind = id;

    if (itemid) tofind += '[' + itemid + ']';
    // Some different source IDs are possible depending on the data type.
    var srcelem = document.getElementById('check_' + tofind);
    if (srcelem == null) srcelem = document.getElementById('radio_' + tofind);
    if (srcelem == null) srcelem = document.getElementById('form_' + tofind) ;
    if (srcelem == null) srcelem = document.getElementById('text_' + tofind);

    if (srcelem == null) {
    // This caters to radio buttons which we treat like droplists.
    var tmp = document.getElementById('form_' + tofind + '[' + value + ']');
    if (tmp != null) {
    srcelem = tmp;
    if (operator == 'eq') operator = 'se';
    if (operator == 'ne') operator = 'ns';
    is_radio = true;
}
}

    if (srcelem == null) {
    if (!cskerror) alert(<?php echo xlj('Cannot find a skip source field for'); ?> + ' "' + tofind + '"');
    myerror = true;
    continue;
}

    var condition = false;
    var is_multiple = false;
    var elem_val;
    if ( is_radio){
    for (var k = 0; k < document.getElementsByName('form_' + tofind).length; k++){
    if (document.getElementsByName('form_' + tofind)[k].checked){
    elem_val= document.getElementsByName('form_' + tofind)[k].value;
}
}
}else if( typeof srcelem.options!=="undefined" && srcelem.type == 'select-one' ){
    elem_val=srcelem.options[srcelem.selectedIndex].value;

}else if( srcelem.type == 'select-multiple' ) {
    elem_val = new Array();
    is_multiple = true;
    for (var k = 0; k < srcelem.length; k++) {
    if (srcelem.options[k].selected) {
    if( elem_val.indexOf(srcelem.options[k].value)<0) {
    elem_val.push(srcelem.options[k].value);
}
}
}
} else {
    elem_val=srcelem.value;

    if(elem_val == null) {
    elem_val = srcelem.getAttribute("data-value");
    if( elem_val !== null && elem_val.indexOf("|") !== -1 ) {
    elem_val = elem_val.split("|");
    is_multiple = true;
}
}
    if(elem_val == null) elem_val = srcelem.innerText;
}
    //this is a feature fix for the multiple select list option
    //collect all the multiselect control values:
    if( is_multiple ) {
    switch(operator) {
    case 'eq':
    condition = (-1 !== elem_val.indexOf(value));break;
    case 'ne':
    condition = (-1 == elem_val.indexOf(value)); break;
    case 'se':
    condition = srcelem.checked  ; break; // doesn't make sense?
    case 'ns':
    condition = !srcelem.checked;  break;
}

} else {
    if (operator == 'eq') condition = elem_val == value; else
    if (operator == 'ne') condition = elem_val != value; else
    if (operator == 'se') condition = srcelem.checked  ; else
    if (operator == 'ns') condition = !srcelem.checked;
}

    // Logic to accumulate multiple conditions for the same target.
    // alert('target = ' + target + ' prevandor = ' + prevandor + ' prevcond = ' + prevcond); // debugging
    if (prevandor == 'and') condition = condition && prevcond; else
    if (prevandor == 'or' ) condition = condition || prevcond;
    prevandor = skipArray[i].andor;
    prevcond = condition;
    var j = i + 1;
    if (j < skipArray.length && skipArray[j].target == target) continue;

    // At this point condition indicates the target should be hidden or have its value set.

    var skip = condition;

    if (action.substring(0, 5) == 'value') {
    skip = false;
}
    else if (action.substring(0, 5) == 'hsval') {
    // This action means hide if true, set value if false.
    if (!condition) {
    action = 'value=' + action.substring(6);
    skip = false;
}
}

    if (true) {
    var trgelem1 = document.getElementById('label_id_' + target);
    var trgelem2 = document.getElementById('value_id_text_' + target);
    if (trgelem2 == null) {
    trgelem2 = document.getElementById('value_id_' + target);
}
    if (trgelem1 == null && trgelem2 == null) {
    var trgelem1 = document.getElementById('label_' + target);
    var trgelem2 = document.getElementById('text_' + target);
    if(trgelem2 == null){
    trgelem2 = document.getElementById('form_' + target);
}
    if (trgelem1 == null && trgelem2 == null) {
    if (!cskerror) alert(<?php echo xlj('Cannot find a skip target field for'); ?> + ' "' + target + '"');
    myerror = true;
    continue;
}
}
    if (trgelem1) myHideOrShow(trgelem1, skip);
    if (trgelem2) myHideOrShow(trgelem2, skip);
}

    if (action.substring(0, 5) == 'value') {
    var trgelem = document.forms[0]['form_' + target];
    if (trgelem == null) {
    if (!cskerror) alert('Cannot find a value target field for "' + target + '"');
    myerror = true;
    continue;
}
    var action_value = action.substring(6);
    if (trgelem.type == 'checkbox') {
    trgelem.checked = !(action_value == '0' || action_value == '');
}
    else {
    trgelem.value = action_value;
    // Handle billing code descriptions.
    var valelem = document.forms[0]['form_' + target + '__desc'];
    if (skipArray[i].valdesc && valelem) {
    // alert('Setting ' + valelem.name + ' value to: ' + skipArray[i].valdesc); // debugging
    valelem.value = skipArray[i].valdesc;
}
}
}
}
    // If any errors, all show in the first pass and none in subsequent passes.
    cskerror = cskerror || myerror;
}

    ///////////////////////////////////////////////////////////////////////
    // Image canvas support starts here.
    ///////////////////////////////////////////////////////////////////////

    var lbfCanvases = {}; // contains the LC instance for each canvas.

    // Initialize the drawing widget.
    // canid is the id of the div that will contain the canvas, and the image
    // element used for initialization should have an id of canid + '_img'.
    //
    function lbfCanvasSetup(canid, canWidth, canHeight) {
    LC.localize({
        "stroke": <?php echo xlj('stroke'); ?>,
        "fill": <?php echo xlj('fill'); ?>,
        "bg": <?php echo xlj('bg{{image canvas label}}'); ?>,
        "Clear": <?php echo xlj('Clear'); ?>,
        // The following are tooltip translations, however they do not work due to
        // a bug in LiterallyCanvas 0.4.13.  We'll leave them here pending a fix.
        "Eraser": <?php echo xlj('Eraser'); ?>,
        "Pencil": <?php echo xlj('Pencil'); ?>,
        "Line": <?php echo xlj('Line'); ?>,
        "Rectangle": <?php echo xlj('Rectangle'); ?>,
        "Ellipse": <?php echo xlj('Ellipse'); ?>,
        "Text": <?php echo xlj('Text'); ?>,
        "Polygon": <?php echo xlj('Polygon'); ?>,
        "Pan": <?php echo xlj('Pan'); ?>,
        "Eyedropper": <?php echo xlj('Eyedropper'); ?>,
        "Undo": <?php echo xlj('Undo'); ?>,
        "Redo": <?php echo xlj('Redo'); ?>,
        "Zoom out": <?php echo xlj('Zoom out'); ?>,
        "Zoom in": <?php echo xlj('Zoom in'); ?>,
    });
    var tmpImage = document.getElementById(canid + '_img');
    var shape = LC.createShape('Image', {x: 0, y: 0, image: tmpImage});
    var lc = LC.init(document.getElementById(canid), {
    imageSize: {width: canWidth, height: canHeight},
    strokeWidths: [1, 2, 3, 5, 8, 12],
    defaultStrokeWidth: 2,
    backgroundShapes: [shape],
    imageURLPrefix: '<?php echo $GLOBALS['assets_static_relative'] ?>/literallycanvas/img'
});
    if (canHeight > 261) {
    // TBD: Do something to make the widget bigger?
    // Look for some help with this in the next LC release.
}
    // lc.saveShape(shape);       // alternative to the above backgroundShapes
    lbfCanvases[canid] = lc;
}

    // This returns a standard "Data URL" string representing the image data.
    // It will typically be a few kilobytes. Here's a truncated example:
    // data:image/png;base64,iVBORw0K ...
    //
    function lbfCanvasGetData(canid) {
    return lbfCanvases[canid].getImage().toDataURL();
}

    // set signture to hidden element for this img
    function lbfSetSignature(el) {
    let imgel = el + "_img";
    let sign = $("#"+ imgel).attr('src');
    $("#"+ el).val(sign);
}

    // This is invoked when a field with edit option M is changed.
    // Its purpose is to make the corresponding change to the member fields (edit option m).
    //
    function checkGroupMembers(elem, groupnumber) {
    var i = elem.id.indexOf('[');
    if (i < 0) {
    alert(<?php echo xlj('Field not suitable for edit option M') ?> + ': ' + elem.name);
    return;
}
    var suffix = elem.id.substring(i);
    var members = document.getElementsByClassName('lbf_memgroup_' + groupnumber);
    if (members.length == 0) {
    alert(<?php echo xlj('No member fields found for') ?> + ': ' + elem.name);
    return;
}
    for (var i = 0; i < members.length; ++i) {
    if (members[i].id.indexOf(suffix) > 1) {
    members[i].checked = true;
}
}
}

    // Support for patient finder. References to the input elements.
    var elem_patient_name;
    var elem_patient_id;

    // This is for callback by the find-patient popup.
    function setpatient(pid, lname, fname, dob) {
    elem_patient_name.value = lname + ', ' + fname + ' (' + pid + ')';
    elem_patient_id.value = pid;
}

    // This invokes the find-patient popup.
    function sel_patient(ename, epid) {
    elem_patient_name = ename;
    elem_patient_id = epid;
    dlgopen('<?php echo $GLOBALS['webroot']; ?>/interface/main/calendar/find_patient_popup.php', '_blank', 500, 400);
}

    // This is a wrapper for specialty forms dialog
    // ajax mode allows calling script to be in the same scope as options.inc.php.
    function specialtyFormDialog(mode = 'iframe', size = 'modal-sm', formHandler = 'name_history') {
    event.preventDefault();
    let url = '<?php echo $GLOBALS['webroot']; ?>/library/specialty_forms.php?';
    url += "form_handler=" + encodeURIComponent(formHandler);
    url += "&csrf_token_form=" + <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>;
    let title = xl("Add to History");
    dlgopen('', '', size, 500, '', '', {
    allowResize: true,
    allowDrag: true,
    sizeHeight: 'auto',
    dialogId: '',
    type: mode,
    url: url
});
};
</script>
