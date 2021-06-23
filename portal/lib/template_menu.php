<?php

/**
 * /template_menu.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

if ($include_auth !== true) {
    die('Not allowed');
}
$base_dir = $GLOBALS['OE_SITE_DIR'] . "/documents/onsite_portal_documents/templates/";

$dir_list = get_template_list($base_dir);
render_template_list($dir_list);

function get_template_list($root_directory)
{
    global $pid;

    $gen_const = xlt("Priority Request");
    $rtn = sqlStatement("SELECT `option_id`, `title`, `seq` FROM `list_options` WHERE `list_id` = ? ORDER BY `seq`", array('Document_Template_Categories'));
    $category_list = array();
    while ($row = sqlFetchArray($rtn)) {
        $category_list[] = $row;
    }

    // default templates first
    $dir_list = get_template_dir_array($root_directory) ?? [];
    // does patient have any special documents.
    if (!empty($pid)) {
        $pid_path = convert_safe_file_dir_name($pid . "_tpls");
        $tpls = get_template_dir_array($root_directory . $pid_path, $pid_path);
        if (count($tpls) > 0) {
            $dir_list[$gen_const] = $tpls;
        }
    }
    // get only directories from our category list
    foreach ($category_list as $cat) {
        if (stripos($cat['option_id'], 'repository') !== false) {
            continue;
        }
        if (substr($root_directory, -1) !== "/") {
            $root_directory .= "/";
        }
        if ($cat_dir_iter = get_template_dir_array($root_directory . convert_safe_file_dir_name($cat['option_id']), $cat['option_id'])) {
            $dir_list[$cat['title']] = $cat_dir_iter;
        }
    }

    return $dir_list;
}

function render_template_list($tree)
{
    global $pid, $cuser;

    foreach ($tree as $key => $file) {
        if (is_array($file)) {
            $is_category = $key;

            $cat_name = text($is_category);
            echo "<li class='text-center'><h5 class='mb-0'>$cat_name</h5></li>\n";
            foreach ($file as $filename) {
                if (is_array($filename)) {
                    continue;
                }
                $basefile = basename($filename, ".tpl");
                $btnname = text(ucwords(str_replace('_', ' ', $basefile)));
                $btnfile = attr($filename);
                echo '<li class="nav-item mb-1"><a class="nav-link text-success btn btn-outline-success" id="' . $basefile . '"' . ' href="#" onclick="page.newDocument(' . "$pid,'$cuser','$btnfile')" . '"' . ">$btnname</a></li>\n";
            }
            echo '<strong><hr class="mb-2 mt-1" /></strong>';
            continue;
        }
        // default template
        $basefile = basename($file, ".tpl");
        $btnname = text(ucwords(str_replace('_', ' ', $basefile)));
        $btnfile = attr($file);
        if ($btnname === "Help") {
            continue;
        }
        echo '<li class="nav-item mb-1"><a class="nav-link text-success btn btn-outline-success" id="' . $basefile . '"' . ' href="#" onclick="page.newDocument(' . "$pid,'$cuser','$btnfile')" . '"' . ">$btnname</a></li>\n";
    }
}

function get_template_dir_array($dir, $cat_dir = ''): array
{
    $ret_val = array();
    if (substr($dir, -1) !== "/") {
        $dir .= "/";
    }

    if (!is_dir($dir)) {
        return [];
    }
    if (false === ($d = @dir($dir))) {
        return [];
    }
    while (false !== ($entry = $d->read())) {
        if ($entry[0] === "." || substr($entry, -3) !== 'tpl') {
            continue;
        }
        if (is_dir("$dir$entry")) {
            continue;
        }

        if (is_readable("$dir$entry")) {
            $ret_val[] = text($cat_dir ? ($cat_dir . '/') . $entry : $entry);
        }
    }
    $d->close();

    return $ret_val;
}
