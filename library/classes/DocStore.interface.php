<?php
/*
 * Specifies the methods every interface to documents feature must provide
 *
 * Current document stores are :
 * DocStore_OS class for OS filesystem folder based storage of documents
 * DocStore_CDB class for Couchdb based based storage of documents
 *
 */
interface DocStore_interface
{
    /* Status of the object */
    public function get_status();
    
    /* Provide access to document's raw content */
    public function get_content_raw();
}
