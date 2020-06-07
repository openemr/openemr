<?php

/**
 * import_template.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Terry Hill <teryhill@yahoo.com>
 * @copyright Copyright (c) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

    //require_once ("./../verify_session.php");
    require_once("../../library/options.inc.php");
    $this->assign('title', xlt("Patient Portal") . " | " . xlt("Patient Data"));
    $this->assign('nav', 'patientdata');
    /*
     *  row keys are js underscore camelcase and follow the underscores varables used in this template
     *  $row['city'] or $row['postalCode'] e.t.c.. The keys do not match table columns ie postalCode here is postal_code in table.
     *  */
    $row = array();
if ($this->trow) {
    $row = $this->trow;
}

    echo "<script>var register='" . attr($this->register) . "';var recid='" . attr($this->recid) . "';var webRoot='" . $GLOBALS['web_root'] . "';var cpid='" . attr($this->cpid) . "';var cuser='" . attr($this->cuser) . "';</script>";
    $_SESSION['whereto'] = 'profilecard';

    $this->display('_modalFormHeader.tpl.php');
?>

<script>

    // bring in the datepicker and datetimepicker localization and setting elements
    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4-alternate.js.php'); ?>

    $LAB.script("scripts/app/patientdata.js?v=<?php echo $GLOBALS['v_js_includes']; ?>").wait(function(){
        $(function () {
            page.init();
        });
        // hack for browsers or slow sessions which may respond inconsistently with document.ready-second chance at init
        setTimeout(function(){
            if (!page.isInitialized) page.init();
        },1000);
    });
</script>
<?php if (attr($this->register)) {?>
<style>
    .form-group .inline .dynhide {
        display: none;
    }

    body {
        padding-top: 0;
        padding-bottom: 5px;
        background-color: #fff !important;
    }
</style>
<script>
    // Fixes iFrame in Patient Registratiion
    setInterval(function() {
        window.top.postMessage(document.body.scrollHeight, "*");
    }, 500);
</script>
<?php }?>
<body>
<div class="container-fluid">
<script type="text/template" id="patientCollectionTemplate"></script>
    <!-- Could add/manage table list here -->
<script type="text/template" id="patientModelTemplate"> <!-- -->

