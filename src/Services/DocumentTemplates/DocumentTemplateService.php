<?php

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
        $content = file_get_contents($file);
        $id = 0;
        foreach ($pids as $pid) {
            $id = $this->insertTemplate($pid, $category, $template_name, $content);
        }
        return $id;
    }

    public function insertTemplate($pid, $category, $template, $content): int
    {
        $name = null;
        if (!empty($pid)) {
            $name = sqlQuery("SELECT patient_data.pid, Concat_Ws(', ', patient_data.lname, patient_data.fname) as name FROM patient_data WHERE pid = ?", array($pid))['name'];
        } elseif ($pid == -1) {
            $name = 'Repository';
        }
        $sql = 'REPLACE INTO `document_templates` (`pid`, `provider`, `category`, `template_name`, `location`, `status`, `template_content`, `size`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
        return sqlInsert($sql, array($pid, ($_SESSION['authUserID'] ?? null), $category, $template, $name, 'New', $content, strlen($content)));
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

    public function updateTemplate($id, $category, $template, $content)
    {
    }

    public function deleteTemplate($id): bool | array | null
    {
        return sqlQuery('DELETE FROM `document_templates` WHERE `id` = ?', array($id));
    }

    public function fetchTemplate($id): bool | array | null
    {
        return sqlQuery('SELECT * FROM `document_templates` WHERE `id` = ?', array($id));
    }

    public function getDefaultCategories(): array
    {
        $rtn = sqlStatement('SELECT `option_id`, `title`, `seq` FROM `list_options` WHERE `list_id` = ? ORDER BY `seq`', array('Document_Template_Categories'));
        $category_list = array();
        while ($row = sqlFetchArray($rtn)) {
            $category_list[] = $row;
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
