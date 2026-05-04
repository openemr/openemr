<?php

/**
 * ONoteService
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2017 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;

class ONoteService
{
    /**
     * Default constructor.
     */
    public function __construct()
    {
    }

    /**
     * Creates a new office note.
     *
     * @param  string $body The text of the office note.
     * @return int The id of the newly inserted office note.
     * @throws \OpenEMR\Common\Database\SqlQueryException On database error from the underlying insert.
     */
    public function add($body)
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        return QueryUtils::sqlInsert("INSERT INTO `onotes` (`date`, `body`, `user`, `groupname`, `activity`) VALUES (NOW(), ?, ?, ?, 1)", [$body, $session->get("authUser"), $session->get('authProvider')]);
    }

    /**
     * Toggles an office note to be enabled.
     *
     * @param  int|string $id The office note id.
     * @return void
     * @throws \OpenEMR\Common\Database\SqlQueryException On database error from the underlying update.
     */
    public function enableNoteById($id)
    {
        QueryUtils::sqlStatementThrowException("UPDATE `onotes` SET `activity` = 1 WHERE `id` = ?", [$id]);
    }

    /**
     * Toggles an office note to be disabled.
     *
     * @param  int|string $id The office note id.
     * @return void
     * @throws \OpenEMR\Common\Database\SqlQueryException On database error from the underlying update.
     */
    public function disableNoteById($id)
    {
        QueryUtils::sqlStatementThrowException("UPDATE `onotes` SET `activity` = 0 WHERE `id` = ?", [$id]);
    }

    public function updateNoteById($id, $body)
    {
        QueryUtils::sqlStatementThrowException("UPDATE `onotes` SET `body` = ? WHERE `id` = ?", [$body, $id]);
    }

    public function deleteNoteById($id)
    {
        QueryUtils::sqlStatementThrowException("DELETE FROM `onotes` WHERE `id` = ?", [$id]);
    }

    /**
     * Get office notes with filters.
     *
     * @param $activity -1/0/1 to indicate filtered notes.
     * @param $offset   The start index for pagination.
     * @param $limit    The limit for pagination.
     * @return array of office notes.
     */
    public function getNotes($activity, $offset, $limit)
    {
        if (($activity == 0) || ($activity == 1)) {
            return QueryUtils::fetchRecords("SELECT * FROM `onotes` WHERE `activity` = ? ORDER BY `date` DESC LIMIT " . escape_limit($limit) . " OFFSET " . escape_limit($offset), [$activity]);
        }
        return QueryUtils::fetchRecords("SELECT * FROM `onotes` ORDER BY `date` DESC LIMIT " . escape_limit($limit) . " OFFSET " . escape_limit($offset));
    }

    public function countNotes($active)
    {
        $row = QueryUtils::querySingleRow("SELECT COUNT(*) AS cnt FROM onotes WHERE activity = ? OR ? = -1", [$active, $active]);
        return $row['cnt'] ?? 0;
    }
}
