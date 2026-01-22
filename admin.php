<?php

/**
 * Admin Redirect
 *
 * This file redirects to the new secure admin area.
 * The multi-site administration now requires authentication.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 * @deprecated This file is deprecated. Use admin/login.php instead.
 */

// Redirect to new admin location
header('Location: admin/login.php');
exit;
