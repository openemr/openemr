<?php
require_once(dirname(__FILE__) . "/../interface/globals.php");
require_once(dirname(__FILE__) . "/../library/classes/Document.class.php");
require_once(dirname(__FILE__) . "/../library/classes/CouchDB.class.php");

$type = $_GET['type'];
$document_id = $_GET['doc_id'];
$d = new Document($document_id);
$url =  $d->get_url();
$storagemethod = $d->get_storagemethod();
$couch_docid = $d->get_couch_docid();
$couch_revid = $d->get_couch_revid();

if($couch_docid && $couch_revid){
	$couch = new CouchDB();
	$data = array($GLOBALS['couchdb_dbase'],$couch_docid);
	$resp = $couch->retrieve_doc($data);
	$xml = base64_decode($resp->data);
	if($content=='' && $GLOBALS['couchdb_log']==1){				
		$log_content = date('Y-m-d H:i:s')." ==> Retrieving document\r\n";
		$log_content = date('Y-m-d H:i:s')." ==> URL: ".$url."\r\n";
		$log_content .= date('Y-m-d H:i:s')." ==> CouchDB Document Id: ".$couch_docid."\r\n";
		$log_content .= date('Y-m-d H:i:s')." ==> CouchDB Revision Id: ".$couch_revid."\r\n";
		$log_content .= date('Y-m-d H:i:s')." ==> Failed to fetch document content from CouchDB.\r\n";
		//$log_content .= date('Y-m-d H:i:s')." ==> Will try to download file from HardDisk if exists.\r\n\r\n";
		$this->document_upload_download_log($d->get_foreign_id(),$log_content);
		die(xlt("File retrieval from CouchDB failed"));
	}
}else{
  $url = preg_replace("|^(.*)://|","",$url);
  // Collect filename and path
  $from_all = explode("/",$url);
  $from_filename = array_pop($from_all);
  $from_pathname_array = array();
  for ($i=0;$i<$d->get_path_depth();$i++) {
  $from_pathname_array[] = array_pop($from_all);
  }
  $from_pathname_array = array_reverse($from_pathname_array);
  $from_pathname = implode("/",$from_pathname_array);
  $temp_url = $GLOBALS['OE_SITE_DIR'] . '/documents/' . $from_pathname . '/' . $from_filename;
  if (!file_exists($temp_url)) {
  echo xl('The requested document is not present at the expected location on the filesystem or there are not sufficient permissions to access it.','','',' ') . $temp_url;
  }else{
  $url = $temp_url;
  $f = fopen($url,"r");
  $xml = fread($f,filesize($url));
  fclose($f);
  }
}

if($type == "CCR"){
    $xmlDom = new DOMDocument();
    $xmlDom->loadXML($xml);
    $ss = new DOMDocument();
    $ss->load(dirname(__FILE__).'/stylesheet/ccr.xsl');
    $proc = new XSLTProcessor();
    $proc->importStylesheet($ss);
    $s_html = $proc->transformToXML($xmlDom);
    echo $s_html;
}else{
    $xmlDom = new DOMDocument();
    $xmlDom->loadXML($xml);
    $ss = new DOMDocument();
		$ss->load(dirname(__FILE__).'/stylesheet/cda.xsl');
		$xslt = new XSLTProcessor();
		$xslt->importStyleSheet($ss);
		$html = $xslt->transformToXML($xmlDom);
		echo $html;
}
?>
