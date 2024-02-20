<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2022 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('./../../interface/globals.php');
require_once("$srcdir/patient.inc.php");

use OpenEMR\Core\Header;
use OpenEMR\Services\DocumentTemplates\DocumentTemplateService;

$templateService = new DocumentTemplateService();
$group_list =  $templateService->fetchDefaultGroups();
$profile_list = $templateService->fetchDefaultProfiles();
$_POST['mode'] = $_POST['mode'] ?? null;

if ($_POST['mode'] === 'save_profile_groups') {
    $groups = json_decode(($_POST['patient_groups'] ?? ''), true, 512, JSON_THROW_ON_ERROR);
    $work_ids = [];
    foreach ($groups as $group) {
        if (empty($group)) {
            continue;
        }
        foreach ($group as $item) {
            $work_ids[$item['profile']][] = $item;
        }
    }

    $rtn = $templateService->savePatientGroupsByProfile($work_ids);
    if ($rtn) {
        echo xlt('Groups successfully saved.');
    } else {
        echo xlt('Error! Groups save failed. Check your Group lists.');
    }
    exit;
}

if ($_POST['mode'] === 'save_groups') {
    $groups = json_decode(($_POST['groups'] ?? ''), true, 512, JSON_THROW_ON_ERROR);
    $master_ids = $work_ids = [];
    foreach ($groups as $group) {
        if (empty($group)) {
            continue;
        }
        foreach ($group as $item) {
            $work_ids[$item['id']][$item['group']][] = $item['group'];
        }
    }
    foreach ($work_ids as $id => $items) {
        $work_grps = [];
        foreach ($items as $grp => $item) {
            $work_grps[] = $grp;
        }
        $master_ids[$id] = implode('|', array_unique($work_grps));
    }

    $rtn = $templateService->updateGroupsInPatients($master_ids);
    if ($rtn) {
        echo xlt('Groups successfully saved.');
    } else {
        echo xlt('Error! Groups save failed. Check your Group lists.');
    }
    exit;
}

