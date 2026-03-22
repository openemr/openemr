<?php

/**
 * Setup Service - Handles MedEx registration and setup wizard
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018 MedEx <support@MedExBank.com>
 * @license   Proprietary - All Rights Reserved
 */

namespace MedExApi\Services;

use OpenEMR\Core\OEGlobalsBag;

class SetupService extends BaseService
{
    private ?string $lastError = null;

    /**
     * Display MedEx Bank registration wizard
     *
     * @param string $stage
     * @return void
     */
    public function MedExBank(string $stage): void
    {
        if ($stage == '1') {
            ?>
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <div id="setup_1">
                        <div class="row">
                            <div class="col-12 text-center">
                                <h2>MedEx</h2>
                                <p class="font-italic">
                                    <?php echo xlt('Using technology to improve productivity'); ?>.
                                </p>
                            </div>
                            <div class="col-md-6 text-center">
                                <h3 class="border-bottom"><?php echo xlt('Targets'); ?>:</h3>
                                <ul class="list-group list-group-flush text-left">
                                    <li class="list-group-item"> <?php echo xlt('Appointment Reminders'); ?></li>
                                    <li class="list-group-item"> <?php echo xlt('Patient Recalls'); ?></li>
                                    <li class="list-group-item"> <?php echo xlt('Office Announcements'); ?></li>
                                    <li class="list-group-item"> <?php echo xlt('Patient Surveys'); ?></li>
                                </ul>
                            </div>
                            <div class="col-md-6 text-center">
                                <h3 class="border-bottom"><?php echo xlt('Channels'); ?>:</h3>
                                <ul class="list-group list-group-flush text-right">
                                    <li class="list-group-item"> <?php echo xlt('SMS Messages'); ?></li>
                                    <li class="list-group-item"> <?php echo xlt('Voice Messages'); ?></li>
                                    <li class="list-group-item"> <?php echo xlt('E-mail Messaging'); ?></li>
                                    <li class="list-group-item"> <?php echo xlt('Postcards'); ?></li>
                                    <li class="list-group-item"> <?php echo xlt('Address Labels'); ?></li>
                                </ul>
                            </div>
                        </div>
                        <div class="text-center row showReminders">
                            <input value="<?php echo xla('Sign-up'); ?>" onclick="goReminderRecall('setup&stage=2');" class="btn btn-primary" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
            <?php
        } elseif ($stage == '2') {
            $globalsBag = OEGlobalsBag::getInstance();
            $userData = $globalsBag->get('user_data') ?? [];
            $userEmail = $userData['email'] ?? '';
            ?>
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="text-center"><?php echo xlt('Register'); ?>: MedEx Bank</h2>
                    <form name="medex_start" id="medex_start" class="jumbotron p-5">
                        <div id="setup_1">
                            <div id="answer" name="answer">
                                <div class="form-group mt-3">
                                    <label for="new_email"><?php echo xlt('E-mail'); ?>:</label>
                                    <i id="email_check" name="email_check" class="top_right_corner nodisplay text-success fa fa-check"></i>
                                    <input type="text" data-rule-email="true" class="form-control" id="new_email" name="new_email" value="<?php echo attr($userEmail); ?>" placeholder="<?php echo xla('your email address'); ?>" required />
                                    <div class="signup_help nodisplay" id="email_help" name="email_help"><?php echo xlt('Please provide a valid e-mail address to proceed'); ?>...</div>
                                </div>
                                <div class="form-group mt-3">
                                    <label for="new_password"><?php echo xlt('Password'); ?>:</label>
                                    <i id="pwd_check" name="pwd_check" class="top_right_corner nodisplay text-success fa fa-check"></i>
                                    <i class="fa top_right_corner fa-question" id="pwd_ico_help" aria-hidden="true" onclick="$('#pwd_help').toggleClass('nodisplay');"></i>
                                    <input type="password" placeholder="<?php xla('Password'); ?>" id="new_password" name="new_password" class="form-control" required />
                                    <div id="pwd_help" class="nodisplay signup_help"><?php echo xlt('Secure Password Required') . ": " . xlt('8-12 characters long, including at least one upper case letter, one lower case letter, one number, one special character and no common strings'); ?>...</div>
                                </div>
                                <div class="form-group mt-3">
                                    <label for="new_rpassword"><?php echo xlt('Repeat'); ?>:</label>
                                    <i id="pwd_rcheck" name="pwd_rcheck" class="top_right_corner nodisplay text-success fa fa-check"></i>
                                    <input type="password" placeholder="<?php echo xla('Repeat password'); ?>" id="new_rpassword" name="new_rpassword" class="form-control" required />
                                    <div id="pwd_rhelp" class="nodisplay signup_help" style=""><?php echo xlt('Passwords do not match.'); ?></div>
                                </div>
                            </div>
                            <div id="ihvread" name="ihvread" class="text-left showReminders">
                                <input type="checkbox" class="updated required" name="TERMS_yes" id="TERMS_yes" required />
                                <label for="TERMS_yes" class="input-helper input-helper--checkbox" data-toggle="tooltip" data-placement="auto" title="Terms and Conditions"><?php echo xlt('I have read and my practice agrees to the'); ?>
                                    <a href="#" onclick="cascwin('https://medexbank.com/cart/upload/index.php?route=information/information&information_id=5','TERMS',800, 600);">MedEx <?php echo xlt('Terms and Conditions'); ?></a>
                                </label>
                                <br />
                                <input type="checkbox" class="updated required" name="BusAgree_yes" id="BusAgree_yes" required />
                                <label for="BusAgree_yes" class="input-helper input-helper--checkbox" data-toggle="tooltip" data-placement="auto" title="BAA"><?php echo xlt('I have read and accept the'); ?>
                                    <a href="#" onclick="cascwin('https://medexbank.com/cart/upload/index.php?route=information/information&information_id=8','Bus Assoc Agree',800, 600);">MedEx <?php echo xlt('Business Associate Agreement'); ?></a>
                                </label>
                                <br />
                                <div class="align-center showReminders">
                                    <input id="Register" class="btn btn-primary" value="<?php echo xla('Register'); ?>" />
                                </div>

                                <div id="myModal" class="modal fade" role="dialog">
                                    <div class="modal-dialog">
                                        <!-- Modal content-->
                                        <div class="modal-content">
                                            <div class="modal-header text-white font-weight-bold" style="background-color: #0d4867;">
                                                <button type="button" class="close text-white" data-dismiss="modal" style="opacity:1;box-shadow:unset !important;">&times;</button>
                                                <h2 class="modal-title" style="font-weight:600;">Sign-Up Confirmation</h2>
                                            </div>
                                            <div class="modal-body" style="padding: 10px 45px;">
                                                <p>You are opening a secure connection to MedExBank.com.  During this step your EHR will synchronize with the MedEx servers.  <br />
                                                    <br />
                                                    Re-enter your username (e-mail) and password in the MedExBank.com login window to:
                                                    <ul class="text-left mx-auto" style="width: 90%;">
                                                        <li> confirm your practice and providers' information</li>
                                                        <li> choose your service options</li>
                                                        <li> update and activate your messages </li>
                                                    </ul>
                                                </p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                <button type="button" class="btn btn-secondary" onlick="actualSignUp();" id="actualSignUp">Proceed</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script>
        function signUp() {
            var email = $("#new_email").val();
            if (!validateEmail(email))  return alert('<?php echo xlt('Please provide a valid e-mail address to proceed'); ?>...');
            var password = $("#new_password").val();
            var passed = check_Password(password);
            if (!passed) return alert('<?php echo xlt('Passwords must be 8-12 characters long and include one capital letter, one lower case letter and one special character'); ?> ... ');
            if ($("#new_rpassword").val() !== password) return alert('<?php echo xlt('Passwords do not match'); ?>!');
            if (!$("#TERMS_yes").is(':checked')) return alert('<?php echo xlt('You must agree to the Terms & Conditions before signing up');?>... ');
            if (!$("#BusAgree_yes").is(':checked')) return alert('<?php echo xlt('You must agree to the HIPAA Business Associate Agreement');?>... ');
            $("#myModal").modal();
            return false;
        }

        function validateEmail(email) {
            var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(email);
        }
        function check_Password(password) {
            var passed = validatePassword(password, {
                length:   [8, Infinity],
                lower:    1,
                upper:    1,
                numeric:  1,
                special:  1,
                badWords: ["password", "qwerty", "12345"],
                badSequenceLength: 4
            });
            return passed;
        }
        function validatePassword (pw, options) {
            // default options (allows any password)
            var o = {
                lower:    0,
                upper:    0,
                alpha:    0, /* lower + upper */
                numeric:  0,
                special:  0,
                length:   [0, Infinity],
                custom:   [ /* regexes and/or functions */ ],
                badWords: [],
                badSequenceLength: 0,
                noQwertySequences: false,
                noSequential:      false
            };

            for (var property in options)
                o[property] = options[property];

            var re = {
                    lower:   /[a-z]/g,
                    upper:   /[A-Z]/g,
                    alpha:   /[A-Z]/gi,
                    numeric: /[0-9]/g,
                    special: /[\W_]/g
                },
                rule, i;

            // enforce min/max length
            if (pw.length < o.length[0] || pw.length > o.length[1])
                return false;

            // enforce lower/upper/alpha/numeric/special rules
            for (rule in re) {
                if ((pw.match(re[rule]) || []).length < o[rule])
                    return false;
            }

            // enforce word ban (case insensitive)
            for (i = 0; i < o.badWords.length; i++) {
                if (pw.toLowerCase().indexOf(o.badWords[i].toLowerCase()) > -1)
                    return false;
            }

            // enforce the no sequential, identical characters rule
            if (o.noSequential && /([\S\s])\1/.test(pw))
                return false;

            // enforce alphanumeric/qwerty sequence ban rules
            if (o.badSequenceLength) {
                var lower   = "abcdefghijklmnopqrstuvwxyz",
                    upper   = lower.toUpperCase(),
                    numbers = "0123456789",
                    qwerty  = "qwertyuiopasdfghjklzxcvbnm",
                    start   = o.badSequenceLength - 1,
                    seq     = "_" + pw.slice(0, start);
                for (i = start; i < pw.length; i++) {
                    seq = seq.slice(1) + pw.charAt(i);
                    if (
                        lower.indexOf(seq)   > -1 ||
                        upper.indexOf(seq)   > -1 ||
                        numbers.indexOf(seq) > -1 ||
                        (o.noQwertySequences && qwerty.indexOf(seq) > -1)
                    ) {
                        return false;
                    }
                }
            }

            // enforce custom regex/function rules
            for (i = 0; i < o.custom.length; i++) {
                rule = o.custom[i];
                if (rule instanceof RegExp) {
                    if (!rule.test(pw))
                        return false;
                } else if (rule instanceof Function) {
                    if (!rule(pw))
                        return false;
                }
            }

            // great success!
            return true;
        }
        $(function () {
            $("#Register").click(function() {
                 signUp();
            });
            $("#actualSignUp").click(function() {
                var url = "save.php?MedEx=start";
                var email = $("#new_email").val();
                $("#actualSignUp").html('<i class="fa fa-spinner fa-pulse fa-fw"></i><span class="sr-only">Loading...</span>');
                formData = $("form#medex_start").serialize();
                top.restoreSession();
                $.ajax({
                    type   : 'POST',
                    url    : url,
                    data   : formData
                    })
                .done(function(result) {
                    obj = JSON.parse(result);
                    $("#answer").html(obj.show);
                    $("#ihvread").addClass('nodisplay');
                    $('#myModal').modal('toggle');
                    if (obj.success) {
                        url="https://www.medexbank.com/login/"+email;
                        window.open(url, 'clinical', 'resizable=1,scrollbars=1');
                        refresh_me();
                    }
                });
            });
            $("#new_email").blur(function(e) {
                                e.preventDefault();
                                var email = $("#new_email").val();
                                if (validateEmail(email))  {
                                    $("#email_help").addClass('nodisplay');
                                    $("#email_check").removeClass('nodisplay');
                                } else {
                                    $("#email_help").removeClass('nodisplay');
                                    $("#email_check").addClass('nodisplay');
                                }
                            });
            $("#new_password,#new_rpassword").keyup(function(e) {
                                e.preventDefault();
                                var pwd = $("#new_password").val();
                                if (check_Password(pwd))  {
                                    $('#pwd_help').addClass('nodisplay');
                                    $("#pwd_ico_help").addClass('nodisplay');
                                    $("#pwd_check").removeClass('nodisplay');
                                } else {
                                    $("#pwd_help").removeClass('nodisplay');
                                    $("#pwd_ico_help").removeClass('nodisplay');
                                    $("#pwd_check").addClass('nodisplay');
                                }
                                if (this.id === "new_rpassword") {
                                    var pwd1 = $("#new_password").val();
                                    var pwd2 = $("#new_rpassword").val();
                                    if (pwd1 === pwd2) {
                                        $('#pwd_rhelp').addClass('nodisplay');
                                        $("#pwd_rcheck").removeClass('nodisplay');
                                    } else {
                                        $("#pwd_rhelp").removeClass('nodisplay');
                                        $("#pwd_rcheck").addClass('nodisplay');
                                    }
                                }
                            });
        });
        </script>
            <?php
        }
    }

    /**
     * Auto-registration API call to MedEx
     *
     * @param array<string,mixed> $data
     * @return array<string,mixed>|false
     */
    public function autoReg(array $data): array|false
    {
        if (empty($data)) {
            return false;
        }

        $this->curl->setUrl($this->medEx->getUrl('custom/signUp'));
        $this->curl->setData($data);

        try {
            $this->curl->makeRequest();
        } catch (\Exception $e) {
            error_log("MedEx autoReg failed: " . $e->getMessage());
            throw $e;
        }

        $response = $this->curl->getResponse();

        if (isset($response['success'])) {
            return $response;
        } elseif (isset($response['error'])) {
            $this->lastError = $response['error'];
        }

        return false;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }
}
