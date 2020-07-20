<?php

/**
 * Open websearch for patient education materials
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Tony McCormick <tony@mi-squared.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @copyright Copyright (C) 2011 Tony McCormick <tony@mi-squared.com>
 * @copyright Copyright (C) 2011-2018 Brady Miller   <brady.g.miller@gmail.com>
 * @copyright Copyright (C) 2017 Roberto Vasquez <robertogagliotta@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE CNU General Public License 3
 *
 */

//Include required scripts/libraries
require_once("../globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

// Reference website links
$websites = array(
    'Medline'   => 'http://vsearch.nlm.nih.gov/vivisimo/cgi-bin/query-meta?v%3Aproject=medlineplus&query=[%]&x=12&y=15',
    'eMedicine' => 'http://search.medscape.com/reference-search?newSearchHeader=1&queryText=[%]',
    'WebMD'     => 'http://www.webmd.com/search/search_results/default.aspx?query=[%]&sourceType=undefined'
);

// Collect variables
$form_lookup_at = (isset($_POST['form_lookup_at'])) ? $_POST['form_lookup_at'] : '';
$form_diagnosis = (isset($_POST['form_diagnosis'])) ? $_POST['form_diagnosis'] : '';
?>

<html>
<head>
    <?php Header::setupHeader(); ?>
    <title><?php echo xlt('Web Search'); ?> - <?php echo xlt('Patient Education Materials'); ?></title>
    <script>
        function searchResultsPopup(search_term,link)
        {
            link_formatted = link.replace("[%]",encodeURIComponent(search_term));
            top.restoreSession();
            window.open(link_formatted);
        }
    </script>
</head>

<body class="body_top" onload="document.forms[0].form_diagnosis.focus()">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h2><?php echo  xlt('Web Search'); ?> - <?php echo xlt('Patient Education Materials'); ?></h2>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <form method='post' action='patient_edu_web_lookup.php' id='theform' class='form-horizontal' onsubmit='return top.restoreSession()'>
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                    <div class="form-group">
                        <label for='form_lookup_at' class='control-label col-sm-2'><?php echo xlt('Patient Resource'); ?></label>
                        <div class='col-sm-12'>
                            <select name='form_lookup_at' id='form_lookup_at'  class='form-control'>
                                <?php
                                foreach ($websites as $key => $value) {
                                    echo "    <option value='" . attr($key) . "'";
                                    if ($key == $form_lookup_at) {
                                        echo ' selected';
                                    }

                                    echo ">" .  text($key) . "</option>\n";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for='form_diagnosis' class='control-label col-sm-2'><?php echo xlt('Search'); ?></label>
                        <div class='col-sm-12'>
                            <input type='text' name='form_diagnosis' id='form_diagnosis' class='form-control' aria-describedby='searchHelpBox'
                                value='<?php echo attr($form_diagnosis); ?>' title='<?php echo xla('Search Text'); ?>'>
                            <span id="searchHelpBox" class="help-block">
                                <?php echo xlt('Please input search criteria above, and click Submit to view results. (Results will be displayed in a pop up window)'); ?>
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class='col-sm-12'>
                            <div class="btn-group" role="group">
                                <button type='submit' class='btn btn-secondary btn-search'><?php echo xlt("Submit"); ?></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php if (!empty($form_diagnosis) && !empty($form_lookup_at)) { ?>
        <script>
            searchResultsPopup(<?php echo js_escape($form_diagnosis); ?>,<?php echo js_escape($websites[$form_lookup_at]) ?>);
        </script>
    <?php } ?>
</body>
</html>
