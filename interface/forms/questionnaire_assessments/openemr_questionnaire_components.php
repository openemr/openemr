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

declare(strict_types=1);

use OpenEMR\Common\Utils\CacheUtils;
use OpenEMR\Core\OEGlobalsBag;

$questionnaireAssetRoot = OEGlobalsBag::getInstance()->getWebRoot()
    . '/interface/forms/questionnaire_assessments/native';
$questionnaireStylesheet = CacheUtils::addAssetCacheParamToPath(
    $questionnaireAssetRoot . '/openemr_questionnaire.css'
);
$questionnaireScript = CacheUtils::addAssetCacheParamToPath(
    $questionnaireAssetRoot . '/openemr_questionnaire.js'
);
?>
<link href="<?php echo attr($questionnaireStylesheet); ?>" media="screen" rel="stylesheet" />
<script src="<?php echo attr($questionnaireScript); ?>"></script>
