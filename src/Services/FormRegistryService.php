<?php

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;

class FormRegistryService
{
    const FORM_SOURCE_MODULE = 'module';
    const FORM_SOURCE_CORE = 'core';
    const FORM_SQL_INSTALLED = 1;
    const FORM_SQL_NOT_INSTALLED = 0;
    const FORM_STATE_ENABLED = 1;
    const FORM_STATE_REGISTERED = 0;
    const FORM_PACKAGE_STATE_UNPACKAGED = 1;
    const FORM_PATIENT_ENCOUNTER_ENABLED = 1;
    const FORM_PATIENT_ENCOUNTER_DISABLED = 0;
    const FORM_THERAPY_GROUP_ENCOUNTER_ENABLED = 1;
    const FORM_THERAPY_GROUP_ENCOUNTER_DISABLED = 0;

    private string $formDir;
    public function __construct()
    {
        // realpath strips last slash
        $this->formDir = realpath($GLOBALS['srcdir'] . "/../interface/forms") . DIRECTORY_SEPARATOR;
    }

    public function getRegisteredForms(string $state = self::FORM_STATE_ENABLED, string $encounterType = 'all'): array
    {
        $sql = "SELECT * FROM registry WHERE state LIKE ? ";
        if ($encounterType !== 'all') {
            $sql .= match ($encounterType) {
                'patient' => 'AND patient_encounter = 1 ',
                'therapy_group' => 'AND therapy_group_encounter = 1 ',
                default => ''
            };
        }
        $sql .= "ORDER BY priority, name";

        $res = sqlStatement($sql, array($state));
        $forms = [];
        while ($row = sqlFetchArray($res)) {
            $row = $this->hydrateRegistryRecord($row);
            $forms[] = array_merge($row, ['status' => 'registered']);
        }
        return $forms;
    }

    public function getUnregisteredForms(): array
    {
        $forms = [];
        $registeredDirs = array_column($this->getRegisteredForms('%'), 'directory');

        $dp = opendir($this->formDir);
        while (false !== ($fname = readdir($dp))) {
            if ($this->isValidFormDirectory($fname) && !in_array($fname, $registeredDirs)) {
                $forms[] = $this->createUnregisteredFormRecord($fname);
            }
        }
        closedir($dp);

        return $forms;
    }

    public function getAllForms(): array
    {
        return array_merge(
            $this->getRegisteredForms('%'),
            $this->getUnregisteredForms()
        );
    }

    private function isValidFormDirectory(string $fname): bool
    {
        return $fname != "." && $fname != ".." && $fname != "CVS" && $fname != "LBF" &&
            (is_dir($this->formDir . $fname) ||
                stristr($fname, ".tar.gz") ||
                stristr($fname, ".tar") ||
                stristr($fname, ".zip") ||
                stristr($fname, ".gz"));
    }

    private function createUnregisteredFormRecord(string $fname): array
    {
        $info_file = $this->getRealFormPath($this->formDir . $fname . "/info.txt");
        $info = $this->getFormInfo($fname, $info_file);
        return [
            'name' => $info['name'],
            'directory' => $fname,
            'id' => 0,
            'sql_run' => 0,
            'unpackaged' => $this->isExtractedForm($fname) ? 1 : 0,
            'state' => 0,
            'category' => $info['category'],
            'nickname' => '',
            'date' => date('Y-m-d H:i:s'),
            'aco_spec' => 'encounters|notes',
            'form_source' => self::FORM_SOURCE_CORE,
            'status' => 'unregistered',
            'sql_file' => $this->formDir . $fname . "/table.sql",
            'info_file' => $info_file
        ];
    }

    private function getFormInfo(string $fname, string $filePath): array
    {
        $form_title_file = @file($filePath);
        return [
            'name' => $form_title_file ? $form_title_file[0] : $fname,
            'category' => $form_title_file ? ($form_title_file[1] ?? 'Miscellaneous') : 'Miscellaneous'
        ];
    }

    private function isExtractedForm(string $fname): bool
    {
        return !stristr($fname, ".tar.gz") && !stristr($fname, ".tar") &&
            !stristr($fname, ".zip") && !stristr($fname, ".gz");
    }

    // ai generated
    public function registerModuleForm(array $form): int
    {
        self::validateModuleForm($form);
        self::verifyActiveModule($form['mod_id']);

        return self::insertOrUpdateRegistry($form);
    }

    private function validateModuleForm(array $form): void
    {
        $required = ['name', 'directory', 'state', 'nickname', 'priority', 'category', 'aco_spec', 'sql_run', 'mod_id'];
        foreach ($required as $key) {
            if (!isset($form[$key])) {
                throw new \InvalidArgumentException("Missing required key $key in module form registration");
            }
        }
    }

