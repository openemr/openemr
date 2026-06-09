<?php

/**
 *  Lab Requisition Form
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016-2023 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");

use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;

// Hoist legacy `globals.php` locals so PHPStan can see them (#11792 Phase 5).
$srcdir = OEGlobalsBag::getInstance()->getSrcDir();

require_once("$srcdir/api.inc.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/lab.inc.php");

formHeader("Form:Lab Requisition");

$session = SessionWrapperFactory::getInstance()->getActiveSession();

$returnurl = 'encounter_top.php';

$formid = (int) ($_GET['id'] ?? 0);
$obj = $formid ? formFetch("form_requisition", $formid) : [];

global $pid ;

$encounter = $session->get('encounter');

$oid = fetchProcedureId($pid, $encounter);



    $patient_id = $pid;
    $pdata      = getPatientData($pid);
    $facility   = getFacility();
    $ins        = getAllinsurances($pid);
    $orders     = getProceduresInfo($oid, $encounter);
    // Order-level fields are identical across rows; read from the first row.
    $firstRow  = $orders[0] ?? [];

    $prov_id   = $firstRow['provider_id'] ?? '';
    $lab       = $firstRow['lab_id'] ?? '';
    $provider  = getLabProviders($prov_id);
    $npi       = getNPI($prov_id);
    $pp        = getProcedureProvider($lab);
    $provLabId = $lab ? getLabconfig((int) $lab) : false;

    // Determine responsible party from the procedure order billing_type.
    // 'C' = Client/Clinic, 'P' = Patient, 'T' = Third Party/Insurance
    $billingType      = $oid ? getProcedureBillingType((int) $oid) : '';
    $responsibleParty = buildResponsibleParty($billingType, $facility, $pdata, $ins[0] ?? []);
?>

<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(); ?>
<style>
table, th, td {
     border: 1px solid black;
     border-collapse: collapse;
 }

 .req {
     margin: auto;
     width: 90%;
     padding: 10px;
 }

 .reqHeader {
     margin: auto;
     width: 90%;
     padding: 10px;
 }

 .cinfo {
     float: left;

 }

 .pdata {

     position: relative;
     right: -205px;
     z-index: -5;

 }

 #printable { display: none; }

    @media print
    {
        #non-printable { display: none; }
        #printable { display: block; }
    }

   .notes {
       padding: 5px;
       position: relative;
       float: left;
       width: 255px;
       height: 125px;
   }

  .dx {
      padding: 5px;
      position: relative;
      float: right;
      border-style: solid;
      border-width: 1px;
      width: 130px;
      height: 125px;
  }

  .plist {
      padding: 5px;
      position: relative;
      float: left;

  }

  .pFill {
      float: left;

  }

</style>
    <title><?php echo xlt('Lab Requisition') . ' ' . text($lab ?? ''); ?></title>
</head>

<body>
<div class="container">
    <?php
    if (empty($orders)) {
            echo "<div class='text-center mt-5'><span>" .
                xlt('procedure order not found in database contact tech support') . "</span></div></div></body></html>";
            exit;
    }
    if (empty($oid)) {
            print "<div class='text-center mt-5'><span>" .
         xlt('No Order found, please enter procedure order first') . "</span></div></div></body></html>";
            exit;
    }
    ?>
        <div class="text-center mt-3">
                <?php
                /**
                 *  This is to store the requisition bar code number to use again if the form needs to be printed or viewed again
                 *  But save it the first time through.
                 */
                   $lab_id = $firstRow['procedure_order_id'];
                   $storeBar = getBarId($lab_id, $pid);

                if (!empty($storeBar)) {
                    $bar = $storeBar['req_id'];
                } else {
                    $bar = random_int(1000, 999999);
                    saveBarCode($bar, $pid, $firstRow['procedure_order_id']);
                }

                ?>
                <img  src="../../forms/requisition/barcode.php?text=<?php echo attr_url($bar); ?>" alt="barcode" /><br />
             <h3><?php echo text($bar); ?></h3>
        </div>
        <div class="reqHeader" id="printableArea">
        <p><span class="fs-4"><b><?php print xlt('Requisition Number') ?>:</b> <?php echo text($bar); ?>  &#160;&#160;&#160;&#160;&#160;&#160;<b><?php print xlt('Client Number') ?>:</b> <?php echo text($provLabId['recv_fac_id']); ?></span></p>
           <div class="cinfo">
           <span class="fs-3">
                <?php echo text($facility['name']) . "<br />" . text($facility['street']) . "<br />" .
                          text($facility['city']) . "," . text($facility['state']) . "," . text($facility['postal_code']) . "<br />" .
                          text($facility['phone']); ?>
                          </span>
           </div>
           <div class="pdata">
                 <p><span class="fs-3">
            <?php echo text($pp['organization']) . "<br />" .
            text($pp['street']) . " | " . text($pp['city']) . ", " . text($pp['state']) . " " . text($pp['zip']) . "<br />" .
            "O:" . text($pp['phone']) . " | F:" . text($pp['fax']) . "<br />";
            ?></span></p>

           </div>
        </div>
        <div class="req" id="printableArea">
            <table class="table" style="width:800px border=1">
               <tr style="height:125px;">
                   <td style="vertical-align:top; width:400px;" >
                   <div class="plist">
                       <b><?php echo xlt('Collection Date/Time')?>:</b><br />
                       <b><?php echo xlt('Lab Reference ID') ?>:</b><br />
                       <b><?php echo xlt('Fasting')?>:</b><br />
                       <b><?php echo xlt('Hours')?>:</b><br />
                     </div>
                    <div class="pFill">
                        <?php echo text($firstRow['date_collected'] ?? ''); ?> <br />
                        <?php echo text($firstRow['procedure_order_id'] ?? ''); ?>
                    </div>
                   </td>
                   <td style="vertical-align:top width: 800px">
                    <div class="plist">
                       <b><?php echo xlt('Patient ID') ?>: </b>  <br />
                       <b><?php echo xlt('DOB') ?>: </b> <br />
                       <b><?php echo xlt('Sex') ?>: </b>    <br />
                       <b><?php echo xlt('Patient Name') ?>: </b>  <br />
                    </div>
                    <div class="pFill">
                        <?php echo text($pid); ?><br />
                        <?php echo text($pdata['DOB']); ?><br />
                        <?php echo text(getListItemTitle('sex', $pdata['sex'])); ?><br />
                        <?php echo text($pdata['fname']) . " " . text($pdata['lname']); ?><br />
                    </div>
                   </td>
               </tr>

               <tr style="height:125px">
                   <td style="vertical-align:top; width:400px;">
                      <span class="fs-4"><strong><?php print xlt("Ordering Physician") ?>:</strong></span><br />
                      <div class="plist">
                        <?php echo xlt('Name') ?>:        <br />
                        <?php echo xlt('NPI') ?>:         <br />
                        <?php echo xlt('UPIN') ?>:        <br />
                       </div>
                     <div class="pFill"><?php echo text($provider['fname']) . " " . text($provider['lname']); ?><br />
                        <?php echo text($npi[0]); ?><br />
                        <?php echo text($npi[1]); ?><br />

                       </div>
                   </td>
                   <td style="vertical-align:top">
                     <span class="fs-4"><strong><?php print xlt("Responsible Party") ?>:</strong></span><br />
                      <div class="plist">
                        <?php echo xlt('Name') ?>:             <br />
                        <?php echo xlt('Address') ?>:          <br />
                        <?php echo xlt('City,St,Zip') ?>:      <br />
                        <?php echo xlt('Relationship') ?>:     <br />
                       </div>
                       <div class="pFill">
                        <?php echo !empty($responsibleParty['name'])        ? text($responsibleParty['name'])        : '/'; ?><br />
                        <?php echo !empty($responsibleParty['address'])     ? text($responsibleParty['address'])     : '/'; ?><br />
                        <?php echo !empty($responsibleParty['city_st_zip']) ? text($responsibleParty['city_st_zip']) : '/'; ?><br />
                        <?php
                        if (!empty($responsibleParty['relationship'])) {
                            echo ($responsibleParty['relationship_is_list'] ?? false)
                                ? text(getListItemTitle('sub_relation', $responsibleParty['relationship']))
                                : xlt($responsibleParty['relationship']);
                        } else {
                            echo '/';
                        }
                        ?><br />
                       </div>
                   </td>


               </tr>
                  <tr style="height:125px">
                   <td style="vertical-align:top; width:400px;">
                      <span class="fs-4"><strong><?php print xlt("Primary Insurance") ?>:</strong></span><br />
                      <?php if ($billingType === 'T' && !empty($ins[0])): ?>
                      <div class="plist">
                        <?php echo xlt('Bill Type') ?>:<br />
                        <?php echo xlt('Payor/Carrier Code') ?>:<br />
                        <?php echo xlt('Insurance Name') ?>:<br />
                        <?php echo xlt('Insurance Address') ?>:<br />
                        <?php echo xlt('City,St,Zip') ?>:<br />
                        <?php echo xlt('Subscriber/Policy') ?>#:<br />
                        <?php echo xlt('Group') ?> #:<br />
                        <?php echo xlt('Physician\'s UPIN') ?>:<br />
                        <?php echo xlt('Employer') ?>:<br />
                        <?php echo xlt('Relationship') ?>:<br />
                      </div>
                      <div class="pFill">
                        <?php echo xlt('Insurance'); ?><br />
                        <?php echo '/'; ?><br />
                        <?php echo text($ins[0]['name']); ?><br />
                        <?php echo text($ins[0]['line1']); ?><br />
                        <?php echo text($ins[0]['city']) . ', ' . text($ins[0]['state']) . ' ' . text($ins[0]['zip']); ?><br />
                        <?php echo text($ins[0]['policy_number']); ?><br />
                        <?php echo text($ins[0]['group_number']); ?><br />
                        <?php echo '/'; ?><br />
                        <?php echo text($ins[0]['subscriber_employer']); ?><br />
                        <?php echo text(getListItemTitle('sub_relation', $ins[0]['subscriber_relationship'] ?? '')); ?><br />
                      </div>
                      <?php else: ?>
                      <p><?php echo $billingType === 'C' ? xlt('Clinic Billing') : xlt('Patient Billing'); ?></p>
                      <?php endif; ?>
                   </td>
                   <td style="vertical-align:top">
                      <span class="fs-4"><strong><?php print xlt("Secondary Insurance") ?>:</strong></span><br />
                      <?php if ($billingType === 'T' && !empty($ins[1])): ?>
                      <div class="plist">
                        <?php echo xlt('Bill Type') ?>:<br />
                        <?php echo xlt('Payor/Carrier Code') ?>:<br />
                        <?php echo xlt('Insurance Name') ?>:<br />
                        <?php echo xlt('Insurance Address') ?>:<br />
                        <?php echo xlt('City,St,Zip') ?>:<br />
                        <?php echo xlt('Subscriber/Policy') ?>#:<br />
                        <?php echo xlt('Group') ?> #:<br />
                        <?php echo xlt('Physician\'s UPIN') ?>:<br />
                        <?php echo xlt('Employer') ?>:<br />
                        <?php echo xlt('Relationship') ?>:<br />
                       </div>
                      <div class="pFill">
                        <?php echo xlt('Insurance'); ?><br />
                        <?php echo '/'; ?><br />
                        <?php echo text($ins[1]['name']); ?><br />
                        <?php echo text($ins[1]['line1']); ?><br />
                        <?php echo text($ins[1]['city']) . ', ' . text($ins[1]['state']) . ' ' . text($ins[1]['zip']); ?><br />
                        <?php echo text($ins[1]['policy_number']); ?><br />
                        <?php echo text($ins[1]['group_number']); ?><br />
                        <?php echo '/'; ?><br />
                        <?php echo text($ins[1]['subscriber_employer']); ?><br />
                        <?php echo text(getListItemTitle('sub_relation', $ins[1]['subscriber_relationship'] ?? '')); ?><br />
                      </div>
                      <?php else: ?>
                      <p><?php echo xlt('None'); ?></p>
                      <?php endif; ?>
                   </td>
               </tr>

               <tr style="height:125px">
                   <td style="vertical-align:top; width:400px;">
                       <div class="notes">
                         <span class="fs-4"><strong><?php echo xlt('Test Ordered') ?>:</strong></span><br />
                            <?php foreach ($orders as $codeRow): ?>
                            <?php echo text($codeRow['procedure_code'] ?? '') . ' ' . text($codeRow['procedure_name'] ?? ''); ?><br />
                            <?php endforeach; ?>
                       </div>
                   </td>
                   <td style="vertical-align:top">
                    <div class="notes">
                     <span class="fs-4"><strong><?php echo xlt('Order Notes') ?>:</strong></span><br />
                        <?php echo text($firstRow['clinical_hx'] ?? ''); ?>
                     </div>
                   <div class="dx">
                     <span class="fs-4"><strong><?php echo xlt('Dx Codes') ?>:</strong></span><br />
                        <?php foreach ($orders as $codeRow): ?>
                        <?php echo text($codeRow['diagnoses'] ?? ''); ?><br />
                        <?php endforeach; ?>
                   </div>
                   </td>
               </tr>

            </table>
            <?php if (!empty($firstRow['question_text'])) { // display this table only if there are questions ?>
            <table style="width:800px; border=1">
               <tr style="height:125px">
                  <td style="vertical-align:top">
                       <span class="fs-4"><strong><?php echo xlt('AOE Q&A') ?>: </strong></span><br />
                       <b>Question:</b> <?php print text($firstRow['question_text']); ?><br />
                       <b>Answer:</b> <?php print text($firstRow['answer']); ?>
                  </td>
               </tr>
            </table>
            <?php } ?>
            <br />
            <br />
            <span class="text-center"><?php echo xlt('End of Requisition') ?> #:  <?php echo text($bar); ?></span>
        </div>
</div>
<div class="reqHeader" id="non-printable">
     <input type="button" onclick="window.print()" value="<?php echo xla('Print'); ?>">
</div>
<script>
// Print is handled via window.print() directly.
// The @media print CSS above hides #non-printable and shows #printable.
</script>
</body>
</html>