<div id='profileHelp' class='jumbotron jumbotron-fluid' style='display: none; width: 650px; margin: 0 auto;'>
<p>
<?php echo xlt('Any changes here will be reviewed by provider staff before committing to your chart. The following apply'); ?>:<br />
<?php echo xlt('Change any item available and when ready click Send for review. The changes will be flagged and staff notified to review changes before committing them to chart. During the time period before changes are reviewed the Revised button will show Pending and profile data is still available for changes. When accessing profile in pending state all previous edits will appear in Blue and current chart values in Red. You may revert any edit to chart value by clicking that red item (or vica versa) but remember that when you click Send for Review then items that populate the field items are the ones that are sent. Revert Edits button changes everything back to chart values and you may make changes from there. So to recap: Items in BLUE are patient edits with items in RED being original values before any edits.'); ?>
</p>
        <button class="btn btn-primary btn-sm" type="button" id='dismissHelp'><?php echo xlt('Dismiss'); ?></button>
        </div>
        <form onsubmit="return false;">
            <fieldset>
            <div class="form-row">
                <!-- <div class="col-sm-auto px-3 form-group plist-group" id="idInputContainer">
                    <label class="plist-label" for="id">Id</label>
                    <div class="controls inline-inputs">
                        <span class="form-control uneditable-input" id="id"><%= _.escape(item.get('id') || '') %></span>
                        <span class="help-inline"></span>
                    </div>
                </div> -->
            <div class="col-sm-auto px-3 form-group plist-group" id="provideridInputContainer">
                    <label class="plist-label" for="providerid"><?php echo xlt('Select Primary Physician')?></label>
                    <div class="controls inline-inputs">
                        <select class="form-control" id="providerid"  value="<%= _.escape(item.get('providerid') || '') %>"></select>
                        <span class="help-inline"></span>
                    </div>
            </div>
            <div class="col-sm-auto px-3 form-group plist-group" id="titleInputContainer">
                <label class="plist-label" for="title"><?php echo xlt('Title')?></label><br />
                <div class="controls inline-inputs">
                    <?php
                  # Generate drop down list for Title
                    echo generate_select_list('title', 'titles', $row['title'], xl('Title'), 'Unassigned', "form-control");
                    ?>
                 <span class="help-inline"></span>
                </div>
            </div>

            <!-- <div class="col-sm-auto px-3 form-group plist-group" id="financialInputContainer">
                    <label class="plist-label" for="financial"><?php echo xlt('Financial')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="financial" placeholder="Financial" value="<%= _.escape(item.get('financial') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div> -->
                <div class="col-sm-auto px-3 form-group plist-group" id="fnameInputContainer">
                    <label class="plist-label" for="fname"><?php echo xlt('First{{Name}}')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="fname" required placeholder="<?php echo xla('First Name'); ?>" value="<%= _.escape(item.get('fname') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="mnameInputContainer">
                    <label class="plist-label" for="mname"><?php echo xlt('Middle{{Name}}')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="mname" placeholder="<?php echo xla('Middle Name'); ?>" value="<%= _.escape(item.get('mname') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="lnameInputContainer">
                    <label class="plist-label" for="lname"><?php echo xlt('Last{{Name}}')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="lname" required placeholder="<?php echo xla('Last Name'); ?>" value="<%= _.escape(item.get('lname') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group  dynhide hide" id="pidInputContainer">
                    <label class="plist-label" for="pid"><?php echo xlt('Pid')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="pid" placeholder="<?php echo xla('Pid')?>" value="<%= _.escape(item.get('pid') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="pubpidInputContainer">
                    <label class="plist-label" for="pubpid"><?php echo xlt('Public Patient Id')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="pubpid" disabled value="<%= _.escape(item.get('pubpid') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="dobInputContainer">
                    <label class="plist-label" for="dob"><?php echo xlt('Birth Date')?></label>
                    <div class="controls inline-inputs">
                        <div class="input-group" >
                            <input id="dob" type="text" required class="form-control jquery-date-picker" placeholder="<?php echo xla('I know but we need it!'); ?>" value="<%= item.get('dob') %>" />
                        </div>
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="ssInputContainer">
                    <label class="plist-label" for="ss"><?php echo xlt('SSN')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="ss" title="###-##-####" placeholder="<?php echo xla('Social Security(Optional)'); ?>" value="<%= _.escape(item.get('ss') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="sexInputContainer">
                    <label class="plist-label" for="sex"><?php echo xlt('Gender')?></label><br />
                    <div class="controls inline-inputs">
                        <?php
                      # Generate drop down list for Sex
                        echo generate_select_list('sex', 'sex', $row['sex'], xl('Sex'), 'Unassigned');
                        ?>
                     <span class="help-inline"></span>
                    </div>
                </div>
                <!--<div class="col-sm-auto px-3 form-group plist-group" id="pharmacyIdInputContainer">
                    <label class="plist-label" for="pharmacyId"><?php echo xlt('Pharmacy Id')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="pharmacyId" placeholder="<?php echo xla('Pharmacy Id'); ?>" value="<%= _.escape(item.get('pharmacyId') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>-->
                <div class="col-sm-auto px-3 form-group plist-group" id="statusInputContainer">
                    <label class="plist-label" for="status"><?php echo xlt('Marital Status')?></label><br />
                    <div class="controls inline-inputs">
                    <?php
                  # Generate drop down list for Marital Status
                    echo generate_select_list('status', 'marital', $row['marital'], xl('Marital Status'), 'Unassigned', "form-control");
                    ?>
                    <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="streetInputContainer">
                    <label class="plist-label" for="street"><?php echo xlt('Street')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="street" required placeholder="<?php echo xla('Street'); ?>" value="<%= _.escape(item.get('street') || '') %>"/>
                        <span class="help-inline"></span>
                    </div>
                </div>
            <div class="col-sm-auto px-3 form-group plist-group" id="cityInputContainer">
                    <label class="plist-label" for="city"><?php echo xlt('City')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="city" required placeholder="<?php echo xla('City'); ?>" value="<%= _.escape(item.get('city') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
            <div class="col-sm-auto px-3 form-group plist-group" id="stateInputContainer">
                <label class="plist-label" for="state"><?php echo xlt('State')?></label><br />
                <div class="controls inline-inputs">
                    <?php
                  # Generate drop down list for State
                    echo generate_select_list('state', 'state', $row['state'], xl('State'), 'Unassigned', "form-control");
                    ?>
                 <span class="help-inline"></span>
                </div>
            </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="postalCodeInputContainer">
                    <label class="plist-label" for="postalCode"><?php echo xlt('Postal Code')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="postalCode" placeholder="<?php echo xla('Postal Code'); ?>" value="<%= _.escape(item.get('postalCode') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="countyInputContainer">
                    <label class="plist-label" for="county"><?php echo xlt('County')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="county" placeholder="<?php echo xla('County'); ?>" value="<%= _.escape(item.get('county') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="countryCodeInputContainer">
                    <label class="plist-label" for="countryCode"><?php echo xlt('Country Code')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="countryCode" placeholder="<?php echo xla('Country Code'); ?>" value="<%= _.escape(item.get('countryCode') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="phoneHomeInputContainer">
                    <label class="plist-label" for="phoneHome"><?php echo xlt('Home Phone')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="phoneHome" placeholder="<?php echo xla('Phone Home'); ?>" value="<%= _.escape(item.get('phoneHome') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="phoneBizInputContainer">
                    <label class="plist-label" for="phoneBiz"><?php echo xlt('Business Phone')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="phoneBiz" placeholder="<?php echo xla('Phone Biz'); ?>" value="<%= _.escape(item.get('phoneBiz') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="phoneCellInputContainer">
                    <label class="plist-label" for="phoneCell"><?php echo xlt('Cell Phone')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="phoneCell" placeholder="<?php echo xla('Phone Cell'); ?>" value="<%= _.escape(item.get('phoneCell') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="phoneContactInputContainer">
                    <label class="plist-label" for="phoneContact"><?php echo xlt('Contact or Notify Phone')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="phoneContact" placeholder="<?php echo xla('Phone Contact'); ?>" value="<%= _.escape(item.get('phoneContact') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="contactRelationshipInputContainer">
                    <label class="plist-label" for="contactRelationship"><?php echo xlt('Contact Relationship')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="contactRelationship" placeholder="<?php echo xla('Contact Relationship'); ?>" value="<%= _.escape(item.get('contactRelationship') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="dateInputContainer">
                    <label class="plist-label" for="date"><?php echo xlt('Date')?></label>
                    <div class="controls inline-inputs">
                        <div class="input-group">
                            <input disabled id="date" type="text" class="form-control jquery-date-time-picker" value="<%= item.get('date') %>" />
                        </div>
                        <span class="help-inline"></span>
                    </div>
                </div><!-- -->
                <div class="col-sm-auto px-3 form-group plist-group dynhide" id="refProvideridInputContainer">
                    <label class="plist-label" for="refProviderid"><?php echo xlt('Referral Provider')?></label>
                    <div class="controls inline-inputs">
                        <select  disabled class="form-control" id="refProviderid"  value="<%= _.escape(item.get('refProviderid') || '') %>"></select>
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="emailInputContainer">
                    <label class="plist-label" for="email"><?php echo xlt('Email')?></label>
                    <div class="controls inline-inputs">
                        <input type="email" class="form-control" id="email" required placeholder="<?php echo xla('Email'); ?>" value="<%= _.escape(item.get('email') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group dynhide" id="emailDirectInputContainer">
                    <label class="plist-label" for="emailDirect"><?php echo xlt('Email Direct')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="emailDirect" placeholder="<?php echo xla('Direct Email'); ?>" value="<%= _.escape(item.get('emailDirect') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="languageInputContainer">
                    <label class="plist-label" for="language"><?php echo xlt('Preferred Language')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="language" placeholder="<?php echo xla('Language'); ?>" value="<%= _.escape(item.get('language') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
            <div class="col-sm-auto px-3 form-group plist-group" id="raceInputContainer">
                <label class="plist-label" for="race"><?php echo xlt('Race')?></label><br />
                <div class="controls inline-inputs">
                    <?php
                  # Generate drop down list for Race
                    echo generate_select_list('race', 'race', $row[ 'race'], xl('Race'), 'Unassigned', "form-control");
                    ?>
                <span class="help-inline"></span>
                </div>
           </div>
           <div class="col-sm-auto px-3 form-group plist-group" id="ethnicityInputContainer">
                    <label class="plist-label" for="ethnicity"><?php echo xlt('Ethnicity')?></label><br />
                    <div class="controls inline-inputs">
                        <?php
                      # Generate drop down list for Ethnicity
                        echo generate_select_list('ethnicity', 'ethnicity', $row['ethnicity'], xl('Ethnicity'), 'Unassigned', "form-control");
                        ?>
                    <span class="help-inline"></span>
                    </div>
            </div>
            <div class="col-sm-auto px-3 form-group plist-group" id="religionInputContainer">
                <label class="plist-label" for="religion"><?php echo xlt('Religion')?></label><br />
                <div class="controls inline-inputs">
                    <?php
                  # Generate drop down list for Religion
                    echo generate_select_list('religion', 'religious_affiliation', $row['religion'], xl('Religion'), 'Unassigned', "form-control");
                    ?>
                 <span class="help-inline"></span>
                </div>
            </div>
            <div class="col-sm-auto px-3 form-group plist-group dynhide" id="familySizeInputContainer">
                    <label class="plist-label" for="familySize"><?php echo xlt('Family Size')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="familySize" placeholder="<?php echo xla('Family Size'); ?>" value="<%= _.escape(item.get('familySize') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="referralSourceInputContainer">
                    <label class="plist-label" for="referralSource"><?php echo xlt('How Referred')?></label><br />
                    <div class="controls inline-inputs">
                        <?php
                      # Generate drop down list for Referral Source
                        echo generate_select_list('referralSource', 'refsource', $row['referralSource'], xl('Referral Source'), 'Unassigned', "form-control");
                        ?>
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group dynhide" id="regdateInputContainer">
                    <label class="plist-label" for="regdate"><?php echo xlt('Registration Date')?></label>
                    <div class="controls inline-inputs">
                        <div class="input-group">
                            <input disabled id="regdate" type="text" class="form-control jquery-date-picker" value="<%= item.get('regdate') %>" />
                        </div>
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="mothersnameInputContainer">
                    <label class="plist-label" for="mothersname"><?php echo xlt('Mothers Name')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="mothersname" placeholder="<?php echo xla('Mothers Name'); ?>" value="<%= _.escape(item.get('mothersname') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group dynhide" id="guardiansnameInputContainer">
                    <label class="plist-label" for="guardiansname"><?php echo xlt('Guardians Name')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="guardiansname" placeholder="<?php echo xla('Guardians Name'); ?>" value="<%= _.escape(item.get('guardiansname') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="hipaaMailInputContainer">
                    <label class="plist-label" for="hipaaMail"><?php echo xlt('Allow Postal Mail')?></label>
                    <div class="controls inline-inputs">
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="hipaaMail0" name="hipaaMail" type="radio" value="NO"<% if (item.get('hipaaMail')=="NO") { %> checked="checked"<% } %>><?php echo xlt('NO'); ?></label>
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="hipaaMail1" name="hipaaMail" type="radio" value="YES"<% if (item.get('hipaaMail')=="YES") { %> checked="checked"<% } %>><?php echo xlt('YES'); ?></label>
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="hipaaMail2" name="hipaaMail" type="radio" value=""<% if (item.get('hipaaMail')=="") { %> checked="checked"<% } %>><?php echo xlt('Unassigned'); ?></label>
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="hipaaVoiceInputContainer">
                    <label class="plist-label" for="hipaaVoice"><?php echo xlt('Allow Voice Call')?></label>
                    <div class="controls inline-inputs">
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="hipaaVoice0" name="hipaaVoice" type="radio" value="NO"<% if (item.get('hipaaVoice')=="NO") { %> checked="checked"<% } %>><?php echo xlt('NO'); ?></label>
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="hipaaVoice1" name="hipaaVoice" type="radio" value="YES"<% if (item.get('hipaaVoice')=="YES") { %> checked="checked"<% } %>><?php echo xlt('YES'); ?></label>
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="hipaaVoice2" name="hipaaVoice" type="radio" value=""<% if (item.get('hipaaVoice')=="") { %> checked="checked"<% } %>><?php echo xlt('Unassigned'); ?></label>
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="hipaaNoticeInputContainer">
                    <label class="plist-label" for="hipaaNotice"><?php echo xlt('Allow Notice')?></label>
                    <div class="controls inline-inputs">
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="hipaaNotice0" name="hipaaNotice" type="radio" value="NO"<% if (item.get('hipaaNotice')=="NO") { %> checked="checked"<% } %>><?php echo xlt('NO'); ?></label>
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="hipaaNotice1" name="hipaaNotice" type="radio" value="YES"<% if (item.get('hipaaNotice')=="YES") { %> checked="checked"<% } %>><?php echo xlt('YES'); ?></label>
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="hipaaNotice2" name="hipaaNotice" type="radio" value=""<% if (item.get('hipaaNotice')=="") { %> checked="checked"<% } %>><?php echo xlt('Unassigned'); ?></label>
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group dynhide" id="hipaaMessageInputContainer">
                    <label class="plist-label" for="hipaaMessage"><?php echo xlt('Hipaa Message')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="hipaaMessage" placeholder="<?php echo xla('Hipaa Message'); ?>" value="<%= _.escape(item.get('hipaaMessage') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="hipaaAllowsmsInputContainer">
                    <label class="plist-label" for="hipaaAllowsms"><?php echo xlt('Allow SMS')?></label>
                    <div class="controls inline-inputs">
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="hipaaAllowsms0" name="hipaaAllowsms" type="radio" value="NO"<% if (item.get('hipaaAllowsms')=="NO") { %> checked="checked"<% } %>><?php echo xlt('NO'); ?></label>
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="hipaaAllowsms1" name="hipaaAllowsms" type="radio" value="YES"<% if (item.get('hipaaAllowsms')=="YES") { %> checked="checked"<% } %>><?php echo xlt('YES'); ?></label>
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="hipaaAllowsms2" name="hipaaAllowsms" type="radio" value=""<% if (item.get('hipaaAllowsms')=="") { %> checked="checked"<% } %>><?php echo xlt('Unassigned'); ?></label>
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="hipaaAllowemailInputContainer">
                    <label class="plist-label" for="hipaaAllowemail"><?php echo xlt('Allow Email')?></label>
                    <div class="controls inline-inputs">
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="hipaaAllowemail0" name="hipaaAllowemail" type="radio" value="NO"<% if (item.get('hipaaAllowemail')=="NO") { %> checked="checked"<% } %>><?php echo xlt('NO'); ?></label>
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="hipaaAllowemail1" name="hipaaAllowemail" type="radio" value="YES"<% if (item.get('hipaaAllowemail')=="YES") { %> checked="checked"<% } %>><?php echo xlt('YES'); ?></label>
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="hipaaAllowemail2" name="hipaaAllowemail" type="radio" value=""<% if (item.get('hipaaAllowemail')=="") { %> checked="checked"<% } %>><?php echo xlt('Unassigned'); ?></label>
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="allowImmRegUseInputContainer">
                    <label class="plist-label" for="allowImmRegUse"><?php echo xlt('Allow Immunization Registry Use')?></label>
                    <div class="controls inline-inputs">
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="allowImmRegUse0" name="allowImmRegUse" type="radio" value="NO"<% if (item.get('allowImmRegUse')=="NO") { %> checked="checked"<% } %>><?php echo xlt('NO'); ?></label>
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="allowImmRegUse1" name="allowImmRegUse" type="radio" value="YES"<% if (item.get('allowImmRegUse')=="YES") { %> checked="checked"<% } %>><?php echo xlt('YES'); ?></label>
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="allowImmRegUse2" name="allowImmRegUse" type="radio" value=""<% if (item.get('allowImmRegUse')=="") { %> checked="checked"<% } %>><?php echo xlt('Unassigned'); ?></label>
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="allowImmInfoShareInputContainer">
                    <label class="plist-label" for="allowImmInfoShare"><?php echo xlt('Allow Immunization Info Share')?></label>
                    <div class="controls inline-inputs">
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="allowImmInfoShare0" name="allowImmInfoShare" type="radio" value="NO"<% if (item.get('allowImmInfoShare')=="NO") { %> checked="checked"<% } %>><?php echo xlt('NO'); ?></label>
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="allowImmInfoShare1" name="allowImmInfoShare" type="radio" value="YES"<% if (item.get('allowImmInfoShare')=="YES") { %> checked="checked"<% } %>><?php echo xlt('YES'); ?></label>
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="allowImmInfoShare2" name="allowImmInfoShare" type="radio" value=""<% if (item.get('allowImmInfoShare')=="") { %> checked="checked"<% } %>><?php echo xlt('Unassigned'); ?></label>
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="allowHealthInfoExInputContainer">
                    <label class="plist-label" for="allowHealthInfoEx"><?php echo xlt('Allow Health Info Exchange')?></label>
                    <div class="controls inline-inputs">
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="allowHealthInfoEx0" name="allowHealthInfoEx" type="radio" value="NO"<% if (item.get('allowHealthInfoEx')=="NO") { %> checked="checked"<% } %>><?php echo xlt('NO'); ?></label>
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="allowHealthInfoEx1" name="allowHealthInfoEx" type="radio" value="YES"<% if (item.get('allowHealthInfoEx')=="YES") { %> checked="checked"<% } %>><?php echo xlt('YES'); ?></label>
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="allowHealthInfoEx2" name="allowHealthInfoEx" type="radio" value=""<% if (item.get('allowHealthInfoEx')=="") { %> checked="checked"<% } %>><?php echo xlt('Unassigned'); ?></label>
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group" id="allowPatientPortalInputContainer">
                    <label class="plist-label" for="allowPatientPortal"><?php echo xlt('Allow Patient Portal')?></label>
                    <div class="controls inline-inputs">
                            <label class="btn btn-secondary btn-gradient btn-sm"><input disabled id="allowPatientPortal0" name="allowPatientPortal" type="radio" value="NO"<% if (item.get('allowPatientPortal')=="NO") { %> checked="checked"<% } %>><?php echo xlt('NO'); ?></label>
                            <label class="btn btn-secondary btn-gradient btn-sm"><input disabled id="allowPatientPortal1" name="allowPatientPortal" type="radio" value="YES"<% if (item.get('allowPatientPortal')=="YES") { %> checked="checked"<% } %>><?php echo xlt('YES'); ?></label>
                            <label class="btn btn-secondary btn-gradient btn-sm"><input disabled id="allowPatientPortal2" name="allowPatientPortal" type="radio" value=""<% if (item.get('allowPatientPortal')=="") { %> checked="checked"<% } %>><?php echo xlt('Unassigned'); ?></label>
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group dynhide" id="careTeamInputContainer">
                    <label class="plist-label" for="careTeam"><?php echo xlt('Care Team')?></label>
                    <div class="controls inline-inputs">
                        <select class="form-control" id="careTeam" placeholder="<?php echo xla('Care Team'); ?>" value="<%= _.escape(item.get('careTeam') || '') %>"></select>
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="col-sm-auto px-3 form-group plist-group dynhide" id="noteInputContainer">
                    <label class="plist-label" style="color:green" for="note"><?php echo xlt('Message to Reviewer')?></label>
                    <div class="controls inline-inputs">
                        <textarea class="form-control" id="note" rows="1" style='min-width:180px'><%= _.escape("To Admin: ") %></textarea>
                        <span class="help-inline"></span>
                    </div>
                </div>
                </div>
            </fieldset>
        </form>

