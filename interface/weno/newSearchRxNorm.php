<form name="druglookup" method="post" action="">
<input type="text" name="drug" size="15" value="<?php echo $drug; ?>">
<input type="submit" value="Submit">

</form>
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(!empty($_POST['drug'])){
     $drug = $_POST['drug'];
	 $xml = new SimpleXMLElement(file_get_contents("https://rxnav.nlm.nih.gov/REST/Prescribe/drugs?name=".$drug));


echo "<pre>";
//print_r($xml);
echo "</pre>";
echo "Search term: ". $xml->drugGroup->name ."<br><br>";

if(empty($xml->drugGroup->conceptGroup[1]->conceptProperties[0])){
  echo "no results for this seach";
  exit;
}

if(!empty($xml->drugGroup->conceptGroup[1]->conceptProperties[0])){
	$d=1;
}
if(!empty($xml->drugGroup->conceptGroup[2]->conceptProperties[0])){
	$d=2;
}
//var_dump($xml->drugGroup->conceptGroup[1]->conceptProperties[0]);

$i=0;

do{
     echo $xml->drugGroup->conceptGroup[$d]->conceptProperties[$i]->rxcui."<br>";
     echo $xml->drugGroup->conceptGroup[$d]->conceptProperties[$i]->name."<br>";
     echo $xml->drugGroup->conceptGroup[$d]->conceptProperties[$i]->synonym."<br>";
     echo $xml->drugGroup->conceptGroup[$d]->conceptProperties[$i]->umlscui."<br><br>";
$i++;
}while(!empty($xml->drugGroup->conceptGroup[$d]->conceptProperties[$i]->rxcui));

if(!empty($xml->drugGroup->conceptGroup[3]->conceptProperties[0])){
$d=3;
if(!empty($xml->drugGroup->conceptGroup[$d]->conceptProperties[0]->rxcui)){
    $i=0;

  do{
      echo $xml->drugGroup->conceptGroup[$d]->conceptProperties[$i]->rxcui."<br>";
      echo $xml->drugGroup->conceptGroup[$d]->conceptProperties[$i]->name."<br>";
      echo $xml->drugGroup->conceptGroup[$d]->conceptProperties[$i]->synonym."<br>";
      echo $xml->drugGroup->conceptGroup[$d]->conceptProperties[$i]->umlscui."<br>";
    $i++;
  }while(!empty($xml->drugGroup->conceptGroup[$d]->conceptProperties[$i]->rxcui));	
  }else{
	echo "End of Data" ;

  }
 }
} 
?>

