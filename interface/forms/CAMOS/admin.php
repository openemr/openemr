<?php

/**
 * CAMOS admin.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Mark Leeds <drleeds@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (C) 2006-2009 Mark Leeds <drleeds@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

require_once('../../globals.php');

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;

$session = SessionWrapperFactory::getInstance()->getActiveSession();

// Check authorization.
if (!AclMain::aclCheckCore('admin', 'super')) {
    AccessDeniedHelper::denyWithTemplate("ACL check failed for admin/super: CAMOS admin", xl("admin"));
}

// Cache escaped table names to avoid repeated SHOW TABLES lookups.
// escape_table_name() on literals is needed for case-insensitive matching
// on MySQL installs where the actual table case differs from the code.
$tbl_camos_category = escape_table_name("form_CAMOS_category");
$tbl_camos_subcategory = escape_table_name("form_CAMOS_subcategory");
$tbl_camos_item = escape_table_name("form_CAMOS_item");

if (filter_input(INPUT_POST, 'export')) {
    if (!CsrfUtils::verifyCsrfToken(filter_input(INPUT_POST, 'csrf_token_form') ?? '', session: $session)) {
        CsrfUtils::csrfNotVerified();
    }

    $temp = tmpfile();
    if ($temp) {
        try {
            $statement1 = QueryUtils::sqlStatementThrowException("SELECT id, category FROM {$tbl_camos_category}");
            /** @var array{id: int, category: ?string} $result1 */
            while ($result1 = QueryUtils::fetchArrayFromResultSet($statement1)) {
                fwrite($temp, "<category>" . htmlspecialchars($result1['category'] ?? '', ENT_XML1) . "</category>\n");
                $statement2 = QueryUtils::sqlStatementThrowException("SELECT id, subcategory FROM {$tbl_camos_subcategory} WHERE category_id = ?", [$result1['id']]);
                /** @var array{id: int, subcategory: ?string} $result2 */
                while ($result2 = QueryUtils::fetchArrayFromResultSet($statement2)) {
                    fwrite($temp, "<subcategory>" . htmlspecialchars($result2['subcategory'] ?? '', ENT_XML1) . "</subcategory>\n");
                    $statement3 = QueryUtils::sqlStatementThrowException("SELECT item, content FROM {$tbl_camos_item} WHERE subcategory_id = ?", [$result2['id']]);
                    /** @var array{item: ?string, content: ?string} $result3 */
                    while ($result3 = QueryUtils::fetchArrayFromResultSet($statement3)) {
                        fwrite($temp, "<item>" . htmlspecialchars($result3['item'] ?? '', ENT_XML1) . "</item>\n");
                        $content = is_string($result3['content']) ? $result3['content'] : '';
                        $tmp = preg_replace(["/\n/", "/\r/"], ["\\\\n", "\\\\r"], $content) ?? '';
                        fwrite($temp, "<content>" . htmlspecialchars($tmp, ENT_XML1) . "</content>\n");
                    }
                }
            }

            rewind($temp);
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-Type: text/plain");
            header('Content-Disposition: attachment; filename="CAMOS_export.txt"');
            fpassthru($temp);
        } finally {
            fclose($temp);
        }

        exit;
    } else {
        echo "<h1>" . xlt("failed") . "</h1>";
    }
}

