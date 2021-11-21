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

use OpenEMR\Services\DocumentTemplates\DocumentTemplateService;

if ($include_auth !== true) {
    die('Not allowed');
}

render_template_list($pid, $cuser);

function render_template_list($pid, $cuser)
{
    $templateService = new DocumentTemplateService();
    $category_list = $templateService->getFormattedCategories();
    $templates = $templateService->getTemplateListAllCategories();
    $patient_templates = $templateService->getTemplateCategoriesByPatient();
    $templates = array_merge_recursive($templates, $patient_templates);
    foreach ($templates as $key => $file) {
        if (is_array($file)) {
            $is_category = $category_list[$key]['title'] ?? $key;
            if ($is_category == 'Default') {
                $is_category = '';
            }
            $cat_name = text($is_category);

            $flag = false;
            foreach ($file as $filename) {
                if ($filename['template_name'] == 'Help') {
                    continue;
                }
                if ((int)$filename['pid'] !== 0 && (int)$filename['pid'] !== (int)$pid) {
                    continue;
                }
                if (!$flag) {
                    $flag = true;
                    echo "<li class='text-center'><h5 class='mb-0'>$cat_name</h5></li>\n";
                }
                $id = attr($filename['id']);
                $btnname = text($filename['template_name']);
                echo '<li class="nav-item mb-1 template-item"><a class="nav-link text-success btn btn-sm btn-outline-success" id="' . $id . '"' . ' href="#" onclick="page.newDocument(' . "'$pid','$cuser','$btnname', '$id')" . '"' . ">$btnname</a></li>\n";
            }
            if (!$flag) {
                echo '<strong><hr class="mb-2 mt-1" /></strong>';
            }

            continue;
        }
    }
}
