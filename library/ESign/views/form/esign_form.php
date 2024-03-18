<?php

/**
 * Signature form view script for form module
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
        
        <div class="esign-signature-form-element gs-hide-element">
              <span id='esign-signature-form-prompt'><?php echo xlt("Your password is your signature"); ?></span> 
        </div>

        <div class="esign-signature-form-element form-group gs-hide-element">
            <label for='password'><?php echo xlt('Password'); ?></label> 
            <input type='password' class="form-control" id='password' name='password' size='10' placeholder="<?php echo xla("Enter your password to sign the form"); ?>" />
        </div>
        
        <?php if ($this->form->showLock) { ?>
        <div class="esign-signature-form-element form-group">
              <label for='lock'><?php echo xlt('Lock?');?></label> 
              <input type="checkbox" id="lock" name="lock" />
        </div>
        <?php } ?>
        
        <div class="esign-signature-form-element form-group">
            <label for='amendment'><?php echo xlt("Amendment"); ?></label>
            <textarea class="form-control" name='amendment' id='amendment' placeholder='<?php echo xla("Enter an amendment..."); ?>'></textarea> 
        </div>

        <!-- Google sign in for esign -->
        <?php if ($this->form->displayGoogleSignin) { ?>
          <div class="mb-3 mt-2">
            <div class="g_id_signin" data-type="standard" ></div>
            <div>
              <input type="hidden" id="used-google-signin" name="used_google_signin" value="">
              <input type="hidden" id="google-signin-token" name="google_signin_token" value="">
              <div id="google-signin" onclick="return gsi.do_google_signin();">
                  <!-- This message is displayed if the google platform API cannot render the button -->
                  <span id="google-signin-service-unreachable-alert" style="display:none;">
                      <?php echo xlt('Google Sign-In is enabled but the service is unreachable.');?>
                  </span>
              </div>
              <div id="google-signout">
                  <a href="#" onclick="gsi.signOut();"><?php echo xlt('Sign out');?></a>
              </div>
            </div>
          </div>
        <?php } ?>
        
        <div class="esign-signature-form-element">
              <input type='submit' class="btn btn-secondary btn-sm" value='<?php echo xla('Back'); ?>' id='esign-back-button' /> 
              <input type='button' class="btn btn-primary btn-sm" value='<?php echo xla('Sign'); ?>' id='esign-sign-button-form' />
        </div>
        
        <input type='hidden' id='formId' name='formId' value='<?php echo attr($this->form->formId); ?>' /> 
        <input type='hidden' id='table' name='table' value='<?php echo attr($this->form->table); ?>' /> 
        <input type='hidden' id='formDir' name='formDir' value='<?php echo attr($this->form->formDir); ?>' />
        <input type='hidden' id='encounterId' name='encounterId' value='<?php echo attr($this->form->encounterId); ?>' />
        <input type='hidden' id='userId' name='userId' value='<?php echo attr($this->form->userId); ?>' />
        
    </form> 
</div>

<!-- Google sign in for esign -->
<?php if ($this->form->displayGoogleSignin) { ?>
<script type="text/javascript">
    let gsi = Object.create(GoogleSigin);
    gsi.init(<?php echo js_escape($this->form->googleSigninClientID); ?>, {
      ele : '#esign-form-container',
      signin_btn : '#esign-sign-button-form',
      error_container : '#esign-signature-form'
    });
</script>
<?php } ?>
<!-- End -->
