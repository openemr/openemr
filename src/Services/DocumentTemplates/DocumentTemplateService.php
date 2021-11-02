<?php

namespace OpenEMR\Services\DocumentTemplates;

class DocumentTemplateService
{
    public function __construct()
    {
    }

    public function getTemplateListAllCategories(): array
    {
        $results = array();
        $query_result = sqlStatement('SELECT * FROM `document_templates` WHERE pid = ? ORDER BY `category`', array(0));
        while ($row = sqlFetchArray($query_result)) {
            if (is_array($row)) {
                $results[$row['category'] ?: 'Default'][] = array(
                    'id' => $row['id'],
                    'pid' => $row['pid'],
                    'category' => $row['category'],
                    'name' => $row['template_name'],
                    'size' => strlen($row['template_content'] ?? ''),
                    'modified_date' => $row['modified_date'],
                    'location' => $row['location']
                );
            }
        }
        return $results;
    }

    public function getTemplateListByCategory($category = null): array
    {
        $results = array($category => null);
        $query_result = sqlStatement('SELECT * FROM `document_templates` WHERE category = ? AND pid = ?', array($category, 0));
        while ($row = sqlFetchArray($query_result)) {
            if (is_array($row)) {
                $results[$category][] = array(
                    'id' => $row['id'],
                    'pid' => $row['pid'],
                    'category' => $row['category'],
                    'name' => $row['template_name'],
                    'size' => strlen($row['template_content'] ?? ''),
                    'modified_date' => $row['modified_date'],
                    'location' => $row['location']
                );
            }
        }
        return $results;
    }

    public function getTemplateCategoriesByPatient($pid = null, $category = null): array
    {
        $results = array();
        $bind = array($pid ?? 0);
        if (empty($pid)) {
            $where = 'WHERE pid > ?';
        } else {
            $where = 'WHERE pid = ?';
        }
        if (!empty($category)) {
            $where .= ' AND category = ?';
            $bind[] = $category;
        }
        $sql = "SELECT * FROM `document_templates` $where ORDER BY pid, category";
        $query_result = sqlStatement($sql, $bind);
        while ($row = sqlFetchArray($query_result)) {
            if (is_array($row)) {
                $results[$row['category']][] = array(
                    'id' => $row['id'],
                    'pid' => $row['pid'],
                    'category' => $row['category'],
                    'name' => $row['template_name'],
                    'size' => strlen($row['template_content'] ?? ''),
                    'modified_date' => $row['modified_date'],
                    'location' => $row['location']
                );
            }
        }
        return $results;
    }

    public function uploadTemplate($template_name, $category, $file, $pid = 0): int
    {
        $content = file_get_contents($file);
        return $this->insertTemplate($pid, $category, $template_name, $content);
    }

    public function insertTemplate($pid, $category, $template, $content): int
    {
        $name = null;
        if (!empty($pid)) {
            $name = sqlQuery("SELECT patient_data.pid, Concat_Ws(', ', patient_data.lname, patient_data.fname) as name FROM patient_data WHERE pid = ?", array($pid))['name'];
        }
        $sql = 'INSERT INTO `document_templates` (`pid`, `provider`, `category`, `template_name`, `location`, `template_content`) VALUES (?, ?, ?, ?, ?, ?)';
        return sqlInsert($sql, array($pid, ($_SESSION['authUserID'] ?? null), $category, $template, $name, $content));
    }

    public function updateTemplateContent($id, $content): bool|array|null
    {
        return sqlQuery('UPDATE `document_templates` SET `template_content` = ?, modified_date = NOW() WHERE `id` = ?', array($content, $id));
    }

    public function updateTemplate($id, $category, $template, $content)
    {

    }

    public function deleteTemplate($id): bool|array|null
    {
        return sqlQuery('DELETE FROM `document_templates` WHERE `id` = ?', array($id));
    }

    public function fetchTemplate($id): bool|array|null
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

}