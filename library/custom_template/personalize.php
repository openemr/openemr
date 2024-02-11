<?php

/**
 * personalize.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eldho Chacko <eldho@zhservices.com>
 * @author    Jacob T Paul <jacob@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../interface/globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;

$filter_context =  $_REQUEST['filter_context'] ?? '';
$filter_users = $_REQUEST['filter_users'] ?? '';
$list_id = $_REQUEST['list_id'] ?: $filter_context;

function Delete_Rows($id): void
{
    sqlStatement("DELETE FROM template_users WHERE tu_template_id=? AND tu_user_id=?", array($id, $_SESSION['authUserID']));
}

function Insert_Rows($id, $order = ""): void
{
    sqlStatement("REPLACE INTO template_users (tu_template_id,tu_user_id,tu_template_order) VALUES (?,?,?)", array($id, $_SESSION['authUserID'], $order));
}

if (isset($_REQUEST['submitform']) && $_REQUEST['submitform'] == 'save') {
    $topersonalized = $_REQUEST['topersonalized'];
    $personalized = $_REQUEST['personalized'];
    foreach ($topersonalized as $key => $value) {
        $arr = explode("|", $value);
        $res = sqlStatement("SELECT * FROM template_users WHERE tu_template_id=? AND tu_user_id=?", array($arr[0], $_SESSION['authUserID']));
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
            $res = sqlStatement("SELECT * FROM template_users WHERE tu_template_id=? AND tu_user_id=?", array($arr[0], $_SESSION['authUserID']));
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

    <?php Header::setupHeader(['common', 'opener']); ?>

    <script>

        function refreshme() {
            top.restoreSession();
            document.location.reload();
        }

        $(function () {

            tabbify();

            $(".iframe_small").on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                dlgopen('', '', 400, 250, '', '', {
                    buttons: [
                        {text: '<?php echo xla('Close'); ?>', close: true, style: 'secondary btn-sm'}
                    ],
                    onClosed: 'refreshme',
                    type: 'iframe',
                    url: $(this).attr('href')
                });
            });

            $(".iframe_medium").on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                dlgopen('', '', 500, 500, '', '', {
                    buttons: [
                        {text: '<?php echo xla('Close'); ?>', close: true, style: 'secondary btn-sm'}
                    ],
                    onClosed: 'refreshme',
                    type: 'iframe',
                    url: $(this).attr('href')
                });
            });

            $(".iframe_abvmedium").on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                dlgopen('', '', 700, 550, '', '', {
                    buttons: [
                        {text: '<?php echo xla('Close'); ?>', close: true, style: 'secondary btn-sm'}
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
                                } else {
                                    msg += form.elements[selectedList].options[total_selected].text + "\n";
                                }
                            },
                            error: function () {
                                alert("fail");
                            }
                        });
                    } else {
                        total_clients = form.elements[selectFrom].length;
                        opt = new Option(form.elements[selectedList].options[total_selected].text, form.elements[selectedList].options[total_selected].value);
                        form.elements[selectFrom].options[total_clients] = opt;
                        form.elements[selectedList].options[total_selected] = null;
                    }
                }
            }
            jsub_sortNow(form.elements[selectFrom]);
            if (msg != '') {
                if (confirm(<?php echo xlj('The following categories will be removed from your category List');?> + "\n" + msg + "\n"  + <?php echo xlj('Do you want to continue?');?>)) {
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
            event.preventDefault();
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
            return false;
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
            } else if (len == 1) {
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
        <div class="container-fluid">
            <h3><?php echo xlt('Filter'); ?></h3>
            <div class="row">
                <label class="col-form-label col-sm-1"><?php echo xlt('Context'); ?></label>
                <div class="col-sm-5">
                    <select name='filter_context' class="form-control" id='filter_context' onchange='javascript:document.myform.submit();'>
                        <option value=''><?php echo xlt('Select a Context'); ?></option>
                        <?php
                        $context_sql = "SELECT * FROM customlists WHERE cl_list_type=2 AND cl_deleted=0";
                        $context_res = sqlStatement($context_sql);
                        while ($context_row = sqlFetchArray($context_res)) {
                            echo "<option value='" . attr($context_row['cl_list_slno']) . "' ";
                            echo ($filter_context == $context_row['cl_list_slno']) ? 'selected' : '';
                            echo ">" . text($context_row['cl_list_item_long']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <label class="col-form-label col-sm-1"><?php echo xlt('Users'); ?></label>
                <div class="col-sm-5">
                    <select name='filter_users' class="form-control" id='filter_users' onchange='javascript:document.myform.submit();'>
                        <option value=''><?php echo xlt('Select a User'); ?></option>
                        <?php
                        $user_sql = "SELECT DISTINCT(tu.tu_user_id),u.fname,u.lname FROM template_users AS tu LEFT OUTER JOIN users AS u ON tu.tu_user_id=u.id WHERE tu.tu_user_id!=?";
                        $user_res = sqlStatement($user_sql, array($_SESSION['authUserID']));
                        while ($user_row = sqlFetchArray($user_res)) {
                            echo "<option value='" . attr($user_row['tu_user_id']) . "' ";
                            echo ($filter_users == $user_row['tu_user_id']) ? 'selected' : '';
                            echo ">" . text($user_row['fname'] . " " . $user_row['lname']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-12 my-2 text-center">
                    <a href="#" class="btn btn-primary" onclick="top.restoreSession();personalize_save()"><?php echo xlt('Save'); ?></a>
                    <?php
                    if (AclMain::aclCheckCore('nationnotes', 'nn_configure')) {
                        ?>
                        <a href="delete_category.php" id="share_link" class="iframe_medium btn btn-primary" onclick="top.restoreSession();"><?php echo xlt('Delete Category'); ?></a>
                        <?php
                    }
                    ?>
                    <?php
                    if (AclMain::aclCheckCore('nationnotes', 'nn_configure')) {
                        ?>
                        <a href="add_template.php?list_id=<?php echo attr($_REQUEST['list_id']); ?>" onclick="top.restoreSession();" class="iframe_small btn btn-primary" title="<?php echo xla('Add Category'); ?>"><?php echo xlt('Add Category'); ?></a>
                        <?php
                    }
                    ?>
                    <?php
                    if (AclMain::aclCheckCore('nationnotes', 'nn_configure')) {
                        ?>
                        <a href="add_context.php" class="iframe_medium btn btn-primary" onclick="top.restoreSession();" title="<?php echo xla('Add Context'); ?>"><?php echo xlt('Add Context'); ?></a>
                        <?php
                    }
                    ?>
                </div>
                <div class="col-sm-5 text">
                    <?php echo xlt('Available categories'); ?>
                </div>
                <div class="col-sm-2">
                    &nbsp;
                </div>
                <div class="col-sm-5 text">
                    <?php $user = sqlQuery("SELECT * FROM users WHERE id=?", array($_SESSION['authUserID'])); ?>
                    <?php echo xlt('Categories for') . " " . text($user['fname']) . " " . text($user['lname']); ?>
                </div>
                <div class="col-sm-5">
                    <select multiple name="topersonalized[]" class="form-control" id="topersonalized" size="6" onchange="display_category_item(document.myform,'topersonalized');">
                        <?php
                        $where = '';
                        $join = '';
                        $arval = array($_SESSION['authUserID']);
                        $arval1 = array($filter_users, $_SESSION['authUserID']);
                        if ($filter_context ?? null) {
                            $where .= " AND cl_list_id=?";
                            $arval[] = $filter_context;
                            $arval1[] = $filter_context;
                        }
                        $sql = "SELECT * FROM template_users AS tu LEFT OUTER JOIN customlists AS c ON tu.tu_template_id=c.cl_list_slno
                          WHERE cl_list_type=3 AND cl_deleted=0 AND tu.tu_template_id NOT IN (SELECT tu_template_id FROM template_users AS tuser WHERE
                          tu_user_id=?) " . $where . " ORDER BY cl_list_id,tu_user_id,cl_list_item_long";
                        $resTemplates = sqlStatement($sql, $arval);
                        if ($filter_users) {
                            $sql = "SELECT * FROM template_users AS tu LEFT OUTER JOIN customlists AS c ON tu.tu_template_id=c.cl_list_slno WHERE 
                                    tu.tu_user_id=? AND c.cl_list_type=3 AND cl_deleted=0 AND tu.tu_template_id NOT IN
                                    (SELECT tu_template_id FROM template_users AS tuser WHERE tu_user_id=?)" . $where . " ORDER BY cl_list_id,tu_user_id,c.cl_list_item_long";
                            $resTemplates = sqlStatement($sql, $arval1);
                        }
                        while ($rowTemplates = sqlFetchArray($resTemplates)) {
                            $cntxt = '';
                            if (!$filter_context ?? null) {
                                $context = sqlQuery("SELECT * FROM customlists WHERE cl_list_slno=?", array($rowTemplates['cl_list_id']));
                                $cntxt .= $context['cl_list_item_long'] . "->";
                            }
                            if (!$filter_users ?? null) {
                                $context = sqlQuery("SELECT * FROM users WHERE id=?", array($rowTemplates['tu_user_id']));
                                $cntxt .= $context['username'] . "->";
                            }
                            echo "<option value='" . attr($rowTemplates['cl_list_slno'] . "|" . $rowTemplates['tu_user_id']) . "'>" . text($cntxt . $rowTemplates['cl_list_item_long']) . "</option>";
                        }
                        $sqlorphan = "SELECT * FROM customlists WHERE cl_list_type=3 AND cl_deleted=0 AND cl_list_slno" .
                            " NOT IN (SELECT DISTINCT tu_template_id FROM template_users) " . $where . " ORDER BY cl_list_id,cl_list_item_long";
                        if (empty($where)) {
                            $resorphan = sqlStatement($sqlorphan);
                        }
                        while ($roworphan = sqlFetchArray($resorphan ?? '')) {
                            $cntxt = '';
                            if (!$filter_context ?? null) {
                                $context = sqlQuery("SELECT * FROM customlists WHERE cl_list_slno=?", array($roworphan['cl_list_id']));
                                $cntxt .= $context['cl_list_item_long'] . "->";
                            }
                            echo "<option value='" . attr($roworphan['cl_list_slno'] . "|") . "'>" . text($cntxt . $roworphan['cl_list_item_long']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-sm-2 text-center">
                    <button name="remove" class="btn btn-secondary" onclick="jsub_selected(document.myform,'personalized','topersonalized')">&raquo;</button>
                    <br />
                    <button name="remove" class="btn btn-secondary" onclick="check_user_category(document.myform,'topersonalized','personalized')">&laquo;</button>
                </div>
                <div class="col-sm-5">
                    <select multiple class="form-control" name="personalized[]" id="personalized" size="6">
                        <?php
                        $where = '';
                        if ($filter_context ?? null) {
                            $where .= " AND cl_list_id = ?";
                            $sqlbind = array($filter_context);
                        }
                        $sql = "SELECT * FROM template_users AS tu LEFT OUTER JOIN customlists AS c ON tu.tu_template_id=c.cl_list_slno WHERE tu.tu_user_id=? AND c.cl_list_type=3 AND cl_deleted=0 " . $where .  " ORDER BY c.cl_list_item_long";
                        $resTemplates = sqlStatement($sql, array_merge(array($_SESSION['authUserID']), $sqlbind ?? []));
                        while ($rowTemplates = sqlFetchArray($resTemplates)) {
                            $cntxt = '';
                            if (!$filter_context ?? null) {
                                $context = sqlQuery("SELECT * FROM customlists WHERE cl_list_slno=?", array($rowTemplates['cl_list_id']));
                                $cntxt .= $context['cl_list_item_long'] . "->";
                            }
                            echo "<option value='" . attr($rowTemplates['cl_list_slno'] . "|" . $rowTemplates['tu_user_id']) . "'>" . text($cntxt . $rowTemplates['cl_list_item_long']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-12">
                    <input type="hidden" name="submitform" id="submitform" value="" />
                    <div class="w-100 overflow-auto" style="height:150px" id="itemdiv"></div>
                </div>
            </div>
        </div>
    </form>
</body>
</html>
