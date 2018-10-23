<?php
/**
 * AppointmentService
 *
 * Copyright (C) 2018 Matthew Vita <matthewvita48@gmail.com>
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

namespace OpenEMR\Services;

use Particle\Validator\Validator;

class DocumentService
{
    public function __construct()
    {

    }

    public function isValidPath($path)
    {
        $docPathParts = explode("/", $path);

        unset($docPathParts[0]);

        $categoriesSql  = "  SELECT parent, id";
        $categoriesSql .= "    FROM categories";
        $categoriesSql .= "    WHERE replace(LOWER(name), ' ', '') = ?";

        $lastParent = null;
        $isValidPath = true;
        foreach ($docPathParts as $index => $part) {
            $categoryResults = sqlQuery($categoriesSql, str_replace("_", "", $part));

            if ($index === 1) {
                $lastParent = $categoryResults["id"];
                continue;
            }

            if ($categoryResults["parent"] === $lastParent) {
                $lastParent = $categoryResults["id"];
            } else {
                $isValidPath = false;
                break;
            }
        }

        return $isValidPath;
    }

    public function getLastIdOfPath($path)
    {
        $docPathParts = explode("/", $path);
        $lastInPath = end($docPathParts);

        $sql  = "  SELECT id";
        $sql .= "    FROM categories";
        $sql .= "    WHERE replace(LOWER(name), ' ', '') = ?";

        $results = sqlQuery($sql, str_replace("_", "", $lastInPath));
        return $results['id'];
    }

    public function getAllAtPath($pid, $path)
    {
        if (!$this->isValidPath($path)) {
            return false;
        }

        $categoryId = $this->getLastIdOfPath($path);

        $documentsSql  = " SELECT doc.url, doc.id, doc.mimetype, doc.docdate";
        $documentsSql .= " FROM documents doc";
        $documentsSql .= " JOIN categories_to_documents ctd on ctd.document_id = doc.id";
        $documentsSql .= " WHERE ctd.category_id = ? and doc.foreign_id = ?";

        $documentResults = sqlStatement($documentsSql, array($categoryId, $pid));

        $fileResults = array();
        while ($row = sqlFetchArray($documentResults)) {
            array_push($fileResults, array(
                "filename" => basename($row["url"]),
                "id" =>  $row["id"],
                "mimetype" =>  $row["mimetype"],
                "docdate" =>  $row["docdate"]
            ));
        }
        return $fileResults;
    }

    public function getFile($pid, $did)
    {
        return sqlQuery("SELECT url FROM documents WHERE id = ? AND foreign_id = ?", array($did, $pid));
    }
}