if (!isset($_GET['render_group_assignments'])) {
    $info_msg = '';
    $result = '';
    if (!empty($_REQUEST['searchby']) && !empty($_REQUEST['searchparm'])) {
        $searchby = $_REQUEST['searchby'];
        $searchparm = trim($_REQUEST['searchparm'] ?? '');

        if ($searchby == 'Last') {
            $result = getPatientLnames("$searchparm", 'pid, pubpid, lname, fname, mname, providerID, DOB');
        } elseif ($searchby == 'Phone') {
            $result = getPatientPhone("$searchparm");
        } elseif ($searchby == 'ID') {
            $result = getPatientId("$searchparm");
        } elseif ($searchby == 'DOB') {
            $result = getPatientDOB(DateToYYYYMMDD($searchparm));
        } elseif ($searchby == 'SSN') {
            $result = getPatientSSN("$searchparm");
        } elseif ($searchby == 'Issues') {
            $result = $templateService->fetchPatientListByIssuesSearch("$searchparm");
        }
    } else {
        $result = getPatientLnames("", 'pid, pubpid, lname, fname, mname, providerID, DOB');
    }
    ?>
<!DOCTYPE html>
<html>
<head>
    <?php
    if (empty($GLOBALS['openemr_version'] ?? null)) {
        Header::setupHeader(['opener','datetime-picker', 'sortablejs']);
    } else {
        Header::setupHeader(['opener','datetime-picker']); ?>
        <script src="<?php echo $GLOBALS['web_root']; ?>/portal/public/assets/sortablejs/Sortable.min.js?v=<?php echo $GLOBALS['v_js_includes']; ?>"></script>
    <?php } ?>
</head>
<style>
  body {
    overflow:hidden;
  }
  .list-group-item {
    cursor: move;
  }
  strong {
    font-weight: 600;
  }
  .col-height {
    max-height: 95vh;
    overflow-y:auto;
  }
</style>
<script>
    const groups = <?php echo js_escape($group_list); ?>;
    const profiles = <?php echo js_escape($profile_list); ?>;
    const demoPatient = <?php echo js_escape($_REQUEST['from_demo_pid'] ?? '0'); ?>;
    document.addEventListener('DOMContentLoaded', function () {
        // init drag and drop
        let patientRepository = document.getElementById('searchResults');
        Sortable.create(patientRepository, {
            group: {
                name: 'patientGroup',
                pull: 'clone',
            },
            multiDrag: true,
            selectedClass: 'active',
            fallbackTolerance: 3,
            sort: false,
            swapThreshold: 0.25,
            animation: 150,
            revertClone: true,
            removeCloneOnHide: true,
            onAdd: function (evt) {
                if (evt.items.length > 0) {
                    for (let i = 0; i < evt.items.length; i++) {
                        let el = evt.items[i];
                        el.parentNode.removeChild(el);
                    }
                } else {
                    let el = evt.item;
                    el.parentNode.removeChild(el);
                }
            }
        });
        Object.keys(groups).forEach(key => {
            let groupEl = groups[key]['option_id']
            let dropGroup = document.getElementById(groupEl);
            Sortable.create(dropGroup, {
                group: {
                    name: 'patientGroup',
                    delay: 1000,
                },
                multiDrag: true,
                selectedClass: 'active',
                fallbackTolerance: 3,
                animation: 150,
                sort: true,
                swapThreshold: 0.25,
                removeCloneOnHide: false,
                onAdd: function (evt) { // make group unique
                    let toList = evt.to.children;
                    let dedup = {};
                    let list = [...toList];
                    list.forEach(function (toEl) {
                        if (dedup[toEl.getAttribute('data-pid')]) {
                            toEl.remove();
                        } else {
                            dedup[toEl.getAttribute('data-pid')] = true;
                        }
                    });
                }
            });
        });

        $('#searchparm').focus();
        $('#theform').submit(function () {
            SubmitForm(this);
        });

        $('select[name="searchby"]').on('change', function () {
            if ($(this).val() === 'DOB') {
                $('#searchparm').datetimepicker({
                    <?php $datetimepicker_timepicker = false; ?>
                    <?php $datetimepicker_showseconds = false; ?>
                    <?php $datetimepicker_formatInput = true; ?>
                    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                });
            } else {
                $('#searchparm').datetimepicker("destroy");
            }
        });
    });

    function submitGroups() {
        top.restoreSession();
        document.getElementById('search_spinner').classList.toggle('d-none');
        let target = document.getElementById('edit-groups');
        let groupTarget = target.querySelectorAll('ul');
        let groupArray = [];
        let listArray = [];
        let listData = {};
        groupTarget.forEach((ulItem, index) => {
            let lists = ulItem.querySelectorAll('li');
            lists.forEach((item, index) => {
                console.log({index, item})
                listData = {
                    'group': ulItem.dataset.group,
                    'groups': item.dataset.groups,
                    'id': item.dataset.pid
                }
                listArray.push(listData);
                listData = {};
            });
            groupArray.push(listArray);
            listArray = [];
        });
        const data = new FormData();
        data.append('groups', JSON.stringify(groupArray));
        data.append('mode', 'save_groups');
        fetch('./patient_groups.php', {
            method: 'POST',
            body: data,
        }).then(rtn => rtn.text()).then((rtn) => {
            document.getElementById('search_spinner').classList.toggle('d-none');
            (async (time) => {
                await asyncAlertMsg(rtn, time, 'success', 'lg');
            })(1500).then(rtn => {
                if (typeof opener.document.edit_form !== 'undefined') {
                    opener.document.edit_form.submit();
                }
                dlgclose();
            });
        }).catch((error) => {
            console.error('Error:', error);
        });
    }

    const SubmitForm = function (eObj) {
        $("#submit_button").css("disabled", "true");
        return true;
    }
</script>
<body>
    <div class='container-fluid'>
        <div class='row'>
            <div class='col-6 col-height p-0 pb-1'>
                <nav id='searchCriteria' class='navbar navbar-light bg-light sticky-top'>
                    <form class="form-inline" method='post' name='theform' id='theform' action=''>
                        <div class='form-row'>
                            <select name='searchby' id='searchby' class="form-control form-control-sm ml-1">
                                <option value="Last"><?php echo xlt('Name'); ?></option>
                                <option value='Issues'<?php if (!empty($searchby) && ($searchby === 'Issues')) {
                                    echo ' selected'; } ?>><?php echo xlt('Problems or Code'); ?></option>
                            </select>
                            <div class='input-group'>
                                <input type='text' class="form-control form-control-sm" id='searchparm' name='searchparm' value='<?php echo attr($_REQUEST['searchparm'] ?? ''); ?>' title='<?php echo xla('If name, any part of lastname or lastname,firstname') ?>' placeholder='<?php echo xla('Search criteria.') ?>' />
                                <button class='btn btn-primary btn-sm btn-search' type='submit' id="submit_button" value='<?php echo xla('Search'); ?>'></button>
                            </div>
                        </div>
                    </form>
                    <div class='btn-group'>
                        <button type='button' class='btn btn-secondary btn-cancel btn-sm' onclick='dlgclose();'><?php echo xlt('Quit'); ?></button>
                        <span id='search_spinner' class="d-none"><i class='fa fa-spinner fa-spin fa-2x ml-1'></i></span>
                    </div>
                </nav>
                <div class="">
                    <?php if (!is_countable($result)) : ?>
                        <div id="searchstatus" class="alert alert-danger m-1 p-1 rounded-0"><?php echo xlt('No records found. Please expand your search criteria.'); ?><br />
                        </div>
                    <?php elseif (count($result) >= 1000) : ?>
                        <div id="searchstatus" class="alert alert-danger m-1 p-1 rounded-0"><?php echo xlt('More than 1000 records found. Please narrow your search criteria.'); ?></div>
                    <?php else : ?>
                        <div id="searchstatus" class="alert alert-success m-1 p-1 rounded-0"><?php echo text(count($result)) . ' '; ?><?php echo xlt('records found.'); ?></div>
                    <?php endif; ?>
                    <ul id='searchResults' class='list-group mx-1'>
                        <?php
                        if (isset($result) && is_countable($result)) {
                            foreach ($result as $pt) {
                                $name = $pt['lname'] . ', ' . $pt['fname'] . ' ' . $pt['mname'];
                                $this_name = attr($name);
                                $pt_pid = attr($pt['pid']);
                                $groups_esc = attr($pt['patient_groups'] ?? '');
                                echo "<li class='list-group-item px-1 py-1 mb-1' data-pid='$pt_pid' data-groups='$groups_esc'>" .
                                    '<strong>' . text($name) . '</strong>' . ' ' . xlt('Dob') . ': ' .
                                    '<strong>' . text(oeFormatShortDate($pt['DOB'])) . '</strong>' . ' ' . xlt('ID') . ': ' .
                                    '<strong>' . text($pt['pubpid']) . '</strong>';
                                if (!empty($searchby) && ($searchby === 'Issues')) {
                                    echo ' ' . xlt('Result') . ': ' . text($pt['title']) . ' ' . text($pt['diagnosis']);
                                }
                                    echo '</li>' . "\n";
                            }
                        }
                        ?>
                    </ul>
                </div>
            </div>
            <div class='col-6 col-height p-0 pb-1'>
                <nav id='dispose' class='navbar navbar-light bg-light sticky-top'>
                    <div class='btn-group ml-auto'>
                        <button type='button' class='btn btn-primary btn-save btn-sm' onclick='return submitGroups();'><?php echo xlt('Save'); ?></button>
                        <button type='button' class='btn btn-secondary btn-cancel btn-sm' onclick='dlgclose();'><?php /*echo xlt('Quit'); */?></button>
                        <span id='search_spinner' class="d-none"><i class='fa fa-spinner fa-spin fa-2x ml-1'></i></span>
                    </div>
                </nav>
                <div id="edit-groups" class='control-group mx-1 border-left border-right'>
                    <?php
                    $result = $templateService->getPatientsByAllGroups();
                    foreach ($group_list as $group => $groups) {
                        $group_esc = attr($groups['option_id']);
                        $groups_esc = attr($groups['option_id']);
                        echo "<h5 class='bg-dark text-light text-center' data-toggle='collapse' data-target='#$group_esc' role='button'><i class='fa fa-eye mr-1'></i>" . text($groups['title']) . "</h5>\n";
                        echo "<ul id='$group_esc' class='list-group mx-1 px-1 show' data-group='$group_esc'>\n";
                        if (!empty($result[$groups['option_id']] ?? '')) {
                            foreach ($result[$groups['option_id']] as $pt) {
                                $name = $pt['lname'] . ', ' . $pt['fname'] . ' ' . $pt['mname'];
                                $this_name = attr($name);
                                $pt_pid = attr($pt['pid']);
                                $groups_esc = attr($pt['patient_groups']);
                                echo "<li class='list-group-item px-1 py-1 mb-1' data-pid='$pt_pid' data-groups='$groups_esc'>" .
                                    '<strong>' . text($name) . '</strong>' . ' ' . xlt('Dob') . ': ' .
                                    '<strong>' . text(oeFormatShortDate($pt['DOB'])) . '</strong>' . ' ' . xlt('ID') . ': ' .
                                    '<strong>' . text($pt['pubpid']) . '</strong>' . '</li>' . "\n";
                            }
                        }
                        echo "</ul>\n";
                    }
                    // so list is responsive.
                    echo "<div class='py-1'></div>\n";
                    ?>
                </div>
            </div>
        </div>
    </div>
    <hr />
</body>
</html>
<?php } elseif ($_GET['render_group_assignments'] === 'true') { ?>
<!-- ***                                    Groups                                                 *** -->
<!DOCTYPE html>
<html>
<head>
    <?php
    if (empty($GLOBALS['openemr_version'] ?? null)) {
        Header::setupHeader(['opener','datetime-picker', 'sortablejs']);
    } else {
        Header::setupHeader(['opener','datetime-picker']); ?>
        <script src="<?php echo $GLOBALS['web_root']; ?>/portal/public/assets/sortablejs/Sortable.min.js?v=<?php echo $GLOBALS['v_js_includes']; ?>"></script>
    <?php } ?>
</head>
<style>
  body {
    overflow:hidden;
  }
  .move-handle {
    cursor: move;
  }
  strong {
    font-weight: 600;
  }
  .col-height {
    max-height: 95vh;
    overflow-y:auto;
  }
</style>
<script>
const groups = <?php echo js_escape($group_list); ?>;
const profiles = <?php echo js_escape($profile_list); ?>;
const demoPatient = <?php echo js_escape($_REQUEST['from_demo_pid'] ?? '0'); ?>;

function thisPopPatientDialog() {
    top.restoreSession();
    let url = './patient_groups.php';
    dlgopen(url, 'pop-profile', 'modal-lg', 850, '', '', {
        allowDrag: true,
        allowResize: true,
        sizeHeight: 'full',
        resolvePromiseOn: 'close',
    }).then(() => {
        location.reload();
    })
}

function submitPatientGroups() {
    top.restoreSession();
    let target = document.getElementById('edit-profiles');
    let groupTarget = target.querySelectorAll('ul');
    let groupArray = [];
    let listArray = [];
    let listData = {};
    groupTarget.forEach((ulItem, index) => {
        let lists = ulItem.querySelectorAll('li');
        lists.forEach((item, index) => {
            console.log({index, item})
            listData = {
                'profile': ulItem.dataset.profile,
                'active': ulItem.dataset.active,
                'group': item.dataset.group
            }
            listArray.push(listData);
            listData = {};
        });
        groupArray.push(listArray);
        listArray = [];
    });
    const data = new FormData();
    data.append('patient_groups', JSON.stringify(groupArray));
    data.append('mode', 'save_profile_groups');
    fetch('./patient_groups.php', {
        method: 'POST',
        body: data,
    }).then(rtn => rtn.text()).then((rtn) => {
        (async (time) => {
            await asyncAlertMsg(rtn, time, 'success', 'lg');
        })(1500).then(rtn => {
            if (typeof opener.document.edit_form !== undefined) {
                opener.document.edit_form.submit();
            }
            dlgclose();
        });
    }).catch((error) => {
        console.error('Error:', error);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    // init drag and drop
    let patientRepository = document.getElementById('edit-groups');
    Sortable.create(patientRepository, {
        group: {
            name: 'patientGroup',
            pull: 'clone',
        },
        fallbackTolerance: 3,
        sort: false,
        swapThreshold: 0.25,
        animation: 150,
        revertClone: true,
        removeCloneOnHide: true,
        filter: '.nested-item',
        onAdd: function (evt) {
            if (evt.items.length > 0) {
                for (let i = 0; i < evt.items.length; i++) {
                    let el = evt.items[i];
                    el.parentNode.removeChild(el);
                }
            } else {
                let el = evt.item;
                el.parentNode.removeChild(el);
            }
        }
    });
    Object.keys(profiles).forEach(key => {
        let profileEl = profiles[key]['option_id']
        let id = document.getElementById(profileEl);
        Sortable.create(id, {
            group: {
                name: 'patientGroup',
            },
            selectedClass: 'active',
            animation: 150,
            sort: true,
            filter: '.nested-item',
            onAdd: function (evt) { // make group unique
                let toList = evt.to.children;
                let dedup = {};
                let list = [...toList];
                list.forEach(function (toEl) {
                    if (dedup[toEl.getAttribute('data-group')]) {
                        toEl.remove();
                    } else {
                        dedup[toEl.getAttribute('data-group')] = true;
                    }
                });
            }
        });
    });
});
</script>
<body>
<div class='container-fluid'>
    <div class='row'>
        <div class='col-6 col-height p-0 pb-1'>
            <nav id='dispose' class='navbar navbar-light bg-light sticky-top'>
                <div class='navbar-brand'><?php echo xlt('Patient Groups'); ?></div>
                <form class='form-inline'id='groupsForm'>
                <div class='btn-group'>
                    <button type='button' class='btn btn-sm btn-primary' onclick='return thisPopPatientDialog()'><?php echo xlt('Groups') ?></button>
                </div>
                </form>
            </nav>
            <div id="edit-groups" class='control-group mx-1  border-left border-right'>
                <?php
                $result = $templateService->getPatientsByAllGroups();
                foreach ($group_list as $group => $groups) {
                    $group_esc = attr($groups['option_id']);
                    $groups_esc = attr($groups['option_id']);
                    echo "<li class='list-group-item move-handle text-center bg-light text-dark font-weight-bolder p-1 mt-1 mb-0' data-group='$group_esc'>" . text($groups['title']) . "<i class='fa fa-eye float-right my-1 mr-2' data-toggle='collapse' data-target='#$group_esc' role='button'></i></li>\n";
                    echo "<ul id='$group_esc' class='list-group-flush m-1 p-1 collapse'>\n";
                    if (!empty($result[$groups['option_id']] ?? '')) {
                        foreach ($result[$groups['option_id']] as $pt) {
                            $name = $pt['lname'] . ', ' . $pt['fname'] . ' ' . $pt['mname'];
                            $this_name = attr($name);
                            $pt_pid = attr($pt['pid']);
                            $groups_esc = attr($pt['patient_groups']);
                            echo "<li class='list-group-item nested-item m-1 p-0' data-pid='$pt_pid' data-groups='$groups_esc'>" .
                                '<strong>' . text($name) . '</strong>' . ' ' . xlt('Dob') . ': ' .
                                '<strong>' . text(oeFormatShortDate($pt['DOB'])) . '</strong>' . ' ' . xlt('ID') . ': ' .
                                '<strong>' . text($pt['pubpid']) . '</strong>' . '</li>' . "\n";
                        }
                    }
                    echo "</ul>\n";
                }
                // so list is responsive.
                echo "<div class='py-1'></div>\n";
                ?>
            </div>
        </div>
        <div class='col-6 col-height p-0 pb-1'>
            <nav id='dispose' class='navbar navbar-light bg-light sticky-top'>
                <div class="navbar-brand"><?php echo xlt('Profiles');  ?></div>
                <form class='form-inline' id='profileForm'>
                    <div class='btn-group'>
                        <button type="button" class='btn btn-primary btn-save btn-sm' onclick='return submitPatientGroups();'><?php echo xlt('Save');  ?></button>
                        <button type='button' class='btn btn-secondary btn-cancel btn-sm' onclick='dlgclose();'><?php echo xlt('Quit');  ?></button>
                        <span id='search_spinner' class="d-none"><i class='fa fa-spinner fa-spin fa-2x ml-1'></i></span>
                    </div>
                </form>
            </nav>
            <div id='edit-profiles' class='control-group mx-1 border-left border-right'>
                <?php
                foreach ($profile_list as $profile => $profiles) {
                    $profile_items_list = $templateService->getPatientGroupsByProfile($profile);
                    $active = $templateService->getProfileActiveStatus($profile);
                    $active_esc = attr($active);
                    if (!empty($active)) {
                        $active_text = '<span class="small float-left">' . xlt('Active') . '</span>';
                    } else {
                        $active_text = '';
                    }
                    $profile_esc = attr($profile);
                    echo "<h5 class='text-center bg-dark text-light p-1 mt-1 mb-0'>" . $active_text . text($profiles['title']) .
                        "<i class='fa fa-eye float-right my-1 mr-2' data-toggle='collapse' data-target='#$profile_esc' role='button'></i></h5>\n";
                    echo "<ul id='$profile_esc' class='list-group-flush m-1 p-1 show' data-profile='$profile_esc' data-active='$active_esc'>\n";
                    foreach ($profile_items_list as $grp_profile => $groups) {
                        foreach ($groups as $group) {
                            $group_esc = attr($group['member_of']);
                            $title = $group_list[$group['member_of']]['title'] ?: $group['member_of'];
                            echo "<li class='list-group-item move-handle text-center bg-light text-dark font-weight-bolder p-1 mt-1 mb-0' data-group='$group_esc'>" . text($title) . "</li>\n";
                        }
                    }
                    echo "</ul>\n";
                }
                ?>
            </div>
        </div>
    </div>
</div>
<hr />
</body>
</html>
<?php }
