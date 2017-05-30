<?php
/**
 * OpenEMR (http://open-emr.org)
 *
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Patient;

/**
 * Class Timeline.
 *
 * @package OpenEMR\Patient
 */
class Timeline
{

    private $pid;

    public function __construct($pid)
    {
        $this->pid = $pid;
    }

    /**
     * List all forms associated with a patient
     *
     * @param array $columns Subset of column names to return. Optional.
     * @return array
     */
    public function forms($columns = null)
    {
        //acl_check() @TODO determine best ACO for this.
        $sql = "SELECT id, date, encounter, form_name, form_id, user, 
                authorized, deleted, formdir, therapy_group_id 
                FROM forms where pid = ?";
        $res = sqlStatement($sql, [$this->pid]);
        $forms = [];
        while ($row = sqlFetchArray($res)) {
            $tmp = [];
            if ($columns !== null) {
                foreach ($row as $key => $value) {
                    if (in_array($key, $columns)) {
                        $tmp["{$key}"] = $value;
                    }
                }
                $forms[] = $tmp;
            } else {
                $forms[] = $row;
            }
        }
        return $forms;
    }

}
