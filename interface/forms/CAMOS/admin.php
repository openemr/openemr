<?php
include_once ('../../globals.php'); 
include_once("../../../library/formdata.inc.php");
?>
<?php
if ($_POST['export']) {
	$temp = tmpfile();
	if ($temp === false) {echo "<h1>" . xl("failed") . "</h1>";}
	else {
		$query1 = "select id, category from form_CAMOS_category";
		$statement1 = sqlStatement($query1);
		while ($result1 = sqlFetchArray($statement1)) {
		        $tmp = $result1['category'];
		        $tmp = "<category>$tmp</category>"."\n";
		        fwrite($temp, $tmp);
		        $query2 = "select id,subcategory from form_CAMOS_subcategory where category_id=".$result1['id'];
		        $statement2 = sqlStatement($query2);
		        while ($result2 = sqlFetchArray($statement2)) {
		                $tmp = $result2['subcategory'];
		                $tmp = "<subcategory>$tmp</subcategory>"."\n";
		                fwrite($temp, $tmp);
		                $query3 = "select item, content from form_CAMOS_item where subcategory_id=".$result2['id'];
		                $statement3 = sqlStatement($query3);
		                while ($result3 = sqlFetchArray($statement3)) {
		                        $tmp = $result3['item'];
		                        $tmp = "<item>$tmp</item>"."\n";
		                        fwrite($temp, $tmp);
		                        $tmp = preg_replace(array("/\n/","/\r/"),array("\\\\n","\\\\r"),$result3['content']);
		                        $tmp = "<content>$tmp</content>"."\n";
		                        fwrite($temp, $tmp);
		                }
		        }
		}
		rewind($temp);
	        header("Pragma: public");
	        header("Expires: 0");
	        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: text/plain");
	        header("Content-Disposition: attachment; filename=\"CAMOS_export.txt\"");
	
		fpassthru($temp);
		fclose($temp);
	}
}
if ($_POST['import']) {
?>
<?php
	$fname = '';
	foreach($_FILES as $file) {
		$fname = $file['tmp_name'];
//		echo "<p>tmp filename: ".$file['tmp_name']."</p>";
	}
	$handle = @fopen($fname,"r");
	if ($handle === false) {
		echo "<h1>" . xl('Error opening uploaded file for reading') . "</h1>";
	} else {
		$category = '';
		$category_id = 0;
		$subcategory = '';
		$subcategory_id = 0;
		$item = '';
		$item_id = 0;
		$content = '';
		while (!feof($handle)) {
			$buffer = fgets($handle);
			if (preg_match('/<category>(.*?)<\/category>/',$buffer,$matches)) {

				$category = add_escape_custom(trim($matches[1])); //trim in case someone edited by hand and added spaces
				$statement = sqlStatement("select id from form_CAMOS_category where category like \"$category\"");
				if ($result = sqlFetchArray($statement)) {
					$category_id = $result['id'];
				} else {
					$query = "INSERT INTO form_CAMOS_category (user, category) ". 
						"values ('".$_SESSION['authUser']."', \"$category\")"; 
					sqlInsert($query);
					$statement = sqlStatement("select id from form_CAMOS_category where category like \"$category\"");
					if ($result = sqlFetchArray($statement)) {
						$category_id = $result['id'];
					}
				}
			}
			if (preg_match('/<subcategory>(.*?)<\/subcategory>/',$buffer,$matches)) {

				$subcategory = add_escape_custom(trim($matches[1]));
				$statement = sqlStatement("select id from form_CAMOS_subcategory where subcategory " .
					"like \"$subcategory\" and category_id = $category_id");
				if ($result = sqlFetchArray($statement)) {
					$subcategory_id = $result['id'];
				} else {
					$query = "INSERT INTO form_CAMOS_subcategory (user, subcategory, category_id) ". 
						"values ('".$_SESSION['authUser']."', \"$subcategory\", $category_id)"; 
					sqlInsert($query);
					$statement = sqlStatement("select id from form_CAMOS_subcategory where subcategory " .
						"like \"$subcategory\" and category_id = $category_id");
					if ($result = sqlFetchArray($statement)) {
						$subcategory_id = $result['id'];
					}
				}
			}
			if ((preg_match('/<(item)>(.*?)<\/item>/',$buffer,$matches)) || 
			(preg_match('/<(content)>(.*?)<\/content>/s',$buffer,$matches))) {

				$mode = $matches[1];
				$value = add_escape_custom(trim($matches[2]));
				$insert_value = '';
				if ($mode == 'item') {
					$postfix = 0;
					$statement = sqlStatement("select id from form_CAMOS_item where item like \"$value\" " .
						"and subcategory_id = $subcategory_id");
					if ($result = sqlFetchArray($statement)) {//let's count until we find a number available
						$postfix = 1;
						$inserted_duplicate = false;
						while ($inserted_duplicate === false) {
							$insert_value = $value."_".$postfix;
							$inner_statement = sqlStatement("select id from form_CAMOS_item " .
								"where item like \"$insert_value\" " .
								"and subcategory_id = $subcategory_id");
							if (!($inner_result = sqlFetchArray($inner_statement))) {//doesn't exist
								$inner_query = "INSERT INTO form_CAMOS_item (user, item, subcategory_id) ". 
									"values ('".$_SESSION['authUser']."', \"$insert_value\", ".
									"$subcategory_id)"; 
								sqlInsert($inner_query);
								$inserted_duplicate = true;
							} else {$postfix++;}
						}
					} else {
						$query = "INSERT INTO form_CAMOS_item (user, item, subcategory_id) ". 
							"values ('".$_SESSION['authUser']."', \"$value\", $subcategory_id)"; 
						sqlInsert($query);
					}
					if ($postfix == 0) {$insert_value = $value;}
					$statement = sqlStatement("select id from form_CAMOS_item where item like \"$insert_value\" " .
						"and subcategory_id = $subcategory_id");
					if ($result = sqlFetchArray($statement)) {
						$item_id = $result['id'];
					}
				}
				elseif ($mode == 'content') {
					$statement = sqlStatement("select content from form_CAMOS_item where id = ".$item_id);
					if ($result = sqlFetchArray($statement)) {
						//$content = "/*old*/\n\n".$result['content']."\n\n/*new*/\n\n$value";
						$content = $value;
					} else {
						$content = $value;
					}
					$query = "UPDATE form_CAMOS_item set content = \"$content\" where id = ".$item_id;
					sqlInsert($query);
				}
			}
		}
		fclose($handle);
	}
}
?>
<html>
<head>
<title>
admin
</title>
</head>
<body>
<p>
<?php xl("Click 'export' to export your Category, Subcategory, Item, Content data to a text file. Any resemblance of this file to an XML file is purely coincidental. The opening and closing tags must be on the same line, they must be lowercase with no spaces. To import, browse for a file and click 'import'. If the data is completely different, it will merge with your existing data. If there are similar item names, The old one will be kept and the new one saved with a number added to the end.","e"); ?>
<?php xl("This feature is very experimental and not fully tested. Use at your own risk!","e"); ?>
</p>
<form enctype="multipart/form-data" method="POST">
<input type="hidden" name="MAX_FILE_SIZE" value="12000000" />
<?php xl('Send this file','e'); ?>: <input type="file" name="userfile"/>
<input type="submit" name="import" value='<?php xl("Import","e"); ?>'/>
<input type="submit" name="export" value='<?php xl("Export","e"); ?>'/>
</form>
</body>
</html>
