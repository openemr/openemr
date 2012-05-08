<?php
// +-----------------------------------------------------------------------------+ 
// Copyright (C) 2012 Z&H Consultancy Services Private Limited <sam@zhservices.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// 
// Author:   Eldho Chacko <eldho@zhservices.com>
//           Jacob T Paul <jacob@zhservices.com> 
//
// +------------------------------------------------------------------------------+

class CouchDB {
    function CouchDB() {
        $this->host = $GLOBALS['couchdb_host'];
        $this->user = ($GLOBALS['couchdb_user'] != '') ? $GLOBALS['couchdb_user'] : null;
        $this->pass = ($GLOBALS['couchdb_pass'] != '') ? $GLOBALS['couchdb_pass'] : null;
        $this->port = $GLOBALS['couchdb_port'];
        $this->dbase = $GLOBALS['couchdb_dbase'];
    }
    
    function check_connection(){
        $resp = $this->send("GET", "/"); // response: string(46) "{"couchdb": "Welcome", "version": "0.7.0a553"}"
	$response = json_decode($resp);
     	if($response->couchdb && $response->version)
        return true;
        else
        return false;
    }
    
    function createDB($db){
        $resp = $this->send("PUT", "/".$db);
        return true;
    }
    
    function createView($db){
        
        $resp = $this->send("PUT", "/".$db."/_design/FilteringViews", '{"_id":"_design/FilteringViews","views": {"FilterPid": {"map": "function(doc) { if(doc.pid){emit(doc._id, doc);} }"},
                                                                                                                "FilterEncounter": {"map": "function(doc) { if(doc.encounter){emit(doc._id, doc);} }"},
                                                                                                                "FilterPidEncounter": {"map": "function(doc) { if(doc.pid && doc.encounter){emit(doc._id, doc);} }"}}}');
        return json_decode($resp);
    }
    
    function check_saveDOC($data){
        list($db,$docid,$patient_id,$encounter,$type,$json) = $data;
        $resp = $this->send("PUT", "/".$db."/".$docid, '{"_id":"'.$docid.'","pid":"'.$patient_id.'","encounter":"'.$encounter.'","mimetype":"'.$type.'","data":'.$json.'}');
        return json_decode($resp);
    }
    
    function update_doc($data){
	list($db,$docid,$revid,$patient_id,$encounter,$type,$json) = $data;
        $resp = $this->send("PUT", "/".$db."/".$docid, '{"_id":"'.$docid.'","_rev":"'.$revid.'","pid":"'.$patient_id.'","encounter":"'.$encounter.'","mimetype":"'.$type.'","data":'.$json.'}');
        return json_decode($resp);
    }
    
    function DeleteDoc($db,$docid,$revid){
        $resp = $this->send("DELETE", "/".$db."/".$docid."?rev=".$revid);
        return true;
    }
    
    function retrieve_doc($data){
        list($db,$docid) = $data;
        $resp = $this->send("GET", "/".$db."/".$docid); 
        return json_decode($resp); // string(47) "{"_id":"123","_rev":"2039697587","data":"Foo"}" 
    }
    
    function stringToId( $string, $replace = '_' )
    {
        // First translit string to ASCII, as this characters are most probably
        // supported everywhere
        $string = iconv( 'UTF-8', 'ASCII//TRANSLIT', $string );

        // And then still replace any obscure characters by _ to ensure nothing
        // "bad" happens with this string.
        $string = preg_replace( '([^A-Za-z0-9.-]+)', $replace, $string );

        // Additionally we convert the string to lowercase, so that we get case
        // insensitive fetching
        return strtolower( $string );
    }
    
    function send($method, $url, $post_data = NULL) {
       $s = fsockopen($this->host, $this->port, $errno, $errstr); 
       if(!$s) {
          return false;
       } 
 
       $request = "$method $url HTTP/1.0\r\nHost: $this->host\r\n"; 
 
       if ($this->user) {
          $request .= 'Authorization: Basic '.base64_encode($this->user.':'.$this->pass)."\r\n";
       }
 
       if($post_data) {
          $request .= "Content-Length: ".strlen($post_data)."\r\n\r\n"; 
          $request .= "$post_data\r\n";
       } 
       else {
          $request .= "\r\n";
       }
 
       fwrite($s, $request); 
       $response = ""; 
 
       while(!feof($s)) {
          $response .= fgets($s);
       }
 
       list($this->headers, $this->body) = explode("\r\n\r\n", $response); 
       return $this->body;
    }
}
?>