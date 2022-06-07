<?php

/**
 * Document - This class is the logical representation of a physical file on some system somewhere
 * that can be referenced with a URL of some type. This URL is not necessarily a web url,
 * it could be a file URL or reference to a BLOB in a db.
 * It is implicit that a document can have other related tables to it at least a one document to many notes
 *  which join on a documents id and categories which do the same.
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Unknown -- No ownership was listed on this document prior to February 5th 2021
 * @author    Stephen Nielson <stephen@nielson.org>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright OpenEMR contributors (c) 2021
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../pnotes.inc");
require_once(__DIR__ . "/../gprelations.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\ORDataObject\ORDataObject;
use OpenEMR\Common\Uuid\UuidRegistry;

class Document extends ORDataObject
{
    public const TABLE_NAME = "documents";

    /**
     * Use the native filesystem to store files at
     */
    public const STORAGE_METHOD_FILESYSTEM = 0;

    /**
     * Use CouchDb to store files at
     */
    public const STORAGE_METHOD_COUCHDB = 1;

    /**
     * Flag that the encryption is on.
     */
    public const ENCRYPTED_ON = 1;

    /**
     * Flag the encryption is off.
     */
    public const ENCRYPTED_OFF = 0;

    /**
     * Date format for the expires field
     */
    public const EXPIRES_DATE_FORMAT = 'Y-m-d H:i:s';

    /*
    *   Database unique identifier
    *   @public id
    */
    public $id;

    /**
     * @var Unique User Identifier that is for both external reference to this entity and for future offline use.
     */
    public $uuid;

    /*
    *  DB unique identifier reference to A PATIENT RECORD, this is not unique in the document table. For actual foreign
    *  keys to a NON-Patient record use foreign_reference_id.  For backwards compatability we ONLY use this for patient
    *  documents.
    *   @public int
    */
    public $foreign_id;

    /**
     * DB Unique identifier reference to another table record in the database.  This is not unique in the document. The
     * table that this record points to is in the $foreign_reference_table
     * @public int
     */
    public $foreign_reference_id;

    /**
     * Database table name for the foreign_reference_id.  This value must be populated if $foreign_reference_id is
     * populated.
     * @public string
     */
    public $foreign_reference_table;

    /*
    *   Enumerated DB field which is met information about how to use the URL
    *   @public int can also be a the properly enumerated string
    */
    public $type;

    /*
    *   Array mapping of possible for values for the type variable
    *   mapping is array text name to index
    *   @public array
    */
    public $type_array = array();

    /*
    *   Size of the document in bytes if that is available
    *   @public int
    */
    public $size;

    /*
    *   Date the document was first persisted
    *   @public string
    */
    public $date;

    /**
     * @public string at which the document can no longer be accessed.
     */
    public $date_expires;

    /*
    *   URL which point to the document, may be a file URL, a web URL, a db BLOB URL, or others
    *   @public string
    */
    public $url;

    /*
    *   URL which point to the thumbnail document, may be a file URL, a web URL, a db BLOB URL, or others
    *   @public string
    */
    public $thumb_url;

    /*
    *   Mimetype of the document if available
    *   @public string
    */
    public $mimetype;

    /*
    *   If the document is a multi-page format like tiff and has at least 1 page this will be 1 or greater,
    *   if a non-multi-page format this should be null or empty
    *   @public int
    */
    public $pages;

    /*
    *   Foreign key identifier of who initially persisited the document,
    *   potentially ownership could be changed but that would be up to an external non-document object process
    *   @public int
    */
    public $owner;

    /*
    *   Timestamp of the last time the document was changed and persisted, auto maintained by DB,
    *   manually change at your own peril
    *   @public int
    */
    public $revision;

    /*
    * Date (YYYY-MM-DD) logically associated with the document, e.g. when a picture was taken.
    * @public string
    */
    public $docdate;

    /*
    * hash key of the document from when it was uploaded.
    * @public string
    */
    public $hash;

    /*
    * DB identifier reference to the lists table (the related issue), 0 if none.
    * @public int
    */
    public $list_id;

    // For name (used in OpenEMR 6.0.0+)
    public $name = null;

    // For label on drive (used in OpenEMR 6.0.0+)
    public $drive_uuid = null;

    // For tagging with the encounter
    public $encounter_id;
    public $encounter_check;

    /*
    *   Whether the file is already imported
    *   @public int
    */
    public $imported;

    /*
    *   Whether the file is encrypted
    *   @public int
    */
    public $encrypted;

    // Storage method
    public $storagemethod;

    // For storing couch docid
    public $couch_docid;

    // For storing couch revid
    public $couch_revid;

    // For storing path depth
    public $path_depth;

    /**
     * Flag that marks the document as deleted or not
     * @public int 1 if deleted, 0 if not
     */
    public $deleted;

    /**
     * Constructor sets all Document attributes to their default value
     * @param int $id optional existing id of a specific document, if omitted a "blank" document is created
     */
    public function __construct($id = "")
    {
        //call the parent constructor so we have a _db to work with
        parent::__construct();

        //shore up the most basic ORDataObject bits
        $this->id = $id;
        $this->_table = self::TABLE_NAME;

        //load the enum type from the db using the parent helper function,
        //this uses psuedo-class variables so it is really cheap
        $this->type_array = $this->_load_enum("type");

        $this->type = $this->type_array[0] ?? '';
        $this->size = 0;
        $this->date = date("Y-m-d H:i:s");
        $this->date_expires = null; // typically no expiration date here
        $this->url = "";
        $this->mimetype = "";
        $this->docdate = date("Y-m-d");
        $this->hash = "";
        $this->list_id = 0;
        $this->encounter_id = 0;
        $this->encounter_check = "";
        $this->encrypted = 0;
        $this->deleted = 0;

        if ($id != "") {
            $this->populate();
        }
    }

    /**
     * Retrieves all of the categories associated with this document
     */
    public function get_categories()
    {
        if (empty($this->get_id())) {
            return [];
        }

        $categories = "Select `id`, `name`, `value`, `parent`, `lft`, `rght`, `aco_spec`,`codes` FROM `categories` "
        . "JOIN `categories_to_documents` `ctd` ON `ctd`.`category_id` = `categories`.`id` "
        . "WHERE `ctd`.`document_id` = ? ";
        $resultSet = sqlStatement($categories, [$this->get_id()]);
        $categories = [];
        while ($category = sqlGetAssoc($resultSet)) {
            $categories[] = $category;
        }
        return $categories;
    }

    /**
     *
     * @return bool true if the document expiration date has expired
     */
    public function has_expired()
    {
        if (!empty($this->date_expires)) {
            $dateTime = DateTime::createFromFormat("Y-m-d H:i:s", $this->date_expires);
            return $dateTime->getTimestamp() >= time();
        }
        return false;
    }

    /**
     * Checks whether the passed in $user can access the document or not.  It checks against all of the access
     * permissions for the categories the document is in.  If there are any categories that the document is tied to
     * that the owner does NOT have access rights to, the request is denied.  If there are no categories tied to the
     * document, default access is granted.
     * @param string|null $username The user (username) we are checking.
     *                              If no user is provided it checks against the currently logged in user
     * @return bool True if the passed in user or current user can access this document, false otherwise.
     */
    public function can_access($username = null)
    {
        $categories = $this->get_categories();

        // no categories to prevent access
        if (empty($categories)) {
            return true;
        }

        // verify that we can access every single category this document is tied to
        foreach ($categories as $category) {
            if (AclMain::aclCheckAcoSpec($category['aco_spec'], $username) === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks if a document has been deleted or not
     * @return bool true if the document is deleted, false otherwise
     */
    public function is_deleted()
    {
        return $this->get_deleted() != 0;
    }

    /**
     * Handles the deletion of a document
     */
    public function process_deleted()
    {
        $this->set_deleted(1);
        $this->persist();
    }

    /**
     * Returns the Document deleted value.  Needed for the ORM to process this value.  Recommended you use
     * is_deleted() instead of this function
     * @return int
     */
    public function get_deleted()
    {
        return $this->deleted;
    }

    /**
     * Sets the Document deleted value.  Used by the ORM to set this flag.
     * @param $deleted 1 if deleted, 0 if not
     */
    public function set_deleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * Convenience function to get an array of many document objects that are linked to a patient
     * For really large numbers of documents there is a way more efficient way to do this
     * by overwriting the populate method
     * @param int $foreign_id optional id use to limit array on to a specific relation,
     *                        otherwise every document object is returned
     */
    function documents_factory($foreign_id = "")
    {
        $documents = array();

        $sqlArray = array();

        if (empty($foreign_id)) {
            $foreign_id_sql = " like '%'";
        } else {
            $foreign_id_sql = " = ?";
            $sqlArray[] = strval($foreign_id);
        }

        $d = new Document();
        $sql = "SELECT id FROM " . escape_table_name($d->_table) . " WHERE foreign_id " . $foreign_id_sql;
        $result = $d->_db->Execute($sql, $sqlArray);

        while ($result && !$result->EOF) {
            $documents[] = new Document($result->fields['id']);
            $result->MoveNext();
        }

        return $documents;
    }

    /**
     * Returns an array of many documents that are linked to a foreign table.  If $foreign_reference_id is populated
     * it will return documents that are specific that that foreign record.
     * @param string $foreign_reference_table The table name that we are retrieving documents for
     * @param string $foreign_reference_id The table record that this document references
     * @return array
     */
    public function documents_factory_for_foreign_reference(string $foreign_reference_table, $foreign_reference_id = "")
    {
        $documents = array();

        $sqlArray = array($foreign_reference_table);

        if (empty($foreign_reference_id)) {
            $foreign_reference_id_sql = " like '%'";
        } else {
            $foreign_reference_id_sql = " = ?";
            $sqlArray[] = strval($foreign_reference_id);
        }

        $d = new Document();
        $sql = "SELECT id FROM " . escape_table_name($d->_table) . " WHERE foreign_reference_table = ? "
        . "AND foreign_reference_id " . $foreign_reference_id_sql;

        (new \OpenEMR\Common\Logging\SystemLogger())->debug(
            "documents_factory_for_foreign_reference",
            ['sql' => $sql,
            'sqlArray' => $sqlArray]
        );

        $result = $d->_db->Execute($sql, $sqlArray);

        while ($result && !$result->EOF) {
            $documents[] = new Document($result->fields['id']);
            $result->MoveNext();
        }

        return $documents;
    }
    public static function getDocumentForUuid($uuid)
    {
        $sql = "SELECT id from " . escape_table_name(self::TABLE_NAME) . " WHERE uuid = ?";
        $id = \OpenEMR\Common\Database\QueryUtils::fetchSingleValue($sql, 'id', [UuidRegistry::uuidToBytes($uuid)]);
        if (!empty($id)) {
            return new Document($id);
        } else {
            return null;
        }
    }

    /**
     * Returns all of the documents for a specific patient
     * @param int $patient_id
     * @return array
     */
    public static function getDocumentsForPatient(int $patient_id)
    {
        $doc = new Document();
        return $doc->documents_factory($patient_id);
    }

    /**
     * Return an array of documents that are connected to another table record in the system.
     * @param int $foreign_id
     * @return Document[]
     */
    public static function getDocumentsForForeignReferenceId(string $foreign_table, int $foreign_id)
    {
        $doc = new self();
        return $doc->documents_factory_for_foreign_reference($foreign_table, $foreign_id);
    }

    /**
     * Convenience function to generate string debug data about the object
     */
    function toString($html = false)
    {
        $string .= "\n"
        . "ID: " . $this->id . "\n"
        . "FID: " . $this->foreign_id . "\n"
        . "type: " . $this->type . "\n"
        . "type_array: " . print_r($this->type_array, true) . "\n"
        . "size: " . $this->size . "\n"
        . "date: " . $this->date . "\n"
        . "url: " . $this->url . "\n"
        . "mimetype: " . $this->mimetype . "\n"
        . "pages: " . $this->pages . "\n"
        . "owner: " . $this->owner . "\n"
        . "revision: " . $this->revision . "\n"
        . "docdate: " . $this->docdate . "\n"
        . "hash: " . $this->hash . "\n"
        . "list_id: " . $this->list_id . "\n"
        . "encounter_id: " . $this->encounter_id . "\n"
        . "encounter_check: " . $this->encounter_check . "\n";

        if ($html) {
            return nl2br($string);
        } else {
            return $string;
        }
    }

    /**#@+
    *   Getter/Setter methods used by reflection to affect object in persist/poulate operations
    *   @param mixed new value for given attribute
    */
    function set_id($id)
    {
        $this->id = $id;
    }
    function get_id()
    {
        return $this->id;
    }

    /**
     * This is a Patient record id
     * @param $fid Unique database identifier for a patient record
     */
    function set_foreign_id($fid)
    {
        $this->foreign_id = $fid;
    }

    /**
     * Sets the unique database identifier that this Document is referenced to. If unlinking this document
     * with a foreign table you must set $reference_id and $table_name to be null
     */
    public function set_foreign_reference_id($reference_id)
    {
        $this->foreign_reference_id = $reference_id;
    }

    /**
     * Sets the table name that this Document references in the foreign_reference_id
     * @param $table_name The database table name
     */
    public function set_foreign_reference_table($table_name)
    {
        $this->foreign_reference_table = $table_name;
    }

    /**
     * The unique database reference to another table record (Foreign Key)
     * @return int|null
     */
    public function get_foreign_reference_id(): ?int
    {
        return $this->foreign_reference_id;
    }

    /**
     * Returns the database table name for the foreign reference id
     * @return string|null
     */
    public function get_foreign_reference_table(): ?string
    {
        return $this->foreign_reference_table;
    }

    function get_foreign_id()
    {
        return $this->foreign_id;
    }
    function set_type($type)
    {
        $this->type = $type;
    }
    function get_type()
    {
        return $this->type;
    }
    function set_size($size)
    {
        $this->size = $size;
    }
    function get_size()
    {
        return $this->size;
    }
    function set_date($date)
    {
        $this->date = $date;
    }
    function get_date()
    {
        return $this->date;
    }

    /**
     * @return string|null The datetime that the document expires at
     */
    function get_date_expires(): ?string
    {
        return $this->date_expires;
    }
    function set_hash($hash)
    {
        $this->hash = $hash;
    }
    function get_hash()
    {
        return $this->hash;
    }
    function get_hash_algo_title()
    {
        if (!empty($this->hash) && strlen($this->hash) < 50) {
            return "SHA1";
        } else {
            return "SHA3-512";
        }
    }
    function set_url($url)
    {
        $this->url = $url;
    }
    function get_url()
    {
        return $this->url;
    }
    function set_thumb_url($url)
    {
        $this->thumb_url = $url;
    }
    function get_thumb_url()
    {
        return $this->thumb_url;
    }
    /**
    * get the url without the protocol handler
    */
    function get_url_filepath()
    {
        return preg_replace("|^(.*)://|", "", $this->url);
    }

    /**
     * OpenEMR installation media can be moved to other instances, to get the real filesystem path we use this method.
     * If the document is a couch db document this will return null;
     */
    protected function get_filesystem_filepath()
    {
        if ($this->get_storagemethod() === self::STORAGE_METHOD_COUCHDB) {
            return null;
        }
        //change full path to current webroot.  this is for documents that may have
        //been moved from a different filesystem and the full path in the database
        //is not current.  this is also for documents that may of been moved to
        //different patients. Note that the path_depth is used to see how far down
        //the path to go. For example, originally the path_depth was always 1, which
        //only allowed things like documents/1/<file>, but now can have more structured
        //directories. For example a path_depth of 2 can give documents/encounters/1/<file>
        // etc.
        // NOTE that $from_filename and basename($url) are the same thing
        $filepath = $this->get_url_filepath();
        $from_all = explode("/", $filepath);
        $from_filename = array_pop($from_all);
        $from_pathname_array = array();
        for ($i = 0; $i < $this->get_path_depth(); $i++) {
            $from_pathname_array[] = array_pop($from_all);
        }
        $from_pathname_array = array_reverse($from_pathname_array);
        $from_pathname = implode("/", $from_pathname_array);
        $filepath = $GLOBALS['OE_SITE_DIR'] . '/documents/' . $from_pathname . '/' . $from_filename;
        return $filepath;
    }
    /**
    * get the url filename only
    */
    function get_url_file()
    {
        return basename_international(preg_replace("|^(.*)://|", "", $this->url));
    }
    /**
    * get the url path only
    */
    function get_url_path()
    {
        return dirname(preg_replace("|^(.*)://|", "", $this->url)) . "/";
    }
    function get_path_depth()
    {
        return $this->path_depth;
    }
    function set_path_depth($path_depth)
    {
        $this->path_depth = $path_depth;
    }
    function set_mimetype($mimetype)
    {
        $this->mimetype = $mimetype;
    }
    function get_mimetype()
    {
        return $this->mimetype;
    }
    function set_pages($pages)
    {
        $this->pages = $pages;
    }
    function get_pages()
    {
        return $this->pages;
    }
    function set_owner($owner)
    {
        $this->owner = $owner;
    }
    function get_owner()
    {
        return $this->owner;
    }
    /*
    *   No getter for revision because it is updated automatically by the DB.
    */
    function set_revision($revision)
    {
        $this->revision = $revision;
    }
    function set_docdate($docdate)
    {
        $this->docdate = $docdate;
    }
    function get_docdate()
    {
        return $this->docdate;
    }
    function set_list_id($list_id)
    {
        $this->list_id = $list_id;
    }
    function get_list_id()
    {
        return $this->list_id;
    }
    function set_name($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the database human readable filename of the document
     * @return string|null
     */
    function get_name()
    {
        return $this->name;
    }
    function set_drive_uuid($drive_uuid)
    {
        $this->drive_uuid = $drive_uuid;
    }
    function get_drive_uuid()
    {
        return $this->drive_uuid;
    }
    function set_encounter_id($encounter_id)
    {
        $this->encounter_id = $encounter_id;
    }
    function get_encounter_id()
    {
        return $this->encounter_id;
    }
    function set_encounter_check($encounter_check)
    {
        $this->encounter_check = $encounter_check;
    }
    function get_encounter_check()
    {
        return $this->encounter_check;
    }

    function get_ccr_type($doc_id)
    {
        $type = sqlQuery(
            "SELECT c.name FROM categories AS c
             LEFT JOIN categories_to_documents AS ctd ON c.id = ctd.category_id
             WHERE ctd.document_id = ?",
            array($doc_id)
        );
        return $type['name'];
    }
    function set_imported($imported)
    {
        $this->imported = $imported;
    }
    function get_imported()
    {
        return $this->imported;
    }
    function update_imported($doc_id)
    {
        sqlQuery("UPDATE documents SET imported = 1 WHERE id = ?", array($doc_id));
    }
    function set_encrypted($encrypted)
    {
        $this->encrypted = $encrypted;
    }
    function get_encrypted()
    {
        return $this->encrypted;
    }
    function is_encrypted()
    {
        return $this->encrypted == self::ENCRYPTED_ON;
    }
    /*
    *   Overridden function to stor current object state in the db.
    *   current overide is to allow for a just in time foreign id, often this is needed
    *   when the object is never directly exposed and is handled as part of a larger
    *   object hierarchy.
    *   @param int $fid foreign id that should be used so that this document can be related (joined) on it later
    */

    function persist($fid = "")
    {
        if (!empty($fid)) {
            $this->foreign_id = $fid;
        }

        // need to populate our uuid if its empty

        parent::persist();
    }

    function set_storagemethod($str)
    {
        $this->storagemethod = $str;
    }

    function get_storagemethod()
    {
        return $this->storagemethod;
    }

    function set_couch_docid($str)
    {
        $this->couch_docid = $str;
    }

    function get_couch_docid()
    {
        return $this->couch_docid;
    }

    function set_couch_revid($str)
    {
        $this->couch_revid = $str;
    }

    function get_couch_revid()
    {
        return $this->couch_revid;
    }

    function set_uuid($uuid)
    {
        $this->uuid = $uuid;
    }

    function get_uuid()
    {
        return $this->uuid;
    }

    // Function added by Rod to change the patient associated with a document.
    // This just moves some code that used to be in C_Document.class.php,
    // changing it as little as possible since I'm not set up to test it.
    //
    function change_patient($new_patient_id)
    {
        // Set the new patient.
        $this->set_foreign_id($new_patient_id);
        $this->persist();

        // Return true for success.
        return true;
    }

  /**
   * Create a new document and store its data.
   * This is a mix of new code and code moved from C_Document.class.php.
   *
   * @param  string  $patient_id   Patient pid; if not known then this may be a simple directory name
   * @param  integer $category_id  The desired document category ID
   * @param  string  $filename     The desired filename
   * @param  string  $mimetype     MIME type
   * @param  string  &$data        The actual data to store (not encoded)
   * @param  string  $higher_level_path Optional subdirectory within the local document repository
   * @param  string  $path_depth   Number of directory levels in $higher_level_path, if specified
   * @param  integer $owner        Owner/user/service that is requesting this action
   * @param  string  $tmpfile      The tmp location of file (require for thumbnail generator)
   * @param  string  $date_expires The datetime that the document should no longer be accessible in the system
   * @param  string  $foreign_reference_id The table id to another table record in OpenEMR
   * @param  string  $foreign_reference_table The table name of the foreign_reference_id this document refers to.
   * @return string                Empty string if success, otherwise error message text
   */
    function createDocument(
        $patient_id,
        $category_id,
        $filename,
        $mimetype,
        &$data,
        $higher_level_path = '',
        $path_depth = 1,
        $owner = 0,
        $tmpfile = null,
        $date_expires = null,
        $foreign_reference_id = null,
        $foreign_reference_table = null
    ) {
        if (
            !empty($foreign_reference_id) && empty($foreign_reference_table)
            || empty($foreign_reference_id) && !empty($foreign_reference_table)
        ) {
            return xl('Reference table and reference id must both be set');
        }
        $this->set_foreign_reference_id($foreign_reference_id);
        $this->set_foreign_reference_table($foreign_reference_table);
        // The original code used the encounter ID but never set it to anything.
        // That was probably a mistake, but we reference it here for documentation
        // and leave it empty. Logically, documents are not tied to encounters.

        // Create a crypto object that will be used for for encryption/decryption
        $cryptoGen = new CryptoGen();

        if ($GLOBALS['generate_doc_thumb']) {
            $thumb_size = ($GLOBALS['thumb_doc_max_size'] > 0) ? $GLOBALS['thumb_doc_max_size'] : null;
            $thumbnail_class = new Thumbnail($thumb_size);

            if (!is_null($tmpfile)) {
                $has_thumbnail = $thumbnail_class->file_support_thumbnail($tmpfile);
            } else {
                $has_thumbnail = false;
            }

            if ($has_thumbnail) {
                $thumbnail_resource = $thumbnail_class->create_thumbnail(null, $data);
                if ($thumbnail_resource) {
                    $thumbnail_data = $thumbnail_class->get_string_file($thumbnail_resource);
                } else {
                    $has_thumbnail = false;
                }
            }
        } else {
            $has_thumbnail = false;
        }

        $encounter_id = '';
        $this->storagemethod = $GLOBALS['document_storage_method'];
        $this->mimetype = $mimetype;
        if ($this->storagemethod == self::STORAGE_METHOD_COUCHDB) {
            // Store it using CouchDB.
            if ($GLOBALS['couchdb_encryption']) {
                $document = $cryptoGen->encryptStandard($data, null, 'database');
            } else {
                $document = base64_encode($data);
            }
            if ($has_thumbnail) {
                if ($GLOBALS['couchdb_encryption']) {
                    $th_document = $cryptoGen->encryptStandard($thumbnail_data, null, 'database');
                } else {
                    $th_document = base64_encode($thumbnail_data);
                }
                $this->thumb_url = $this->get_thumb_name($filename);
            } else {
                $th_document = false;
            }

            $couch = new CouchDB();
            $docid = $couch->createDocId('documents');
            if (!empty($th_document)) {
                $couchdata = ['_id' => $docid, 'data' => $document, 'th_data' => $th_document];
            } else {
                $couchdata = ['_id' => $docid, 'data' => $document];
            }
            $resp = $couch->save_doc($couchdata);
            if (!$resp->id || !$resp->rev) {
                return xl('CouchDB save failed');
            } else {
                $docid = $resp->id;
                $revid = $resp->rev;
            }

            $this->url = $filename;
            $this->couch_docid = $docid;
            $this->couch_revid = $revid;
        } else {
            // Storing document files locally.
            $repository = $GLOBALS['oer_config']['documents']['repository'];
            $higher_level_path = preg_replace("/[^A-Za-z0-9\/]/", "_", $higher_level_path);
            if ((!empty($higher_level_path)) && (is_numeric($patient_id) && $patient_id > 0)) {
                // Allow higher level directory structure in documents directory and a patient is mapped.
                $filepath = $repository . $higher_level_path . "/";
            } elseif (!empty($higher_level_path)) {
                // Allow higher level directory structure in documents directory and there is no patient mapping
                // (will create up to 10000 random directories and increment the path_depth by 1).
                $filepath = $repository . $higher_level_path . '/' . rand(1, 10000)  . '/';
                ++$path_depth;
            } elseif (!(is_numeric($patient_id)) || !($patient_id > 0)) {
                // This is the default action except there is no patient mapping (when patient_id is 00 or direct)
                // (will create up to 10000 random directories and set the path_depth to 2).
                $filepath = $repository . $patient_id . '/' . rand(1, 10000)  . '/';
                $path_depth = 2;
                $patient_id = 0;
            } else {
                // This is the default action where the patient is used as one level directory structure
                // in documents directory.
                $filepath = $repository . $patient_id . '/';
                $path_depth = 1;
            }

            if (!file_exists($filepath)) {
                if (!mkdir($filepath, 0700, true)) {
                    return xl('Unable to create patient document subdirectory');
                }
            }

            // collect the drive storage filename
            $this->drive_uuid = (new UuidRegistry(['document_drive' => true]))->createUuid();
            $filenameUuid = UuidRegistry::uuidToString($this->drive_uuid);

            $this->url = "file://" . $filepath . $filenameUuid;
            if (is_numeric($path_depth)) {
                // this is for when directory structure is more than one level
                $this->path_depth = $path_depth;
            }

            // Store the file.
            if ($GLOBALS['drive_encryption']) {
                $storedData = $cryptoGen->encryptStandard($data, null, 'database');
            } else {
                $storedData = $data;
            }
            if (file_exists($filepath . $filenameUuid)) {
                // this should never happen with current uuid mechanism
                return xl('Failed since file already exists') . " $filepath$filenameUuid";
            }
            if (file_put_contents($filepath . $filenameUuid, $storedData) === false) {
                return xl('Failed to create') . " $filepath$filenameUuid";
            }

            if ($has_thumbnail) {
                // Store the thumbnail.
                $this->thumb_url = "file://" . $filepath . $this->get_thumb_name($filenameUuid);
                if ($GLOBALS['drive_encryption']) {
                    $storedThumbnailData = $cryptoGen->encryptStandard($thumbnail_data, null, 'database');
                } else {
                    $storedThumbnailData = $thumbnail_data;
                }
                if (file_exists($filepath . $this->get_thumb_name($filenameUuid))) {
                    // this should never happend with current uuid mechanism
                    return xl('Failed since file already exists') .  $filepath . $this->get_thumb_name($filenameUuid);
                }
                if (
                    file_put_contents(
                        $filepath . $this->get_thumb_name($filenameUuid),
                        $storedThumbnailData
                    ) === false
                ) {
                    return xl('Failed to create') .  $filepath . $this->get_thumb_name($filenameUuid);
                }
            }
        }

        if (
            ($GLOBALS['drive_encryption'] && ($this->storagemethod != 1))
            || ($GLOBALS['couchdb_encryption'] && ($this->storagemethod == 1))
        ) {
            $this->set_encrypted(self::ENCRYPTED_ON);
        } else {
            $this->set_encrypted(self::ENCRYPTED_OFF);
        }
        // we need our external unique reference identifier that can be mapped back to our table.
        $docUUID = (new UuidRegistry(['table_name' => $this->_table]))->createUuid();
        $this->set_uuid($docUUID);
        $this->name = $filename;
        $this->size  = strlen($data);
        $this->hash  = hash('sha3-512', $data);
        $this->type  = $this->type_array['file_url'];
        $this->owner = $owner ? $owner : ($_SESSION['authUserID'] ?? null);
        $this->date_expires = $date_expires;
        $this->set_foreign_id($patient_id);
        $this->persist();
        $this->populate();
        if (is_numeric($this->get_id()) && is_numeric($category_id)) {
            $sql = "REPLACE INTO categories_to_documents SET category_id = ?, document_id = ?";
            $this->_db->Execute($sql, array($category_id, $this->get_id()));
        }

        return '';
    }

    /**
     * Retrieves the document data that has been saved to the filesystem or couch db.  If the $force_no_decrypt flag is
     * set to true, it will return the encrypted version of the data for the document.
     * @param bool $force_no_decrypt True if the document should have its data returned encrypted, false otherwise
     * @throws BadMethodCallException Thrown if the method is called when the document has been marked as deleted
     *                                or expired
     * @return false|string Returns false if the data failed to decrypt, or a string if the data decrypts
     *                      or is unencrypted.
     */
    function get_data($force_no_decrypt = false)
    {
        $storagemethod = $this->get_storagemethod();

        if ($this->has_expired()) {
            throw new BadMethodCallException("Should not attempt to retrieve data from expired documents");
        }
        if ($this->is_deleted()) {
            throw new BadMethodCallException("Should not attempt to retrieve data from deleted documents");
        }

        $base64Decode = false;

        if ($storagemethod === self::STORAGE_METHOD_COUCHDB) {
            // encrypting does not use base64 encoding
            if (!$this->is_encrypted()) {
                $base64Decode = true;
            }
            // Taken from ccr/display.php
            $couch_docid = $this->get_couch_docid();
            $couch_revid = $this->get_couch_revid();
            $couch = new CouchDB();
            $resp = $couch->retrieve_doc($couch_docid);
            $data = $resp->data;
        } else {
            $data = $this->get_content_from_filesystem();
        }

        if (!empty($data)) {
            if ($this->is_encrypted() && !$force_no_decrypt) {
                $data = $this->decrypt_content($data);
            }
            if ($base64Decode) {
                $data = base64_decode($data);
            }
        }
        return $data;
    }

    /**
     * Given a document data contents it decrypts the document data
     * @param $data The data that needs to be decrypted
     * @return string  Returns false if the encryption failed, otherwise it returns a string
     * @throws RuntimeException If the data cannot be decrypted
     */
    public function decrypt_content($data)
    {
        $cryptoGen = new CryptoGen();
        $decryptedData = $cryptoGen->decryptStandard($data, null, 'database');
        if ($decryptedData === false) {
            throw new RuntimeException("Failed to decrypt the data");
        }
        return $decryptedData;
    }

    /**
     * Returns the content from the filesystem for this document
     * @return string
     * @throws BadMethodCallException If you attempt to retrieve a document that is not stored on the file system
     * @throws RuntimeException if the filesystem file does not exist or content cannot be accessed.
     */
    protected function get_content_from_filesystem()
    {
        $path = $this->get_filesystem_filepath();
        if (empty($path)) {
            throw new BadMethodCallException(
                "Attempted to retrieve the content from the filesystem " .
                "for a file that uses a different storage mechanism"
            );
        }
        if (!file_exists($path)) {
            throw new RuntimeException("Saved filepath does not exist at location " . $path);
        }
        $data = file_get_contents($path);
        if ($data === false) {
            throw new RuntimeException(
                "The data could not be retrieved for the file at " .
                $path .
                " Check that access rights to the file have been granted"
            );
        }
        return $data;
    }

  /**
   * Return file name for thumbnail (adding 'th_')
   */
    function get_thumb_name($file_name)
    {
        return 'th_' . $file_name;
    }

  /**
   * Post a patient note that is linked to this document.
   *
   * @param  string  $provider     Login name of the provider to receive this note.
   * @param  integer $category_id  The desired document category ID
   * @param  string  $message      Any desired message text for the note.
   */
    function postPatientNote($provider, $category_id, $message = '')
    {
        // Build note text in a way that identifies the new document.
        // See pnotes_full.php which uses this to auto-display the document.
        $note = $this->get_url_file();
        for ($tmp = $category_id; $tmp;) {
            $catrow = sqlQuery("SELECT name, parent FROM categories WHERE id = ?", array($tmp));
            $note = $catrow['name'] . "/$note";
            $tmp = $catrow['parent'];
        }

        $note = "New scanned document " . $this->get_id() . ": $note";
        if ($message) {
            $note .= "\n" . $message;
        }

        $noteid = addPnote($this->get_foreign_id(), $note, 0, '1', 'New Document', $provider);
        // Link the new note to the document.
        setGpRelation(1, $this->get_id(), 6, $noteid);
    }

  /**
   * Return note objects associated with this document using Note::notes_factory
   *
   */
    function get_notes()
    {
        return (Note::notes_factory($this->get_id()));
    }
// end of Document
}
