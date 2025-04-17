<?php

/**
 * Adding thumbnails to all files
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
 * @author  Amiel Elboim <amielel@matrix.co.il>
 * @link    http://www.open-emr.org
 */


/**
 * Class ThumbnailGenerator
 */
class ThumbnailGenerator
{
    public static $types_support = array('image/png', 'image/jpeg', 'image/jpg', 'image/gif');
    private $thumb_obj = null;
    private $couch_obj = null;

    /**
     * ThumbnailGenerator constructor.
     */
    public function __construct()
    {
        $thumb_size = ($GLOBALS['thumb_doc_max_size'] > 0) ? $GLOBALS['thumb_doc_max_size'] : null;
        $this->thumb_obj = new Thumbnail($thumb_size);
    }

    /**
     * @return array
     */
    public static function get_types_support()
    {
        foreach (self::$types_support as $value) {
            $types_support[] = "'$value'";
        }

        return $types_support;
    }

    static function count_not_generated()
    {
        $sql = "SELECT COUNT(*) AS c FROM documents
        WHERE mimetype IN (" . implode(',', self::get_types_support()) . ") AND thumb_url IS NULL";

        $results = sqlStatement($sql);
        $row = sqlFetchArray($results);

        return $row['c'];
    }

    /**
     * Generating all files with match format that still isn't generated
     * @return array $result (count failed/success)
     */
    public function generate_all()
    {

        $feedback = array('sum_success' => 0, 'sum_failed' => 0, 'success' => array(), 'failed' => array());

        $sql = "SELECT id, url, couch_docid, storagemethod, path_depth FROM documents
        WHERE mimetype IN (" . implode(',', self::get_types_support()) . ") AND thumb_url IS NULL";

        $results = sqlStatement($sql);
        while ($row = sqlFetchArray($results)) {
            switch ((int)$row['storagemethod']) {
                //for hard disk store
                case 0:
                    $new_file = $this->generate_HD_file($row['url'], $row['path_depth']);
                    break;
                //for CouchDB store
                case 1:
                    $new_file =  $this->generate_couch_file($row['couch_docid'], $row['url']);
                    break;
            }

            // Write error to log if failed
            if (!$new_file) {
                $this->error_log($row['url']);
                $feedback['sum_failed']++;
                $feedback['failed'][] = $row['url'];
                continue;
            }

            $sql = "UPDATE documents SET thumb_url = ? WHERE id = ?";
            $update = sqlStatement($sql, array($new_file, $row['id']));
            if ($update) {
                $feedback['sum_success']++;
                $feedback['success'][] = $row['url'];
            }
        }

        return $feedback;
    }

    /**
     * Generate new file and store it in hard disk
     * @param $url
     * @return bool|string
     */
    private function generate_HD_file($url, $path_depth)
    {
        //remove 'file://'
        $url = preg_replace("|^(.*)://|", "", $url);

        //change full path to current webroot.  this is for documents that may have
        //been moved from a different filesystem and the full path in the database
        //is not current.  this is also for documents that may of been moved to
        //different patients. Note that the path_depth is used to see how far down
        //the path to go. For example, originally the path_depth was always 1, which
        //only allowed things like documents/1/<file>, but now can have more structured
        //directories. For example a path_depth of 2 can give documents/encounters/1/<file>
        // etc.
        // NOTE that $from_filename and basename($url) are the same thing
        $from_all = explode("/", $url);
        $from_filename = array_pop($from_all);
        $from_pathname_array = array();
        for ($i = 0; $i < $path_depth; $i++) {
            $from_pathname_array[] = array_pop($from_all);
        }

        $from_pathname_array = array_reverse($from_pathname_array);
        $from_pathname = implode("/", $from_pathname_array);

        $temp_url = $GLOBALS['OE_SITE_DIR'] . '/documents/' . $from_pathname . '/' . $from_filename;

        if (file_exists($temp_url)) {
            $url = $temp_url;
        } else {
            return false;
        }

        $path_parts = pathinfo($url);

        $resource = $this->thumb_obj->create_thumbnail($url);
        if (!$resource) {
            return false;
        }

        $thumb_name = $this->get_thumb_name($path_parts['basename']);
        $full_path = $path_parts['dirname'] . '/' . $thumb_name;
        $new_thumb_file = $this->thumb_obj->image_to_file($resource, $full_path);

        return ($new_thumb_file) ? 'file://' . $full_path : false;
    }


    /**
     * Generate new file and store it in CouchDB
     * @param $doc_id
     * @return bool|string
     */
    private function generate_couch_file($doc_id, $file_name)
    {
        if (is_null($this->couch_obj)) {
            $this->couch_obj = new CouchDB();
        }

        $resp = $this->couch_obj->retrieve_doc($doc_id);

        if (empty($resp->data)) {
            return false;
        }

        $resource = $this->thumb_obj->create_thumbnail(null, base64_decode($resp->data));
        if (!$resource) {
            return false;
        }

        $new_file_content = $this->thumb_obj->get_string_file($resource);

        if (!$new_file_content) {
            return false;
        }

        $couch_row = get_object_vars($resp);

        $couch_row['th_data'] = json_encode(base64_encode($new_file_content));
        $array_update = array_values($couch_row);
        $update_couch = $this->couch_obj->update_doc($array_update);

        $thumb_name = $this->get_thumb_name($file_name);
        return $thumb_name;
    }

    /**
     * Return file name for thumbnail (adding 'th_')
     * @param $file_name
     */
    private function get_thumb_name($file_name)
    {
        return 'th_' . $file_name;
    }


    /**
     * save error in error log
     * @param $url
     */
    private function error_log($url)
    {

        error_log('Failed to create thumbnail of ' . errorLogEscape($url));
    }
}
