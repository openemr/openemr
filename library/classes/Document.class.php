<?php

require_once(dirname(__FILE__) . "/ORDataObject.class.php");
require_once(dirname(__FILE__) . "/CouchDB.class.php");

/**
 * class Document
 * This class is the logical representation of a physical file on some system somewhere that can be referenced with a URL
 * of some type. This URL is not necessarily a web url, it could be a file URL or reference to a BLOB in a db.
 * It is implicit that a document can have other related tables to it at least a one document to many notes which join on a documents 
 * id and categories which do the same. 
 */
 
class Document extends ORDataObject{
	
	/*
	*	Database unique identifier
	*	@var id
	*/
	var $id;
	
	/*
	*	DB unique identifier reference to some other table, this is not unique in the document table
	*	@var int
	*/
	var $foreign_id;
	
	/*
	*	Enumerated DB field which is met information about how to use the URL
	*	@var int can also be a the properly enumerated string
	*/
	var $type;
	
	/*
	*	Array mapping of possible for values for the type variable
	*	mapping is array text name to index
	*	@var array
	*/
	var $type_array = array();
	
	/*
	*	Size of the document in bytes if that is available
	*	@var int
	*/
	var $size;
	
	/*
	*	Date the document was first persisted
	*	@var string
	*/
	var $date;
	
	/*
	*	URL which point to the document, may be a file URL, a web URL, a db BLOB URL, or others
	*	@var string
	*/
	var $url;
	
	/*
	*	Mimetype of the document if available
	*	@var string
	*/
	var $mimetype;
	
	/*
	*	If the document is a multi-page format like tiff and has at least 1 page this will be 1 or greater, if a non-multi-page format this should be null or empty
	*	@var int
	*/
	var $pages;
	
	/*
	*	Foreign key identifier of who initially persisited the document,
	*	potentially ownership could be changed but that would be up to an external non-document object process
	*	@var int
	*/
	var $owner;
	
	/*
	*	Timestamp of the last time the document was changed and persisted, auto maintained by DB, manually change at your own peril
	*	@var int
	*/
	var $revision;

	/*
	* Date (YYYY-MM-DD) logically associated with the document, e.g. when a picture was taken.
	* @var string
	*/
	var $docdate;
	
	/*
	* 40-character sha1 hash key of the document from when it was uploaded. 
	* @var string
	*/
	var $hash;
	
	/*
	* DB identifier reference to the lists table (the related issue), 0 if none.
	* @var int
	*/
	var $list_id;
	
	// For tagging with the encounter
	var $encounter_id;
	var $encounter_check;

  /*
	*	Whether the file is already imported
	*	@var int
	*/
	var $imported;

	/**
	 * Constructor sets all Document attributes to their default value
	 * @param int $id optional existing id of a specific document, if omitted a "blank" document is created 
	 */
	function Document($id = "")	{
		//call the parent constructor so we have a _db to work with
		parent::ORDataObject();
		
		//shore up the most basic ORDataObject bits
		$this->id = $id;
		$this->_table = "documents";
		
		//load the enum type from the db using the parent helper function, this uses psuedo-class variables so it is really cheap
		$this->type_array = $this->_load_enum("type");
		
		$this->type = $this->type_array[0];
		$this->size = 0;
		$this->date = date("Y-m-d H:i:s");
		$this->url = "";
		$this->mimetype = "";
		$this->docdate = date("Y-m-d");
		$this->hash = "";
		$this->list_id = 0;
		$this->encounter_id = 0;
		$this->encounter_check = "";
		
		if ($id != "") {
			$this->populate();
		}
	}
	
	/**
	 * Convenience function to get an array of many document objects
	 * For really large numbers of documents there is a way more efficient way to do this by overwriting the populate method
	 * @param int $foreign_id optional id use to limit array on to a specific relation, otherwise every document object is returned 
	 */
	function documents_factory($foreign_id = "") {
		$documents = array();
		
		if (empty($foreign_id)) {
			 $foreign_id= "like '%'";
		}
		else {
			$foreign_id= " = '" . mysql_real_escape_string(strval($foreign_id)) . "'";
		}
		
		$d = new Document();
		$sql = "SELECT id FROM  " . $d->_table . " WHERE foreign_id " .$foreign_id ;
		$result = $d->_db->Execute($sql);
		
		while ($result && !$result->EOF) {
			$documents[] = new Document($result->fields['id']);
			$result->MoveNext();
		}

		return $documents;
	}
	
