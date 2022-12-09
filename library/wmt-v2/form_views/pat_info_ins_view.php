<?php 
$email_field = 'email';
if(!(isset($GLOBALS['wmt::use_email_direct'])))
	$GLOBALS['wmt::use_email_direct'] = FALSE;
if($GLOBALS['wmt::use_email_direct']) $email_field = 'email_direct';
$chp_printed = PrintChapter($chp_title, $chp_printed, 'padding: 0px;');
?>
	<tr>
		<td style="width: 50%; border-right: solid 1px black;">
		<table width="100%" border="0" cellspacing="0" cellpadding="2">
			<tr>
				<td style="width: 35%" class="wmtPrnBody4">DOB</td>
				<td style="width: 35%" class="wmtPrnBody4">Age</td>
				<td style="width: 35%" class="wmtPrnBody4">Race</td>
				<td style="width: 35%" class="wmtPrnBody4">Marital </td>
			</tr>
			<tr>
				<td class="wmtPrnBody">&nbsp;&nbsp;<?php echo htmlspecialchars($patient->DOB, ENT_QUOTES, '', FALSE); ?></td>
				<td class="wmtPrnBody">&nbsp;&nbsp;<?php echo htmlspecialchars($patient->age, ENT_QUOTES, '', FALSE); ?></td>
				<td class="wmtPrnBody">&nbsp;&nbsp;<?php echo ListLook($patient->race, 'race'); ?></td>
				<td class="wmtPrnBody">&nbsp;&nbsp;<?php echo ListLook($patient->status, 'marital'); ?></td>
			</tr>
      <tr>
        <td colspan="2" class="wmtPrnBody4">Occupation</td>
        <td colspan="2" class="wmtPrnBody4">Education</td>
      </tr>
      <tr>
        <td colspan="2" class="wmtPrnBody">&nbsp;&nbsp;<?php echo htmlspecialchars($patient->occupation, ENT_QUOTES, '', FALSE); ?></td>
        <td colspan="2" class="wmtPrnBody">&nbsp;&nbsp;<?php echo htmlspecialchars($patient->wmt_education, ENT_QUOTES, '', FALSE); ?></td>
      </tr>

      <tr>
        <td colspan="3" class="wmtPrnBody4">Address</td>
      </tr>
      <tr>
        <td colspan="3" class="wmtPrnBody">&nbsp;&nbsp;<?php echo htmlspecialchars($patient->street, ENT_QUOTES, '', FALSE); ?></td>
      </tr>

      <tr>
        <td colspan="2" class="wmtPrnBody4">City</td>
        <td class="wmtPrnBody4">State</td>
        <td class="wmtPrnBody4">ZIP</td>
      </tr>
      <tr>
        <td colspan="2" class="wmtPrnBody">&nbsp;&nbsp;<?php echo htmlspecialchars($patient->city, ENT_QUOTES, '', FALSE); ?></td>
        <td class="wmtPrnBody">&nbsp;&nbsp;<?php echo ListLook($patient->state, 'state'); ?></td>
        <td class="wmtPrnBody">&nbsp;&nbsp;<?php echo htmlspecialchars($patient->postal_code, ENT_QUOTES, '', FALSE); ?></td>
      </tr>
      <tr>
        <td colspan="2" class="wmtPrnBody4">Home Phone</td>
        <td colspan="2" class="wmtPrnBody4">Work Phone</td>
      </tr>
      <tr>
        <td colspan="2" class="wmtPrnBody">&nbsp;&nbsp;<?php echo htmlspecialchars($patient->phone_home, ENT_QUOTES, '', FALSE); ?></td>
        <td colspan="2" class="wmtPrnBody">&nbsp;&nbsp;<?php echo htmlspecialchars($patient->phone_biz, ENT_QUOTES, '', FALSE); ?></td>
      </tr>

		</table></td>

		<td style="width: 50%">
		<table width="100%" border="0" cellspacing="0" cellpadding="2">
      <tr>
        <td colspan="3" class="wmtPrnBody4">Email</td>
        <td colspan="2" class="wmtPrnBody4">Cell Phone</td>
      </tr>
      <tr>
        <td colspan="3" class="wmtPrnBody">&nbsp;&nbsp;<?php echo htmlspecialchars($patient->$email_field, ENT_QUOTES, '', FALSE); ?></td>
        <td colspan="2" class="wmtPrnBody">&nbsp;&nbsp;<?php echo htmlspecialchars($patient->phone_cell, ENT_QUOTES, '', FALSE); ?></td>
      </tr>
			<tr>
				<td colspan="3" class="wmtPrnBody4">Primary Insurance</td>
				<td style="width: 20%;" class="wmtPrnBody4">Policy #</td>
				<td style="width: 20%;" class="wmtPrnBody4">Group #</td>
			</tr>
			<tr>
				<td colspan="3" class="wmtPrnBody">&nbsp;&nbsp;<?php echo $patient->primary ? htmlspecialchars($patient->primary, ENT_QUOTES, '', FALSE) : 'No Insurance'; ?></td>
				<td class="wmtPrnBody">&nbsp;&nbsp;<?php echo htmlspecialchars($patient->primary_id, ENT_QUOTES, '', FALSE); ?></td>
				<td class="wmtPrnBody">&nbsp;&nbsp;<?php echo htmlspecialchars($patient->primary_group, ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<tr>
				<td style="width: 20%;" class="wmtPrnBody4">Subscriber First</td>
				<td style="width: 10%;" class="wmtPrnBody4">Middle</td>
				<td class="wmtPrnBody4">Last Name</td>
				<td class="wmtPrnBody4">Birth Date</td>
				<td class="wmtPrnBody4">Relationship</td>
			</tr>
			<tr>
				<td class="wmtPrnBody">&nbsp;&nbsp;<?php echo htmlspecialchars($patient->primary_fname, ENT_QUOTES, '', FALSE); ?></td>
				<td class="wmtPrnBody">&nbsp;&nbsp;<?php echo htmlspecialchars($patient->primary_mname, ENT_QUOTES, '', FALSE); ?></td>
				<td class="wmtPrnBody">&nbsp;&nbsp;<?php echo htmlspecialchars($patient->primary_lname, ENT_QUOTES, '', FALSE); ?></td>
				<td class="wmtPrnBody">&nbsp;&nbsp;<?php echo htmlspecialchars($patient->primary_DOB, ENT_QUOTES, '', FALSE); ?> </td>
				<td class="wmtPrnBody">&nbsp;&nbsp;<?php echo htmlspecialchars(ListLook($patient->primary_relat, 'sub_relation'), ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<tr>
				<td colspan="3" class="wmtPrnBody4">Secondary Insurance</td>
				<td class="wmtPrnBody4">Policy #</td>
				<td class="wmtPrnBody4">Group #</td>
			</tr>
			<tr>
				<td colspan="3" class="wmtPrnBody">&nbsp;&nbsp;<?php echo htmlspecialchars($patient->secondary, ENT_QUOTES, '', FALSE); ?></td>
				<td class="wmtPrnBody">&nbsp;&nbsp;<?php echo htmlspecialchars($patient->secondary_id, ENT_QUOTES, '', FALSE); ?></td>
				<td class="wmtPrnBody">&nbsp;&nbsp;<?php echo htmlspecialchars($patient->secondary_group, ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<tr>
				<td style="width: 20%;" class="wmtPrnBody4">Subscriber First</td>
				<td style="width: 10%;" class="wmtPrnBody4">Middle</td>
				<td class="wmtPrnBody4">Last Name</td>
				<td class="wmtPrnBody4">Birth Date</td>
				<td class="wmtPrnBody4">Relationship</td>
			</tr>
			<tr>
				<td class="wmtPrnBody">&nbsp;&nbsp;<?php echo htmlspecialchars($patient->secondary_fname, ENT_QUOTES, '', FALSE); ?></td>
				<td class="wmtPrnBody">&nbsp;&nbsp;<?php echo htmlspecialchars($patient->secondary_mname, ENT_QUOTES, '', FALSE); ?></td>
				<td class="wmtPrnBody">&nbsp;&nbsp;<?php echo htmlspecialchars($patient->secondary_lname, ENT_QUOTES, '', FALSE); ?></td>
				<td class="wmtPrnBody">&nbsp;&nbsp;<?php echo htmlspecialchars($patient->secondary_DOB, ENT_QUOTES, '', FALSE); ?> </td>
				<td class="wmtPrnBody">&nbsp;&nbsp;<?php echo htmlspecialchars(ListLook($patient->secondary_relat, 'sub_relation'), ENT_QUOTES, '', FALSE); ?></td>
			</tr>
    </table></td>
	</tr>
