<?php

/**
 * sdoh form
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Char Miller <charjmiller@gmail.com>
 * @copyright Copyright (c) 2022 Char Miller <charjmiller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// block of code to securely support use by the patient portal
//   since need this class before autoloader, need to manually include it and then set it in line below with use command
require_once(__DIR__ . "/../../../src/Common/Forms/CoreFormToPortalUtility.php");
use OpenEMR\Common\Forms\CoreFormToPortalUtility;

// block of code to securely support use by the patient portal
$patientPortalSession = CoreFormToPortalUtility::isPatientPortalSession($_GET);
if ($patientPortalSession) {
    $ignoreAuth_onsite_portal = true;
}
$patientPortalOther = CoreFormToPortalUtility::isPatientPortalOther($_GET);

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_GET['id'])) {
    $obj = formFetch("form_sdoh", $_GET["id"]);
    $mode = 'update';
    // if running from patient portal, then below will ensure patient can only see their forms
    CoreFormToPortalUtility::confirmFormBootstrapPatient($patientPortalSession, $_GET['id'], 'sdoh', $_SESSION['pid']);
} else {
    $mode = 'new';
}

?>
<html>
<head>
    <title><?php echo xlt("Social Screening Tool"); ?></title>

    <?php Header::setupHeader();?>

    <script>

        $(function () {
            document.body.addEventListener('click', CalculateTotal, true);
        })

        function CalculateTotal() {
            let totalscore = 0;
            if (document.getElementById('lessthanhs').checked == true) {totalscore +=5; }
            if (document.getElementById('highschool').checked == true) {totalscore +=3; }
            if (document.getElementById('associate').checked == true) {totalscore +=1; }
            if (document.getElementById('disabilityyes').checked == true) {totalscore +=5; }
            if (document.getElementById('housetemporary').checked == true) {totalscore +=2; }
            if (document.getElementById('houseunsafe').checked == true) {totalscore +=2; }
            if (document.getElementById('housecar').checked == true) {totalscore +=3; }
            if (document.getElementById('houseunshelter').checked == true) {totalscore +=5; }
            if (document.getElementById('houseother').checked == true) {totalscore +=1; }
            if (document.getElementById('worktemporary').checked == true) {totalscore +=1; }
            if (document.getElementById('workseasonal').checked == true) {totalscore +=1; }
            if (document.getElementById('worklooking').checked == true) {totalscore +=3; }
            if (document.getElementById('workretired').checked == true) {totalscore +=1; }
            if (document.getElementById('workdisabled').checked == true) {totalscore +=3; }
            if (document.getElementById('careunder5').checked == true) {totalscore +=5; }
            if (document.getElementById('care5to12').checked == true) {totalscore +=3; }
            if (document.getElementById('careover12').checked == true) {totalscore +=1; }
            if (document.getElementById('carespecneeds').checked == true) {totalscore +=5; }
            if (document.getElementById('caredisabled').checked == true) {totalscore +=5; }
            if (document.getElementById('careelderly').checked == true) {totalscore +=5; }
            if (document.getElementById('careother').checked == true) {totalscore +=1; }
            if (document.getElementById('debtmedical').checked == true) {totalscore +=3; }
            if (document.getElementById('debtcreditcards').checked == true) {totalscore +=1; }
            if (document.getElementById('debtrent').checked == true) {totalscore +=1; }
            if (document.getElementById('debtstudentloans').checked == true) {totalscore +=1; }
            if (document.getElementById('debttaxes').checked == true) {totalscore +=1; }
            if (document.getElementById('debtlegal').checked == true) {totalscore +=1; }
            if (document.getElementById('debtcar').checked == true) {totalscore +=1; }
            if (document.getElementById('debtutilities').checked == true) {totalscore +=1; }
            if (document.getElementById('debtother').checked == true) {totalscore +=1; }
            if (document.getElementById('moneyfood').checked == true) {totalscore +=3; }
            if (document.getElementById('moneymedical').checked == true) {totalscore +=2; }
            if (document.getElementById('moneychildcare').checked == true) {totalscore +=2; }
            if (document.getElementById('moneyutilities').checked == true) {totalscore +=1; }
            if (document.getElementById('moneyphone').checked == true) {totalscore +=1; }
            if (document.getElementById('moneyrent').checked == true) {totalscore +=2; }
            if (document.getElementById('moneytransportation').checked == true) {totalscore +=1; }
            if (document.getElementById('moneyclothing').checked == true) {totalscore +=1; }
            if (document.getElementById('moneyeducation').checked == true) {totalscore +=1; }
            if (document.getElementById('moneyother').checked == true) {totalscore +=1; }
            if (document.getElementById('transportmedical').checked == true) {totalscore +=1; }
            if (document.getElementById('transportfood').checked == true) {totalscore +=2; }
            if (document.getElementById('transportwork').checked == true) {totalscore +=2; }
            if (document.getElementById('transportschool').checked == true) {totalscore +=1; }
            if (document.getElementById('transportfamily').checked == true) {totalscore +=1; }
            if (document.getElementById('transportother').checked == true) {totalscore +=1; }
            if (document.getElementById('medicalnoinsurance').checked == true) {totalscore +=3; }
            if (document.getElementById('medicalcopay').checked == true) {totalscore +=2; }
            if (document.getElementById('medicalnotcovered').checked == true) {totalscore +=2; }
            if (document.getElementById('medicalwork').checked == true) {totalscore +=1; }
            if (document.getElementById('medicalnoprovider').checked == true) {totalscore +=1; }
            if (document.getElementById('medicalunderstand').checked == true) {totalscore +=1; }
            if (document.getElementById('medicaltrust').checked == true) {totalscore +=1; }
            if (document.getElementById('medicalchildcare').checked == true) {totalscore +=1; }
            if (document.getElementById('medicalother').checked == true) {totalscore +=1; }
            if (document.getElementById('dentistnoinsurance').checked == true) {totalscore +=1; }
            if (document.getElementById('dentistnoprovider').checked == true) {totalscore +=1; }
            if (document.getElementById('dentistnowork').checked == true) {totalscore +=1; }
            if (document.getElementById('dentistnoother').checked == true) {totalscore +=1; }
            if (document.getElementById('sociallessthan1').checked == true) {totalscore +=3; }
            if (document.getElementById('social1').checked == true) {totalscore +=2; }
            if (document.getElementById('social2to3').checked == true) {totalscore +=1; }
            if (document.getElementById('stresslevelsomewhat').checked == true) {totalscore +=1; }
            if (document.getElementById('stresslevelalot').checked == true) {totalscore +=2; }
            if (document.getElementById('stresslevelverymuch').checked == true) {totalscore +=3; }
            if (document.getElementById('stressdeath').checked == true) {totalscore +=5; }
            if (document.getElementById('stressdivorce').checked == true) {totalscore +=3; }
            if (document.getElementById('stressjob').checked == true) {totalscore +=3; }
            if (document.getElementById('stressmoved').checked == true) {totalscore +=2; }
            if (document.getElementById('stressillness').checked == true) {totalscore +=3; }
            if (document.getElementById('stressvictim').checked == true) {totalscore +=3; }
            if (document.getElementById('stresswitness').checked == true) {totalscore +=1; }
            if (document.getElementById('stresslegal').checked == true) {totalscore +=2; }
            if (document.getElementById('stresshomeless').checked == true) {totalscore +=3; }
            if (document.getElementById('stressincarcerated').checked == true) {totalscore +=3; }
            if (document.getElementById('stressbankruptcy').checked == true) {totalscore +=3; }
            if (document.getElementById('stressmarriage').checked == true) {totalscore +=1; }
            if (document.getElementById('stressbirth').checked == true) {totalscore +=1; }
            if (document.getElementById('stressadultchild').checked == true) {totalscore +=1; }
            if (document.getElementById('stressother').checked == true) {totalscore +=1; }
            if (document.getElementById('safeday').checked == true) {totalscore +=1; }
            if (document.getElementById('safeno').checked == true) {totalscore +=3; }
            if (document.getElementById('partnerunsafe').checked == true) {totalscore +=5; }
            if (document.getElementById('femaleyes').checked == true) {totalscore +=3; }
            if (document.getElementById('addictionyes').checked == true) {totalscore +=3; }
            if (document.getElementById('armedservicesyes').checked == true) {totalscore +=3; }
            if (document.getElementById('refugeeyes').checked == true) {totalscore +=5; }
            if (document.getElementById('discrimrace').checked == true) {totalscore +=5; }
            if (document.getElementById('discrimgender').checked == true) {totalscore +=2; }
            if (document.getElementById('discrimsexpref').checked == true) {totalscore +=3; }
            if (document.getElementById('discrimgenexp').checked == true) {totalscore +=3; }
            if (document.getElementById('discrimreligion').checked == true) {totalscore +=2; }
            if (document.getElementById('discrimdisability').checked == true) {totalscore +=3; }
            if (document.getElementById('discrimage').checked == true) {totalscore +=1; }
            if (document.getElementById('discrimweight').checked == true) {totalscore +=1; }
            if (document.getElementById('discrimses').checked == true) {totalscore +=1; }
            if (document.getElementById('discrimedu').checked == true) {totalscore +=1; }
            if (document.getElementById('discrimmarital').checked == true) {totalscore +=1; }
            if (document.getElementById('discrimcitizen').checked == true) {totalscore +=1; }
            if (document.getElementById('discrimaccent').checked == true) {totalscore +=1; }
            if (document.getElementById('discrimcriminalhist').checked == true) {totalscore +=1; }
            if (document.getElementById('discrimother').checked == true) {totalscore +=1; }
            if (document.getElementById('displacework').checked == true) {totalscore +=1; }
            if (document.getElementById('displacehousing').checked == true) {totalscore +=1; }
            if (document.getElementById('displacehealth').checked == true) {totalscore +=1; }
            if (document.getElementById('displacelaw').checked == true) {totalscore +=1; }
            if (document.getElementById('displaceedu').checked == true) {totalscore +=1; }
            if (document.getElementById('displacepublic').checked == true) {totalscore +=1; }
            if (document.getElementById('displaceclubs').checked == true) {totalscore +=1; }
            if (document.getElementById('displacegovt').checked == true) {totalscore +=1; }
            if (document.getElementById('displacefinance').checked == true) {totalscore +=1; }
            if (document.getElementById('displaceother').checked == true) {totalscore +=1; }

            document.getElementById('totalscorerender').innerHTML = totalscore;
            document.getElementById('totalscore').value = totalscore;
        }

        <?php echo CoreFormToPortalUtility::javascriptSupportPortal($patientPortalSession, $patientPortalOther, $mode, $_GET['id'] ?? null); ?>

    </script>

</head>
<body>

    <div class="container mt-3">
        <div class="row">
            <div class="col-12">
                <h2><?php echo xlt("Social Screening Tool");?></h2>
                <?php if ($mode == "new") { ?>
                    <form method="post" action="<?php echo $rootdir;?>/forms/sdoh/save.php?mode=new<?php echo ($patientPortalSession) ? '&isPortal=1' : '' ?>" name="my_form" onsubmit="return top.restoreSession()">
                <?php } else { // $mode == "update" ?>
                    <form method="post" action="<?php echo $rootdir;?>/forms/sdoh/save.php?mode=update&id=<?php echo attr_url($_GET["id"]); ?><?php echo ($patientPortalSession) ? '&isPortal=1' : '' ?><?php echo ($patientPortalOther) ? '&formOrigin=' . attr_url($_GET['formOrigin']) : '' ?>" name="my_form" onsubmit="return top.restoreSession()">
                <?php } ?>
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                    <fieldset>
                        <legend><?php echo xlt('What is the highest level of education that you have completed?')?></legend>
                        <div class="container">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <div class="form-radio">
                                                <input type="radio" name="education" id="lessthanhs" value="lessthanhs" <?php echo (($obj["education"] ?? '') == "lessthanhs") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="lessthanhs"><?php echo xlt('Less than High School');?></label>
                                            </div>
                                            <div class="form-radio">
                                                <input type="radio" name="education" id="highschool" value="highschool" <?php echo (($obj["education"] ?? '') == "highschool") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="highschool"><?php echo xlt('High School Diploma or GED');?></label>
                                            </div>
                                            <div class="form-radio">
                                                <input type="radio" name="education" id="associate" value="associate"<?php echo (($obj["education"] ?? '') == "associate") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="associate"><?php echo xlt('2 Year College or Vocational Degree');?></label>
                                            </div>
                                            <div class="form-radio">
                                                <input type="radio" name="education" id="bachelor" value="bachelor"<?php echo (($obj["education"] ?? '') == "bachelor") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="bachelor"><?php echo xlt('Bachelors Degree');?></label>
                                            </div>
                                            <div class="form-radio">
                                                <input type="radio" name="education" id="advanced" value="advanced"<?php echo (($obj["education"] ?? '') == "advanced") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="advanced"><?php echo xlt('Advanced Degree, Masters or Doctorate');?></label>
                                            </div>
                                            <div class="form-radio">
                                                <input type="radio" name="education" id="edunotanswer" value="edunotanswer"<?php echo (($obj["education"] ?? '') == "edunotanswer") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="edunotans"><?php echo xlt('Choose not to answer');?></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('Do you or any of your family members have a disability?')?></legend>
                        <div class="container">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <div class="form-radio">
                                                <input type="radio" name="disability" id="disabilityyes" value="disabilityyes"<?php echo (($obj["disability"] ?? '') == "disabilityyes") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="disabilityyes"><?php echo xlt('Yes');?></label>
                                            </div>
                                            <div class="form-radio">
                                                <input type="radio" name="disability" id="disabilityno" value="disabilityno" <?php echo (($obj["disability"] ?? '') == "disabilityno") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="disabilityno"><?php echo xlt('No');?></label>
                                            </div>
                                            <div class="form-radio">
                                                <input type="radio" name="disability" id="disabilitynotans" value="disabilitynotans" <?php echo (($obj["disability"] ?? '') == "disabilitynotans") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="disabilitynotans"><?php echo xlt('Choose not to answer');?></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('What is your housing situation today?')?></legend>
                        <div class="container">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <div class="form-radio">
                                                <input type="radio" name="housing" id="housepermanent" name='housepermanent' value="housepermanent" <?php echo (($obj["housing"] ?? '') == "housepermanent") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="housepermanent"><?php echo xlt('Permanent and Safe');?></label>
                                            </div>
                                            <div class="form-radio">
                                                <input type="radio" name="housing" id="housetemporary" name='housetemporary' value="housetemporary" <?php echo (($obj["housing"] ?? '') == "housetemporary") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="housetemporary"><?php echo xlt('Temporary (shelter, family, friends)');?></label>
                                            </div>
                                            <div class="form-radio">
                                                <input type="radio" name="housing" id="houseunsafe" name='houseunsafe' value="houseunsafe" <?php echo (($obj["housing"] ?? '') == "houseunsafe") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="houseunsafe"><?php echo xlt('Unsafe housing (mold, exposure, unclean)');?></label>
                                            </div>
                                            <div class="form-radio">
                                                <input type="radio" name="housing" id="housecar" name='housecar' value="housecar" <?php echo (($obj["housing"] ?? '') == "housecar") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="housecar"><?php echo xlt('Car, van, or mobile home');?></label>
                                            </div>
                                            <div class="form-radio">
                                                <input type="radio" name="housing" id="houseunshelter" name='houseunshelter' value="houseunshelter" <?php echo (($obj["housing"] ?? '') == "houseunshelter") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="houseunshelter"><?php echo xlt('Unsheltered (tent, park, vacant lot)');?></label>
                                            </div>
                                            <div class="form-radio">
                                                <input type="radio" name="housing" id="houseother" name='houseother' value="houseother" <?php echo (($obj["housing"] ?? '') == "houseother") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="houseother"><?php echo xlt('Other') . ':';?></label>
                                                <input type="text" id="housingotherinput" name='housingotherinput' size="30" value="<?php echo attr($obj["housingotherinput"] ?? ''); ?>"/>
                                            </div>
                                            <div class="form-radio">
                                                <input type="radio" name="housing" id="housenotans" name='housenotans' value="housenotans" <?php echo (($obj["housing"] ?? '') == "housenotans") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="housenotans"><?php echo xlt('Choose not to answer');?></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('What is your current work situation? Check all that apply.')?></legend>
                        <div class="container">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="workfulltime" name='workfulltime' <?php echo (($obj["workfulltime"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="workfulltime"><?php echo xlt('Full Time');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="workparttime" name='workparttime' <?php echo (($obj["workparttime"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="workparttime"><?php echo xlt('Part Time');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="worktemporary" name='worktemporary'  <?php echo (($obj["worktemporary"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="worktemporary"><?php echo xlt('Temporary');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="workseasonal" name='workseasonal' <?php echo (($obj["workseasonal"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="workseasonal"><?php echo xlt('Seasonal or Migrant');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="worklooking" name='worklooking' <?php echo (($obj["worklooking"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="worklooking"><?php echo xlt('Looking for Work');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="workretired" name='workretired' <?php echo (($obj["workretired"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="workretired"><?php echo xlt('Retired');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="workdisabled" name='workdisabled' <?php echo (($obj["workdisabled"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="workdisabled"><?php echo xlt('Disabled');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="workstudent" name='workstudent'  <?php echo (($obj["workstudent"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="workstudent"><?php echo xlt('Student');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="worknotemployed" name='worknotemployed' <?php echo (($obj["worknotemployed"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="worknotemployed"><?php echo xlt('Not Employed Outside the Home');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="worknotans" name='worknotans' <?php echo (($obj["worknotans"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="worknotans"><?php echo xlt('Choose not to answer');?></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('How many hours do you work in a week?')?></legend>
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <div class="form-number">
                                            <input type="number" id="workhours" name='workhours' min="0" max="200" value="<?php echo attr($obj["workhours"] ?? ''); ?>"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('What is the total income for all your family in the past year? (This will help us know if you are eligible for benefits)')?></legend>
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <div class="form-number">
                                            <input type="number" id="hhincome" name='hhincome' min="0" max="10000000" value="<?php echo attr($obj["hhincome"] ?? ''); ?>"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('How many people are in your household? Including yourself.')?></legend>
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <div class="form-number">
                                            <input type="number" id="hhsize" name='hhsize' min="1" max="20" value="<?php echo attr($obj["hhsize"] ?? ''); ?>"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('Are you a primary caregiver for any of the following? Check all that apply.')?></legend>
                        <div class="container">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="careno" name='careno'  <?php echo (($obj["careno"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="careno"><?php echo xlt('Not a primary caregiver');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="careunder5" name='careunder5' <?php echo (($obj["careunder5"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="careunder5"><?php echo xlt('Children under 5');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="care5to12" name='care5to12' <?php echo (($obj["care5to12"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="care5to12"><?php echo xlt('Children age 5 to 12');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="careover12" name='careover12' <?php echo (($obj["careover12"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="careover12"><?php echo xlt('Children over 12');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="carespecneeds" name='carespecneeds'  <?php echo (($obj["carespecneeds"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="carespecneeds"><?php echo xlt('Special Needs Child');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="caredisabled" name='caredisabled' <?php echo (($obj["caredisabled"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="caredisabled"><?php echo xlt('Disabled or Ill Adult');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="careelderly" name='careelderly' <?php echo (($obj["careelderly"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="careelderly"><?php echo xlt('Elderly');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="careother" name='careother' <?php echo (($obj["careother"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="careother"><?php echo xlt('Other');?></label>
                                                <input type="text" id="careotherinput" name='careotherinput' size="30" value="<?php echo attr($obj["careotherinput"] ?? ''); ?>"/>
                                            </div>
                                    </div>
                                </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('Do you or a family member owe money that you struggle to pay back? Check all that apply.')?></legend>
                        <div class="container">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="debtno" name='debtno' <?php echo (($obj["debtno"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="debtno"><?php echo xlt('No debt');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="debtmedical" name='debtmedical' <?php echo (($obj["debtmedical"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="debtmedical"><?php echo xlt('Medical Bills');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="debtcreditcards" name='debtcreditcards' <?php echo (($obj["debtcreditcards"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="debtcreditcards"><?php echo xlt('Credit Cards');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="debtrent" name='debtrent' <?php echo (($obj["debtrent"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="debtrent"><?php echo xlt('Rent/Mortgage');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="debtstudentloans" name='debtstudentloans' <?php echo (($obj["debtstudentloans"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="debtstudentloans"><?php echo xlt('Student Loans');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="debttaxes" name='debttaxes' <?php echo (($obj["debttaxes"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="debttaxes"><?php echo xlt('Taxes');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="debtlegal" name='debtlegal' <?php echo (($obj["debtlegal"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="debtlegal"><?php echo xlt('Legal Fees');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="debtcar" name='debtcar' <?php echo (($obj["debtcar"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="debtcar"><?php echo xlt('Car Loan or License');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="debtutilities" name='debtutilities' <?php echo (($obj["debtutilities"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="debtutilities"><?php echo xlt('Utilities');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="debtother" name='debtother' <?php echo (($obj["debtother"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="debtother"><?php echo xlt('Other');?></label>
                                                <input type="text" id="debtotherinput" name='debtotherinput' size="30" value="<?php echo attr($obj["debtotherinput"] ?? ''); ?>"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('In the past year, have you or a family member struggled to pay for any of the following? Check all that apply.')?></legend>
                        <div class="container">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="moneyno" name='moneyno' <?php echo (($obj["moneyno"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="moneyno"><?php echo xlt('No Financial Struggles');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="moneyfood" name='moneyfood' <?php echo (($obj["moneyfood"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="moneyfood"><?php echo xlt('Healthy Food');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="moneymedical" name='moneymedical' <?php echo (($obj["moneymedical"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="moneymedical"><?php echo xlt('Medicine or Medical Care');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="moneychildcare" name='moneychildcare' <?php echo (($obj["moneychildcare"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="moneychildcare"><?php echo xlt('Child Care or School');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="moneyutilities" name='moneyutilities' <?php echo (($obj["moneyutilities"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="moneyutilities"><?php echo xlt('Utilities (Power, water)');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="moneyphone" name='moneyphone' <?php echo (($obj["moneyphone"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="moneyphone"><?php echo xlt('Phone, Internet');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="moneyrent" name='moneyrent' <?php echo (($obj["moneyrent"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="moneyrent"><?php echo xlt('Rent or Mortgage');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="moneytransportation" name='moneytransportation' <?php echo (($obj["moneytransportation"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="moneytransportation"><?php echo xlt('Transportation');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="moneyclothing" name='moneyclothing' <?php echo (($obj["moneyclothing"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="moneyclothing"><?php echo xlt('Clothing');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="moneyeducation" name='moneyeducation' <?php echo (($obj["moneyeducation"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="moneyeducation"><?php echo xlt('Education');?></label>
                                            </div>
                                             <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="moneyother" name='moneyother' <?php echo (($obj["moneyother"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="moneyother"><?php echo xlt('Other');?></label>
                                                 <input type="text" id="moneyotherinput" name='moneyotherinput' size="30" value="<?php echo attr($obj["moneyotherinput"] ?? ''); ?>"/>
                                            </div>
                                         </div>
                                    </div>
                                </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('In the past year, has lack of transportation prevented you or a family member from any of the following? Check all that apply.')?></legend>
                        <div class="container">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="transportationno" name='transportationno' <?php echo (($obj["transportationno"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="transportationno"><?php echo xlt('No Transportation Problems');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="transportmedical" name='transportmedical' <?php echo (($obj["transportmedical"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="transportmedical"><?php echo xlt('Medical Care');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="transportfood" name='transportfood' <?php echo (($obj["transportfood"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="transportfood"><?php echo xlt('Access to Healthy Food');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="transportwork" name='transportwork' <?php echo (($obj["transportwork"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="transportwork"><?php echo xlt('Work or Meetings');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="transportschool" name='transportschool' <?php echo (($obj["transportschool"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="transportschool"><?php echo xlt('School or Childcare');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="transportfamily" name='transportfamily' <?php echo (($obj["transportfamily"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="transportfamily"><?php echo xlt('Visit Family or Friends');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="transportother" name='transportother' <?php echo (($obj["transportother"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="transportother"><?php echo xlt('Other');?></label>
                                                <input type="text" id="transportotherinput" name='transportotherinput' size="30" value="<?php echo attr($obj["transportotherinput"] ?? ''); ?>"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('In the past year, have you or a family member not gotten medical care because of any of the following? Check all that apply.')?></legend>
                        <div class="container">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="medicalno" name='medicalno' <?php echo (($obj["medicalno"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="medicalno"><?php echo xlt('No delayed medical care');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="medicalnoinsurance" name='medicalnoinsurance' <?php echo (($obj["medicalnoinsurance"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="medicalnoinsurance"><?php echo xlt('No Insurance');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="medicalcopay" name='medicalcopay' <?php echo (($obj["medicalcopay"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="medicalcopay"><?php echo xlt('Copay or Deductible is too high');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="medicalnotcovered" name='medicalnotcovered' <?php echo (($obj["medicalnotcovered"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="medicalnotcovered"><?php echo xlt('Needed care is not covered by insurance');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="medicalwork" name='medicalwork' <?php echo (($obj["medicalwork"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="medicalwork"><?php echo xlt('Not able to take time off work');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="medicalnoprovider" name='medicalnoprovider' <?php echo (($obj["medicalnoprovider"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="medicalnoprovider"><?php echo xlt('No provider available');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="medicalunderstand" name='medicalunderstand' <?php echo (($obj["medicalunderstand"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="medicalunderstand"><?php echo xlt('Did not understand provider recommendations');?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="medicaltrust" name='medicaltrust' <?php echo (($obj["medicaltrust"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                <label class="form-check-label" for="medicaltrust"><?php echo xlt('Lack of trust in medical care');?></label>
                                            </div>
                                            <div class="form-check">
                                                 <input type="checkbox" class="form-check-input" id="medicalchildcare" name='medicalchildcare' <?php echo (($obj["medicalchildcare"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                 <label class="form-check-label" for="medicalchildcare"><?php echo xlt('No child care');?></label>
                                            </div>
                                            <div class="form-check">
                                                 <input type="checkbox" class="form-check-input" id="medicalother" name='medicalother' <?php echo (($obj["medicalother"] ?? '') == "on") ? "checked" : ""; ?>/>
                                                 <label class="form-check-label" for="medicalother"><?php echo xlt('Other');?></label>
                                                <input type="text" id="medicalotherinput" name='medicalotherinput' size="30" value="<?php echo attr($obj["medicalotherinput"] ?? ''); ?>"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('In the past year, have you and your family members seen dentists?')?></legend>
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <div class="form-radio">
                                            <input type="radio" name="dentist" id="dentistyes" value="dentistyes" <?php echo (($obj["dentist"] ?? '') == "dentistyes") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="dentistyes"><?php echo xlt('Yes');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="dentist" id="dentistnoinsurance" value="dentistnoinsurance" <?php echo (($obj["dentist"] ?? '') == "dentistnoinsurance") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="dentistnoinsurance"><?php echo xlt('No, not insured');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="dentist" id="dentistnoprovider" value="dentistnoprovider" <?php echo (($obj["dentist"] ?? '') == "dentistnoprovider") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="dentistnoprovider"><?php echo xlt('No, need dentist');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="dentist" id="dentistnowork" value="dentistnowork" <?php echo (($obj["dentist"] ?? '') == "dentistnowork") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="dentistnowork"><?php echo xlt('No, not able to take time off work');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="dentist" id="dentistnoother" value="dentistnoother" <?php echo (($obj["dentist"] ?? '') == "dentistnoother") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="dentistnoother"><?php echo xlt('No, other');?></label>
                                            <input type="text" id="dentistotherinput" name='dentistotherinput' size="30" value="<?php echo attr($obj["dentistotherinput"] ?? ''); ?>"/>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="dentist" id="dentistnotans" value="dentistnotans" <?php echo (($obj["dentist"] ?? '') == "dentistnotans") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="dentistnotans"><?php echo xlt('Choose not to answer');?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('How often do you see or talk to people that you care about or feel close to?  (For example: talking to friends on the phone, visiting friends or family, going to church or club meetings)')?></legend>
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <div class="form-radio">
                                            <input type="radio" name="social" id="sociallessthan1" value="sociallessthan1" <?php echo (($obj["social"] ?? '') == "sociallessthan1") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="sociallessthan1"><?php echo xlt('Less than once a week');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="social" id="social1" value= "social1" <?php echo (($obj["social"] ?? '') == "social1") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="social1"><?php echo xlt('1 time a week');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="social" id="social2to3" value="social2to3" <?php echo (($obj["social"] ?? '') == "social2to3") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="social2to3"><?php echo xlt('2-3 times a week');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="social" id="social4to5" value="social4to5" <?php echo (($obj["social"] ?? '') == "social4to5") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="social4to5"><?php echo xlt('4-5 times a week');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="social" id="socialdaily" value="socialdaily" <?php echo (($obj["social"] ?? '') == "socialdaily") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="socialdaily"><?php echo xlt('Almost every day');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="social" id="socialnotans" value="socialnotans" <?php echo (($obj["social"] ?? '') == "socialnotans") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="socialnotans"><?php echo xlt('Choose not to answer');?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('Stress is when someone feels tense, nervous, anxious, or can’t sleep at night because their mind is troubled. How stressed are you?')?></legend>
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <div class="form-radio">
                                            <input type="radio" name="stress" id="stresslevelno" value="stresslevelno" <?php echo (($obj["stress"] ?? '') == "stresslevelno") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="stresslevelno"><?php echo xlt('Not at all');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="stress" id="stresslevellittle" value="stresslevellittle" <?php echo (($obj["stress"] ?? '') == "stresslevellittle") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="stresslevellittle"><?php echo xlt('A little bit');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="stress" id="stresslevelsomewhat" value="stresslevelsomewhat" <?php echo (($obj["stress"] ?? '') == "stresslevelsomewhat") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="stresslevelsomewhat"><?php echo xlt('Somewhat');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="stress" id="stresslevelalot" value="stresslevelalot" <?php echo (($obj["stress"] ?? '') == "stresslevelalot") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="stresslevelalot"><?php echo xlt('Quite a bit');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="stress" id="stresslevelverymuch" value="stresslevelverymuch" <?php echo (($obj["stress"] ?? '') == "stresslevelverymuch") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="stresslevelverymuch"><?php echo xlt('Very Much');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="stress" id="stresslevelnotans" value="stresslevelnotans" <?php echo (($obj["stress"] ?? '') == "stresslevelnotans") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="stresslevelnotans"><?php echo xlt('Choose not to answer');?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('In the past year, have you had any of the following stressful life events occur? Check all that apply.')?></legend>
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="stressno" name='stressno' <?php echo (($obj["stressno"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="stressno"><?php echo xlt('No Stressful Life Events');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="stressdeath" name='stressdeath' <?php echo (($obj["stressdeath"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="stressdeath"><?php echo xlt('Death of a loved one');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="stressdivorce" name='stressdivorce' <?php echo (($obj["stressdivorce"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="stressdivorce"><?php echo xlt('Divorce or separation');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="stressjob" name='stressjob' <?php echo (($obj["stressjob"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="stressjob"><?php echo xlt('Loss of job');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="stressmoved" name='stressmoved' <?php echo (($obj["stressmoved"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="stressmoved"><?php echo xlt('Moved');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="stressillness" name='stressillness' <?php echo (($obj["stressillness"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="stressillness"><?php echo xlt('Major illness or injury');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="stressvictim" name='stressvictim' <?php echo (($obj["stressvictim"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="stressvictim"><?php echo xlt('Victim of a crime');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="stresswitness" name='stresswitness' <?php echo (($obj["stresswitness"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="stresswitness"><?php echo xlt('Witness of a crime or accident');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="stresslegal" name='stresslegal' <?php echo (($obj["stresslegal"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="stresslegal"><?php echo xlt('Legal Issues');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="stresshomeless" name='stresshomeless' <?php echo (($obj["stresshomeless"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="stresshomeless"><?php echo xlt('Homeless');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="stressincarcerated" name='stressincarcerated' <?php echo (($obj["stressincarcerated"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="stressincarcerated"><?php echo xlt('Incarcerated');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="stressbankruptcy" name='stressbankruptcy' <?php echo (($obj["stressbankruptcy"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="stressbankruptcy"><?php echo xlt('Bankruptcy');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="stressmarriage" name='stressmarriage' <?php echo (($obj["stressmarriage"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="stressmarriage"><?php echo xlt('Marriage');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="stressbirth" name='stressbirth' <?php echo (($obj["stressbirth"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="stressbirth"><?php echo xlt('Birth of a child');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="stressadultchild" name='stressadultchild' <?php echo (($obj["stressadultchild"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="stressadultchild"><?php echo xlt('Child moving out');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="stressother" name='stressother' <?php echo (($obj["stressother"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="stressother"><?php echo xlt('Other');?></label>
                                            <input type="text" id="stressotherinput" name='stressotherinput' size="30" value="<?php echo attr($obj["stressotherinput"] ?? ''); ?>"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('Do you feel safe walking and living in your neighborhood?')?></legend>
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <div class="form-radio">
                                            <input type="radio" name="safety" id="safeyes" value="safeyes" <?php echo (($obj["safety"] ?? '') == "safeyes") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="safeyes"><?php echo xlt('Yes, all the time');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="safety" id="safeday" value="safeday" <?php echo (($obj["safety"] ?? '') == "safeday") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="safeday"><?php echo xlt('Yes, during the day');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="safety" id="safeno" value="safeno" <?php echo (($obj["safety"] ?? '') == "safeno") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="safeno"><?php echo xlt('No');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="safety" id="safenotans" value="safenotans" <?php echo (($obj["safety"] ?? '') == "safenotans") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="safenotans"><?php echo xlt('Choose not to answer');?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('In the past year, have you or a family member been afraid of a partner or ex-partner?')?></legend>
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <div class="form-radio">
                                            <input type="radio" name="partnersafety" id="partnerunsafe" value="partnerunsafe" <?php echo (($obj["partnersafety"] ?? '') == "partnerunsafe") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="partnerunsafe"><?php echo xlt('Yes');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="partnersafety" id="partnersafe" value="partnersafe" <?php echo (($obj["partnersafety"] ?? '') == "partnersafe") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="partnersafe"><?php echo xlt('No');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="partnersafety" id="partnernotans" value="partnernotans" <?php echo (($obj["partnersafety"] ?? '') == "partnernotans") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="partnernotans"><?php echo xlt('Choose not to answer');?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('In the past year, have you been a female headed household?')?></legend>
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <div class="form-radio">
                                            <input type="radio" name="female" id="femaleyes" value="femaleyes" <?php echo (($obj["female"] ?? '') == "femaleyes") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="femaleyes"><?php echo xlt('Yes');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="female" id="femaleno" value="femaleno" <?php echo (($obj["female"] ?? '') == "femaleno") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="femaleno"><?php echo xlt('No');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="female" id="femalenotans" value="femalenotans" <?php echo (($obj["female"] ?? '') == "femalenotans") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="femalenotans"><?php echo xlt('Choose not to answer');?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend><?php echo xlt('In the past year, have you or anyone in your family struggled with addiction?')?></legend>
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <div class="form-radio">
                                            <input type="radio" name="addiction" id="addictionyes" value="addictionyes" <?php echo (($obj["addiction"] ?? '') == "addictionyes") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="addictionyes"><?php echo xlt('Yes');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="addiction" id="addictionno" value="addictionno" <?php echo (($obj["addiction"] ?? '') == "addictionno") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="addictionno"><?php echo xlt('No');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="addiction" id="addictionnotans" value="addictionnotans" <?php echo (($obj["addiction"] ?? '') == "addictionnotans") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="addictionnotans"><?php echo xlt('Choose not to answer');?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('Have you ever been discharged from the Armed Services?')?></legend>
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <div class="form-radio">
                                            <input type="radio" name="armedservices" id="armedservicesyes" value="armedservicesyes" <?php echo (($obj["armedservices"] ?? '') == "armedservicesyes") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="armedservicesyes"><?php echo xlt('Yes');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="armedservices" id="armedservicesno" value="armedservicesno" <?php echo (($obj["armedservices"] ?? '') == "armedservicesno") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="armedservicesno"><?php echo xlt('No');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="armedservices" id="armedservicesnotans" value="armedservicesnotans" <?php echo (($obj["armedservices"] ?? '') == "armedservicesnotans") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="armedservicesnotans"><?php echo xlt('Choose not to answer');?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('Are you a refugee?')?></legend>
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <div class="form-radio">
                                            <input type="radio" name="refugee" id="refugeeyes" value="refugeeyes" <?php echo (($obj["refugee"] ?? '') == "refugeeyes") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="refugeeyes"><?php echo xlt('Yes');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="refugee" id="refugeeno" value="refugeeno" <?php echo (($obj["refugee"] ?? '') == "refugeeno") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="refugeeno"><?php echo xlt('No');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="refugee" id="refugeenotans" value="refugeenotans" <?php echo (($obj["refugee"] ?? '') == "refugeenotans") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="refugeenotans"><?php echo xlt('Choose not to answer');?></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('In the past year, have you been discriminated against because of any of the following? Check all that apply.')?></legend>
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="discrimno" name='discrimno' <?php echo (($obj["discrimno"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="discrimno"><?php echo xlt('No Discrimination');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="discrimrace" name='discrimrace' <?php echo (($obj["discrimrace"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="discrimrace"><?php echo xlt('Race/Ethnicity');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="discrimgender" name='discrimgender' <?php echo (($obj["discrimgender"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="discrimgender"><?php echo xlt('Gender');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="discrimsexpref" name='discrimsexpref' <?php echo (($obj["discrimsexpref"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="discrimsexpref"><?php echo xlt('Sexual Preference');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="discrimgenexp" name='discrimgenexp' <?php echo (($obj["discrimgenexp"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="discrimgenexp"><?php echo xlt('Gender Expression');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="discrimreligion" name='discrimreligion' <?php echo (($obj["discrimreligion"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="discrimreligion"><?php echo xlt('Religion');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="discrimdisability" name='discrimdisability' <?php echo (($obj["discrimdisability"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="discrimdisability"><?php echo xlt('Disability');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="discrimage" name='discrimage' <?php echo (($obj["discrimage"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="discrimage"><?php echo xlt('Age');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="discrimweight" name='discrimweight' <?php echo (($obj["discrimweight"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="discrimweight"><?php echo xlt('Weight');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="discrimses" name='discrimses' <?php echo (($obj["discrimses"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="discrimses"><?php echo xlt('Socioeconomic Status');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="discrimedu" name='discrimedu' <?php echo (($obj["discrimedu"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="discrimedu"><?php echo xlt('Education');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="discrimmarital" name='discrimmarital' <?php echo (($obj["discrimmarital"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="discrimmarital"><?php echo xlt('Marital Status');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="discrimcitizen" name='discrimcitizen' <?php echo (($obj["discrimcitizen"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="discrimcitizen"><?php echo xlt('Citizenship');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="discrimaccent" name='discrimaccent' <?php echo (($obj["discrimaccent"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="discrimaccent"><?php echo xlt('Accent or Language');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="discrimcriminalhist" name='discrimcriminalhist' <?php echo (($obj["discrimcriminalhist"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="discrimcriminalhist"><?php echo xlt('Criminal History');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="discrimother" name='discrimother' <?php echo (($obj["discrimother"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="discrimother"><?php echo xlt('Other');?></label>
                                            <input type="text" id="discrimotherinput" name='discrimotherinput' size="30" value="<?php echo attr($obj["discrimotherinput"] ?? ''); ?>"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('In what situations have you been discriminated in? Check all that apply.')?></legend>
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="displaceno" name='displaceno' <?php echo (($obj["displaceno"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="displaceno"><?php echo xlt('No Discrimination');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="displacework" name='displacework' <?php echo (($obj["displacework"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="displacework"><?php echo xlt('Employment');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="displacehousing" name='displacehousing' <?php echo (($obj["displacehousing"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="displacehousing"><?php echo xlt('Housing');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="displacehealth" name='displacehealth' <?php echo (($obj["displacehealth"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="displacehealth"><?php echo xlt('Health Care');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="displacelaw" name='displacelaw' <?php echo (($obj["displacelaw"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="displacelaw"><?php echo xlt('Law Enforcement');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="displaceedu" name='displaceedu' <?php echo (($obj["displaceedu"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="displaceedu"><?php echo xlt('Education');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="displacepublic" name='displacepublic' <?php echo (($obj["displacepublic"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="displacepublic"><?php echo xlt('In Public (Shopping, Dining, Parks)');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="displaceclubs" name='displaceclubs' <?php echo (($obj["displaceclubs"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="displaceclubs"><?php echo xlt('Religious or Civic Organizations');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="displacegovt" name='displacegovt' <?php echo (($obj["displacegovt"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="displacegovt"><?php echo xlt('Government');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="displacefinance" name='displacefinance' <?php echo (($obj["displacefinance"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="displacefinance"><?php echo xlt('Banks or Finance Services');?></label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="displaceother" name='displaceother' <?php echo (($obj["displaceother"] ?? '') == "on") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="displaceother"><?php echo xlt('Other');?></label>
                                            <input type="text" id="displaceotherinput" name='displaceotherinput' size="30" value="<?php echo attr($obj["displaceotherinput"] ?? ''); ?>"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('Would you like to be contacted with resources or assistance?')?></legend>
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <div class="form-radio">
                                            <input type="radio" name="contact" id="contactphone" value="contactphone" <?php echo (($obj["contact"] ?? '') == "contactphone") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="contactphone"><?php echo xlt('Yes, by phone');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="contact" id="contactemail" value="contactemail" <?php echo (($obj["contact"] ?? '') == "contactemail") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="contactemail"><?php echo xlt('Yes, by email');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="contact" id="contactportal" value="contactportal" <?php echo (($obj["contact"] ?? '') == "contactportal") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="contactportal"><?php echo xlt('Yes, by portal message');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="contact" id="contactno" value="contactno" <?php echo (($obj["contact"] ?? '') == "contactno") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="contactno"><?php echo xlt('No');?></label>
                                        </div>
                                        <div class="form-radio">
                                            <input type="radio" name="contact" id="contactother" value="contactother" <?php echo (($obj["contact"] ?? '') == "contactother") ? "checked" : ""; ?>/>
                                            <label class="form-check-label" for="contactother"><?php echo xlt('Other');?></label>
                                            <input type="text" id="contactotherinput" name='contactotherinput' size="30" value="<?php echo attr($obj["contactotherinput"] ?? ''); ?>"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('Your total score is ')?><span id="totalscorerender"><?php echo text($obj["totalscore"] ?? 0); ?></span></legend>
                        <input type="hidden" id="totalscore" name="totalscore" value="<?php echo attr($obj["totalscore"] ?? 0); ?>">

                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('Other Comments');?></legend>
                        <div class="container">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <textarea name="additional_notes" class="form-control" cols="80" rows="5" ><?php echo text($obj["additional_notes"] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <?php if (!$patientPortalSession && !$patientPortalOther) { ?>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-12 position-override">
                                    <div class="btn-group" role="group">
                                        <button type="submit" onclick="top.restoreSession()" class="btn btn-primary btn-save"><?php echo xlt('Save'); ?></button>
                                        <button type="button" class="btn btn-secondary btn-cancel" onclick="top.restoreSession(); parent.closeTab(window.name, false);"><?php echo xlt('Cancel');?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
