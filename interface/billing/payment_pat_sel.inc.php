<?php

/**
 * payment_pat_sel.inc.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Eldho Chacko <eldho@zhservices.com>
 * @author    Paul Simon K <paul@zhservices.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2010 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2018 Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2019-2020 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Rod Roark <rod@sunsetsystems.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Billing\SLEOB;

//-----------------------------------------------------------------------+
//===============================================================================
//Patient ajax section and listing of charges..Used in New Payment and Edit Payment screen.
//===============================================================================
if (isset($_POST["mode"])) {
    if (
           ($_POST["mode"] == "search") || (($_POST["default_search_patient"] ?? null) == "default_search_patient") &&
           isset($_REQUEST['hidden_patient_code']) &&
           (int)$_REQUEST['hidden_patient_code'] > 0
    ) {
        $hidden_patient_code = $_REQUEST['hidden_patient_code'];
        $RadioPaid = $_REQUEST['RadioPaid'] ?? null;
        if ($RadioPaid == 'Show_Paid') {
            $StringForQuery = '';
        } elseif ($RadioPaid == 'Non_Paid') {
            $StringForQuery = " and last_level_closed = 0 ";
        } elseif ($RadioPaid == 'Show_Primary_Complete') {
            $StringForQuery = " and last_level_closed >= 1 ";
        }
        $ResultSearchNew = sqlStatement("SELECT b.id,last_level_closed,b.encounter,fe.`date`,b.code_type,b.code,b.modifier,fee
            FROM billing AS b,form_encounter AS fe, code_types AS ct
            WHERE b.encounter=fe.encounter AND b.code_type=ct.ct_key AND ct.ct_diag=0
            AND b.activity!=0 AND fe.pid =? AND b.pid =?
            " . ($StringForQuery ?? '') . " ORDER BY fe.`date`, fe.encounter,b.code,b.modifier", array($hidden_patient_code, $hidden_patient_code));
        $res = sqlStatement("SELECT fname,lname,mname FROM patient_data
                         where pid =?", array($hidden_patient_code));
        $row = sqlFetchArray($res);
        $fname = $row['fname'] ?? '';
        $lname = $row['lname'] ?? '';
        $mname = $row['mname'] ?? '';
        $NameNew = $lname . ' ' . $fname . ' ' . $mname;
    }
}
//===============================================================================
?>
                    <fieldset>
                    <legend><?php echo xlt('Distribute')?></legend>
                    <div class="row pb-2" id="TablePatientPortion">
                        <div class="frames col-3">
                            <input id="hidden_ajax_patient_close_value" type="hidden" value="<?php echo (empty($Message)) ? attr($NameNew ?? '') : '' ;?>" />
                            <label class="control-label" for="patient_code"><?php echo xlt('Patient'); ?>:</label>
                            <input class="form-control" name='patient_code' class="text" id='patient_code' onKeyDown="PreventIt(event)" value="<?php echo (empty($Message)) ? attr($NameNew ?? '') : '' ;?>" autocomplete="off" />
                        </div>
                        <div class="frames col-2">
                            <label class="control-label" for="patient_name"><?php echo xlt('Patient Id'); ?>:</label>
                            <div class="form-control" name="patient_name" id="patient_name">
                                <?php echo (empty($Message)) ? text($hidden_patient_code ?? '') : ''; ?>
                            </div>
                        </div>
                        <div class="frames col">
                            <label class="control-label" for="type_code"><?php echo xlt('Select'); ?>:</label>
                            <div>
                                <label class="radio-inline">
                                  <input type="radio" id="Non_Paid" name="RadioPaid" onclick="SearchOnceMore()" <?php echo (empty($_REQUEST['RadioPaid']) || ($_REQUEST['RadioPaid'] == 'Non_Paid')) ? 'checked' : '' ; ?> value="Non_Paid" /><?php echo xlt('Non Paid'); ?>
                                </label>
                                <label class="radio-inline">
                                  <input type="radio" id="Show_Primary_Complete" name="RadioPaid" onclick="SearchOnceMore()" <?php echo (!empty($_REQUEST['RadioPaid']) && ($_REQUEST['RadioPaid'] == 'Show_Primary_Complete')) ? 'checked' : '' ; ?> value="Show_Primary_Complete" /><?php echo xlt('Show Primary Complete'); ?>
                                </label>
                                <label class="radio-inline">
                                  <input type="radio" id="Show_Paid" name="RadioPaid" onclick="SearchOnceMore()" <?php echo (!empty($_REQUEST['RadioPaid']) && ($_REQUEST['RadioPaid'] == 'Show_Paid')) ? 'checked' : '' ; ?> value="Show_Paid" /><?php echo xlt('Show All Transactions'); ?>
                                </label>
                            </div>
                        </div>
                    </div>
                        <div id='ajax_div_patient_section'>
                            <div id='ajax_div_patient_error'>
                            </div>
                            <div id="ajax_div_patient" style="display: none;">
                            </div>
                        </div>
                    </fieldset>
                <?php //New distribution section
                //$CountIndex=0;
                $CountIndexBelow = 0;
                $PreviousEncounter = 0;
                $PreviousPID = 0;
                if (!empty($ResultSearchNew)) {
                    if ($RowSearch = sqlFetchArray($ResultSearchNew)) { ?>
                    <div class="overflow-auto">
                        <div class="table-responsive-lg">
                            <table class="table table-sm table-bordered table-light" id="TableDistributePortion">
                                <thead class="bg-dark text-light">
                                    <tr>
                                        <th><?php echo xlt('Post For'); ?></th>
                                        <th><?php echo xlt('Service Date'); ?></th>
                                        <th><?php echo xlt('Enc#'); ?></th>
                                        <th><?php echo xlt('Service Code'); ?></th>
                                        <th><?php echo xlt('Charge'); ?></th>
                                        <th><?php echo xlt('Copay'); ?></th>
                                        <th><?php echo xlt('Bal Due'); ?></th>
                                        <th><?php echo xlt('Allowed'); ?></th>
                                        <th><?php echo xlt('Payment'); ?></th>
                                        <th><?php echo xlt('Adj Amount'); ?></th>
                                        <th><?php echo xlt('Deductible'); ?></th>
                                        <th><?php echo xlt('Takeback'); ?></th>
                                        <th><?php echo xlt('MSP'); ?></th>
                                        <th><?php echo xlt('Follow Up'); ?></th>
                                        <th><?php echo xlt('Follow Up Reason'); ?></th>
                                    </tr>
                                </thead>
                        <?php
                        do {
                            $CountIndex = $CountIndex ?? null;
                            $CountIndex++;
                            $CountIndexBelow++;
                            $Ins = 0;
                            // Determine the next insurance level to be billed.
                            $ferow = sqlQuery("SELECT date, last_level_closed " .
                              "FROM form_encounter WHERE " .
                              "pid = ? AND encounter = ?", array($hidden_patient_code, $RowSearch['encounter']));
                            $date_of_service = substr($ferow['date'], 0, 10);
                            $new_payer_type = 0 + $ferow['last_level_closed'];
                            if ($new_payer_type <= 3 && !empty($ferow['last_level_closed']) || $new_payer_type == 0) {
                                ++$new_payer_type;
                            }
                            $new_payer_id = SLEOB::arGetPayerID($hidden_patient_code, $date_of_service, $new_payer_type);

                            if ($new_payer_id == 0) {
                                $Ins = 0;
                            } elseif ($new_payer_id > 0) {
                                $Ins = $new_payer_type;
                            }


                            $ServiceDateArray = explode(' ', $RowSearch['date']);
                            $ServiceDate = oeFormatShortDate($ServiceDateArray[0]);
                            $Codetype = $RowSearch['code_type'];
                            $Code = $RowSearch['code'];
                            $Modifier = $RowSearch['modifier'];
                            if ($Modifier != '') {
                                $ModifierString = ", $Modifier";
                            } else {
                                $ModifierString = "";
                            }
                            $Fee = $RowSearch['fee'];
                            $Encounter = $RowSearch['encounter'];

                            //Always associating the copay to a particular charge.
                            $BillingId = $RowSearch['id'];
                            $resId = sqlStatement("SELECT b.id FROM billing AS b, code_types AS ct
                                                   WHERE b.code_type=ct.ct_key AND ct.ct_diag=0 AND
                                                   b.pid=? AND b.encounter=?
                                                   AND b.activity!=0 ORDER BY id", array($hidden_patient_code, $Encounter));
                            $rowId = sqlFetchArray($resId);
                            $Id = $rowId['id'];

                            if ($BillingId != $Id) {//multiple cpt in single encounter
                                $Copay = 0.00;
                            } else {
                                $resCopay = sqlStatement("SELECT sum(fee) as copay FROM billing where code_type='COPAY' and
                                pid =? and  encounter  =? and billing.activity!=0", array($hidden_patient_code, $Encounter));
                                $rowCopay = sqlFetchArray($resCopay);
                                $Copay = $rowCopay['copay'] * -1;

                                $resMoneyGot = sqlStatement(
                                    "SELECT sum(pay_amount) as PatientPay FROM ar_activity where " .
                                    "deleted IS NULL AND pid = ? and encounter = ? and payer_type = 0 and " .
                                    "account_code = 'PCP'",
                                    array($hidden_patient_code, $Encounter)
                                );//new fees screen copay gives account_code='PCP'
                                $rowMoneyGot = sqlFetchArray($resMoneyGot);
                                $PatientPay = $rowMoneyGot['PatientPay'];

                                $Copay = $Copay + $PatientPay;
                            }
                            //payer_type!=0, supports both mapped and unmapped code_type in ar_activity
                            $resMoneyGot = sqlStatement(
                                "SELECT sum(pay_amount) as MoneyGot FROM ar_activity where " .
                                "deleted IS NULL AND pid = ? and (code_type = ? or code_type = '') and " .
                                "code = ? and modifier = ? and encounter = ? and ! (payer_type = 0 and " .
                                "account_code = 'PCP')",
                                array($hidden_patient_code, $Codetype, $Code, $Modifier, $Encounter)
                            );//new fees screen copay gives account_code='PCP'
                            $rowMoneyGot = sqlFetchArray($resMoneyGot);
                            $MoneyGot = $rowMoneyGot['MoneyGot'];
                            //supports both mapped and unmapped code_type in ar_activity
                            $resMoneyAdjusted = sqlStatement(
                                "SELECT sum(adj_amount) as MoneyAdjusted FROM ar_activity where " .
                                "deleted IS NULL AND pid = ? and (code_type = ? or code_type = '') and " .
                                "code = ? and modifier = ? and encounter = ?",
                                array($hidden_patient_code, $Codetype, $Code, $Modifier, $Encounter)
                            );
                            $rowMoneyAdjusted = sqlFetchArray($resMoneyAdjusted);
                            $MoneyAdjusted = $rowMoneyAdjusted['MoneyAdjusted'];

                            $Remainder = $Fee - $Copay - $MoneyGot - $MoneyAdjusted;

                            $TotalRows = sqlNumRows($ResultSearchNew);

                            if ($Ins == 1) {
                                $bgcolor = '#ddddff';
                            } elseif ($Ins == 2) {
                                $bgcolor = '#ffdddd';
                            } elseif ($Ins == 3) {
                                $bgcolor = '#F2F1BC';
                            } elseif ($Ins == 0) {
                                $bgcolor = '#AAFFFF';
                            }
                            ?>
                            <tr class="text" bgcolor='<?php echo attr($bgcolor); ?>' id="trCharges<?php echo attr($CountIndex); ?>">
                                <td class="text-left">
                                    <input name="HiddenIns<?php echo attr($CountIndex); ?>" id="HiddenIns<?php echo attr($CountIndex); ?>" value="<?php echo attr($Ins); ?>" type="hidden"/>
                                    <?php echo generate_select_list("payment_ins$CountIndex", "payment_ins", "$Ins", "Insurance/Patient", '', 'oe-payment-select form-input-sm', 'ActionOnInsPat("' . $CountIndex . '")');?>
                                </td>
                                <td>
                                    <?php echo text($ServiceDate); ?>
                                </td>
                                <td class="text-right">
                                    <input name="HiddenEncounter<?php echo attr($CountIndex); ?>" value="<?php echo attr($Encounter); ?>" type="hidden" />
                                    <?php echo text($Encounter); ?>
                                </td>
                                <td>
                                    <input name="HiddenCodetype<?php echo attr($CountIndex); ?>" value="<?php echo attr($Codetype); ?>" type="hidden" />
                                    <input name="HiddenCode<?php echo attr($CountIndex); ?>" value="<?php echo attr($Code); ?>" type="hidden" />
                                    <?php echo text($Codetype . "-" . $Code . $ModifierString); ?>
                                    <input name="HiddenModifier<?php echo attr($CountIndex); ?>" value="<?php echo attr($Modifier); ?>" type="hidden" />
                                </td>
                                <td class="text-right">
                                    <input name="HiddenChargeAmount<?php echo attr($CountIndex); ?>" id="HiddenChargeAmount<?php echo attr($CountIndex); ?>" value="<?php echo attr($Fee); ?>" type="hidden"/>
                                    <?php echo text($Fee); ?>
                                </td>
                                <td class="text-right">
                                    <input name="HiddenCopayAmount<?php echo attr($CountIndex); ?>" id="HiddenCopayAmount<?php echo attr($CountIndex); ?>" value="<?php echo attr($Copay); ?>" type="hidden" />
                                    <?php echo text(number_format($Copay, 2)); ?>
                                </td>
                                <td class="text-right" id="RemainderTd<?php echo attr($CountIndex); ?>">
                                    <?php echo text(round($Remainder, 2)); ?>
                                </td>
                                <input name="HiddenRemainderTd<?php echo attr($CountIndex); ?>" id="HiddenRemainderTd<?php echo attr($CountIndex); ?>" value="<?php echo attr(round($Remainder, 2)); ?>" type="hidden" />
                                <td>
                                    <input name="Allowed<?php echo attr($CountIndex); ?>" id="Allowed<?php echo attr($CountIndex); ?>" onKeyDown="PreventIt(event)" autocomplete="off" onChange="ValidateNumeric(this);ScreenAdjustment(this,<?php echo attr_js($CountIndex); ?>);UpdateTotalValues(<?php echo attr_js(($CountIndexAbove ?? null) * 1 + 1); ?>,<?php echo attr_js($TotalRows); ?>,'Allowed','initialallowtotal');UpdateTotalValues(<?php echo attr_js(($CountIndexAbove ?? null) * 1 + 1); ?>,<?php echo attr_js($TotalRows); ?>,'Payment','initialpaymenttotal');UpdateTotalValues(<?php echo attr_js(($CountIndexAbove ?? null) * 1 + 1); ?>,<?php echo attr_js($TotalRows); ?>,'AdjAmount','initialAdjAmounttotal');RestoreValues(<?php echo attr_js($CountIndex); ?>)" type="text" class="text-right amt_input" />
                                </td>
                                <td>
                                    <input type="text" name="Payment<?php echo attr($CountIndex); ?>" onKeyDown="PreventIt(event)" autocomplete="off"  id="Payment<?php echo attr($CountIndex); ?>" onChange="ValidateNumeric(this);ScreenAdjustment(this,<?php echo attr_js($CountIndex); ?>);UpdateTotalValues(<?php echo attr_js(($CountIndexAbove ?? null) * 1 + 1); ?>,<?php echo attr_js($TotalRows); ?>,'Payment','initialpaymenttotal');RestoreValues(<?php echo attr_js($CountIndex); ?>)"  class="text-right amt_input" />
                                </td>
                                <td>
                                    <input name="AdjAmount<?php echo attr($CountIndex); ?>" onKeyDown="PreventIt(event)" autocomplete="off" id="AdjAmount<?php echo attr($CountIndex); ?>" onChange="ValidateNumeric(this);ScreenAdjustment(this,<?php echo attr_js($CountIndex); ?>);UpdateTotalValues(<?php echo attr_js(($CountIndexAbove ?? null) * 1 + 1); ?>,<?php echo attr_js($TotalRows); ?>,'AdjAmount','initialAdjAmounttotal');RestoreValues(<?php echo attr_js($CountIndex); ?>)" type="text" class="text-right amt_input" />
                                </td>
                                <td>
                                    <input name="Deductible<?php echo attr($CountIndex); ?>" id="Deductible<?php echo attr($CountIndex); ?>" onKeyDown="PreventIt(event)" onChange="ValidateNumeric(this);UpdateTotalValues(<?php echo attr_js(($CountIndexAbove ?? null) * 1 + 1); ?>,<?php echo attr_js($TotalRows); ?>,'Deductible','initialdeductibletotal');" autocomplete="off" type="text" class="text-right amt_input" />
                                </td>
                                <td>
                                    <input name="Takeback<?php echo attr($CountIndex); ?>" onKeyDown="PreventIt(event)" autocomplete="off" id="Takeback<?php echo attr($CountIndex); ?>" onChange="ValidateNumeric(this);ScreenAdjustment(this,<?php echo attr_js($CountIndex); ?>);UpdateTotalValues(<?php echo attr_js(($CountIndexAbove ?? null) * 1 + 1); ?>,<?php echo attr_js($TotalRows); ?>,'Takeback','initialtakebacktotal');RestoreValues(<?php echo attr_js($CountIndex); ?>)" type="text" class="text-right amt_input" />
                                </td>
                                <td class="text-left">
                                    <input name="HiddenReasonCode<?php echo attr($CountIndex); ?>" id="HiddenReasonCode<?php echo attr($CountIndex); ?>"  value="<?php echo attr($ReasonCodeDB ?? ''); ?>" type="hidden" />
                                    <?php echo generate_select_list("ReasonCode$CountIndex", "msp_remit_codes", "", "MSP Code", "--", "oe-payment-select"); ?>
                                </td>
                                <td class="text-center">
                                    <input type="checkbox" id="FollowUp<?php echo attr($CountIndex); ?>" name="FollowUp<?php echo attr($CountIndex); ?>" value="y" onClick="ActionFollowUp(<?php echo attr_js($CountIndex); ?>)" />
                                </td>
                                <td>
                                    <input name="FollowUpReason<?php echo attr($CountIndex); ?>" onKeyDown="PreventIt(event)" id="FollowUpReason<?php echo attr($CountIndex); ?>" readonly />
                                </td>
                            </tr>
                            <?php
                        } while ($RowSearch = sqlFetchArray($ResultSearchNew)); ?>
                     <tr class="text">
                        <td class="text-right text-dark text-left" colspan="7"><b><?php echo (xlt("Totals") . ": ") ?></b></td>
                        <td class="bg-dark text-secondary text-center" id="initialallowtotal">0</td>
                        <td class="bg-dark text-secondary text-center" id="initialpaymenttotal">0</td>
                        <td class="bg-dark text-secondary text-center" id="initialAdjAmounttotal" >0</td>
                        <td class="bg-dark text-secondary text-center" id="initialdeductibletotal">0</td>
                        <td class="bg-dark text-secondary text-center" id="initialtakebacktotal">0</td>
                        <td class="text-center">&nbsp;</td>
                        <td class="text-center">&nbsp;</td>
                      </tr>
                    </table>
                        </div>
                    </div>
                    <?php } //if($RowSearch = sqlFetchArray($ResultSearchNew))
                } //if(!empty($ResultSearchNew)) ?>
