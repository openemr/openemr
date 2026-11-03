<?php

/**
 * Bariatric Psych Eval form using forms api - modeled on interface/forms/phq9/common.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Barbara Rix <admin@starbirdrisingwellness.com>
 * @copyright Copyright (c) 2026 Barbara Rix <admin@starbirdrisingwellness.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../globals.php");

$srcdir = \OpenEMR\Core\OEGlobalsBag::getInstance()->getSrcDir();

require_once("bariatric_psych_eval.inc.php");
require_once("$srcdir/api.inc.php");

/**
 * @var string $srcdir
 * @var string $rootdir
 * @var string $viewmode
 * @var string $str_form_name
 * @var string $str_form_title
 * @var string $str_yes
 * @var string $str_no
 * @var array<string, string> $str_substance_use_options
 * @var array<string, string> $str_recommendation_options
 * @var array<string, array<string, array{0:string,1:string}>> $str_sections
 */

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Forms\FormActionBarSettings;
use OpenEMR\Common\Session\PatientSessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;

$obj = $viewmode == 'update' ? formFetch("form_bariatric_psych_eval", $_GET["id"]) : null;
$session = SessionWrapperFactory::getInstance()->getActiveSession();

$isNew = $obj === null;
$pid = PatientSessionUtil::getPid();
$priorEval = $isNew ? bpe_get_latest_prior_eval($pid) : null;
$copyForwardApplied = $isNew && $priorEval !== null && !empty($_GET['copy_from']);

$prefill = [];
if ($copyForwardApplied && $priorEval !== null) {
    $prefill = $priorEval;
    // The evaluation date should default to today, not silently carry the prior eval's date.
    unset($prefill['eval_date']);
}
?>
<html>
<head>
    <title><?php echo text($str_form_title); ?> </title>
    <?php Header::setupHeader(); ?>
    <style>
        .bpe-section { margin-top: 1.5rem; }
        .bpe-field { margin-bottom: 0.75rem; }
        .bpe-field label { font-weight: 600; display: block; margin-bottom: 0.2rem; }
        .bpe-field textarea { width: 100%; min-height: 4.5em; }
        .bpe-field input[type=text], .bpe-field input[type=date], .bpe-field select { width: 100%; max-width: 420px; }
    </style>
</head>
<body class="body_top">
    <div class="col-12">
        <h3><?php echo text($str_form_name); ?></h3>
        <?php if ($isNew && $priorEval !== null) { ?>
        <div class="bpe-section">
            <a class="btn btn-secondary" href="<?php echo $rootdir; ?>/forms/bariatric_psych_eval/new.php?copy_from=1"><?php echo xlt('Copy Forward from Last Evaluation'); ?></a>
            <?php if ($copyForwardApplied) { ?>
                <div class="bpe-copy-forward-note" style="color:#555;font-style:italic;margin-top:0.5rem;"><?php echo xlt('Copied forward from') . ' ' . text(oeFormatShortDate(substr((string) $priorEval['date'], 0, 10))) . '. ' . xlt('Review and edit before saving.'); ?></div>
            <?php } ?>
        </div>
        <?php } ?>
        <form method=post action="<?php echo $rootdir; ?>/forms/bariatric_psych_eval/save.php?mode=<?php echo attr_url($viewmode); ?>&id=<?php echo attr_url($_GET['id'] ?? 0); ?>" name="my_form">
            <input type="hidden" name="csrf_token_form" value="<?php echo CsrfUtils::collectCsrfToken(session: $session); ?>" />
            <?php foreach ($str_sections as $sectionTitle => $fields) { ?>
            <div class="bpe-section">
                <h4><?php echo text($sectionTitle); ?></h4>
                <?php foreach ($fields as $fieldName => $field) {
                    [$label, $type] = $field;
                    $value = $obj !== null ? ($obj[$fieldName] ?? '') : ($prefill[$fieldName] ?? '');
                    ?>
                <div class="bpe-field">
                    <label for="<?php echo attr($fieldName); ?>"><?php echo text($label); ?></label>
                    <?php if ($type === 'textarea') { ?>
                        <textarea id="<?php echo attr($fieldName); ?>" name="<?php echo attr($fieldName); ?>"><?php echo text($value); ?></textarea>
                    <?php } elseif ($type === 'date') { ?>
                        <input type="date" id="<?php echo attr($fieldName); ?>" name="<?php echo attr($fieldName); ?>" value="<?php echo attr($value !== '' ? $value : date('Y-m-d')); ?>" />
                    <?php } elseif ($type === 'yesno') { ?>
                        <select id="<?php echo attr($fieldName); ?>" name="<?php echo attr($fieldName); ?>">
                            <option value=""><?php echo text(xl('Please select')); ?></option>
                            <option value="yes" <?php echo $value === 'yes' ? 'selected' : ''; ?>><?php echo text($str_yes); ?></option>
                            <option value="no" <?php echo $value === 'no' ? 'selected' : ''; ?>><?php echo text($str_no); ?></option>
                        </select>
                    <?php } elseif ($type === 'select_substance') { ?>
                        <select id="<?php echo attr($fieldName); ?>" name="<?php echo attr($fieldName); ?>">
                            <?php foreach ($str_substance_use_options as $optValue => $optLabel) { ?>
                                <option value="<?php echo attr($optValue); ?>" <?php echo $value === $optValue ? 'selected' : ''; ?>><?php echo text($optLabel); ?></option>
                            <?php } ?>
                        </select>
                    <?php } elseif ($type === 'select_recommendation') { ?>
                        <select id="<?php echo attr($fieldName); ?>" name="<?php echo attr($fieldName); ?>">
                            <?php foreach ($str_recommendation_options as $optValue => $optLabel) { ?>
                                <option value="<?php echo attr($optValue); ?>" <?php echo $value === $optValue ? 'selected' : ''; ?>><?php echo text($optLabel); ?></option>
                            <?php } ?>
                        </select>
                    <?php } else { ?>
                        <input type="text" id="<?php echo attr($fieldName); ?>" name="<?php echo attr($fieldName); ?>" value="<?php echo attr($value); ?>" />
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
            <script>
                function nosave_exit() {
                    var conf = confirm(<?php echo js_escape(xl("Are you sure you'd like to quit without saving your answers?")); ?>);
                    if (conf) {
                        window.location.href = "<?php echo FormActionBarSettings::EXIT_URL; ?>";
                    }
                    return (conf);
                }
            </script>
            <div class="bpe-section">
                <button class="btn btn-primary btn-save my-2" type="submit" value="<?php echo xla('Save Form'); ?>"><?php echo xlt('Save Form'); ?></button>
                <button class="btn btn-secondary btn-cancel" type="button" value="<?php echo xla('Cancel'); ?>" onclick="top.restoreSession();return( nosave_exit());"><?php echo xlt('Cancel'); ?></button>
                <?php if ($obj) { ?>
                <a class="btn btn-secondary" href="<?php echo $rootdir; ?>/forms/bariatric_psych_eval/letter.php?id=<?php echo attr_url($_GET['id']); ?>" target="_blank"><?php echo xlt('Generate Clearance Letter'); ?></a>
                <?php } ?>
            </div>
        </form>
    </div>
    <?php
    formFooter();
    ?>
