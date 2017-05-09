<?php
/**
 * Low level service for manipulating amendment data
 *
 * All the nitty gritty functions to CRUD amendment data
 *
 * @package OpenEMR
 * @subpackage Amendment
 * @author Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2017 Robert Down
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License 3
 */

namespace OpenEMR\Amendment\Service;

use OpenEMR\Amendment\Exception\AmendmentNotFound;
use Symfony\Component\HttpFoundation\Request;

class Amendment
{

    /** @var Request */
    protected $request;

    protected $twig;

    public function __construct()
    {
        $this->request = Request::createFromGlobals();
    }

    public function save()
    {
        $pid = 0; // @todo somehow get the pid
        $r = $this->request;
        $user = $_SESSION['authUserID'];
        $createdTime = date('Y-m-d H:i');
        $where = "";
        $bindings = [
            DateToYYYYMMDD($r->request->get('amendment_date')),
            $r->request->get('form_amendment_by'),
            $r->request->get('form_amendment_status'),
            $pid,
            $r->request->get('desc'),
            $user,
            $createdTime];

        if ($r->request->has('amendment_id')) {
            $type = "INSERT INTO";
        } else {
            $type = "UPDATE";
            $where = "WHERE amendment_id = ?";
            $bindings[] = $r->request->get('amendmend_id');
        }

        $sql = "{$type} amendments SET
                amendment_date = ?,
                amendment_by = ?,
                amendment_status = ?,
                modified_by = ?,
                modified_time = ?
                {$where}";
        $amendmentId = sqlStatement($sql, $bindings);

        $sql = "INSERT INTO amendments_history SET 
                amendment_id = ?,
                amendment_note = ?,
                amendment_status = ?,
                created_by = ?,
                created_time = ?";
        $bindings = [
            $amendmentId,
            $r->request->get('note'),
            $r->request->get('form_amendment_status'),
            $user,
            $createdTime,
        ];
        sqlStatement($sql, $bindings);

        return true;
    }

    /**
     * Get details of an amendment
     *
     * @param $id string Amendment ID
     * @throws AmendmentNotFound
     * @return array
     */
    public function get($id)
    {
        $sql = "SELECT * FROM amendments WHERE amendment_id = ?";
        $result = sqlStatement($sql, [$id]);
        $resultArray = sqlFetchArray($result);

        if ($resultArray === false) {
            throw new AmendmentNotFound();
        }

        $sql = "SELECT *
                FROM amendments_history AS hx
                INNER JOIN users ON hx.created_by = users.id
                WHERE amendment_id = ?";
        $hxResult = sqlStatement($sql, [$id]);
        $resultArray['history'] = [];
        while ($hxResultArray = sqlFetchArray($hxResult)) {
            $resultArray['history'][] = $hxResultArray;
        }

        return $resultArray;
    }

    /**
     * Return all amendments from a patient.
     *
     * @param $pid int Patient ID
     * @return array
     */
    public function all($pid)
    {
        $sql = "SELECT * FROM amendments WHERE pid = ? ORDER BY amendment_date DESC";
        $result = sqlStatement($sql, [$pid]);
        $return = [];
        if (sqlNumRows($result) > 0) {
            while ($row = sqlFetchArray($result)) {
                $return[] = $row;
            }
        }

        return $return;
    }

}
