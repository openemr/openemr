<?php

/**
 * ONoteService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2017 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

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
     * @param The text of the office note.
     * @return $body New id.
     */
    public function add($body)
    {
        return sqlInsert("INSERT INTO `onotes` (`date`, `body`, `user`, `groupname`, `activity`) VALUES (NOW(), ?, ?, ?, 1)", [$body, $_SESSION["authUser"], $_SESSION['authProvider']]);
    }

    /**
     * Toggles a office note to be enabled.
     *
     * @param $id The office note id.
     * @return true/false if the update was successful.
     */
    public function enableNoteById($id)
    {
        sqlStatement("UPDATE `onotes` SET `activity` = 1 WHERE `id` = ?", [$id]);
    }

    /**
     * Toggles a office note to be disabled.
     *
     * @param $id The office note id.
     * @return true/false if the update was successful.
     */
    public function disableNoteById($id)
    {
        sqlStatement("UPDATE `onotes` SET `activity` = 0 WHERE `id` = ?", [$id]);
    }

    /**
     * Get office notes with filters.
     *
     * @param $activity -1/0/1 to indicate filtered notes.
     * @param $offset The start index for pagination.
     * @param $limit The limit for pagination.
     * @return array of office notes.
     */
    public function getNotes($activity, $offset, $limit)
    {
        $notes = [];
        if (($activity == 0) || ($activity == 1)) {
            $note = sqlStatement("SELECT * FROM `onotes` WHERE `activity` = ? ORDER BY `date` DESC LIMIT " . escape_limit($limit) . " OFFSET " . escape_limit($offset), [$activity]);
        } else {
            $note = sqlStatement("SELECT * FROM `onotes` ORDER BY `date` DESC LIMIT " . escape_limit($limit) . " OFFSET " . escape_limit($offset));
        }
        while ($row = sqlFetchArray($note)) {
            $notes[] = $row;
        }
        return $notes;
    }
}
