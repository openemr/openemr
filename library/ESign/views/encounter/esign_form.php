<?php

/**
 * Signature form view script for encounter module
 *
 * Copyright (C) 2013 OEMR 501c3 www.oemr.org
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Ken Chapple <ken@mi-squared.com>
 * @author  Medical Information Integration, LLC
 * @link    http://www.open-emr.org
 **/

?>
<div id='esign-form-container'>
    <form id='esign-signature-form' method='post' action='<?php echo attr($this->form->action); ?>'>
        
        <div class="esign-signature-form-element">
              <span id='esign-signature-form-prompt'><?php echo xlt("Your password is your signature"); ?></span> 
        </div>

        <div class="esign-signature-form-element">
              <label for='password'><?php echo xlt('Password');?></label> 
              <input type='password' id='password' name='password' size='10' />
        </div>
        
        <div class="esign-signature-form-element">
              <span id='esign-signature-form-prompt'><?php echo xlt("Checking the lock checkbox will prevent any futher edits on any forms in this encounter."); ?></span> 
        </div>
        
        <?php if ($this->form->showLock) { ?>
        <div class="esign-signature-form-element">
              <label for='lock'><?php echo xlt('Lock?');?></label> 
              <input type="checkbox" id="lock" name="lock" />
        </div>
        <?php } ?>
        
        <div class="esign-signature-form-element">
              <textarea name='amendment' id='amendment' placeholder='<?php echo xla("Enter an amendment..."); ?>'></textarea> 
        </div>
        
        <div class="esign-signature-form-element">
              <input type='submit' class="btn btn-secondary btn-sm" value='<?php echo xla('Back'); ?>' id='esign-back-button' /> 
              <input type='button' class="btn btn-primary btn-sm" value='<?php echo xla('Sign'); ?>' id='esign-sign-button-encounter' />
        </div>
        
        <input type='hidden' id='table' name='table' value='<?php echo attr($this->form->table); ?>' /> 
        <input type='hidden' id='encounterId' name='encounterId' value='<?php echo attr($this->form->encounterId); ?>' />
        <input type='hidden' id='userId' name='userId' value='<?php echo attr($this->form->userId); ?>' />
        
    </form> 
</div>
