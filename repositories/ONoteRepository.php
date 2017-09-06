<?php
/**
 * Office note repository.
 *
 * Copyright (C) 2017 Matthew Vita <matthewvita48@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Matthew Vita <matthewvita48@gmail.com>
 * @link    http://www.open-emr.org
 */

namespace OpenEMR\Repositories;

use Doctrine\ORM\EntityRepository;
use OpenEMR\Entities\ONote;

class ONoteRepository extends EntityRepository
{
    /**
     * Add new office note.
     *
     * @param $note The new office note.
     * @return the new id.
     */
    public function save(ONote $note)
    {
        $this->_em->persist($note);
        $this->_em->flush();
        return $note->getId();
    }

    /**
     * @param $id The office note id.
     * @return The single note.
     */
    public function findNoteById($id)
    {
        $result = $this->_em->getRepository($this->_entityName)->findOneBy(array("id" => $id));
        return $result;
    }

    /**
     * Toggles a office note to be enabled.
     *
     * @param $id The office note id.
     * @return true/false if the update was successful.
     */
    public function enableNoteById($id)
    {
        $result = false;

        try {
            $note = $this->findNoteById($id);

            if ($note) {
                $note->setActivity(1);
                $this->_em->persist($note);
                $this->_em->flush();
                $result = true;
            }
        } catch (Exception $e) {
        }

        return $result;
    }

    /**
     * Toggles a office note to be enabled.
     *
     * @param $id The office note id.
     * @return true/false if the update was successful.
     */
    public function disableNoteById($id)
    {
        $result = false;

        try {
            $note = $this->findNoteById($id);

            if ($note) {
                $note->setActivity(0);
                $this->_em->persist($note);
                $this->_em->flush();
                $result = true;
            }
        } catch (Exception $e) {
        }

        return $result;
    }

    /**
     * Get office notes with filters, Sorted by DESC. Note
     * that -1 indicates that all activity types should be
     * returned.
     *
     * @param $activity -1/0/1 to indicate filtered notes.
     * @param $offset The start index for pagination.
     * @param $limit The limit for pagination.
     * @return list of office notes.
     */
    public function getNotes($activity, $offset, $limit)
    {
        if (!is_numeric($offset) || !is_numeric($limit)) {
            return null;
        }

        $criteria = array();

        if ($activity == 1) {
            $criteria["activity"] = 1;
        } else if ($activity == 0) {
            $criteria["activity"] = 0;
        }

        $result = $this->_em->getRepository($this->_entityName)->findBy(
            $criteria,
            array("date" => "DESC"),
            $limit,
            $offset
        );

        return $result;
    }

    /**
     * An example of how to use HQL with JOINs. Only use
     * HQL when the methods that EntityRepository provides
     * cannot sufficiently meet the complexity of your query.
     */
    public function findAllHqlExample()
    {
        // $sql  = "SELECT o ";
        // $sql .= "FROM ONote o ";
        // $sql .= "JOIN User u ";
        // $sql .= "WITH o.user = u.username";

        // return $this->_em->createQuery($sql)->getResult();
    }
}
