<?php

/**
 * Signature form view script for encounter module
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @link      https://www.open-emr.org/wiki/index.php/OEMR_wiki_page OEMR
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Medical Information Integration, LLC
 * @copyright Copyright (c) 2013 OEMR
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

?>
<div id='esign-form-container'>
    <form id='esign-signature-form' method='post' action='<?php echo attr($this->form->action); ?>'>

        <div class="esign-signature-form-element">
              <span id='esign-signature-form-prompt'><?php echo xlt("Your password is your signature"); ?></span>
        </div>

        <div class="esign-signature-form-element gs-hide-element">
              <label for='password'><?php echo xlt('Password');?></label>
              <input type='password' id='password' name='password' size='10' />
        </div>

        <div class="esign-signature-form-element gs-hide-element">
              <span id='esign-signature-form-prompt'><?php echo xlt("Checking the lock checkbox will prevent any further edits on any forms in this encounter."); ?></span>
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
              <input type='button' class="btn btn-primary btn-sm" value='<?php echo xla('Sign'); ?>' id='esign-sign-button-encounter' />
        </div>

        <input type='hidden' id='table' name='table' value='<?php echo attr($this->form->table); ?>' />
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
      signin_btn : '#esign-sign-button-encounter',
      error_container : '#esign-signature-form'
    });
</script>
<?php } ?>
<!-- End -->
