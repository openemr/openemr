    <?php
/**
 * Adding thumbnails to all files
 * User: amiel
 * Date: 30/05/16
 * Time: 14:55
 */
require_once dirname(__FILE__) . '/../../../interface/globals.php';
require_once dirname(__FILE__) . '/Thumbnail.class.php';
require_once dirname(__FILE__) . '/../CouchDB.class.php';

/**
 * Class ThumbnailGenerator
 */
class ThumbnailGenerator{

    public static $types_support = array('image/png', 'image/jpeg', 'image/jpg', 'image/gif');
    private $thumb_obj = null;
    private $couch_obj = null;

    /**
     * ThumbnailGenerator constructor.
     */
    public function __construct()
    {
        $thumb_size = ($GLOBALS['thumb_size'] > 0) ? $GLOBALS['thumb_size'] : null;
        $this->thumb_obj = new Thumbnail($thumb_size);
    }

    /**
     * @return array
     */
    public static function get_types_support()
    {
        foreach(self::$types_support as $value){
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

        $sql = "SELECT id, url, couch_docid, storagemethod FROM documents
        WHERE mimetype IN (" . implode(',', self::get_types_support()) . ") AND thumb_url IS NULL";

        $results = sqlStatement($sql);
        while($row = sqlFetchArray($results)) {

            switch((int)$row['storagemethod']) {
                //for hard disk store
                case 0:
                    $new_file = $this->generate_HD_file($row['url']);
                    break;
                //for CouchDB store
                case 1:
                    $new_file =  $this->generate_couch_file($row['couch_docid'], $row['url']);
                    break;
                default:
                    $this->error_log($row['url']);continue;
            }
            // Write error to log if failed
            if (!$new_file) {
                $this->error_log($row['url']);
                $feedback['sum_failed'] ++;
                $feedback['failed'][] = $row['url'];
                continue;
            }

            $sql = "UPDATE documents SET thumb_url = ? WHERE id = ?";
            $update = sqlStatement($sql, array($new_file, $row['id']));
            if($update) {
               $feedback['sum_success'] ++;
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
    private function generate_HD_file($url)
    {
        //remove 'file://'
        $url = substr($url, 7);
        $path_parts = pathinfo($url);

        if(!is_file($url) || empty($path_parts['extension'])) {
            return false;
        }

        $resource = $this->thumb_obj->create_thumbnail($url);
        if(!$resource) {
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
        if( is_null($this->couch_obj)) {
            $this->couch_obj = new CouchDB();
        }
        $data = array($GLOBALS['couchdb_dbase'],$doc_id);
        $resp = $this->couch_obj->retrieve_doc($data);

        if(empty($resp->data)) {
            return false;
        }

        $resource = $this->thumb_obj->create_thumbnail(null,base64_decode($resp->data));
        if(!$resource) {
            return false;
        }

        $new_file_content = $this->thumb_obj->get_string_file($resource);

        if(!$new_file_content) {
            return false;
        }
        $couch_row = get_object_vars($resp);

        $couch_row['th_data'] = json_encode(base64_encode($new_file_content));
        $array_update = array_values($couch_row);
        array_unshift($array_update,$GLOBALS['couchdb_dbase']);
        $update_couch = $this->couch_obj->update_doc($array_update);

        $thumb_name = $this->get_thumb_name($file_name);
        return $thumb_name;
    }

    /**
     * Return file name for thumbnail (adding 'th_')
     * @param $file_name
     */
    private function get_thumb_name($file_name) {
        return 'th_' . $file_name;
    }


    /**
     * save error in error log
     * @param $url
     */
    private function error_log($url){

        error_log('Failed to create thumbnail of ' . $url);
    }
}



