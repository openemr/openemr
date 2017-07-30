<?php
/*
 * Class for OS filesystem folder based storage of documents
 *
 * Required methods used by Document object are specified by DocStore_interface :
 *
 */
class DocStore_OS implements DocStore_interface
{
    /* Associated Document object */
    var $_doc;
    var $_doc_path;
    
    var $status;
    
    function __construct(Document $_Document)
    {
        $this->_doc = $_Document;
        $this->_doc_path = $this->_doc->get_url_filepath();
        $this->status = file_exists($this->_doc_path);
        if (!$this->status) {
            $this->_doc_path = $this->_check_relocation($this->_doc->get_url());
        }
        $this->status = file_exists($this->_doc_path);
    }
    
    public function get_status()
    {
        return ($this->status);
    }
    
    /**  Function to accomodate the relocation of entire "documents" folder to another host or filesystem  **
     * Also usable for documents that may of been moved to different patients.
     *
     * @param string $url - Current url string from database.
     * @param string $new_pid - Include pid corrections to receive corrected url during move operation.
     * @param string $new_name - Include name corrections to receive corrected url during rename operation.
     *
     * @return string
     */
    private function _check_relocation($url, $new_pid = null, $new_name = null)
    {
        //strip url of protocol handler
        $url = preg_replace("|^(.*)://|", "", $url);
        $fsnodes = explode(DIRECTORY_SEPARATOR, $url);
        while (current($fsnodes) != "documents") {
            array_shift($fsnodes);
        }
        if ($new_pid) {
            $fsnodes[1] = $new_pid;
        }
        if ($new_name) {
            $fsnodes[count($fsnodes)-1] = $new_name;
        }
        $url = $GLOBALS['OE_SITE_DIR'].DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $fsnodes);
        // Make sure the url is available after corrections
        if ($new_pid || $new_name) {
            $url = $this->_rename_file($url);
        }
        //Add full path and remaining nodes
        return $url;
    }
    
    /* Provide access to document's raw content
     * TBD: Enforce file size restrictions
    */
    public function get_content_raw()
    {
        if ($this->status) {
            $doc_contents = file_get_contents($this->_doc_path);
            return (base64_encode($doc_contents));
        }
    }
}
