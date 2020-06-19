<?php
/**
 * Runs when site ID is missing from session data
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

function sessionExpired($web_root) { ?>
  <!DOCTYPE HTML>
  <html>
  <head>
    <script src="main/tabs/js/include_opener.js"></script>
    <script>
      // Equivalent to jQuery ready function
      window.onload = function() {
        // Call that session timed out using opener
        top.timed_out = true;
        // Get main frame and redirect to login page
        top.location.href = "<?php echo $web_root; ?>/interface/login/login.php";
      };
    </script>
    <style>
      body {
        font-family: 'Arial', sans-serif;
        text-align: center;
      }
    </style>
  </head>
  <body>
    <h2>Invalid Session</h2>
    <p>Please logout of OpenEMR and log back in again if you are not redirected.</p>
  </body>
  </html>
<?php } ?>
