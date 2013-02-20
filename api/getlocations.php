<?php 
	header ("Content-Type:text/xml"); 
	require_once 'includes/class.database.php';
	require_once 'includes/functions.php';

	$xml_string = "";
	$xml_string .= "<LocationsList>\n";
	
	$strQuery = "SELECT id,
			name,
			phone,
			fax,
			street,
			city,
			state,
			postal_code,
			country_code
		FROM facility
		WHERE service_location = '1' 
		ORDER BY name, city ";

	$dbresult = $db->query($strQuery);
	if($dbresult)
	{
		$xml_string .= "<status>\n";
		$xml_string .= "0\n";
		$xml_string .= "</status>\n";
		$xml_string .= "<reason>\n";
		$xml_string .= "The locations Record has been fetched\n";
		$xml_string .= "</reason>\n";
		$counter = 0;
		while($row = $db->get_row($query=$strQuery,$output=ARRAY_A,$y=$counter)) 
		{
			$xml_string .= "<Location>\n";
			foreach ($row as $fieldname => $fieldvalue)
			{	  	 
				$rowvalue = xmlsafestring($fieldvalue);
				$xml_string .= "<$fieldname>$rowvalue</$fieldname>\n";
				$x++;
			} // foreach
			$xml_string .= "</Location>\n";
			$counter++;
		}
	} else {
		$xml_string .= "<status>\n";
		$xml_string .= "-1\n";
		$xml_string .= "</status>\n";
		$xml_string .= "<reason>\n";
		$xml_string .= "ERROR: Sorry, there was an error processing your data. Please re-submit the information again.";
		$xml_string .= "</reason>\n";
	}	
	$xml_string .= "</LocationsList>\n";

	echo $xml_string;
?>