<?php
/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sun PC Solutions LLC
 * @copyright Copyright (c) 2025 Sun PC Solutions LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/**
 * audio_to_note.inc - common includes for the audio_to_note form
 */

// Define base path for OpenEMR includes relative to this file's directory.
// This assumes the form is located at interface/forms/form_name/
$form_base_dir = __DIR__; // Current directory: interface/forms/audio_to_note/
$openemr_interface_dir = dirname(dirname($form_base_dir)); // interface/
$openemr_base_dir = dirname($openemr_interface_dir); // openemr/ (htdocs/openemr or similar)

require_once $openemr_interface_dir . "/globals.php";
require_once $openemr_base_dir . "/library/api.inc.php";
require_once $openemr_base_dir . "/library/patient.inc.php";

?>