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

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Questionnaire\QuestionnaireRuntimeAssets;

$questionnaireRuntime = (new QuestionnaireRuntimeAssets(OEGlobalsBag::getInstance()))->describe();
?>
<link href="<?php echo attr($questionnaireRuntime['stylesheet']); ?>" media="screen" rel="stylesheet" />
<script src="<?php echo attr($questionnaireRuntime['script']); ?>" data-openemr-version="<?php echo attr($questionnaireRuntime['version']); ?>" data-openemr-strings="<?php echo attr($questionnaireRuntime['strings']); ?>"></script>
