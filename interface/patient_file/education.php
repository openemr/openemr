<?php

/**
 * This is called as a pop-up to display patient education materials.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2014 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$educationdir = "$OE_SITE_DIR/documents/education";

$codetype  = empty($_REQUEST['type'    ]) ? '' : $_REQUEST['type'    ];
$codevalue = empty($_REQUEST['code'    ]) ? '' : $_REQUEST['code'    ];
$language  = empty($_REQUEST['language']) ? '' : strtolower($_REQUEST['language']);
$source    = empty($_REQUEST['source'  ]) ? '' : $_REQUEST['source'  ];

$errmsg = '';

if (!empty($_POST['bn_submit'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    if ($source == 'MLP') {
        // MedlinePlus Connect Web Application.  See:
        // https://www.nlm.nih.gov/medlineplus/connect/application.html
        $url = 'https://connect.medlineplus.gov/application';
        // Set code type in URL.
        $url .= '?mainSearchCriteria.v.cs=';
        if ('ICD9'   == $codetype) {
            $url .= '2.16.840.1.113883.6.103';
        } elseif ('ICD10'  == $codetype) {
            $url .= '2.16.840.1.113883.6.90' ;
        } elseif ('SNOMED' == $codetype) {
            $url .= '2.16.840.1.113883.6.96' ;
        } elseif ('RXCUI'  == $codetype) {
            $url .= '2.16.840.1.113883.6.88' ;
        } elseif ('NDC'    == $codetype) {
            $url .= '2.16.840.1.113883.6.69' ;
        } elseif ('LOINC'  == $codetype) {
            $url .= '2.16.840.1.113883.6.1'  ;
        } else {
            die(xlt('Code type not recognized') . ': ' . text($codetype));
        }

        // Set code value in URL.
        $url .= '&mainSearchCriteria.v.c=' . urlencode($codevalue);
        // Set language in URL if relevant. MedlinePlus supports only English or Spanish.
        if ($language == 'es' || $language == 'spanish') {
            $url .= '&informationRecipient.languageCode.c=es';
        }

        echo "<html><body>"
        //."<script type=\"text/javascript\" src=\"". $webroot ."/interface/main/tabs/js/include_opener.js\"></script>"
        . "<script>\n";
        echo "document.location.href = " . js_escape($url) . ";\n";
        echo "</script></body></html>\n";

        exit();
    } else {
        $lang = 'en';
        if ($language == 'es' || $language == 'spanish') {
            $lang = 'es';
        }
        $filename = strtolower("{$codetype}_{$codevalue}_{$lang}.pdf");
        check_file_dir_name($filename);
        $filepath = "$educationdir/$filename";

        if (is_file($filepath)) {
            $fileData = file_get_contents($filepath);

            // Decrypt file, if applicable.
            $cryptoGen = new CryptoGen();
            if ($cryptoGen->cryptCheckStandard($fileData)) {
                $fileData = $cryptoGen->decryptStandard($fileData, null, 'database');
            }

            header('Content-Description: File Transfer');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            // attachment, not inline
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Content-Type: application/pdf");
            header("Content-Length: " . strlen($fileData));
            ob_clean();
            flush();
            echo $fileData;
            exit();
        } else {
            $errmsg = xl('There is no local content for this topic.');
        }
    }
}
?>
<html>
<head>

    <title><?php echo xlt('Education'); ?></title>

    <?php Header::setupHeader(); ?>

</head>
<body>
    <div class="container mt-3">
        <div class="row">
            <div class="col-12">
                <h3>
                    <?php
                    echo xlt('Educational materials for');
                    echo ' ' . text($codetype) . ' ';
                    echo xlt('code');
                    echo ' "' . text($codevalue) . '"';
                    if ($language) {
                        echo ' ' . xlt('with preferred language') . ' ' .
                        text(getListItemTitle('language', $_REQUEST['language']));
                    }
                    ?>
                </h3>
                <?php
                if ($errmsg) {
                    echo "<p class='text-danger'>" . text($errmsg) . "</p>\n";
                }
                ?>
            </div>
        </div>
        <div class='row'>
            <div class='col-12'>
                <form method='post' action='education.php' onsubmit='return top.restoreSession()'>
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                    <input type='hidden' name='type' value='<?php echo attr($codetype); ?>' />
                    <input type='hidden' name='code' value='<?php echo attr($codevalue); ?>' />
                    <input type='hidden' name='language' value='<?php echo attr($language); ?>' />
                    <div class='form-group'>
                        <label for="source"><?php echo xlt('Select source'); ?></label>
                        <select name='source' id='source' class='form-control'>
                            <option value='MLP'><?php echo xlt('MedlinePlus Connect'); ?></option>
                            <option value='Local'><?php echo xlt('Local Content'); ?></option>
                        </select>
                    </div>
                    <div class='form-group'>
                        <div class='btn-group' role='group'>
                            <button type='submit' class='btn btn-primary btn-search' name='bn_submit' value='bn_submit'>
                                <?php echo xlt('Submit'); ?>
                            </button>
                            <button type='button' class='btn btn-secondary btn-cancel' onclick='window.close()'>
                                <?php echo xlt('Cancel'); ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
