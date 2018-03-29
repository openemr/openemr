<?php
/**
 * personalize.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Eldho Chacko <eldho@zhservices.com>
 * @author    Jacob T Paul <jacob@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../interface/globals.php");
$list_id = $_REQUEST['list_id'] ? $_REQUEST['list_id'] : $_REQUEST['filter_context'];

use OpenEMR\Core\Header;

function Delete_Rows($id)
{
    sqlStatement("DELETE FROM template_users WHERE tu_template_id=? AND tu_user_id=?", array($id, $_SESSION['authId']));
}

function Insert_Rows($id, $order = "")
{
    sqlStatement("REPLACE INTO template_users (tu_template_id,tu_user_id,tu_template_order) VALUES (?,?,?)", array($id, $_SESSION['authId'], $order));
}

if (isset($_REQUEST['submitform']) && $_REQUEST['submitform'] == 'save') {
    $topersonalized = $_REQUEST['topersonalized'];
    $personalized = $_REQUEST['personalized'];
    foreach ($topersonalized as $key => $value) {
        $arr = explode("|", $value);
        $res = sqlStatement("SELECT * FROM template_users WHERE tu_template_id=? AND tu_user_id=?", array($arr[0], $_SESSION['authId']));
        if (sqlNumRows($res)) {
            Delete_Rows($arr[0]);
            $qry = sqlStatement("SELECT * FROM customlists WHERE cl_list_id=? AND cl_deleted=0", array($arr[0]));
            while ($row = sqlFetchArray($qry)) {
                Delete_Rows($row['cl_list_slno']);
            }
        }
    }

    //Add new Categories
    foreach ($personalized as $key => $value) {
        $arr = explode("|", $value);
        if ($arr[1]) {
            $res = sqlStatement("SELECT * FROM template_users WHERE tu_template_id=? AND tu_user_id=?", array($arr[0], $_SESSION['authId']));
            Insert_Rows($arr[0]);
            $qry = sqlStatement("SELECT * FROM customlists WHERE cl_list_id=? AND cl_deleted=0", array($arr[0]));
            while ($row = sqlFetchArray($qry)) {
                $qryTU = sqlStatement("SELECT * FROM template_users WHERE tu_template_id=? AND tu_user_id=?", array($row['cl_list_slno'], $arr[1]));
                while ($rowTU = sqlFetchArray($qryTU)) {
                    Insert_Rows($rowTU['tu_template_id'], $rowTU['tu_template_order']);
                }
            }
        } else {
            Insert_Rows($arr[0]);
            $qry = sqlStatement("SELECT * FROM customlists WHERE cl_list_id=? AND cl_deleted=0", array($arr[0]));
            while ($row = sqlFetchArray($qry)) {
                Insert_Rows($row['cl_list_slno'], $row['cl_order']);
            }
        }
    }
}
?>
<html>
<head>

    <?php Header::setupHeader(['common', 'opener', 'jquery-ui',]); ?>

    <script type="text/javascript">

        function refreshme() {
            top.restoreSession();
            document.location.reload();
        }

        $(document).ready(function () {

            tabbify();

            $(".iframe_small").on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                dlgopen('', '', 400, 170, '', '', {
                    buttons: [
                        {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
                    ],
                    onClosed: 'refreshme',
                    type: 'iframe',
                    url: $(this).attr('href')
                });
            });

            $(".iframe_medium").on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                dlgopen('', '', 450, 250, '', '', {
                    buttons: [
                        {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
                    ],
                    onClosed: 'refreshme',
                    type: 'iframe',
                    url: $(this).attr('href')
                });
            });

            $(".iframe_abvmedium").on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                dlgopen('', '', 700, 500, '', '', {
                    buttons: [
                        {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
                    ],
                    onClosed: 'refreshme',
                    type: 'iframe',
                    url: $(this).attr('href')
                });
            });

        });

        function check_user_category(form, selectFrom, selectedList) {
            top.restoreSession();
            var total_selected = form.elements[selectedList].length - 1;
            var msg = '';
            for (total_selected; total_selected >= 0; total_selected--) {
                if (form.elements[selectedList].options[total_selected].selected) {
                    if (document.getElementById('filter_users').value) {
                        $.ajax({
                            type: "POST",
                            url: "ajax_code.php",
                            dataType: "html",
                            data: {
                                item: form.elements[selectedList].options[total_selected].value,
                                list_id: document.getElementById('filter_users').value,
                                source: "check_item"
                            },
                            async: false,
                            success: function (thedata) {
                                if (thedata == 'OK') {
                                    total_clients = form.elements[selectFrom].length;
                                    opt = new Option(form.elements[selectedList].options[total_selected].text, form.elements[selectedList].options[total_selected].value);
                                    form.elements[selectFrom].options[total_clients] = opt;
                                    form.elements[selectedList].options[total_selected] = null;
                                }
                                else {
                                    msg += form.elements[selectedList].options[total_selected].text + "\n";
                                }
                            },
                            error: function () {
                                alert("fail");
                            }
                        });
                    }
                    else {
                        total_clients = form.elements[selectFrom].length;
                        opt = new Option(form.elements[selectedList].options[total_selected].text, form.elements[selectedList].options[total_selected].value);
                        form.elements[selectFrom].options[total_clients] = opt;
                        form.elements[selectedList].options[total_selected] = null;
                    }
                }
            }
            jsub_sortNow(form.elements[selectFrom]);
            if (msg != '') {
                if (confirm("<?php echo addslashes(xl('The following categories will be removed from your category List'));?> \n" + msg + "\n <?php echo addslashes(xl('Do you want to continue?'));?>")) {
                    remove_selected(form, selectedList);
                }
            }
            return;
        }

        function remove_selected(form, selectedList) {
            top.restoreSession();
            var total_selected = form.elements[selectedList].length - 1;
            for (total_selected; total_selected >= 0; total_selected--) {
                if (form.elements[selectedList].options[total_selected].selected) {
                    form.elements[selectedList].options[total_selected] = null;
                }
            }
            jsub_sortNow(form.elements[selectFrom]);
            return;
        }

        function all_selected(selectedList) {
            top.restoreSession();
            var total_selected = document.getElementById(selectedList).length - 1;
            for (total_selected; total_selected >= 0; total_selected--) {
                document.getElementById(selectedList).options[total_selected].selected = true;
            }
        }

        function all_deselected(selectedList) {
            top.restoreSession();
            var total_selected = document.getElementById(selectedList).length - 1;
            for (total_selected; total_selected >= 0; total_selected--) {
                document.getElementById(selectedList).options[total_selected].selected = false;
            }
        }

        function jsub_selected(form, selectFrom, selectedList) {
            top.restoreSession();
            var total_selected = form.elements[selectedList].length - 1;
            for (total_selected; total_selected >= 0; total_selected--) {
                if (form.elements[selectedList].options[total_selected].selected) {
                    total_clients = form.elements[selectFrom].length;
                    opt = new Option(form.elements[selectedList].options[total_selected].text, form.elements[selectedList].options[total_selected].value);
                    form.elements[selectFrom].options[total_clients] = opt;
                    form.elements[selectedList].options[total_selected] = null;
                }
            }
            jsub_sortNow(form.elements[selectFrom]);
            return;
        }

        function display_category_item(form, selectedList) {
            top.restoreSession();
            var len = 0;
            var selectedval = '';
            var total_selected = form.elements[selectedList].length - 1;
            for (total_selected; total_selected >= 0; total_selected--) {
                if (form.elements[selectedList].options[total_selected].selected) {
                    selectedval = form.elements[selectedList].options[total_selected].value;
                    len++;
                }
            }
            if (len > 1) {
                document.getElementById('itemdiv').style.display = 'none';
            }
            else if (len == 1) {
                document.getElementById('itemdiv').style.display = '';
                $.ajax({
                    type: "POST",
                    url: "ajax_code.php",
                    dataType: "html",
                    data: {
                        list_id: selectedval,
                        source: "item_show"
                    },
                    async: false,
                    success: function (thedata) {
                        document.getElementById('itemdiv').innerHTML = thedata;
                    },
                    error: function () {
                        alert("fail");
                    }
                });
                return;
            }
        }

        function jsub_sortNow(obj) {
            top.restoreSession();
            var len = obj.length - 1;
            var text = new Array();
            var values = new Array();
            var sortarr = new Array();
            for (var i = len; i >= 0; i--) {
                text[i] = obj.options[i].text;
                values[i] = obj.options[i].value;
                sortarr[i] = obj.options[i].text;
            }
            sortarr.sort();
            obj.length = 0;
            for (i = 0; i <= len; i++) {
                for (j = 0; j <= len; j++) {
                    if (sortarr[i] == text[j]) {
                        break;
                    }
                }
                opt = new Option(text[j], values[j]);
                obj.options[i] = opt;
            }
        }

        function personalize_save() {
            top.restoreSession();
            document.getElementById('submitform').value = 'save';
            all_selected('topersonalized');
            all_selected('personalized');
            document.myform.submit();
        }
    </script>
</head>
<body class="body_top">
<form name="myform" method="post" onsubmit="top.restoreSession();">
    <fieldset>
        <legend><span class="text"><?php echo htmlspecialchars(xl('Filter'), ENT_QUOTES); ?></span></legend>
        <table>
            <tr class="text">
                <td><?php echo htmlspecialchars(xl('Context'), ENT_QUOTES); ?></td>
                <td>
                    <select name='filter_context' id='filter_context' onchange='javascript:document.myform.submit();'>
                        <option value=''><?php echo htmlspecialchars(xl('Select a Context'), ENT_QUOTES); ?></option>
                        <?php
                        $context_sql = "SELECT * FROM customlists WHERE cl_list_type=2 AND cl_deleted=0";
                        $context_res = sqlStatement($context_sql);
                        while ($context_row = sqlFetchArray($context_res)) {
                            echo "<option value='" . htmlspecialchars($context_row['cl_list_slno'], ENT_QUOTES) . "' ";
                            echo ($_REQUEST['filter_context'] == $context_row['cl_list_slno']) ? 'selected' : '';
                            echo ">" . htmlspecialchars($context_row['cl_list_item_long'], ENT_QUOTES) . "</option>";
                        }
                        ?>
                    </select>
                </td>
                <td><?php echo htmlspecialchars(xl('Users'), ENT_QUOTES); ?></td>
                <td>
                    <select name='filter_users' id='filter_users' onchange='javascript:document.myform.submit();'>
                        <option value=''><?php echo htmlspecialchars(xl('Select a User'), ENT_QUOTES); ?></option>
                        <?php
                        $user_sql = "SELECT DISTINCT(tu.tu_user_id),u.fname,u.lname FROM template_users AS tu LEFT OUTER JOIN users AS u ON tu.tu_user_id=u.id WHERE tu.tu_user_id!=?";
                        $user_res = sqlStatement($user_sql, array($_SESSION['authId']));
                        while ($user_row = sqlFetchArray($user_res)) {
                            echo "<option value='" . htmlspecialchars($user_row['tu_user_id'], ENT_QUOTES) . "' ";
                            echo ($_REQUEST['filter_users'] == $user_row['tu_user_id']) ? 'selected' : '';
                            echo ">" . htmlspecialchars($user_row['fname'] . " " . $user_row['lname'], ENT_QUOTES) . "</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>
        </table>
    </fieldset>
    <table align="center" width="100%">
        <tr class="text">
            <td colspan="3">
                <a href=# class="css_button"
                   onclick="top.restoreSession();personalize_save()"><span><?php echo htmlspecialchars(xl('Save'), ENT_QUOTES); ?></span></a>
                <?php
                if (acl_check('nationnotes', 'nn_configure')) {
                    ?>
                    <a href="delete_category.php" id="share_link" class="iframe_medium css_button"
                       onclick="top.restoreSession();"><span><?php echo htmlspecialchars(xl('Delete Category'), ENT_QUOTES); ?></span></a>
                    <?php
                }
                ?>
                <?php
                if (acl_check('nationnotes', 'nn_configure')) {
                    ?>
                    <a href="add_template.php?list_id=<?php echo attr($_REQUEST['list_id']); ?>"
                       onclick="top.restoreSession();" class="iframe_small css_button"
                       title="<?php echo htmlspecialchars(xl('Add Category'), ENT_QUOTES); ?>"><span><?php echo htmlspecialchars(xl('Add Category'), ENT_QUOTES); ?></span></a>
                    <?php
                }
                ?>
                <?php
                if (acl_check('nationnotes', 'nn_configure')) {
                    ?>
                    <a href="add_context.php" class="iframe_medium css_button" onclick="top.restoreSession();"
                       title="<?php echo htmlspecialchars(xl('Add Context'), ENT_QUOTES); ?>"><span><?php echo htmlspecialchars(xl('Add Context'), ENT_QUOTES); ?></span></a>
                    <?php
                }
                ?>
        <tr class="text">
            <th><?php echo htmlspecialchars(xl('Available categories'), ENT_QUOTES); ?></th>
            <th>&nbsp;</th>
            <?php
            $user = sqlQuery("SELECT * FROM users WHERE id=?", array($_SESSION['authId']));
            ?>
            <th><?php echo htmlspecialchars(xl('Categories for') . " " . $user['fname'] . " " . $user['lname'], ENT_QUOTES); ?></th>
        </tr>
        <tr class="text">
            <td align=right>
                <select multiple name="topersonalized[]" id="topersonalized" size="6" style="width:220px"
                        onchange="display_category_item(document.myform,'topersonalized');">
                    <?php
                    $where = '';
                    $join = '';
                    $arval = array($_SESSION['authId']);
                    $arval1 = array($_REQUEST['filter_users'], $_SESSION['authId']);
                    if ($_REQUEST['filter_context']) {
                        $where .= " AND cl_list_id=?";
                        array_push($arval, $_REQUEST['filter_context']);
                        array_push($arval1, $_REQUEST['filter_context']);
                    }
                    $sql = "SELECT * FROM template_users AS tu LEFT OUTER JOIN customlists AS c ON tu.tu_template_id=c.cl_list_slno
                                    WHERE cl_list_type=3 AND cl_deleted=0 AND tu.tu_template_id NOT IN (SELECT tu_template_id FROM template_users AS tuser WHERE
                                    tu_user_id=?) " .
                        $where .
                        " ORDER BY cl_list_id,tu_user_id,cl_list_item_long";
                    $resTemplates = sqlStatement($sql, $arval);
                    if ($_REQUEST['filter_users']) {
                        $sql = " SELECT * FROM template_users AS tu LEFT OUTER JOIN customlists AS c ON tu.tu_template_id=c.cl_list_slno WHERE
                                tu.tu_user_id=? AND c.cl_list_type=3 AND cl_deleted=0 AND tu.tu_template_id NOT IN
                                (SELECT tu_template_id FROM template_users AS tuser WHERE tu_user_id=?)" .
                            $where .
                            "ORDER BY cl_list_id,tu_user_id,c.cl_list_item_long";
                        $resTemplates = sqlStatement($sql, $arval1);
                    }
                    while ($rowTemplates = sqlFetchArray($resTemplates)) {
                        $cntxt = '';
                        if (!$_REQUEST['filter_context']) {
                            $context = sqlQuery("SELECT * FROM customlists WHERE cl_list_slno=?", array($rowTemplates['cl_list_id']));
                            $cntxt .= $context['cl_list_item_long'] . "->";
                        }
                        if (!$_REQUEST['filter_users']) {
                            $context = sqlQuery("SELECT * FROM users WHERE id=?", array($rowTemplates['tu_user_id']));
                            $cntxt .= $context['username'] . "->";
                        }
                        echo "<option value='" . htmlspecialchars($rowTemplates['cl_list_slno'] . "|" . $rowTemplates['tu_user_id'], ENT_QUOTES) . "'>" . htmlspecialchars($cntxt . $rowTemplates['cl_list_item_long'], ENT_QUOTES) . "</option>";
                    }
                    $sqlorphan = "SELECT * FROM customlists WHERE cl_list_type=3 AND cl_deleted=0 AND cl_list_slno " .
                        " NOT IN (SELECT DISTINCT tu_template_id FROM template_users) " .
                        $where .
                        " ORDER BY cl_list_id,cl_list_item_long";
                    $resorphan = sqlStatement($sqlorphan);
                    while ($roworphan = sqlFetchArray($resorphan)) {
                        $cntxt = '';
                        if (!$_REQUEST['filter_context']) {
                            $context = sqlQuery("SELECT * FROM customlists WHERE cl_list_slno=?", array($roworphan['cl_list_id']));
                            $cntxt .= $context['cl_list_item_long'] . "->";
                        }
                        echo "<option value='" . htmlspecialchars($roworphan['cl_list_slno'] . "|", ENT_QUOTES) . "'>" . htmlspecialchars($cntxt . $roworphan['cl_list_item_long'], ENT_QUOTES) . "</option>";
                    }
                    ?>
                </select>
            </td>
            <td align="center">
                <input type="button" name="remove" value=&raquo;
                       onclick="jsub_selected(document.myform,'personalized','topersonalized')"></br>
                <input type="button" name="remove" value=&laquo;
                       onclick="check_user_category(document.myform,'topersonalized','personalized')">
            </td>
            <td align=left>
                <select multiple name="personalized[]" id="personalized" size="6" style="width:220px">
                    <?php
                    $where = '';
                    if ($_REQUEST['filter_context']) {
                        $where .= " AND cl_list_id='" . $_REQUEST['filter_context'] . "'";
                    }
                    $sql = "SELECT * FROM template_users AS tu LEFT OUTER JOIN customlists AS c ON tu.tu_template_id=c.cl_list_slno WHERE
                                tu.tu_user_id=? AND c.cl_list_type=3 AND cl_deleted=0 " .
                        $where .
                        "ORDER BY c.cl_list_item_long";
                    $resTemplates = sqlStatement($sql, array($_SESSION['authId']));
                    while ($rowTemplates = sqlFetchArray($resTemplates)) {
                        $cntxt = '';
                        if (!$_REQUEST['filter_context']) {
                            $context = sqlQuery("SELECT * FROM customlists WHERE cl_list_slno=?", array($rowTemplates['cl_list_id']));
                            $cntxt .= $context['cl_list_item_long'] . "->";
                        }
                        echo "<option value='" . htmlspecialchars($rowTemplates['cl_list_slno'] . "|" . $rowTemplates['tu_user_id'], ENT_QUOTES) . "'>" . htmlspecialchars($cntxt . $rowTemplates['cl_list_item_long'], ENT_QUOTES) . "</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr class="text">
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td><input type="hidden" name="submitform" id="submitform" value=""></td>
        </tr>
        <tr class="text">
            <td colspan="3">
                <div style="width:100%;overflow:auto;height:150px" id="itemdiv"></div>
            </td>
        </tr>
    </table>
</form>
</body>
</html>