	/**
	 * Convenience function to get a document object from a url
	 * Checks to see if there is an existing document with that URL and if so returns that object, otherwise
	 * creates a new one, persists it and returns it
	 * @param string $url  
	 * @return object new or existing document object with the specified URL
	 */
	function document_factory_url($url) {
		$d = new Document();
		//strip url handler, for now we always assume file://
		$filename = preg_replace("|^(.*)://|","",$url);
		
		if (!file_exists($filename)) {
			die("An invalid URL was specified to crete a new document, this would only be caused if files are being deleted as you are working through the queue. '$filename'\n");	
		}
		
		$sql = "SELECT id FROM  " . $d->_table . " WHERE url= '" . mysql_real_escape_string($url) ."'" ;
		$result = $d->_db->Execute($sql);
		
		if ($result && !$result->EOF) {
			if (file_exists($filename)) {
				$d = new Document($result->fields['id']);
			}
			else {
				$sql = "DELETE FROM  " . $d->_table . " WHERE id= '" . $result->fields['id'] ."'";
				$result = $d->_db->Execute($sql);
				echo("There is a database for the file but it no longer exists on the file system. Its document entry has been deleted. '$filename'\n");
			}
		}
		else {
			$file_command = $GLOBALS['oer_config']['document']['file_command_path'] ;
			$cmd_args = "-i ".escapeshellarg($new_path.$fname);
		  		
		  	$command = $file_command." ".$cmd_args;
		  	$mimetype = exec($command);
		  	$mime_array = split(":", $mimetype);
		  	$mimetype = $mime_array[1];
		  	$d->set_mimetype($mimetype);
			$d->url = $url;
		  	$d->size = filesize($filename);
		  	$d->type = $d->type_array['file_url'];
		  	$d->persist();
		  	$d->populate();	
		}

		return $d;
	}
	
