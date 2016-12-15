<?php
/**
 * send the file and 'post', 'get' and 'cookie' data.
 * upload file using openemr/controller.php
 * */

//Initialise the cURL var
$ch = curl_init();

//Get the response from cURL
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$url = $_SERVER['SERVER_NAME'] . $_GET['BASE_PATH'] . "/controller.php?";
foreach($_GET AS $key => $value){
  if($key != 'BASE_PATH'){
    $url .= $key;
    if(!empty($value)){
      $url .= "=" . $value;
    }
    $url .= "&";
  }
}

$fileType = pathinfo($_FILES['RemoteFile']['name'], PATHINFO_EXTENSION);
//set the type of file
$httpType = strtolower($fileType) == 'pdf' ? 'application/pdf' : 'image/jpeg';

//echo http_build_query($_COOKIE);die;
$strCookie = 'OpenEMR=' . $_COOKIE['OpenEMR'] . '; path=/';
session_write_close();

//Set the Url
curl_setopt($ch, CURLOPT_URL, $url);
//set cookie
//curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cookie: " . http_build_query($_COOKIE)));
curl_setopt( $ch, CURLOPT_COOKIE, $strCookie );
//Create a POST array with the file in it
$postData = array(
        'file[]' =>
            '@'            . $_FILES['RemoteFile']['tmp_name']
            . ';filename=' . $_FILES['RemoteFile']['name']
            .';type=' .$httpType,
        'MAX_FILE_SIZE' => '64000000',
        'destination' => '',
        'patient_id' => $_GET['patient_id'],
        'category_id'=> $_GET['parent_id'],
        'process' => 'true'
    );

curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);


// Execute the request
$response = curl_exec($ch);

if($errno = curl_errno($ch)) {
  $error_message = curl_strerror($errno);
  echo "cURL error ({$errno}):\n {$error_message}";
}
// Close the handle
curl_close($ch);

die;
