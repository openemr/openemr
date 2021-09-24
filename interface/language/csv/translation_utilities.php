<?php

/* Copyright (C) 2014 Kevin Yeh <kevin.y@integralemr.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @link    http://www.open-emr.org
 */

function find_or_create_constant($constant)
{
    $sqlFind = " SELECT cons_id , constant_name FROM lang_constants where BINARY constant_name = ?";
    $result = sqlStatement($sqlFind, array($constant));
    if ($result) {
        $row_count = sqlNumRows($result);
        if ($row_count == 1) {
            $row = sqlFetchArray($result);
            return $row['cons_id'];
        }
        if ($row_count > 1) {
            error_log("Duplicate Entries for language constant:" . $constant);
            $row = sqlFetchArray($result);
            $retval = $row['cons_id'];
            while ($row = sqlFetchArray($result)) {
                $sqlDelete = " DELETE FROM lang_constants where cons_id = ? ";
                sqlStatement($sqlDelete, array($row['cons_id']));
                $sqlDelete = " DELETE FROM lang_definitions where cons_id = ? ";
                sqlStatement($sqlDelete, array($row['cons_id']));
                error_log("DELETED Definitions for duplicate constant:" . $constant . "|" . $row['cons_id']);
            }
            return $retval;
        }
        if ($row_count == 0) {
            $sqlInsert = " INSERT INTO lang_constants (constant_name) VALUES (?)";
            $new_index = sqlInsert($sqlInsert, array($constant));
            return $new_index;
        }
    }
}

function verify_translation($constant, $definition, $language, $replace = true, $source = "", $preview = false)
{
    if (empty($constant) || empty($definition)) {
        return '[1]' . xl("Empty Definition");
    }
    $cons_id = find_or_create_constant($constant);
    $whereClause = " lang_id = ? and cons_id = ? ";
    $sqlFind = " SELECT def_id, definition FROM lang_definitions WHERE " . $whereClause;
    $result = sqlStatement($sqlFind, array($language, $cons_id));
    $infoText = $constant . "|" . $definition;
    if ($result) {
        $row_count = sqlNumRows($result);
        if ($row_count == 1) {
            $row = sqlFetchArray($result);
            $row['definition'] = iconv('utf-8', 'utf-8', $row['definition']);
            if ($row['definition'] === $definition) {
                return '[2]' . xl('Definition Exists') . ':' . $infoText;
            } else {
                if ($replace) {
                    $sqlUpdate = " UPDATE lang_definitions SET definition=? WHERE def_id=?";
                    if (!$preview) {
                        $result = sqlStatement($sqlUpdate, array($definition,$row['def_id']));
                    }
                    return '[3]' . xl('Update From') . ':' . $row['definition'] . ' => ' . $definition . ' (' . xl('for') . ': ' . $constant . ')';
                } else {
                    return '[4]' . xl('Definition Not Updated') . ': ' . xl('Current') . $row['definition'] . '|' . $infoText;
                }
            }
        }
        if ($row_count > 1) {
            // Too many definitions, delete then recreate.
            if (!$preview) {
                $sqlDelete = " DELETE FROM lang_definitions WHERE " . $whereClause;
                sqlStatement($sqlDelete, array($language, $cons_id));
            }
            $create = true;
        }
        if ($row_count == 0) {
            $create = true;
        }
        if ($create) {
            $sqlInsert = " INSERT INTO lang_definitions (cons_id,lang_id,definition) VALUES (?,?,?) ";
            if (!$preview) {
                $id = sqlInsert($sqlInsert, array($cons_id, $language, $definition));
            }
            return '[5]' . xl('Create') . ':' . $constant . ' => ' . $definition;
        }
    }
}

function verify_translations($definitions, $language, $replace = true)
{
    foreach ($definitions as $constant => $definition) {
        verify_translation($constant, $definition, $language, $replace);
    }
}

function utf8_fopen_read($fileName)
{
    $fc = iconv('UTF-8', 'UTF-8', file_get_contents($fileName));
    if (empty($fc)) {
        return false;
    }
    $handle = fopen("php://memory", "rw");
    fwrite($handle, $fc);
    fseek($handle, 0);
    return $handle;
}

function verify_file($filename, $language, $replace = true, $source_name = '', $constant_colummn = 0, $definition_column = 1)
{
    if (($handle = utf8_fopen_read("$filename")) !== false) {
        $first = true;
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            $num = count($data);
            if ($num >= 2) {
                $constant   = str_replace("\r\n", "\n", $data[$constant_colummn]);
                $definition = str_replace("\r\n", "\n", $data[$definition_column]);
                if (!$first || $constant != 'constant_name') {
                    $result = verify_translation($constant, $definition, $language, $replace, $source_name);
                    if ((strpos($result, '[2]') !== 0) && (strpos($result, '[1]') !== 0)) {
                        echo text(substr($result, 3)) . "<br>";
                    }
                }
                $first = false;
            }
        }
        fclose($handle);
    }
}
