<?php
$chp_printed = PrintChapter($chp_title, $chp_printed);
?>
    <tr>
      <td class="wmtPrnT" style="width: 50%">
      <table width="100%" border="0" cellspacing="0" cellpadding="3" style="border-right: solid 1px black">
        <tr>
          <td style="width: 25%" class="wmtPrnBody4">Birth Date</td>
          <td style="width: 25%" class="wmtPrnBody4">Age</td>
          <td style="width: 25%" class="wmtPrnBody4">Race</td>
          <td style="width: 25%" class="wmtPrnBody4">Marital Status</td>
        </tr>
        <tr>
          <td class="wmtPrnBody">&nbsp;&nbsp;<?php echo $patient->DOB; ?></td>
          <td class="wmtPrnBody">&nbsp;&nbsp;<?php echo $patient->age; ?></td>
          <td class="wmtPrnBody">&nbsp;&nbsp;<?php echo ListLook($patient->race, 'race'); ?></td>
          <td class="wmtPrnBody">&nbsp;&nbsp;<?php echo ListLook($patient->status, 'marital'); ?></td>
        </tr>

        <tr>
          <td colspan="2" class="wmtPrnBody4">Occupation</td>
          <td colspan="2" class="wmtPrnBody4">Education (Last Completed)</td>
        </tr>
        <tr>
          <td colspan="2" class="wmtPrnBody">&nbsp;&nbsp;<?php echo $patient->occupation; ?></td>
          <td colspan="2" class="wmtPrnBody">&nbsp;&nbsp;<?php echo $patient->wmt_education; ?></td>
        </tr>

        <tr>
          <td colspan="2" class="wmtPrnBody4">Language</td>
          <td colspan="2" class="wmtPrnBody4">Ethnicity</td>
        </tr>
        <tr>
          <td colspan="2" class="wmtPrnBody">&nbsp;&nbsp;<?php echo ListLook($patient->language, 'language'); ?></td>
          <td colspan="2" class="wmtPrnBody">&nbsp;&nbsp;<?php echo ListLook($patient->ethnicity, 'ethnicity'); ?></td>
        </tr>

        <tr>
          <td colspan="2" class="wmtPrnBody4">Husband/Domestic Partner</td>
          <td colspan="2" class="wmtPrnBody4">Phone</td>
        </tr>
        <tr>
          <td colspan="2" class="wmtPrnBody">&nbsp;&nbsp;<?php echo $patient->wmt_partner_name; ?></td>
          <td colspan="2" class="wmtPrnBody">&nbsp;&nbsp;<?php echo $patient->wmt_partner_ph; ?></td>
        </tr>

        <tr>
          <td colspan="2" class="wmtPrnBody4">Father of Baby</td>
          <td colspan="2" class="wmtPrnBody4">Phone</td>
        </tr>
        <tr>
          <td colspan="2" class="wmtPrnBody wmtPrnBorder1B">&nbsp;&nbsp;<?php echo $patient->wmt_father_name; ?></td>
          <td colspan="2" class="wmtPrnBody wmtPrnBorder1B">&nbsp;&nbsp;<?php echo $patient->wmt_father_ph; ?></td>
        </tr>
      </table></td>

      <td class="wmtPrnT" style="width: 50%">
      <table width="100%" border="0" cellspacing="0" cellpadding="3">
        <tr>
          <td colspan="3" class="wmtPrnBody4">Address</td>
        </tr>
        <tr>
          <td colspan="3" class="wmtPrnBody">&nbsp;&nbsp;<?php echo $patient->street; ?></td>
        </tr>

        <tr>
          <td colspan="2" class="wmtPrnBody4">City</td>
          <td class="wmtPrnBody4">State</td>
        </tr>
        <tr>
          <td colspan="2" class="wmtPrnBody">&nbsp;&nbsp;<?php echo $patient->city; ?></td>
          <td class="wmtPrnBody">&nbsp;&nbsp;<?php echo ListLook($patient->state, 'state'); ?></td>
        </tr>

        <tr>
          <td class="wmtPrnBody4">ZIP</td>
          <td class="wmtPrnBody4">Home Phone</td>
          <td class="wmtPrnBody4">Work Phone</td>
        </tr>
        <tr>
          <td class="wmtPrnBody">&nbsp;&nbsp;<?php echo $patient->postal_code; ?></td>
          <td class="wmtPrnBody">&nbsp;&nbsp;<?php echo $patient->phone_home; ?></td>
          <td class="wmtPrnBody">&nbsp;&nbsp;<?php echo $patient->phone_biz; ?></td>
        </tr>

        <tr>
          <td colspan="2" class="wmtPrnBody4">Insurance Carrier / Medicaid #</td>
          <td class="wmtPrnBody4">Policy #</td>
        </tr>
        <tr>
          <td colspan="2" class="wmtPrnBody">&nbsp;&nbsp;<?php echo $patient->primary; ?></td>
          <td class="wmtPrnBody">&nbsp;&nbsp;<?php echo $patient->primary_id; ?></td>
        </tr>

        <tr>
          <td colspan="2" class="wmtPrnBody4">Emergency Contact</td>
          <td class="wmtPrnBody4">Emergency Phone</td>
        </tr>
        <tr>
          <td colspan="2" class="wmtPrnBody wmtPrnBorder1B">&nbsp;&nbsp;<?php echo $patient->contact_relationship; ?></td>
          <td class="wmtPrnBody wmtPrnBorder1B">&nbsp;&nbsp;<?php echo $patient->phone_contact; ?></td>
        </tr>
      </table></td>
    </tr>