    private function verifyActiveModule(int $modId): void
    {
        $moduleActive = sqlQuery(
            "SELECT mod_id FROM modules WHERE mod_id = ? AND mod_active = 1",
            array($modId)
        );
        if (!$moduleActive) {
            throw new \InvalidArgumentException('Module not found or not active');
        }
    }

    private function insertOrUpdateRegistry(array $form): int
    {
        $exists = sqlQuery(
            "SELECT id FROM registry WHERE directory = ? AND module_id = ?",
            array($form['directory'], $form['mod_id'])
        );

        if ($exists) {
            sqlStatement(
                "UPDATE registry SET name = ?, state = ?, nickname = ?, priority = ?,
                category = ?, aco_spec = ?, sql_run = ? WHERE id = ?",
                array(
                    $form['name'], $form['state'], $form['nickname'], $form['priority'],
                    $form['category'], $form['aco_spec'], $form['sql_run'], $exists['id']
                )
            );
            return $exists['id'];
        }

        return sqlInsert(
            "INSERT INTO registry SET name = ?, directory = ?, state = ?, nickname = ?,
            priority = ?, category = ?, aco_spec = ?, sql_run = ?, form_type = 'module',
            module_id = ?, date = NOW()",
            array(
                $form['name'], $form['directory'], $form['state'], $form['nickname'],
                $form['priority'], $form['category'], $form['aco_spec'], $form['sql_run'],
                $form['mod_id']
            )
        );
    }
    // end ai generation



//these are the functions used to access the forms registry database, comes from registry.inc.php
//

    public function registerForm($directory, $sql_run = 0, $unpackaged = 1, $state = 0)
    {
        $check = sqlQuery("select state from registry where directory=?", array($directory));
        if ($check == false) {
            $forms = $this->getAllForms();
            // loop through and find the form
            $foundForm = null;
            foreach ($forms as $form) {
                if ($form['directory'] == $directory) {
                    $foundForm = $form;
                    break;
                }
            }
            if (empty($form)) {
                return false;
            }

            $info = $this->getFormInfo($directory, $form['info_file']);
            if (empty($info)) {
                $info = [
                    'name' => $directory
                    ,'category' => 'Miscellaneous'
                ];
            }
            return sqlInsert("insert into registry set
			name=?,
			state=?,
			directory=?,
			sql_run=?,
            unpackaged=?,
            category=?,
			date=NOW()
		", array($info['name'], $state, $directory, $sql_run, $unpackaged, $info['category']));
        }

        return false;
    }

    public function updateRegistered($id, $mod)
    {
        return QueryUtils::sqlStatementThrowException("update registry set $mod, date=NOW() where id=?", array($id));
    }

    /**
     * @param string $state
     * @param string $limit
     * @param string $offset
     * @param string $encounterType all|patient|therapy_group
     */
    public function getRegistered($state = self::FORM_STATE_ENABLED, $limit = "unlimited", $offset = "0", $encounterType = 'all')
    {
        $sql = "select * from registry where state like ? ";
        if ($encounterType !== 'all') {
            switch ($encounterType) {
                case 'patient':
                    $sql .= 'AND patient_encounter = 1 ';
                    break;
                case 'therapy_group':
                    $sql .= 'AND therapy_group_encounter = 1 ';
                    break;
            }
        }
        $sql .= "order by priority, name ";
        if ($limit != "unlimited") {
            $sql .= " limit " . escape_limit($limit) . ", " . escape_limit($offset);
        }

        $res = sqlStatement($sql, array($state));
        if ($res) {
            for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
                $all[$iter] = $row;
            }
        } else {
            return false;
        }

        return $all;
    }

    public function getRegistryEntry($id)
    {
        $records = QueryUtils::fetchRecords("select * from registry where id=?", array($id));
        if (!empty($records[0])) {
            return $this->hydrateRegistryRecord($records[0]);
        }
        return null;
    }

    private function hydrateRegistryRecord($record)
    {
        $record['form_source'] = self::FORM_SOURCE_CORE;
        $record['sql_file'] = $this->getRealFormPath($this->formDir . $record['directory'] . DIRECTORY_SEPARATOR . "table.sql");
        $record['info_file'] = $this->getRealFormPath($this->formDir . $record['directory'] . DIRECTORY_SEPARATOR . "info.txt");
        return $record;
    }

    public function getRegistryEntryByDirectory($directory, $cols = "*")
    {
        $sql = "select " . escape_sql_column_name(process_cols_escape($cols), array('registry')) . " from registry where directory = ?";
        return sqlQuery($sql, $directory);
    }

