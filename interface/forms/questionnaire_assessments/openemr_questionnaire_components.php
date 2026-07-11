<?php

/**
 * OpenEMR native FHIR Questionnaire runtime assets.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\OEGlobalsBag;

$questionnaireAssetRoot = OEGlobalsBag::getInstance()->getWebRoot()
    . '/interface/forms/questionnaire_assessments/native';
?>
<link href="<?php echo attr($questionnaireAssetRoot); ?>/openemr_questionnaire.css" media="screen" rel="stylesheet" />
<script src="<?php echo attr($questionnaireAssetRoot); ?>/openemr_questionnaire.js"></script>
