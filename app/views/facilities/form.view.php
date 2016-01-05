<form id="facility" name="facility" method="post" action="facilities.php" target="_parent">
  <input type="hidden" name="mode" value="facility">
  <input type="hidden" name="newmode" value="admin_facility"> <!--  Diffrentiate Admin and add post backs -->
  <input type="hidden" name="fid" value="<?php echo $facility->getId(); ?>">

  <table border="0" cellpadding="0" cellspacing="1" style="width:630px;">
    <tr>
      <td width="150px">
        <span class="text"><?php xl('Name','e'); ?>:</span>
      </td>
      <td width="220px">
        <?php echo ViewHelper::textTag('facility', htmlspecialchars($facility->name, ENT_QUOTES), '20') ?>
        <font class="mandatory">*</font>
      </td>
      <td width="200px">
        <span class="text">
          <?php xl('Phone','e'); ?> <?php xl('as','e'); ?> (000) 000-0000:
        </span>
      </td>
      <td width="220px">
        <?php echo ViewHelper::textTag('phone', htmlspecialchars($facility->phone, ENT_QUOTES), '20') ?>
      </td>
     </tr>
     <tr>
      <td>
        <span class="text"><?php xl('Address','e'); ?>:</span>
      </td>
      <td>
        <?php echo ViewHelper::textTag('street', htmlspecialchars($facility->street, ENT_QUOTES), '20') ?>
      </td>
      <td>
        <span class="text">
          <?php xl('Fax','e'); ?> <?php xl('as','e'); ?> (000) 000-0000:
        </span>
      </td>
      <td>
        <?php echo ViewHelper::textTag('fax', htmlspecialchars($facility->fax, ENT_QUOTES), '20') ?>
      </td>
    </tr>
    <tr>
      <td>
        <span class="text"><?php xl('City','e'); ?>: </span>
      </td>
      <td>
        <?php echo ViewHelper::textTag('city', htmlspecialchars($facility->city, ENT_QUOTES), '20') ?>
      </td>
      <td>
        <span class="text"><?php xl('Zip Code','e'); ?>: </span>
      </td>
      <td>
        <?php echo ViewHelper::textTag('postal_code', htmlspecialchars($facility->postal_code, ENT_QUOTES), '20') ?>
      </td>
    </tr>
    <?php 
      $ssn='';
      $ein='';
      if ($facility->tax_id_type == 'SY') {
        $ssn='selected';
      } else{
        $ein='selected';
      }
    ?>
    <tr>
      <td>
        <span class="text"><?php xl('State','e'); ?>: </span>
      </td>
      <td>
        <?php echo ViewHelper::textTag('state', htmlspecialchars($facility->state, ENT_QUOTES), '20') ?>
      </td>
      <td>
        <span class=text><?php xl('Tax ID','e'); ?>: </span>
      </td>
      <td>
        <select name=tax_id_type>
          <option value="EI" <?php echo $ein;?>><?php xl('EIN','e'); ?></option>
          <option value="SY" <?php echo $ssn;?>><?php xl('SSN','e'); ?></option>
        </select>
        <?php echo ViewHelper::textTag('federal_ein', htmlspecialchars($facility->federal_ein, ENT_QUOTES), '20') ?>
      </td>
    </tr>
    <tr>
      <td>
        <span class=text><?php xl('Country','e'); ?>: </span>
      </td>
      <td>
        <input type="text" size="20" name="country_code" maxlength="10" value="<?php echo htmlspecialchars($facility->country_code, ENT_QUOTES) ?>">
      </td>
      <td width="21">
        <span class=text><?php ($GLOBALS['simplified_demographics'] ? xl('Facility Code','e') : xl('Facility NPI','e')); ?>:</span>
      </td>
      <td>
        <?php echo ViewHelper::textTag('facility_npi', htmlspecialchars($facility->facility_npi, ENT_QUOTES), '20') ?>
      </td>
    </tr>
    <tr>
      <td>
        <span class=text><?php xl('Website','e'); ?>: </span>
      </td>
      <td>
        <?php echo ViewHelper::textTag('website', htmlspecialchars($facility->website, ENT_QUOTES), '20') ?>
      </td>
      <td>
        <span class=text><?php xl('Email','e'); ?>:</span>
      </td>
      <td>
        <?php echo ViewHelper::textTag('email', htmlspecialchars($facility->email, ENT_QUOTES), '20') ?>
      </td>
    </tr>
    <tr>
      <td>
        <span class='text'><?php xl('Billing Location','e'); ?>:</span>
      </td>
      <td>
        <input type='checkbox' name='billing_location' value='1' <?php if ($facility->billing_location != 0) echo 'checked'; ?>>
      </td>
      <td rowspan='2'>
        <span class='text'><?php xl('Accepts Assignment','e'); ?><br>(<?php xl('only if billing location','e'); ?>): </span>
      </td>
      <td>
        <input type='checkbox' name='accepts_assignment' value='1' <?php if ($facility->accepts_assignment == 1) echo 'checked'; ?>>
      </td>
    </tr>
    <tr>
      <td>
        <span class='text'><?php xl('Service Location','e'); ?>: </span>
      </td>
      <td>
        <input type='checkbox' name='service_location' value='1' <?php if ($facility->service_location == 1) echo 'checked'; ?>>
      </td>
      <td></td>
    </tr>
    <?php
      $disabled='';
      $resPBE=sqlStatement("select * from facility where primary_business_entity='1' and id!=?", array($my_fid));
      if(sqlNumRows($resPBE)>0) {
        $disabled='disabled';
      }
    ?>
    <tr>
      <td>
        <span class='text'><?php xl('Primary Business Entity','e'); ?>:</span>
      </td>
      <td>
        <input type='checkbox' name='primary_business_entity' id='primary_business_entity' value='1' <?php if ($facility->primary_business_entity == 1) echo 'checked'; ?> <?php if($GLOBALS['erx_enable']){ ?> onchange='return displayAlert()' <?php } ?> <?php echo $disabled;?>>
      </td>
      <td></td>
    </tr>
    <tr>
      <td>
        <span class='text'><?php echo htmlspecialchars(xl('Color'),ENT_QUOTES); ?>: </span>
        <span class="mandatory">&nbsp;*</span>
      </td>
      <td>
        <?php echo ViewHelper::textTag('ncolor', htmlspecialchars($facility->color, ENT_QUOTES), '20') ?>
      </td>
      <td>
        [<a href="javascript:void(0);" onClick="pick('pick','newcolor');return false;" NAME="pick" ID="pick"><?php  echo htmlspecialchars(xl('Pick'),ENT_QUOTES); ?></a>]
      </td>
      <td></td>
    </tr>
    <tr>
      <td>
        <span class=text><?php xl('POS Code','e'); ?>:</span>
      </td>
      <td colspan="6">
        <select name="pos_code">
          <?php
            $pc = new POSRef();

            foreach ($pc->get_pos_ref() as $pos) {
          ?>
          <option value="<?php echo $pos["code"] ?>" <?php echo ($facility->pos_code == $pos['code']) ? 'selected' : '' ?>><?php echo $pos["code"] ?>: <?php echo $pos['title'] ?></option>
          <?php } ?>
        </select>
      </td>
    </tr>
    <tr>
      <td><span class="text"><?php xl('Billing Attn','e'); ?>:</span></td>
      <td colspan="4">
        <?php echo ViewHelper::textTag('attn', htmlspecialchars($facility->attn, ENT_QUOTES), '20') ?>
      </td>
    </tr>
    <tr>
      <td>
        <span class="text"><?php xl('CLIA Number','e'); ?>:</span>
      </td>
      <td colspan="4">
        <?php echo ViewHelper::textTag('domain_identifier', htmlspecialchars($facility->domain_identifier, ENT_QUOTES), '45') ?>
      </td>
    </tr>
    <tr height="20" valign="bottom">
      <td colspan="2">
        <span class="text"><font class="mandatory">*</font> <?php echo xl('Required','e');?></span>
      </td>
    </tr>
  </table>
</form>