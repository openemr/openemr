<?php

/**
 * Service for handling Document templates
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\DocumentTemplates;

class DocumentTemplateService
{
    public function __construct()
    {
    }

    public function getTemplateListAllCategories($pid = 0): array
    {
        $results = array();
        $query_result = sqlStatement('SELECT * FROM `document_templates` WHERE pid = ? ORDER BY `category`', array($pid));
        while ($row = sqlFetchArray($query_result)) {
            if (is_array($row)) {
                $results[$row['category'] ?? ''][] = $row;
            }
        }
        return $results;
    }

    public function getTemplateList($category = null): array
    {
        $results = array($category => null);
        $query_result = sqlStatement('SELECT * FROM `document_templates` WHERE category = ? AND pid = ?', array($category, 0));
        while ($row = sqlFetchArray($query_result)) {
            if (is_array($row)) {
                $results[$category][] = $row;
            }
        }
        return $results;
    }

    public function getTemplateListByCategory($category = null, $pid = 0): array
    {
        $results = array($category => null);
        $query_result = sqlStatement('SELECT * FROM `document_templates` WHERE category = ? AND pid = ?', array($category, $pid));
        while ($row = sqlFetchArray($query_result)) {
            if (is_array($row)) {
                $results[$category][] = $row;
            }
        }
        return $results;
    }

    public function getTemplateCategoriesByPids($pids, $category = null): array
    {
        $results = array();
        foreach ($pids as $pid) {
            if ($pid == -1) {
                continue;
            }
            $result = $this->getTemplateCategoriesByPatient($pid, $category);
            if (empty($result)) {
                continue;
            }
            $results = array_merge_recursive($results, $result);
        }
        return $results;
    }

    public function getTemplateCategoriesByPatient($pid = null, $category = null): array
    {
        $results = array();
        $bind = array();
        if (empty($pid)) {
            $where = 'WHERE pid > ?';
            $bind = array($pid ?? 0);
        } else {
            $where = 'WHERE pid = ?';
            $bind = array($pid);
        }
        if (!empty($category)) {
            $where .= ' AND category = ?';
            $bind[] = $category;
        }
        $sql = "SELECT * FROM `document_templates` $where ORDER BY pid, category";
        $query_result = sqlStatement($sql, $bind);
        while ($row = sqlFetchArray($query_result)) {
            if (is_array($row)) {
                $results[$row['category']][] = $row;
            }
        }
        return $results;
    }

    public function uploadTemplate($template_name, $category, $file, $pids = [])
    {
        $mimetype = null;
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimetype = finfo_file($finfo, $file);
            finfo_close($finfo);
        } elseif (function_exists('mime_content_type')) {
            $mimetype = mime_content_type($file);
        } else {
            if (stripos($file, '.pdf') !== false) {
                $mimetype = 'application/pdf';
            }
        }

        $content = file_get_contents($file);
        $id = 0;
        foreach ($pids as $pid) {
            $id = $this->insertTemplate($pid, $category, $template_name, $content, $mimetype);
        }
        return $id;
    }

    public function insertTemplate($pid, $category, $template, $content, $mimetype = null): int
    {
        $name = null;
        if (!empty($pid)) {
            $name = sqlQuery("SELECT patient_data.pid, Concat_Ws(', ', patient_data.lname, patient_data.fname) as name FROM patient_data WHERE pid = ?", array($pid))['name'];
        } elseif ($pid == -1) {
            $name = 'Repository';
        }
        $sql = "INSERT INTO `document_templates` 
            (`pid`, `provider`, `category`, `template_name`, `location`, `status`, `template_content`, `size`, `mime`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE `provider`= ?, `template_content`= ?, `size`= ?, `modified_date` = NOW(), `mime` = ?";

        return sqlInsert($sql, array($pid, ($_SESSION['authUserID'] ?? null), $category, $template, $name, 'New', $content, strlen($content), $mimetype, ($_SESSION['authUserID'] ?? null), $content, strlen($content), $mimetype));
    }

    public function sendTemplate($pids, $templates, $category = null): int
    {
        $result = 0;
        foreach ($templates as $id) {
            $template = $this->fetchTemplate($id);
            $destination_category = $template['category'];
            if ($destination_category === 'repository') {
                $destination_category = $category;
            }
            $content = $template['template_content'];
            $name = $template['template_name'];
            foreach ($pids as $pid) {
                $result = $this->insertTemplate($pid, $destination_category, $name, $content);
            }
        }
        return $result;
    }

    public function updateTemplateContent($id, $content)
    {
        return sqlQuery('UPDATE `document_templates` SET `template_content` = ?, modified_date = NOW() WHERE `id` = ?', array($content, $id));
    }

    public function updateTemplateCategory($id, $category)
    {
        return sqlQuery('UPDATE  `document_templates`  SET `category` =  ? WHERE `id` = ?', array($category, $id ));
    }

    public function deleteTemplate($id)
    {
        $profile_delete = sqlQuery('DELETE FROM `document_template_profiles` WHERE `template_id` = ?', array($id));
        $delete = sqlQuery('DELETE FROM `document_templates` WHERE `id` = ?', array($id));
        return ($delete && $profile_delete);
    }

    public function fetchTemplate($id, $template_name = null)
    {
        $return = null;
        if (!empty($id)) {
            $return = sqlQuery('SELECT * FROM `document_templates` WHERE `id` = ?', array($id));
        } elseif (!empty($template_name)) {
            $return = sqlQuery('SELECT * FROM `document_templates` WHERE `template_name` = ?', array($template_name));
        }
        return $return;
    }

    public function saveProfileTemplate($id, $profile, $template_name, $category)
    {
        $q = sqlInsert('INSERT INTO `document_template_profiles` (`template_id`, `profile`, `template_name`, `category`, `provider`) 
            VALUES (?, ?, ?, ?, ?)', array($id, $profile, $template_name, $category, ($_SESSION['authUserID'] ?? null)));
    }

    public function saveAllProfileTemplates($profiles_array)
    {
        sqlQuery("TRUNCATE `document_template_profiles`");
        $rtn = false;
        foreach ($profiles_array as $profile_array) {
            $rtn = sqlInsert(
                "INSERT INTO `document_template_profiles` (`template_id`, `profile`, `template_name`, `category`, `provider`) 
            VALUES (?, ?, ?, ?, ?)",
                array($profile_array->id, $profile_array->profile, $profile_array->name, $profile_array->category, ($_SESSION['authUserID'] ?? null))
            );
        }
        return $rtn;
    }

    public function getDefaultCategories(): array
    {
        $rtn = sqlStatement('SELECT `option_id`, `title`, `seq` FROM `list_options` WHERE `list_id` = ? ORDER BY `seq`', array('Document_Template_Categories'));
        $category_list = array();
        while ($row = sqlFetchArray($rtn)) {
            $category_list[$row['option_id']] = $row;
        }
        return $category_list;
    }

    public function getProfileListByProfile($profile): array
    {
        $rtn = sqlStatement('SELECT * FROM `document_template_profiles` WHERE `profile` = ?', array($profile));
        $profile_list = array();
        $fetched_row = [];
        while ($row = sqlFetchArray($rtn)) {
            $fetched_row = $this->fetchTemplate($row['template_id']);
            $profile_list[$row['category']][] = $fetched_row;
        }
        return $profile_list;
    }

    public function getDefaultProfiles(): array
    {
        $rtn = sqlStatement('SELECT `option_id`, `title`, `seq` FROM `list_options` WHERE `list_id` = ? ORDER BY `seq`', array('Document_Template_Profiles'));
        $category_list = array();
        while ($row = sqlFetchArray($rtn)) {
            $category_list[$row['option_id']] = $row;
        }
        return $category_list;
    }

    public function getFormattedCategories(): array
    {
        $rtn = sqlStatement('SELECT `option_id`, `title`, `seq` FROM `list_options` WHERE `list_id` = ? ORDER BY `seq`', array('Document_Template_Categories'));
        $category_list = array();
        while ($row = sqlFetchArray($rtn)) {
            $category_list[$row['option_id']] = $row;
        }
        return $category_list;
    }

    public function fetchtemplateStatus($id)
    {
        return sqlQuery('SELECT status FROM `document_templates` WHERE `id` = ?', array($id))['status'];
    }
}
