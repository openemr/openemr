<?php

/**
 *
 */

namespace OpenEMR\Modules\FormTemplates\Service;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Modules\FormTemplates\Constants;

class FormTemplatesService
{

    const TEMPLATE_TBL = "form_templates_template";

    const FORM_TBL = "form_templates_form";

    public function getRegisteredForms()
    {
        $rs = getRegistered();
        $return = [];
        foreach ($rs as $row) {
            $_tmp = [
                'id' => $row['directory'],
                'name' => $row['name'],
                'type' => 'Registered',
            ];
            $return[] = $_tmp;
        }
        return $return;
    }

    public function getLayoutForms()
    {
        $sql = "SELECT grp_form_id, grp_title, grp_mapping
            FROM layout_group_properties
            WHERE grp_group_id = '' AND grp_activity = 1
            ORDER BY grp_mapping, grp_seq, grp_title";

        $rs = QueryUtils::fetchRecords($sql);
        $return = [];
        foreach ($rs as $row) {
            $_tmp = [
                'id' => $row['grp_form_id'],
                'name' => $row['grp_title'],
                'type' => 'LBF',
            ];
            $return[] = $_tmp;
        }
        return $return;
    }

    public function getAllTemplates()
    {
        $sql = "SELECT * FROM form_templates_template WHERE active = 1";

        // $sql = str_replace(["%template_tbl%, %form_tbl%"], [self::TEMPLATE_TBL, self::FORM_TBL], $sql);
        $query = QueryUtils::fetchRecords($sql);

        $return = [];
        while ($row = QueryUtils::fetchArrayFromResultSet($query)) {
            $return[] = $row;
        }
        return $return;
    }

    public function getTemplateByName(string $templateName)
    {
        $sql = "SELECT * FROM ";
    }

    public function saveNewTemplate(array $template)
    {
        $sql = "SELECT template_id FROM %TEMPLATE_TBL% WHERE form_id = ? AND template_id = ?";
        $sql = str_replace("%TEMPLATE_TBL%", self::TEMPLATE_TBL, $sql);
        $binds = [
            'form_id' => $template['form_id'] ?? '2',
            'template_id' => $template['template_id'] ?? '',
        ];
        $res = QueryUtils::fetchRecordsNoLog($sql, $binds);

        if (count($res) > 1) {
            throw new \Exception("More than 1 template found");
        }

        if (count($res) == 1) {
            // @todo We have a tmeplate, update it!
        }

        $sql = "INSERT INTO %TEMPLATE_TBL% (display_name, machine_name, form_id, acl, beg_effective_date, end_effective_date, active, field_data) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $sql = str_replace("%TEMPLATE_TBL%", self::TEMPLATE_TBL, $sql);
        $res = QueryUtils::sqlInsert($sql, $template);

        if (!$res) {
            throw new \Exception("Could not save data");
        }

        return $res;
    }

    /**
     * Save a new form
     *
     * @param array $form
     * @return void
     */
    public function saveNewForm(array $form)
    {
        $sql = "SELECT form_id FROM %FORM_TBL% WHERE machine_name = ? AND method = ?";
        $sql = str_replace("%FORM_TBL%", self::FORM_TBL, $sql);

        $res = QueryUtils::fetchRecordsNoLog($sql, [$form['machine_name'], $form['method']]);

        if (count($res) > 1) {
            throw new \Exception('More than 1 form identified');
        }

        if (count($res) == 1) {
            // @todo This is an edit
            return $res[0]['form_id'];
        }

        $sql = "INSERT INTO %FORM_TBL% (display_name, machine_name, method, action, active) VALUES (?, ?, ?, ?, ?)";
        $sql = $sql = str_replace("%FORM_TBL%", self::FORM_TBL, $sql);
        $res = QueryUtils::sqlInsert($sql, $form);

        return $res;

    }
}
