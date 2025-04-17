<?php

/**
 * CAMOS admin.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Mark Leeds <drleeds@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (C) 2006-2009 Mark Leeds <drleeds@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../../globals.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;

?>
<?php
// Check authorization.
if (!AclMain::aclCheckCore('admin', 'super')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("admin")]);
    exit;
}


if ($_POST['export']) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $temp = tmpfile();
    if ($temp === false) {
        echo "<h1>" . xlt("failed") . "</h1>";
    } else {
        $query1 = "select id, category from " . mitigateSqlTableUpperCase("form_CAMOS_category");
        $statement1 = sqlStatement($query1);
        while ($result1 = sqlFetchArray($statement1)) {
                $tmp = $result1['category'];
                $tmp = "<category>$tmp</category>" . "\n";
                fwrite($temp, $tmp);
                $query2 = "select id,subcategory from " . mitigateSqlTableUpperCase("form_CAMOS_subcategory") . " where category_id=?";
                $statement2 = sqlStatement($query2, array($result1['id']));
            while ($result2 = sqlFetchArray($statement2)) {
                $tmp = $result2['subcategory'];
                $tmp = "<subcategory>$tmp</subcategory>" . "\n";
                fwrite($temp, $tmp);
                $query3 = "select item, content from " . mitigateSqlTableUpperCase("form_CAMOS_item") . " where subcategory_id=?";
                $statement3 = sqlStatement($query3, array($result2['id']));
                while ($result3 = sqlFetchArray($statement3)) {
                    $tmp = $result3['item'];
                    $tmp = "<item>$tmp</item>" . "\n";
                    fwrite($temp, $tmp);
                    $tmp = preg_replace(array("/\n/","/\r/"), array("\\\\n","\\\\r"), $result3['content']);
                    $tmp = "<content>$tmp</content>" . "\n";
                    fwrite($temp, $tmp);
                }
            }
        }

        rewind($temp);
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: text/plain");
            header("Content-Disposition: attachment; filename=\"CAMOS_export.txt\"");

        fpassthru($temp);
        fclose($temp);
    }
}

if ($_POST['import']) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    ?>
    <?php
    $fname = '';
    foreach ($_FILES as $file) {
        $fname = $file['tmp_name'];
    }

    $handle = @fopen($fname, "r");
    if ($handle === false) {
        echo "<h1>" . xlt('Error opening uploaded file for reading') . "</h1>";
    } else {
        $category = '';
        $category_id = 0;
        $subcategory = '';
        $subcategory_id = 0;
        $item = '';
        $item_id = 0;
        $content = '';
        while (!feof($handle)) {
            $buffer = fgets($handle);
            if (preg_match('/<category>(.*?)<\/category>/', $buffer, $matches)) {
                $category = trim($matches[1]); //trim in case someone edited by hand and added spaces
                $statement = sqlStatement("select id from " . mitigateSqlTableUpperCase("form_CAMOS_category") . " where category like ?", array($category));
                if ($result = sqlFetchArray($statement)) {
                    $category_id = $result['id'];
                } else {
                    $query = "INSERT INTO " . mitigateSqlTableUpperCase("form_CAMOS_category") . " (user, category) " .
                    "values (?, ?)";
                    sqlStatement($query, array($_SESSION['authUser'], $category));
                    $statement = sqlStatement("select id from " . mitigateSqlTableUpperCase("form_CAMOS_category") . " where category like ?", array($category));
                    if ($result = sqlFetchArray($statement)) {
                        $category_id = $result['id'];
                    }
                }
            }

            if (preg_match('/<subcategory>(.*?)<\/subcategory>/', $buffer, $matches)) {
                $subcategory = trim($matches[1]);
                $statement = sqlStatement("select id from " . mitigateSqlTableUpperCase("form_CAMOS_subcategory") . " where subcategory " .
                "like ? and category_id = ?", array($subcategory, $category_id));
                if ($result = sqlFetchArray($statement)) {
                    $subcategory_id = $result['id'];
                } else {
                    $query = "INSERT INTO " . mitigateSqlTableUpperCase("form_CAMOS_subcategory") . " (user, subcategory, category_id) " .
                    "values (?, ?, ?)";
                    sqlStatement($query, array($_SESSION['authUser'], $subcategory, $category_id));
                    $statement = sqlStatement("select id from " . mitigateSqlTableUpperCase("form_CAMOS_subcategory") . " where subcategory " .
                    "like ? and category_id = ?", array($subcategory, $category_id));
                    if ($result = sqlFetchArray($statement)) {
                        $subcategory_id = $result['id'];
                    }
                }
            }

            if (
                (preg_match('/<(item)>(.*?)<\/item>/', $buffer, $matches)) ||
                (preg_match('/<(content)>(.*?)<\/content>/s', $buffer, $matches))
            ) {
                $mode = $matches[1];
                $value = trim($matches[2]);
                $insert_value = '';
                if ($mode == 'item') {
                    $postfix = 0;
                    $statement = sqlStatement("select id from " . mitigateSqlTableUpperCase("form_CAMOS_item") . " where item like ? " .
                    "and subcategory_id = ?", array($value, $subcategory_id));
                    if ($result = sqlFetchArray($statement)) {//let's count until we find a number available
                        $postfix = 1;
                        $inserted_duplicate = false;
                        while ($inserted_duplicate === false) {
                            $insert_value = $value . "_" . $postfix;
                            $inner_statement = sqlStatement("select id from " . mitigateSqlTableUpperCase("form_CAMOS_item") . " " .
                                "where item like ? " .
                                "and subcategory_id = ?", array($insert_value, $subcategory_id));
                            if (!($inner_result = sqlFetchArray($inner_statement))) {//doesn't exist
                                    $inner_query = "INSERT INTO " . mitigateSqlTableUpperCase("form_CAMOS_item") . " (user, item, subcategory_id) " .
                                    "values (?, ?, ?)";
                                    sqlStatement($inner_query, array($_SESSION['authUser'], $insert_value, $subcategory_id));
                                    $inserted_duplicate = true;
                            } else {
                                $postfix++;
                            }
                        }
                    } else {
                        $query = "INSERT INTO " . mitigateSqlTableUpperCase("form_CAMOS_item") . " (user, item, subcategory_id) " .
                        "values (?, ?, ?)";
                        sqlStatement($query, array($_SESSION['authUser'], $value, $subcategory_id));
                    }

                    if ($postfix == 0) {
                        $insert_value = $value;
                    }

                    $statement = sqlStatement("select id from " . mitigateSqlTableUpperCase("form_CAMOS_item") . " where item like ? " .
                    "and subcategory_id = ?", array($insert_value, $subcategory_id));
                    if ($result = sqlFetchArray($statement)) {
                        $item_id = $result['id'];
                    }
                } elseif ($mode == 'content') {
                    $statement = sqlStatement("select content from " . mitigateSqlTableUpperCase("form_CAMOS_item") . " where id = ?", array($item_id));
                    if ($result = sqlFetchArray($statement)) {
                        //$content = "/*old*/\n\n".$result['content']."\n\n/*new*/\n\n$value";
                        $content = $value;
                    } else {
                        $content = $value;
                    }

                    $query = "UPDATE " . mitigateSqlTableUpperCase("form_CAMOS_item") . " set content = ? where id = ?";
                    sqlStatement($query, array($content, $item_id));
                }
            }
        }

        fclose($handle);
    }
}
?>
<html>
<head>
<title>
admin
</title>
</head>
<body>
<p>
<?php echo xlt("Click 'export' to export your Category, Subcategory, Item, Content data to a text file. Any resemblance of this file to an XML file is purely coincidental. The opening and closing tags must be on the same line, they must be lowercase with no spaces. To import, browse for a file and click 'import'. If the data is completely different, it will merge with your existing data. If there are similar item names, The old one will be kept and the new one saved with a number added to the end."); ?>
<?php echo xlt("This feature is very experimental and not fully tested. Use at your own risk!"); ?>
</p>
<form enctype="multipart/form-data" method="POST">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<input type="hidden" name="MAX_FILE_SIZE" value="12000000" />
<?php echo xlt('Send this file'); ?>: <input type="file" name="userfile"/>
<input type="submit" name="import" value='<?php echo xla("Import"); ?>'/>
<input type="submit" name="export" value='<?php echo xla("Export"); ?>'/>
</form>
</body>
</html>