    public function installSQL($entryId)
    {
        if (empty($entryId)) {
            return false;
        }
        $entry = $this->getRegistryEntry($entryId);
        $sqltext = $entry['sql_file'];
        if (empty($sqltext)) {
            return false;
        }
        if ($sqlarray = @file($sqltext)) {
            $sql = implode("", $sqlarray);
            //echo "<br />$sql<br /><br />";
            $sqla = explode(";", $sql);
            foreach ($sqla as $sqlq) {
                if (strlen($sqlq) > 5) {
                    sqlStatement(rtrim("$sqlq"));
                }
            }

            return true;
        } else {
            return false;
        }
    }


    /*
     * is a form registered
     *  (optional - and active)
     * in the database?
     *
     * NOTE - sometimes the Name of a form has a line-break at the end, thus this function might be better
     *
     *  INPUT =   directory => form directory
     *            state => 0=inactive / 1=active
     *  OUTPUT = true or false
     */
    public function isRegistered($directory, $state = self::FORM_STATE_ENABLED)
    {
        $sql = "select id from registry where directory=? and state=?";
        $result = sqlQuery($sql, array($directory, $state));
        if (!empty($result['id'])) {
            return true;
        }

        return false;
    }

    public function getTherapyGroupCategories()
    {
        return array('');
    }

// This gets an array including both standard and LBF visit form types,
// one row per form type, sorted by category, priority, is lbf, name.
//
    public function getFormsByCategory($state = '1', $lbfonly = false)
    {
        global $attendant_type;
        $all = array();
        if (!$lbfonly) {
            // First get the traditional form types from the registry table.
            $sql = "SELECT category, nickname, name, state, directory, id, sql_run, " .
                "unpackaged, date, priority, aco_spec FROM registry WHERE ";
            if (($attendant_type ?? 'pid') == 'pid') {
                $sql .= "patient_encounter = 1 AND ";
            } else {
                $sql .= "therapy_group_encounter = 1 AND ";
            }
            $sql .= "state LIKE ? ORDER BY category, priority, name";
            $res = sqlStatement($sql, array($state));
            if ($res) {
                while ($row = sqlFetchArray($res)) {
                    // Flag this entry as not LBF
                    $row['LBF'] = false;
                    $all[] = $row;
                }
            }
        }

        // Merge LBF form types into the registry array of form types.
        // Note that the mapping value is used as the category name.
        $lres = sqlStatement(
            "SELECT * FROM layout_group_properties " .
            "WHERE grp_form_id LIKE 'LBF%' AND grp_group_id = '' AND grp_activity = 1 " .
            "ORDER BY grp_mapping, grp_seq, grp_title"
        );
        while ($lrow = sqlFetchArray($lres)) {
            $rrow = array();
            $rrow['category'] = $lrow['grp_mapping'] ? $lrow['grp_mapping'] : 'Clinical';
            $rrow['name'] = $lrow['grp_title'];
            $rrow['nickname'] = $lrow['grp_title'];
            $rrow['directory'] = $lrow['grp_form_id']; // should start with LBF
            $rrow['priority'] = $lrow['grp_seq'];
            $rrow['aco_spec'] = $lrow['grp_aco_spec'];
            $rrow['LBF'] = true; // Flag this form as LBF
            $all[] = $rrow;
        }

        // Sort by category, priority, is lbf, name.
        usort($all, function ($a, $b) {
            // Anonymous functions supported as of PHP 5.3. Yay!
            if ($a['category'] == $b['category']) {
                if ($a['priority'] == $b['priority']) {
                    if ($a['LBF'] == $b['LBF']) {
                        $name1 = $a['nickname'] ? $a['nickname'] : $a['name'];
                        $name2 = $b['nickname'] ? $b['nickname'] : $b['name'];
                        if ($name1 == $name2) {
                            return 0;
                        }
                        return $name1 < $name2 ? -1 : 1;
                    } else {
                        // Sort LBF with the same priority after standard forms
                        return $b['LBF'] ? -1 : 1;
                    }
                }
                return $a['priority'] < $b['priority'] ? -1 : 1;
            }
            return $a['category'] < $b['category'] ? -1 : 1;
        });
        return $all;
    }

    private function getRealFormPath(string $formPath)
    {
        $path = realpath($formPath);
        if (str_starts_with($path, $this->formDir)) {
            return $path;
        } else {
            (new SystemLogger())->errorLogCaller("Invalid file path found for module, verify this is not a security risk", ['path' => $path]);
            return null;
        }
    }
}
