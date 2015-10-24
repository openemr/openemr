<?php
/*
 * jQuery File Upload Plugin PHP Example 5.14
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */
include_once("../../../interface/globals.php");

 /*
  * This section is to catch the patient ID and setup
  * the upload directory dynamically 
  */
 $future = $GLOBALS['OE_SITE_DIR'];
 $pid = $GLOBALS[_REQUEST]['patient_id'];
 $home = realpath(dirname()) ;
 $home = str_replace('\\','/', $home);
 $home = str_replace('uploader', 'documents', $home);
 $owner = $GLOBALS['userauthorized'];
 $category_id = $_POST['category_id'];
 
 /*
  * This part is to dynamically build the url needed
  * to override the default destination
  * TODO: Make this dynamic with a foreach loop
  */
  
  $link = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
  $linkParts = explode('/', $link);
  $link = $linkParts[0] .'/'. $linkParts[1].'/'. $linkParts[2].'/'. $linkParts[3];
  
  
  
error_reporting(E_ALL | E_STRICT | E_NOTICE);
  /*
   * This is the options being passed to the ___construct function to override defaults
   *
   */
  $options = array('upload_dir' => $home .'/'.$pid.'/', 'upload_url' => $link .'/documents/'.$pid.'/');

require('UploadHandler.php');

$upload_handler = new UploadHandler($options);
          
		  $fn = $upload_handler->post($files); //returns the file uploaded information array
		  $fnn = $fn['files'][0]->name;
		  $fns = $fn['files'][0]->size;
		  $mtype = $fn['files'][0]->type;
		  
		  $fnn = str_replace(' (1)', '', $fnn);
	          $name = $fnn;
		  $tname = "file://".$home.'/'.$pid.'/'.$name;
		  
	file_put_contents('file.txt', $home . "-\n" . $link);
	
	
	function get_last(){
		$sql = "SELECT id FROM documents ORDER BY id DESC LIMIT 1";
		$results = sqlQuery($sql);
		  
		   //the last id from the documents table
		foreach ($results as $result){
			$lid = $result;
		  }
		   
		   return $lid;
	}

	$nid = get_last();
    $nid = ++$nid; //increments the value by one for change over compatibility
	$docdate = date('Y-m-d H:i:s');
	
	
	function save_file($nid, $tname, $size, $pid, $docdate, $owner, $mtype, $category_id){
	
	   
			$sql = "INSERT INTO documents (id,type,size,date,url,mimetype,pages,owner,revision,foreign_id,docdate,hash,list_id,couch_docid,couch_revid,storagemethod,path_depth,imported)
			VALUES ('".$nid."','file_url','".$size."','".$docdate."','".$tname."','".$mtype."','NULL','".$owner."','','".$pid."','".$docdate."','','','','','','1','')";
			sqlQuery($sql);
			
	        $sql = "INSERT INTO categories_to_documents (category_id, document_id) VALUES ('".$category_id."','".$nid."')";
            sqlQuery($sql);			
            	
	}
	save_file($nid, $tname, $size, $pid, $docdate, $owner, $mtype, $category_id);
    
    //===================== Security ==============    
   //Check for the htaccess file in the patient folder 
   // if not there copy to location
   // *** Function no longer needed since moving files out of documents folder ***
   //=============================================
    /**    
   function xcopy($src, $dst){
       $dir = opendir($src);
       //@mkdir($dst);
       while(false !== ($file = readdir($dir))){
           if(($file !=='.') && ($file !='..')){
               if(is_dir($src . '/' . $file)){
                   recurse_copy($src . '/' . $file,$dst . '/' . $file);
               }
               else{
                   copy($src . '/'. $file,$dst .'/' .$file);
               }
           }
       }
   }      
      $src = $home.'/secure/';
      $dst = $home.'/'.$pid.'/';
      $filename = "fileSecure.txt";
    
      if(!file_exists($filename)){
      xcopy($src, $dst);
    }
	**/
         //$current = var_export($home.'/secure/fileSecure.txt', true);        //testing output
          //file_put_contents('file.txt', $current);
    