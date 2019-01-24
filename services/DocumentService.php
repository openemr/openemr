<?php
/**
 * DocumentService
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

    public function insertAtPath($pid, $path, $fileData)
    {
        if (!$this->isValidPath($path)) {
            return false;
        }

        $categoryId = $this->getLastIdOfPath($path);

        $nextIdResult = sqlQuery("SELECT MAX(id)+1 as id FROM documents");

        $insertDocSql  = " INSERT INTO documents SET";
        $insertDocSql .= "   id=?,";
        $insertDocSql .= "   type='file_url',";
        $insertDocSql .= "   size=?,";
        $insertDocSql .= "   date=NOW(),";
        $insertDocSql .= "   url=?,";
        $insertDocSql .= "   mimetype=?,";
        $insertDocSql .= "   foreign_id=?";

        sqlInsert(
            $insertDocSql,
            array(
                $nextIdResult["id"],
                $fileData["size"],
                $GLOBALS['oer_config']['documents']['repository'] . $categoryId . "/" . $fileData["name"],
                $fileData["type"],
                $pid
            )
        );

        $cateToDocsSql  = " INSERT INTO categories_to_documents SET";
        $cateToDocsSql .= "    category_id=?,";
        $cateToDocsSql .= "    document_id=?";

        sqlInsert(
            $cateToDocsSql,
            array(
                $categoryId,
                $nextIdResult["id"]
            )
        );

        $newPath = $GLOBALS['oer_config']['documents']['repository'] . "/" . $categoryId;
        if (!file_exists($newPath)) {
            mkdir($newPath, 0700, true);
        }

        $moved = move_uploaded_file($fileData["tmp_name"], $newPath . "/" . $fileData["name"]);

        return $moved;
    }

    public function getFile($pid, $did)
    {
        return sqlQuery("SELECT url FROM documents WHERE id = ? AND foreign_id = ?", array($did, $pid));
    }
}