if (filter_input(INPUT_POST, 'import')) {
    if (!CsrfUtils::verifyCsrfToken(filter_input(INPUT_POST, 'csrf_token_form') ?? '', session: $session)) {
        CsrfUtils::csrfNotVerified();
    }

    $fname = '';
    $userfile = $_FILES['userfile'] ?? null;
    if (is_array($userfile) && is_string($userfile['tmp_name'])) {
        $fname = $userfile['tmp_name'];
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
            if (!is_string($buffer)) {
                continue;
            }
            if (preg_match('/<category>(.*?)<\/category>/', $buffer, $matches)) {
                $category = trim(html_entity_decode($matches[1], ENT_XML1));
                $statement = QueryUtils::sqlStatementThrowException("SELECT id FROM {$tbl_camos_category} WHERE category LIKE ?", [$category]);
                /** @var array{id: int} $result */
                if ($result = QueryUtils::fetchArrayFromResultSet($statement)) {
                    $category_id = $result['id'];
                } else {
                    QueryUtils::sqlStatementThrowException("INSERT INTO {$tbl_camos_category} (user, category) VALUES (?, ?)", [$session->get('authUser'), $category]);
                    $statement = QueryUtils::sqlStatementThrowException("SELECT id FROM {$tbl_camos_category} WHERE category LIKE ?", [$category]);
                    if ($result = QueryUtils::fetchArrayFromResultSet($statement)) {
                        $category_id = $result['id'];
                    }
                }
            }

            if (preg_match('/<subcategory>(.*?)<\/subcategory>/', $buffer, $matches)) {
                $subcategory = trim(html_entity_decode($matches[1], ENT_XML1));
                $statement = QueryUtils::sqlStatementThrowException("SELECT id FROM {$tbl_camos_subcategory} WHERE subcategory LIKE ? AND category_id = ?", [$subcategory, $category_id]);
                /** @var array{id: int} $result */
                if ($result = QueryUtils::fetchArrayFromResultSet($statement)) {
                    $subcategory_id = $result['id'];
                } else {
                    QueryUtils::sqlStatementThrowException("INSERT INTO {$tbl_camos_subcategory} (user, subcategory, category_id) VALUES (?, ?, ?)", [$session->get('authUser'), $subcategory, $category_id]);
                    $statement = QueryUtils::sqlStatementThrowException("SELECT id FROM {$tbl_camos_subcategory} WHERE subcategory LIKE ? AND category_id = ?", [$subcategory, $category_id]);
                    if ($result = QueryUtils::fetchArrayFromResultSet($statement)) {
                        $subcategory_id = $result['id'];
                    }
                }
            }

            if (
                (preg_match('/<(item)>(.*?)<\/item>/', $buffer, $matches)) ||
                (preg_match('/<(content)>(.*?)<\/content>/s', $buffer, $matches))
            ) {
                $mode = $matches[1];
                $value = trim(html_entity_decode($matches[2], ENT_XML1));
                $insert_value = '';
                if ($mode == 'item') {
                    $postfix = 0;
                    $statement = QueryUtils::sqlStatementThrowException("SELECT id FROM {$tbl_camos_item} WHERE item LIKE ? AND subcategory_id = ?", [$value, $subcategory_id]);
                    /** @var array{id: int} $result */
                    if ($result = QueryUtils::fetchArrayFromResultSet($statement)) {//let's count until we find a number available
                        $postfix = 1;
                        $inserted_duplicate = false;
                        while ($inserted_duplicate === false) {
                            $insert_value = $value . "_" . $postfix;
                            $inner_statement = QueryUtils::sqlStatementThrowException("SELECT id FROM {$tbl_camos_item} WHERE item LIKE ? AND subcategory_id = ?", [$insert_value, $subcategory_id]);
                            if (!($inner_result = QueryUtils::fetchArrayFromResultSet($inner_statement))) {//doesn't exist
                                QueryUtils::sqlStatementThrowException("INSERT INTO {$tbl_camos_item} (user, item, subcategory_id) VALUES (?, ?, ?)", [$session->get('authUser'), $insert_value, $subcategory_id]);
                                $inserted_duplicate = true;
                            } else {
                                $postfix++;
                            }
                        }
                    } else {
                        QueryUtils::sqlStatementThrowException("INSERT INTO {$tbl_camos_item} (user, item, subcategory_id) VALUES (?, ?, ?)", [$session->get('authUser'), $value, $subcategory_id]);
                    }

                    if ($postfix == 0) {
                        $insert_value = $value;
                    }

                    $statement = QueryUtils::sqlStatementThrowException("SELECT id FROM {$tbl_camos_item} WHERE item LIKE ? AND subcategory_id = ?", [$insert_value, $subcategory_id]);
                    /** @var array{id: int} $result */
                    if ($result = QueryUtils::fetchArrayFromResultSet($statement)) {
                        $item_id = $result['id'];
                    }
                } elseif ($mode == 'content') {
                    $value = str_replace(["\\n", "\\r"], ["\n", "\r"], $value);
                    QueryUtils::sqlStatementThrowException("UPDATE {$tbl_camos_item} SET content = ? WHERE id = ?", [$value, $item_id]);
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
<input type="hidden" name="csrf_token_form" value="<?php echo CsrfUtils::collectCsrfToken(session: $session); ?>" />
<input type="hidden" name="MAX_FILE_SIZE" value="12000000" />
<?php echo xlt('Send this file'); ?>: <input type="file" name="userfile"/>
<input type="submit" name="import" value='<?php echo xla("Import"); ?>'/>
<input type="submit" name="export" value='<?php echo xla("Export"); ?>'/>
</form>
</body>
</html>