	/**
	 * Convenience function to generate string debug data about the object
	 */
	function toString($html = false) {
		$string .= "\n"
		. "ID: " . $this->id."\n"
		. "FID: " . $this->foreign_id."\n"
		. "type: " . $this->type . "\n"
		. "type_array: " . print_r($this->type_array,true) . "\n"
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
		}
		else {
			return $string;
		}
	}

	/**#@+
	*	Getter/Setter methods used by reflection to affect object in persist/poulate operations
	*	@param mixed new value for given attribute
	*/
	function set_id($id) {
		$this->id = $id;
	}
	function get_id() {
		return $this->id;
	}
	function set_foreign_id($fid) {
		$this->foreign_id = $fid;
	}
	function get_foreign_id() {
		return $this->foreign_id;
	}
	function set_type($type) {
		$this->type = $type;
	}
	function get_type() {
		return $this->type;
	}
	function set_size($size) {
		$this->size = $size;
	}
	function get_size() {
		return $this->size;
	}	
	function set_date($date) {
		$this->date = $date;
	}
	function get_date() {
		return $this->date;
	}
    function set_hash($hash) {
		$this->hash = $hash;
	}
	function get_hash() {
	    return $this->hash;
	}
	function set_url($url) {
		$this->url = $url;
	}
	function get_url() {
		return $this->url;
	}
	/**
	* this returns the url stripped down to basename
	*/
	function get_url_web() {
		return basename($this->url);
	}
	/**
	* get the url without the protocol handler
	*/
	function get_url_filepath() {
		return preg_replace("|^(.*)://|","",$this->url);
	}
	/**
	* get the url filename only
	*/
	function get_url_file() {
		return basename(preg_replace("|^(.*)://|","",$this->url));
	}
	/**
	* get the url path only
	*/
	function get_url_path() {
		return dirname(preg_replace("|^(.*)://|","",$this->url)) ."/";
	}
        function get_path_depth() {
                return $this->path_depth;
        }
        function set_path_depth($path_depth) {
                $this->path_depth = $path_depth;
        }
	function set_mimetype($mimetype) {
		$this->mimetype = $mimetype;
	}
	function get_mimetype() {
		return $this->mimetype;
	}
	function set_pages($pages) {
		$this->pages = $pages;
	}
	function get_pages() {
		return $this->pages;
	}
	function set_owner($owner) {
		$this->owner = $owner;
	}
	function get_owner() {
		return $this->owner;
	}
	/*
	*	No getter for revision because it is updated automatically by the DB.
	*/
	function set_revision($revision) {
		$this->revision = $revision;
	}
	function set_docdate($docdate) {
		$this->docdate = $docdate;
	}
	function get_docdate() {
		return $this->docdate;
	}
	function set_list_id($list_id) {
		$this->list_id = $list_id;
	}
	function get_list_id() {
		return $this->list_id;
	}
	function set_encounter_id($encounter_id) {
		$this->encounter_id = $encounter_id;
	}
	function get_encounter_id() {
		return $this->encounter_id;
	}
	function set_encounter_check($encounter_check) {
		$this->encounter_check = $encounter_check;
	}
	function get_encounter_check() {
		return $this->encounter_check;
	}
	
	function get_ccr_type($doc_id){
    $type = sqlQuery("SELECT c.name FROM categories AS c LEFT JOIN categories_to_documents AS ctd ON c.id = ctd.category_id WHERE ctd.document_id = ?",array($doc_id));
    return $type['name'];
  }
  function set_imported($imported) {
		$this->imported = $imported;
	}
	function get_imported() {
		return $this->imported;
	}
  function update_imported($doc_id) {
		sqlQuery("UPDATE documents SET imported = 1 WHERE id = ?",array($doc_id));
	}
	/*
	*	Overridden function to stor current object state in the db.
	*	current overide is to allow for a just in time foreign id, often this is needed 
	*	when the object is never directly exposed and is handled as part of a larger
	*	object hierarchy.
	*	@param int $fid foreign id that should be used so that this document can be related (joined) on it later
	*/
	
	function persist($fid ="") {
		if (!empty($fid)) {
			$this->foreign_id = $fid;
		}
		parent::persist();
	}
        
        function set_storagemethod($str) {
	    $this->storagemethod = $str;
	}
        
        function get_storagemethod() {
	    return $this->storagemethod;
	}
        
        function set_couch_docid($str) {
	    $this->couch_docid = $str;
	}
        
        function get_couch_docid() {
	    return $this->couch_docid;
	}
        
        function set_couch_revid($str) {
	    $this->couch_revid = $str;
	}
        
        function get_couch_revid() {
	    return $this->couch_revid;
	}
        
        function get_couch_url($pid,$encounter){
            $couch_docid = $this->get_couch_docid();
            $couch_url = $this->get_url();
            $couch = new CouchDB();
            $data = array($GLOBALS['couchdb_dbase'],$couch_docid,$pid,$encounter);
            $resp = $couch->retrieve_doc($data);
            $content = $resp->data;
			$temp_url=$couch_url;
			$temp_url = $GLOBALS['OE_SITE_DIR'] . '/documents/temp/' . $pid . '_' . $couch_url;
			$f_CDB = fopen($temp_url,'w');
            fwrite($f_CDB,base64_decode($content));
            fclose($f_CDB);
			return $temp_url;
        }

  // Function added by Rod to change the patient associated with a document.
  // This just moves some code that used to be in C_Document.class.php,
  // changing it as little as possible since I'm not set up to test it.
  //
  function change_patient($new_patient_id) {
    $couch_docid = $this->get_couch_docid();
    $couch_revid = $this->get_couch_revid();

    // Set the new patient in CouchDB.
    if ($couch_docid && $couch_revid) {
      $couch = new CouchDB();
      $db = $GLOBALS['couchdb_dbase'];
      $data = array($db, $couch_docid);
      $couchresp = $couch->retrieve_doc($data);
      // CouchDB doesnot support updating a single value in a document.
      // Have to retrieve the entire document, update the necessary value and save again
      list ($db, $docid, $revid, $patient_id, $encounter, $type, $json) = $data;
      $data = array($db, $couch_docid, $couch_revid, $new_patient_id, $couchresp->encounter,
        $couchresp->mimetype, json_encode($couchresp->data));
      $resp = $couch->update_doc($data);
      // Sometimes the response from CouchDB is not available, still it would
      // have saved in the DB. Hence check one more time.
      if(!$resp->_id || !$resp->_rev){
	      $data = array($db, $couch_docid, $new_patient_id, $couchresp->encounter);
	      $resp = $couch->retrieve_doc($data);
      }
      if($resp->_rev == $couch_revid) {
        return false;
	    }
      else {
        $this->set_couch_revid($resp->_rev);
      }
    }

    // Set the new patient in mysql.
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
   * @param  string  $filename     Desired filename, may be modified for uniqueness
   * @param  string  $mimetype     MIME type
   * @param  string  &$data        The actual data to store (not encoded)
   * @param  string  $higher_level_path Optional subdirectory within the local document repository
   * @param  string  $path_depth   Number of directory levels in $higher_level_path, if specified
   * @param  integer $owner        Owner/user/service that is requesting this action
   * @return string                Empty string if success, otherwise error message text
   */
  function createDocument($patient_id, $category_id, $filename, $mimetype, &$data,
    $higher_level_path='', $path_depth=1, $owner=0) {
    // The original code used the encounter ID but never set it to anything.
    // That was probably a mistake, but we reference it here for documentation
    // and leave it empty. Logically, documents are not tied to encounters.
    $encounter_id = '';
    $this->storagemethod = $GLOBALS['document_storage_method'];
    $this->mimetype = $mimetype;
    if ($this->storagemethod == 1) {
      // Store it using CouchDB.
      $couch = new CouchDB();
      $docname = $_SESSION['authId'] . $filename . $patient_id . $encounter_id . date("%Y-%m-%d H:i:s");
      $docid = $couch->stringToId($docname);
      $json = json_encode(base64_encode($data));
      $db = $GLOBALS['couchdb_dbase'];
      $couchdata = array($db, $docid, $patient_id, $encounter_id, $mimetype, $json);
      $resp = $couch->check_saveDOC($couchdata);
      if(!$resp->id || !$resp->_rev) {
        // Not sure what this is supposed to do.  The references to id, rev,
        // _id and _rev seem pretty weird.
        $couchdata = array($db, $docid, $patient_id, $encounter_id);
        $resp = $couch->retrieve_doc($couchdata);
        $docid = $resp->_id;
        $revid = $resp->_rev;
      }
      else {
        $docid = $resp->id;
        $revid = $resp->rev;
      }
      if(!$docid && !$revid) {
        return xl('CouchDB save failed');
      }
      $this->url = $filename;
      $this->couch_docid = $docid;
      $this->couch_revid = $revid;
    }
    else {
      // Storing document files locally.
      $repository = $GLOBALS['oer_config']['documents']['repository'];
      $higher_level_path = preg_replace("/[^A-Za-z0-9\/]/", "_", $higher_level_path);
      if ((!empty($higher_level_path)) && (is_numeric($patient_id) && $patient_id > 0)) {
        // Allow higher level directory structure in documents directory and a patient is mapped.
        $filepath = $repository . $higher_level_path . "/";
      }
      else if (!empty($higher_level_path)) {
        // Allow higher level directory structure in documents directory and there is no patient mapping
        // (will create up to 10000 random directories and increment the path_depth by 1).
        $filepath = $repository . $higher_level_path . '/' . rand(1,10000)  . '/';
        ++$path_depth;
      }
      else if (!(is_numeric($patient_id)) || !($patient_id > 0)) {
        // This is the default action except there is no patient mapping (when patient_id is 00 or direct)
        // (will create up to 10000 random directories and set the path_depth to 2).
        $filepath = $repository . $patient_id . '/' . rand(1,10000)  . '/';
        $path_depth = 2;
        $patient_id = 0;
      }
      else {
        // This is the default action where the patient is used as one level directory structure in documents directory.
        $filepath = $repository . $patient_id . '/';
        $path_depth = 1;
      }
      if (!file_exists($filepath)) {
        if (!mkdir($filepath, 0700, true)) {
          return xl('Unable to create patient document subdirectory');
        }
      }
      // Filename modification to force valid characters and uniqueness.
      $filename = preg_replace("/[^a-zA-Z0-9_.]/", "_", $filename);
      $fnsuffix = 0;
      $fn1 = $filename;
      $fn2 = '';
      $fn3 = '';
      $dotpos = strrpos($filename, '.');
      if ($dotpos !== FALSE) {
        $fn1 = substr($filename, 0, $dotpos);
        $fn2 = '.';
        $fn3 = substr($filename, $dotpos + 1);
      }
      while (file_exists($filepath . $filename)) {
        if (++$fnsuffix > 10000) return xl('Failed to compute a unique filename');
        $filename = $fn1 . '_' . $fnsuffix . $fn2 . $fn3;
      }
      $this->url = "file://" . $filepath . $filename;
      if (is_numeric($path_depth)) {
        // this is for when directory structure is more than one level
        $this->path_depth = $path_depth;
      }
      // Store the file into its proper directory.
      if (file_put_contents($filepath . $filename, $data) === FALSE) {
        return xl('Failed to create') . " $filepath$filename";
      }
    }
    $this->size  = strlen($data);
    $this->hash  = sha1($data);
    $this->type  = $this->type_array['file_url'];
    $this->owner = $owner ? $owner : $_SESSION['authUserID'];			
    $this->set_foreign_id($patient_id);
    $this->persist();
    $this->populate();
    if (is_numeric($this->get_id()) && is_numeric($category_id)){
      $sql = "REPLACE INTO categories_to_documents set " .
        "category_id = '$category_id', " .
        "document_id = '" . $this->get_id() . "'";
      $this->_db->Execute($sql);
    }
    return '';
  }

} // end of Document
?>
