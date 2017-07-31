<?php
/*
 * Class for CouchDB based storage of documents
 *
 * Required methods used by Document object are specified by DocStore_interface :
 *
 */
class DocStore_CDB implements DocStore_interface
{
    /* Associated Document object */
    var $_doc;
    var $_db;
    var $_db_rec;
    
    var $status;
    
    function __construct($_Document)
    {
        $this->_doc = $_Document;
        $doc_id = $_Document->get_couch_docid();
        if (empty($doc_id)) {
            $this->status = false;
            return;
        }
        // Fetch data
        $this->_db = new CouchDB();
        $this->_db_rec = $this->_db->retrieve_doc(array($GLOBALS['couchdb_dbase'], $doc_id));
        
        $this->status = (!empty($this->_db_rec));
    }
    
    public function get_status()
    {
        return $this->status;
    }
    
    /* Provide access to document's raw content */
    public function get_content_raw()
    {
        return $this->_db_rec->data;
    }
}
