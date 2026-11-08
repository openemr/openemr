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
use OpenEMR\Services\SoftwareVersion;

$questionnaireGlobals = OEGlobalsBag::getInstance();
$questionnaireAssetRoot = $questionnaireGlobals->getWebRoot()
    . '/interface/forms/questionnaire_assessments/native';
$questionnaireStylesheet = CacheUtils::addAssetCacheParamToPath(
    $questionnaireAssetRoot . '/openemr_questionnaire.css'
);
$questionnaireScript = CacheUtils::addAssetCacheParamToPath(
    $questionnaireAssetRoot . '/openemr_questionnaire.js'
);
// The runtime stamps this on QuestionnaireResponse.meta.tag (LForms parity).
$questionnaireOpenemrVersion = SoftwareVersion::fromGlobals($questionnaireGlobals)->full;
// The runtime's own words, translated. Keys and English text match DEFAULT_STRINGS in
// openemr_questionnaire.js; {0}, {1} are filled in by the runtime.
$questionnaireStrings = [
    'add' => xl('+ Add {0}'),
    'addItem' => xl('item'),
    'remove' => xl('Remove'),
    'instance' => xl('#{0}'),
    'yes' => xl('Yes'),
    'no' => xl('No'),
    'other' => xl('Other'),
    'unit' => xl('Unit'),
    'referencePlaceholder' => xl('Reference (e.g. Patient/123)'),
    'currentFile' => xl('Current: {0}'),
    'close' => xl('Close'),
    'help' => xl('Help'),
    'legal' => xl('Legal notice'),
    'copyright' => xl('Copyright'),
    'warningsHeading' => xl('Some questionnaire items could not be fully rendered'),
    'unknownIssue' => xl('Unknown issue'),
    'unsupportedType' => xl('Unsupported Questionnaire item type: {0}'),
    'requiresValue' => xl('{0} requires a value'),
    'requiresAtLeast' => xl('{0} requires at least {1} answers'),
    'allowsAtMost' => xl('{0} allows at most {1} answers'),
    'maxLength' => xl('{0} exceeds the maximum length'),
    'minLength' => xl('{0} needs at least {1} characters'),
    'mimeType' => xl('{0} must be a file of type {1}'),
    'maxSize' => xl('{0} must be at most {1} bytes'),
    'regex' => xl('{0} does not match the required format'),
    'minValue' => xl('{0} must be at least {1}'),
    'maxValue' => xl('{0} must be at most {1}'),
    'maxDecimalPlaces' => xl('{0} allows at most {1} decimal places'),
    'moreInfo' => xl('More information'),
    'referenceTo' => xl('Reference to {0} (e.g. {1}/123)'),
    'referenceType' => xl('{0} must refer to {1}'),
    'signHere' => xl('Sign here'),
    'signaturePad' => xl('Signature pad: draw your signature'),
    'typeToSign' => xl('Or type your full name to sign'),
    'clear' => xl('Clear'),
    'signatureRequired' => xl('{0} needs a signature'),
    'thisForm' => xl('This form'),
    'expand' => xl('Expand'),
    'collapse' => xl('Collapse'),
];
?>
<link href="<?php echo attr($questionnaireStylesheet); ?>" media="screen" rel="stylesheet" />
<script src="<?php echo attr($questionnaireScript); ?>" data-openemr-version="<?php echo attr($questionnaireOpenemrVersion); ?>" data-openemr-strings="<?php echo attr(json_encode($questionnaireStrings, JSON_UNESCAPED_UNICODE) ?: '{}'); ?>"></script>
