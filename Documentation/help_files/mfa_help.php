<?php

    /**
 * Message Center Help.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\Header;

require_once("../../interface/globals.php");
?>
<!DOCTYPE html>
<html>
    <head>
    <?php Header::setupHeader();?>
    <title><?php echo xlt("Multi Factor Authorization Help");?></title>
    <style>
        .oe-help-add-info{
            padding:15px;
            border:6px solid;
            font-style: italic;
        }
    </style>
    </head>
    <body>
        <div class="container oe-help-container">
            <div>
                <h2 class="text-center"><a name='entire_doc'><?php echo xlt("Multi Factor Authorization Help");?></a></h2>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div>
                    <p><?php echo xlt("When dealing with protected health information (PHI) and personally identifying information (PII) it is important to allow only authorized users access to the data");?>.</p>

                    <p><?php echo xlt("Authenticating the user thus becomes an important factor in this endeavor");?>.</p>

                    <p><?php echo xlt("The traditional mechanism of using a login and password is no longer considered to be sufficient to prevent an unauthorized user from gaining access to the application");?>.</p>

                    <p><?php echo xlt("Multi Factor Authorization - MFA has been increasingly used to authenticate a user without making the process of authentication too onerous");?>.</p>

                    <p><?php echo xlt("The most common method is called 2-Factor Authorization or 2FA");?>.</p>

                    <p><?php echo xlt("It combines what the user knows i.e. user password with what the user has i.e. a device that is unique or a mechanism to generate/receive a unique code to be used at each login");?>.</p>

                    <p><?php echo xlt("One-time password or OTP is a commonly used strategy used to provide a unique code for each login");?>.</p>

                    <p><?php echo xlt("For those interested details of OTP");?>.  &nbsp;<i class="show_hide fa fa-eye fa-lg small" title="<?php echo xla('Click to Show'); ?>"></i></p>

                        <div id="otp_details" class='hideaway oe-help-heading oe-help-add-info' style='display: none;'>

                            <p><?php echo xlt("OTP can be generated either using time synchronization where the current time is combined with a secret key and hashed to generate the OTP or using mathematical algorithms were an initial seed (a random number) is combined with the previously used password to generate the OTP");?>.</p>

                            <p><?php echo xlt("Commonly used standards are HOTP (hash-based one-time password, RFC4226), TOTP (time-based one-time password or OCRA (OATH challenge-response algorithm, RFC6287) that were developed and are supported by the OATH (Initiative for Open Authentication)");?>.</p>

                            <p><?php echo xlt("OTP can be delivered by various methods");?>:</p>
                                <ul>
                                    <li><?php echo xlt("Mobile phones"); ?></li>
                                    <li><?php echo xlt("Proprietary hardware tokens"); ?></li>
                                    <li><?php echo xlt("Web based methods"); ?></li>
                                    <li><?php echo xlt("Hard copy OTP"); ?></li>
                                </ul>

                            <p><?php echo xlt("Mobile phones are ubiquitous and are generally easily accessible by any user");?>.</p>

                            <p><?php echo xlt("SMS based delivery of OTP is easy to implement with no additional steps needed by the user");?>.</p>

                            <p><?php echo xlt("However SMS text messages can be intercepted and the use of SMS as a method of implementing out-of-band two-factor authentication is discouraged");?>.</p>

                            <p><?php echo xlt("Cannot be used in case of the absence of the cellular coverage");?>.</p>

                            <p><?php echo xlt("Moreover there is a recurring cost incurred in sending the text messages");?>.</p>

                            <p><?php echo xlt("Smartphones can have authenticator apps that can calculate OTP using either time based or algorithm based approaches and is a common method of OTP delivery");?>.</p>

                            <p><?php echo xlt("They do not share the vulnerabilities of SMS based methods and generally do not require an internet connection except to keep their clocks synchronized with the current time");?>.</p>

                            <p><?php echo xlt("As the secret key need by the authenticator is stored on the mobile phone malware can steal these keys and thus compromise authentication");?>.</p>

                            <p><?php echo xlt("Proprietary hardware tokens are tamper proof and are not connected to the internet or any network");?>.</p>

                            <p><?php echo xlt("Disadvantages being cost of device, potential for loss and running out of battery power");?>.</p>

                            <p><?php echo xlt("USB based tokens are used for Universal 2nd Factor - U2F authentication and as they are connected to a computer do not need batteries");?>.</p>

                            <p><?php echo xlt("Web based methods use Authentication-as-a-service and deliver OTP without need for tokens");?>.</p>

                            <p><?php echo xlt("Hard copy OTP is used for online banking in some countries");?>.</p>
                        </div>
                    </div>

                    <p><?php echo xlt("OpenEMR offers 2 methods of 2FA");?>.</p>
                        <ul>
                            <li><?php echo xlt("TOTP - Time-Based One-Time Password"); ?></li>
                            <li><?php echo xlt("FIDO U2F - Universal 2nd Factor from the Fast IDentity Online alliance"); ?></li>
                        </ul>

                    <p><?php echo xlt("The help file is divided into the following sections");?>:</p>

                    <ul id="top_section">
                        <li><a href="#section1"><?php echo xlt("Setting up 2FA");?></a></li>
                        <li><a href="#section2"><?php echo xlt("Advantages and Disadvantages of TOTP and U2F");?></a></li>
                        <li><a href="#section3"><?php echo xlt("Using 2FA");?></a></li>
                    </ul>
                </div>
            </div>
            <div class= "row" id="section1">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Setting up 2FA"); ?><a href="#top_section"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("Clicking on the Miscellaneous > MFA Management menu item or User Name > MFA Management menu item brings you to the landing page for managing the multi factor authentication page for that user");?>.</p>

                    <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <?php echo xlt("It is important to realize that this manages the MFA for the logged in user");?>.</p>

                    <p><?php echo xlt("Therefore each user has to individually activate this feature");?>.</p>

                    <p><?php echo xlt("There are two sections that are visible");?>:</p>
                        <ul>
                            <li><?php echo xlt("Current Authentication Method for the logged in user"); ?></li>
                            <li><?php echo xlt("Select/Add Authentication Method for the logged in user"); ?></li>
                        </ul>

                    <p><?php echo xlt("The Current Authentication Method lists all the methods that are active");?>.</p>

                    <p><?php echo xlt("OpenEMR allows one TOTP but multiple U2F methods per user");?>.</p>

                    <p><?php echo xlt("This section will allow the logged in user to view existing methods or to delete them ");?>.
                        <button type="button" class="btn btn-secondary btn-search btn-sm oe-no-float"><?php echo xlt("View"); ?></button>
                        <button type="button" class="btn btn-secondary btn-delete btn-sm oe-no-float"><?php echo xlt("Delete"); ?></button>
                    </p>

                    <p><?php echo xlt("When the user initially visits this page the following message will be visible");?> <i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i>  <strong><?php echo xlt("No method enabled") ;?></strong>.</p>

                    <p><?php echo xlt("Click on the Add New dropdown box and select an option");?>.</p>
                    <div>
                        <p><strong><?php echo ("TOTP - TIME-BASED ONE-TIME PASSWORD"); ?> :</strong></p>

                        <p><?php echo xlt("Takes you to the Register Time Based One Time Password Key - TOTP page");?>.</p>

                        <p><?php echo xlt("To register you need an authenticator app installed on your Smartphone");?>.</p>

                        <p><?php echo xlt("These are free and can be downloaded from the respective app store for ios and android devices");?>.</p>

                        <p><?php echo xlt("Enter the OpenEMR login password for the user and click Submit");?>.
                            <button type="button" class="btn btn-secondary btn-save btn-sm oe-no-float"><?php echo xlt("Submit"); ?></button>
                        </p>

                        <p><?php echo xlt("This will reveal the Register TOTP Key section for logged in user");?>.</p>

                        <p><?php echo xlt("It contains a QR code that needs to be captured by the authenticator app");?>.</p>

                        <p><?php echo xlt("Once the app captures the QR code it will show the OpenEMR user name in the app");?>.</p>

                        <p><?php echo xlt("Click Register to register the TOTP");?>.
                            <button type="button" class="btn btn-secondary btn-save btn-sm oe-no-float"><?php echo xlt("Register"); ?></button>
                        </p>

                        <p><i class="fa fa-exclamation-circle  oe-text-orange" aria-hidden="true"></i> <?php echo xlt("If only capture the QR code but do not register the TOTP key with OpenEMR this feature will not be enabled at login");?>.</p>

                        <p><i class="fa fa-exclamation-triangle  oe-text-red" aria-hidden="true"></i> <?php echo xlt("On the other hand if you click register but do not capture the QR code with the authenticator app you will not be able to login");?>.</p>

                        <p><?php echo xlt("Upon successful registration you will be taken back to the Manage Multi Factor Authentication landing page");?>.</p>

                        <p><?php echo xlt("The Current Authentication Method for the logged in user section will now feature the activated methods");?>.</p>

                        <p><?php echo xlt("For those interested in what happens under the hood");?>.   &nbsp;<i class="show_hide fa fa-eye fa-lg small" title="<?php echo xla('Click to Show'); ?>"></i></p>

                        <div id="totp_details" class='hideaway oe-help-heading oe-help-add-info' style='display: none;'>
                            <p><?php echo xlt("For TOTP to work a unique secret key must be shared between OpenEMR and the user");?>.</p>

                            <p><?php echo xlt("This key is generated by the application and is presented to the user in the form of a QR code");?>.</p>

                            <p><?php echo xlt("The QR code also contains the user name and needs to be captured by the user on to their mobile device using an authenticator app");?>.</p>

                            <p><?php echo xlt("Once this is done the shared secret key that is unique for each user should only exist in OpenEMR and on the user's authenticator app");?>.</p>

                            <p><?php echo xlt("Authenticator apps are available for both ios and android devices at their respective app stores and is free to use");?>.</p>

                            <p><?php echo xlt("The basic purpose of the authenticator app is to generate a 20 bytes (160 bits) code encoded in base32 using a secure hash function, SHA-1, and is called HMAC-SHA1 (Hash-based Message Authentication Code)");?>.</p>

                            <p><?php echo xlt("It does so by using an algorithm to combine the current UNIX time with the secret key to generate an ever changing unique key");?>.</p>

                            <p><?php echo xlt("For ease of use it is rendered as a unique 6 digit number");?>.</p>

                            <p><?php echo xlt("All TOTP authenticator apps use the same algorithm and secure hash function - HMAC-SHA1 to generate the unique key");?>.</p>

                            <p><?php echo xlt("An important concept to grasp is that once the secret key delivered via the QR code is captured by the authenticator app there is no further communication between the app and OpenEMR, each will use the current UNIX time and its copy of the user-specific shared secret key to generate the same unique 6 digit number");?>.</p>

                            <p><?php echo xlt("These numbers need to match to successfully authenticate the user");?>.</p>

                            <p><?php echo xlt("Once this feature is enabled you will be required to type in the 6 digit key at each login");?>.</p>
                        </div>
                    </div>
                    <div>
                        <p><strong><?php echo ("FIDO U2F"); ?> :</strong></p>

                        <p><?php echo xlt("Takes you to the Register Universal 2nd Factor Key - U2F page");?>.</p>

                        <p><?php echo xlt("To proceed you need a USB security key, a secure HTTPS web connection and a browser that supports U2F");?>.</p>

                        <p><?php echo xlt("Use the latest versions of the following modern browsers - Chrome, Firefox, Safari, Edge and Opera");?>.</p>

                        <p><i class="fa fa-exclamation-circle oe-text-orange"  aria-hidden="true"></i> <?php echo xlt("No version of Internet Explorer supports U2F");?>.</p>

                        <p><?php echo xlt("Insert the key into the USB port");?>.</p>

                        <p><?php echo xlt("Type a name for the key in the text box");?>.</p>

                        <p><?php echo xlt("Press the flashing button on the USB key within 1 minute of inserting it and click on register to register the key with OpenEMR");?>.</p>

                        <p><?php echo xlt("The most popular maker of Security Keys is Yubico it offers regular USB versions as well as those made for devices that require USB-C connections, such as Apple’s newer Mac OS systems");?>.</p>

                        <p><?php echo xlt("Yubikey also sells more expensive U2F keys designed to work with mobile devices");?>.</p>

                        <p><?php echo xlt("If you are interested in what happens under the hood");?>: &nbsp;<i class="show_hide fa fa-eye fa-lg small" title="<?php echo xla('Click to Show'); ?>"></i></p>
                        <div id="u2f_details" class='hideaway oe-help-heading oe-help-add-info' style='display: none;'>
                            <p><?php echo xlt("A dedicated security key needs to be purchased");?>.</p>

                            <p><?php echo xlt("It uses an open standard and therefore available from various manufactures");?>.</p>

                            <p><?php echo xlt("The USB key needs to be plugged in to the computer and then registered with OpenEMR");?>.</p>

                            <p><?php echo xlt("When the key is first registered it generates a random number, which is called a nonce");?>.</p>

                            <p><?php echo xlt("It uses the HMAC-SHA256 hash function to generate a unique private key for the account");?>.</p>

                            <p><?php echo xlt("This is passed to the application along with checksum value and is unique for each USB key and thereby the user who owns it");?>.</p>

                            <p><?php echo xlt("The USB devices communicate with the host computer using the human interface device (HID) protocol, essentially mimicking a keyboard");?>.</p>

                            <p><?php echo xlt("It allows the browser to communicate directly to the USB device avoiding the need for the user to do anything more than insert the device into an USB port and tap it to activate it");?>.</p>

                            <p><?php echo xlt("Not all old browsers support U2F. Using the latest versions of modern browsers - Chrome, Firefox, Safari, Edge and Opera should suffice");?>.</p>

                            <p><?php echo xlt("Unlike TOTP where there is no communication between the application and the device having the authenticator app each time the USB key is plugged in the application communicates with the USB key via the browser");?>.</p>

                            <p><?php echo xlt("The application generates challenge - a random number and passes it to the USB key along with the nonce and checksum stored in the application");?>.</p>

                            <p><?php echo xlt("The USB key takes this the nonce supplied by the application after confirming the checksum and generates a private key using the process similar to that used in the registration process");?>.</p>

                            <p><?php echo xlt("It uses this private key to sign the challenge and sends the response back to the application");?>.</p>

                            <p><?php echo xlt("The application then uses the public key that the USB device sent on registration to verify the response");?>.</p>

                            <p><?php echo xlt("If successful it authenticates the user");?>.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class= "row" id="section2">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Advantages and Disadvantages of TOTP and U2F"); ?><a href="#top_section"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("TOTP - Pros"); ?>:</p>
                        <ul>
                            <li><?php echo xlt("Free authenticator app"); ?></li>
                            <li><?php echo xlt("Easy to setup"); ?></li>
                        </ul>

                    <p><?php echo xlt("TOTP - Cons"); ?>:</p>
                        <ul>
                            <li><?php echo xlt("Needs a Smartphone"); ?></li>
                            <li><?php echo xlt("If phone is lost will not be able to login"); ?></li>
                            <li><?php echo xlt("Being a software application it can be compromised by malware that steals the secret key or by social engineering"); ?></li>
                        </ul>

                    <p><?php echo xlt("U2F - Pros"); ?>:</p>
                        <ul>
                            <li><?php echo xlt("Easy to use"); ?></li>
                            <li><?php echo xlt("Very secure"); ?></li>
                        </ul>

                    <p><?php echo xlt("U2F - Cons"); ?>:</p>
                        <ul>
                            <li><?php echo xlt("Many companies block operations with USB ports on corporate computers"); ?></li>
                            <li><?php echo xlt("U2F devices are relatively costly"); ?></li>
                            <li><?php echo xlt("Easy to forget on the computer used to login"); ?></li>
                            <li><?php echo xlt("Users may leave it plugged in at all times thus negating its function in 2FA"); ?></li>
                        </ul>
                </div>
            </div>
            <div class= "row" id="section3">
                <div class="col-sm-12">
                    <h4 class="oe-help-heading"><?php echo xlt("Using 2FA"); ?><a href="#top_section"><i class="fa fa-arrow-circle-up oe-pull-away oe-help-redirect" aria-hidden="true"></i></a></h4>
                    <p><?php echo xlt("If you have enabled the TOTP method the next time you login you will be asked to enter the TOTP password");?>.</p>

                    <p><?php echo xlt("With the U2F key you will need to plug it in to the USB port and then access OpenEMR using the latest versions of the following modern browsers - Chrome, Firefox, Safari, Edge and Opera and pressing the button on the device");?>.</p>

                    <p><?php echo xlt("Login with your user id and password and it will automatically authenticate you");?>.</p>

                    <p><?php echo xlt("If you change your Smartphone you can recapture the TOTP key QR code on to a new device by first logging in using the authenticator app on your old phone and then going to Miscellaneous > MFA Authentication and clicking the View button on the Current Authentication Method section");?>.
                        <button type="button" class="btn btn-secondary btn-search btn-sm oe-no-float"><?php echo xlt("View"); ?></button>
                    </p>

                    <p><?php echo xlt("This will re-display the secret key via the QR code that can then be captured and stored on the new Smartphone via its authenticator app");?>.</p>

                    <p><?php echo xlt("If you have lost your Smartphone those with administrator privileges can delete the keys by going to Administration > Users and checking the Clear 2FA checkbox and clicking Save");?>.</p>

                    <p><?php echo xlt("Congratulations for enabling 2FA");?> !!</p>
                </div>
            </div>
        </div><!--end of container div-->
        <script>
        // better script for tackling nested divs
           $('.show_hide').click(function() {
                var elementTitle = $(this).prop('title');
                var hideTitle = '<?php echo xla('Click to Hide'); ?>';
                var showTitle = '<?php echo xla('Click to Show'); ?>';
                //$('.hideaway').toggle('1000');
                $(this).parent().parent().closest('div').children('.hideaway').toggle('1000');
                if (elementTitle == hideTitle) {
                    elementTitle = showTitle;
                    $(this).toggleClass('fa-eye-slash fa-eye');
                } else if (elementTitle == showTitle) {
                    elementTitle = hideTitle;
                    $(this).toggleClass('fa-eye fa-eye-slash');
                }
                $(this).prop('title', elementTitle);
            });
        </script>
    </body>
</html>
