<?php

require_once(dirname(__FILE__) . "/../pnotes.inc");
require_once(dirname(__FILE__) . "/../gprelations.inc.php");

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\ORDataObject\ORDataObject;
use OpenEMR\Common\Uuid\UuidRegistry;

/**
 * class Document
 * This class is the logical representation of a physical file on some system somewhere that can be referenced with a URL
 * of some type. This URL is not necessarily a web url, it could be a file URL or reference to a BLOB in a db.
 * It is implicit that a document can have other related tables to it at least a one document to many notes which join on a documents
 * id and categories which do the same.
 */

class Document extends ORDataObject
{

    /*
    *   Database unique identifier
    *   @var id
    */
    var $id;

    /*
    *   DB unique identifier reference to some other table, this is not unique in the document table
    *   @var int
    */
    var $foreign_id;

    /*
    *   Enumerated DB field which is met information about how to use the URL
    *   @var int can also be a the properly enumerated string
    */
    var $type;

    /*
    *   Array mapping of possible for values for the type variable
    *   mapping is array text name to index
    *   @var array
    */
    var $type_array = array();

    /*
    *   Size of the document in bytes if that is available
    *   @var int
    */
    var $size;

    /*
    *   Date the document was first persisted
    *   @var string
    */
    var $date;

    /*
    *   URL which point to the document, may be a file URL, a web URL, a db BLOB URL, or others
    *   @var string
    */
    var $url;

    /*
    *   URL which point to the thumbnail document, may be a file URL, a web URL, a db BLOB URL, or others
    *   @var string
    */
    var $thumb_url;

    /*
    *   Mimetype of the document if available
    *   @var string
    */
    var $mimetype;

    /*
    *   If the document is a multi-page format like tiff and has at least 1 page this will be 1 or greater, if a non-multi-page format this should be null or empty
    *   @var int
    */
    var $pages;

    /*
    *   Foreign key identifier of who initially persisited the document,
    *   potentially ownership could be changed but that would be up to an external non-document object process
    *   @var int
    */
    var $owner;

    /*
    *   Timestamp of the last time the document was changed and persisted, auto maintained by DB, manually change at your own peril
    *   @var int
    */
    var $revision;

    /*
    * Date (YYYY-MM-DD) logically associated with the document, e.g. when a picture was taken.
    * @var string
    */
    var $docdate;

    /*
    * hash key of the document from when it was uploaded.
    * @var string
    */
    var $hash;

    /*
    * DB identifier reference to the lists table (the related issue), 0 if none.
    * @var int
    */
    var $list_id;

    // For name (used in OpenEMR 6.0.0+)
    var $name = null;

    // For label on drive (used in OpenEMR 6.0.0+)
    var $drive_uuid = null;

    // For tagging with the encounter
    var $encounter_id;
    var $encounter_check;

    /*
    *   Whether the file is already imported
    *   @var int
    */
    var $imported;

    /*
    *   Whether the file is encrypted
    *   @var int
    */
    var $encrypted;

    // Storage method
    var $storagemethod;

    // For storing couch docid
    var $couch_docid;

    // For storing couch revid
    var $couch_revid;

    // For storing path depth
    var $path_depth;

    /**
     * Constructor sets all Document attributes to their default value
     * @param int $id optional existing id of a specific document, if omitted a "blank" document is created
     */
    function __construct($id = "")
    {
        //call the parent constructor so we have a _db to work with
        parent::__construct();

        //shore up the most basic ORDataObject bits
        $this->id = $id;
        $this->_table = "documents";

        //load the enum type from the db using the parent helper function, this uses psuedo-class variables so it is really cheap
        $this->type_array = $this->_load_enum("type");

        $this->type = $this->type_array[0] ?? '';
        $this->size = 0;
        $this->date = date("Y-m-d H:i:s");
        $this->url = "";
        $this->mimetype = "";
        $this->docdate = date("Y-m-d");
        $this->hash = "";
        $this->list_id = 0;
        $this->encounter_id = 0;
        $this->encounter_check = "";
        $this->encrypted = 0;

        if ($id != "") {
            $this->populate();
        }
    }

    /**
     * Convenience function to get an array of many document objects
     * For really large numbers of documents there is a way more efficient way to do this by overwriting the populate method
     * @param int $foreign_id optional id use to limit array on to a specific relation, otherwise every document object is returned
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
    function set_foreign_id($fid)
    {
        $this->foreign_id = $fid;
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
        $type = sqlQuery("SELECT c.name FROM categories AS c LEFT JOIN categories_to_documents AS ctd ON c.id = ctd.category_id WHERE ctd.document_id = ?", array($doc_id));
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
        $tmpfile = null
    ) {
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
        if ($this->storagemethod == 1) {
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
                // This is the default action where the patient is used as one level directory structure in documents directory.
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
                // this should never happend with current uuid mechanism
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
                if (file_put_contents($filepath . $this->get_thumb_name($filenameUuid), $storedThumbnailData) === false) {
                    return xl('Failed to create') .  $filepath . $this->get_thumb_name($filenameUuid);
                }
            }
        }

        if (($GLOBALS['drive_encryption'] && ($this->storagemethod != 1)) || ($GLOBALS['couchdb_encryption'] && ($this->storagemethod == 1))) {
            $this->set_encrypted(1);
        } else {
            $this->set_encrypted(0);
        }
        $this->name = $filename;
        $this->size  = strlen($data);
        $this->hash  = hash('sha3-512', $data);
        $this->type  = $this->type_array['file_url'];
        $this->owner = $owner ? $owner : $_SESSION['authUserID'];
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
} // end of Document
