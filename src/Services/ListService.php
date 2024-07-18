<?php

/**
 * ListService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use Particle\Validator\Validator;
use OpenEMR\Common\Uuid\UuidRegistry;

// TODO: @adunsulag should we rename this to be ListOptions service since that is the table it corresponds to?  The lists table is a patient issues table so this could confuse new developers
class ListService
{
  /**
   * Default constructor.
   */
    public function __construct()
    {
    }

    public function validate($list)
    {
        $validator = new Validator();

        $validator->required('title')->lengthBetween(2, 255);
        $validator->required('type')->lengthBetween(2, 255);
        $validator->required('pid')->numeric();
        $validator->optional('diagnosis')->lengthBetween(2, 255);
        $validator->optional('begdate')->datetime('Y-m-d H:i:s');
        $validator->optional('enddate')->datetime('Y-m-d H:i:s');

        return $validator->validate($list);
    }

    public function getAll($pid, $list_type)
    {
        $sql = "SELECT * FROM lists WHERE pid=? AND type=? ORDER BY date DESC";

        $statementResults = sqlStatement($sql, array($pid, $list_type));

        $results = array();
        while ($row = sqlFetchArray($statementResults)) {
            $row['uuid'] = UuidRegistry::uuidToString($row['uuid']);
            array_push($results, $row);
        }

        return $results;
    }

    public function getListOptionsForLists($lists)
    {
        $sql = "SELECT * FROM list_options WHERE list_id IN (" . str_repeat('?,', count($lists) - 1) . "?) "
            . " ORDER BY list_id, seq";
        $records = QueryUtils::fetchRecords($sql, $lists, false);
        return $records;
    }

    public function getListIds()
    {
        $sql = "SELECT DISTINCT list_id FROM list_options ORDER BY list_id";
        return QueryUtils::fetchTableColumn($sql, 'list_id', []);
    }

    public function getOptionsByListName($list_name, $search = array())
    {
        $sql = "SELECT * FROM list_options WHERE list_id = ? ";
        $binding = [$list_name];


        $whitelisted_columns = [
            "option_id", "seq", "is_default", "option_value", "mapping", "notes", "codes", "activity", "edit_options", "toggle_setting_1", "toggle_setting_2", "subtype"
        ];
        foreach ($whitelisted_columns as $column) {
            if (!empty($search[$column])) {
                $sql .= " AND $column = ? ";
                $binding[] = $search[$column];
            }
        }
        $sql .= " ORDER BY `seq` ";

        $statementResults = sqlStatementThrowException($sql, $binding);

        $results = array();
        while ($row = sqlFetchArray($statementResults)) {
            array_push($results, $row);
        }

        return $results;
    }

    /**
     * Returns the list option record that was found
     * @param $list_id
     * @param $option_id
     * @param array $search
     * @return array Record
     */
    public function getListOption($list_id, $option_id)
    {
        $records = $this->getOptionsByListName($list_id, ['option_id' => $option_id]);
        if (!empty($records)) { // should only be one record
            return $records[0];
        }
        return null;
    }

    /**
     * Retrieves a list option for a given list by the code value
     * @param string $list_id
     * @param string $code The exact code(s) to match.  This must match the string precisely and fuzzy matching is not currently supported.
     * @return array|null
     */
    public function getListOptionByCode(string $list_id, string $code)
    {
        $records = $this->getOptionsByListName($list_id, ['codes' => $code]);
        if (!empty($records)) { // should only be one record
            return $records[0];
        }
        return null;
    }

    public function getOne($pid, $list_type, $list_id)
    {
        $sql = "SELECT * FROM lists WHERE pid=? AND type=? AND id=? ORDER BY date DESC";

        return sqlQuery($sql, array($pid, $list_type, $list_id));
    }

    public function insert($data)
    {
        $sql  = " INSERT INTO lists SET";
        $sql .= "     date=NOW(),";
        $sql .= "     activity=1,";
        $sql .= "     pid=?,";
        $sql .= "     type=?,";
        $sql .= "     title=?,";
        $sql .= "     begdate=?,";
        $sql .= "     enddate=?,";
        $sql .= "     diagnosis=?";

        return sqlInsert(
            $sql,
            array(
                $data['pid'],
                $data['type'],
                $data["title"],
                $data["begdate"],
                $data["enddate"],
                $data["diagnosis"]
            )
        );
    }

    public function update($data)
    {
        $sql  = " UPDATE lists SET";
        $sql .= "     title=?,";
        $sql .= "     begdate=?,";
        $sql .= "     enddate=?,";
        $sql .= "     diagnosis=?";
        $sql .= " WHERE id=?";

        return sqlStatement(
            $sql,
            array(
                $data["title"],
                $data["begdate"],
                $data["enddate"],
                $data["diagnosis"],
                $data["id"]
            )
        );
    }

    public function delete($pid, $list_id, $list_type)
    {
        $sql  = "DELETE FROM lists WHERE pid=? AND id=? AND type=?";

        return sqlStatement($sql, array($pid, $list_id, $list_type));
    }
}