</script>

    <div id="collectionAlert"></div>
    <div id="modelAlert"></div>
    <div id="patientCollectionContainer" class="collectionContainer"></div><!--  -->
    <div id="patientModelContainer" class="modelContainer"></div>
</div> <!-- /container -->
<?php //$this->display('_Footer.tpl.php');?>
</body>
</html>
                <!-- <div class="form-group plist-group" id="ethnoracialInputContainer">
                    <label class="plist-label" for="ethnoracial"><?php echo xlt('Ethnoracial')?></label><br />
                    <div class="controls inline-inputs">
                    <?php
                      # Generate drop down list for Ethnoracial
                      //echo generate_select_list('ethnoracial', 'ethrace', $row['ethnoracial'], xl('Ethnoracial'), 'Unassigned', "form-control");
                    ?>
                        <span class="help-inline"></span>
                    </div>
                </div> -->
                <!-- <div class="form-group plist-group" id="interpretterInputContainer">
                    <label class="plist-label" for="interpretter"><?php //echo xlt('Interpreter')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="interpretter" placeholder="<?php //echo xla('Interpreter')?>" value="<%= _.escape(item.get('interpretter') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="migrantseasonalInputContainer">
                    <label class="plist-label" for="migrantseasonal"><?php //echo xlt('Migrant Seasonal')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="migrantseasonal" placeholder="<?php //echo xla('Migrant Seasonal')?>" value="<%= _.escape(item.get('migrantseasonal') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div> -->
                <!-- <div class="form-group plist-group" id="industryInputContainer">
                    <label class="plist-label" for="industry"><?php //echo xlt('Industry')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="industry" placeholder="<?php //echo xla('Industry')?>" value="<%= _.escape(item.get('industry') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="occupationInputContainer">
                    <label class="plist-label" for="occupation"><?php //echo xlt('Occupation')?></label>
                    <div class="controls inline-inputs">
                        <textarea class="form-control" id="occupation" rows="1" style='min-width:90px'><%= _.escape(item.get('occupation') || '') %></textarea>
                        <span class="help-inline"></span>
                    </div>
                </div> -->
            <!--<div class="form-group plist-group" id="referrerInputContainer">
                    <label class="plist-label" for="referrer"><?php //echo xlt('Referrer')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="referrer" placeholder="<?php //echo xla('Referrer')?>" value="<%= _.escape(item.get('referrer') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="referreridInputContainer">
                    <label class="plist-label" for="referrerid"><?php //echo xlt('Referrerid')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="referrerid" placeholder="<?php //echo xla('Referrerid')?>" value="<%= _.escape(item.get('referrerid') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>-->
                                <!-- <div class="form-group plist-group" id="monthlyIncomeInputContainer">
                    <label class="plist-label" for="monthlyIncome"><?php //echo xlt('Monthly Income')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="monthlyIncome" placeholder="<?php //echo xla('Monthly Income')?>" value="<%= _.escape(item.get('monthlyIncome') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="billingNoteInputContainer">
                    <label class="plist-label" for="billingNote"><?php //echo xlt('Billing Note')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="billingNote" placeholder="<?php //echo xla('Billing Note')?>" value="<%= _.escape(item.get('billingNote') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="homelessInputContainer">
                    <label class="plist-label" for="homeless"><?php //echo xlt('Homeless')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="homeless" placeholder="<?php //echo xla('Homeless')?>" value="<%= _.escape(item.get('homeless') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="financialReviewInputContainer">
                    <label class="plist-label" for="financialReview"><?php //echo xlt('Financial Review')?></label>
                    <div class="controls inline-inputs">
                        <div class="input-group">
                            <input id="financialReview" type="text" class="form-control jquery-date-time-picker" value="<%= item.get('financialReview') %>" />
                        </div>
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="pubpidInputContainer">
                    <label class="plist-label" for="pubpid"><?php //echo xlt('Pubpid')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="pubpid" placeholder="<?php //echo xla('Pubpid')?>" value="<%= _.escape(item.get('pubpid') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>

                <div class="form-group plist-group" id="genericname1InputContainer">
                    <label class="plist-label" for="genericname1"><?php //echo xlt('Genericname1')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="genericname1" placeholder="<?php //echo xla('Genericname1')?>" value="<%= _.escape(item.get('genericname1') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="genericval1InputContainer">
                    <label class="plist-label" for="genericval1"><?php //echo xlt('Genericval1')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="genericval1" placeholder="<?php //echo xla('Genericval1')?>" value="<%= _.escape(item.get('genericval1') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="genericname2InputContainer">
                    <label class="plist-label" for="genericname2"><?php //echo xlt('Genericname2')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="genericname2" placeholder="<?php //echo xla('Genericname2')?>" value="<%= _.escape(item.get('genericname2') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="genericval2InputContainer">
                    <label class="plist-label" for="genericval2"><?php //echo xlt('Genericval2')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="genericval2" placeholder="<?php //echo xla('Genericval2')?>" value="<%= _.escape(item.get('genericval2') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div> -->
                <!-- <div class="form-group plist-group" id="squadInputContainer">
                    <label class="plist-label" for="squad"><?php //echo xlt('Squad')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="squad" placeholder="<?php //echo xla('Squad')?>" value="<%= _.escape(item.get('squad') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="fitnessInputContainer">
                    <label class="plist-label" for="fitness"><?php //echo xlt('Fitness')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="fitness" placeholder="<?php //echo xla('Fitness')?>" value="<%= _.escape(item.get('fitness') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div> -->
               <!-- <div class="form-group plist-group" id="allowPatientPortalInputContainer">
                    <label class="plist-label" for="allowPatientPortal"><?php //echo xlt('Allow Patient Portal')?></label>
                    <div class="controls inline-inputs">
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="allowPatientPortal0" name="allowPatientPortal" type="radio" value="NO"<% if (item.get('allowPatientPortal')=="NO") { %> checked="checked"<% } %>><?php //echo xlt('NO'); ?></label>
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="allowPatientPortal1" name="allowPatientPortal" type="radio" value="YES"<% if (item.get('allowPatientPortal')=="YES") { %> checked="checked"<% } %>><?php //echo xlt('YES'); ?></label>
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="allowPatientPortal2" name="allowPatientPortal" type="radio" value=""<% if (item.get('allowPatientPortal')=="") { %> checked="checked"<% } %>><?php //echo xlt('Unassigned'); ?></label>
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="deceasedDateInputContainer">
                    <label class="plist-label" for="deceasedDate"><?php //echo xlt('Deceased Date')?></label>
                    <div class="controls inline-inputs">
                        <div class="input-group">
                            <input id="deceasedDate" type="text" class="form-control jquery-date-time-picker" value="<%= item.get('deceasedDate') %>" />
                        </div>
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="deceasedReasonInputContainer">
                    <label class="plist-label" for="deceasedReason"><?php //echo xlt('Deceased Reason')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="deceasedReason" placeholder="<?php //echo xla('Deceased Reason')?>" value="<%= _.escape(item.get('deceasedReason') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="soapImportStatusInputContainer">
                    <label class="plist-label" for="soapImportStatus"><?php //echo xlt('Soap Import Status')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="soapImportStatus" placeholder="<?php //echo xla('Soap Import Status')?>" value="<%= _.escape(item.get('soapImportStatus') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="cmsportalLoginInputContainer">
                    <label class="plist-label" for="cmsportalLogin"><?php //echo xlt('Cmsportal Login')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="cmsportalLogin" placeholder="<?php //echo xla('Cmsportal Login')?>" value="<%= _.escape(item.get('cmsportalLogin') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div> -->
                        <!--        <div class="form-group plist-group" id="contrastartInputContainer">
                    <label class="plist-label" for="contrastart"><?php //echo xlt('Contrastart')?></label>
                    <div class="controls inline-inputs">
                        <div class="input-group">
                            <input id="contrastart" type="text" class="form-control jquery-date-time-picker" value="<%= item.get('contrastart') %>" />
                        </div>
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="completedAdInputContainer">
                    <label class="plist-label" for="completedAd"><?php //echo xlt('Completed Ad')?></label>
                    <div class="controls inline-inputs">
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="completedAd0" name="completedAd" type="radio" value="NO"<% if (item.get('completedAd')=="NO") { %> checked="checked"<% } %>><?php //echo xlt('NO'); ?></label>
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="completedAd1" name="completedAd" type="radio" value="YES"<% if (item.get('completedAd')=="YES") { %> checked="checked"<% } %>><?php //echo xlt('YES'); ?></label>
                            <label class="btn btn-secondary btn-gradient btn-sm"><input id="completedAd2" name="completedAd" type="radio" value=""<% if (item.get('completedAd')=="") { %> checked="checked"<% } %>><?php //echo xlt('Unassigned'); ?></label>
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="adReviewedInputContainer">
                    <label class="plist-label" for="adReviewed"><?php //echo xlt('Ad Reviewed')?></label>
                    <div class="controls inline-inputs">
                        <div class="input-group">
                            <input id="adReviewed" type="text" class="form-control jquery-date-time-picker" value="<%= item.get('adReviewed') %>" />
                        </div>
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="vfcInputContainer">
                    <label class="plist-label" for="vfc"><?php //echo xlt('Vfc')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="vfc" placeholder="<?php //echo xla('Vfc')?>" value="<%= _.escape(item.get('vfc') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div> -->
                                <!-- <div class="form-group plist-group" id="usertext1InputContainer">
                    <label class="plist-label" for="usertext1"><?php //echo xlt('Usertext1')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="usertext1" placeholder="<?php //echo xla('Usertext1')?>" value="<%= _.escape(item.get('usertext1') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="usertext2InputContainer">
                    <label class="plist-label" for="usertext2"><?php //echo xlt('Usertext2')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="usertext2" placeholder="<?php //echo xla('Usertext2')?>" value="<%= _.escape(item.get('usertext2') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="usertext3InputContainer">
                    <label class="plist-label" for="usertext3"><?php //echo xlt('Usertext3')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="usertext3" placeholder="<?php //echo xla('Usertext3')?>" value="<%= _.escape(item.get('usertext3') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="usertext4InputContainer">
                    <label class="plist-label" for="usertext4"><?php //echo xlt('Usertext4')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="usertext4" placeholder="<?php //echo xla('Usertext4')?>" value="<%= _.escape(item.get('usertext4') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="usertext5InputContainer">
                    <label class="plist-label" for="usertext5"><?php //echo xlt('Usertext5')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="usertext5" placeholder="<?php //echo xla('Usertext5')?>" value="<%= _.escape(item.get('usertext5') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="usertext6InputContainer">
                    <label class="plist-label" for="usertext6"><?php //echo xlt('Usertext6')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="usertext6" placeholder="<?php //echo xla('Usertext6')?>" value="<%= _.escape(item.get('usertext6') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="usertext7InputContainer">
                    <label class="plist-label" for="usertext7"><?php //echo xlt('Usertext7')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="usertext7" placeholder="<?php //echo xla('Usertext7')?>" value="<%= _.escape(item.get('usertext7') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="usertext8InputContainer">
                    <label class="plist-label" for="usertext8"><?php //echo xlt('Usertext8')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="usertext8" placeholder="<?php //echo xla('Usertext8')?>" value="<%= _.escape(item.get('usertext8') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="userlist1InputContainer">
                    <label class="plist-label" for="userlist1"><?php //echo xlt('Userlist1')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="userlist1" placeholder="<?php //echo xla('Userlist1')?>" value="<%= _.escape(item.get('userlist1') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="userlist2InputContainer">
                    <label class="plist-label" for="userlist2"><?php //echo xlt('Userlist2')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="userlist2" placeholder="<?php //echo xla('Userlist2')?>" value="<%= _.escape(item.get('userlist2') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="userlist3InputContainer">
                    <label class="plist-label" for="userlist3"><?php //echo xlt('Userlist3')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="userlist3" placeholder="<?php //echo xla('Userlist3')?>" value="<%= _.escape(item.get('userlist3') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="userlist4InputContainer">
                    <label class="plist-label" for="userlist4"><?php //echo xlt('Userlist4')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="userlist4" placeholder="<?php //echo xla('Userlist4')?>" value="<%= _.escape(item.get('userlist4') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="userlist5InputContainer">
                    <label class="plist-label" for="userlist5"><?php //echo xlt('Userlist5')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="userlist5" placeholder="<?php //echo xla('Userlist5')?>" value="<%= _.escape(item.get('userlist5') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="userlist6InputContainer">
                    <label class="plist-label" for="userlist6"><?php //echo xlt('Userlist6')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="userlist6" placeholder="<?php //echo xla('Userlist6')?>" value="<%= _.escape(item.get('userlist6') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="userlist7InputContainer">
                    <label class="plist-label" for="userlist7"><?php //echo xlt('Userlist7')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="userlist7" placeholder="<?php //echo xla('Userlist7')?>" value="<%= _.escape(item.get('userlist7') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>
                <div class="form-group plist-group" id="pricelevelInputContainer">
                    <label class="plist-label" for="pricelevel"><?php //echo xlt('Pricelevel')?></label>
                    <div class="controls inline-inputs">
                        <input type="text" class="form-control" id="pricelevel" placeholder="<?php //echo xla('Pricelevel')?>" value="<%= _.escape(item.get('pricelevel') || '') %>">
                        <span class="help-inline"></span>
                    </div>
                </div>-->
