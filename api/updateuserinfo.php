<?php 
	header ("Content-Type:text/xml"); 
	require_once 'includes/class.database.php';

	$firstname	= $_POST['firstname'];
	$lastname	= $_POST['lastname'];
	$phone		= $_POST['phone'];
	$email		= $_POST['email'];
	$username	= $_POST['username'];
	$password	= $_POST['password'];
	$pin		= $_POST['pin'];
	$createDate = date('Y-m-d');

	$xml_string = "";
	$xml_string .= "<MedMasterUser>\n";
	
	$strQuery = "SELECT username FROM medmasterusers WHERE username='".$username."' AND password='".$password."'";
	$result = $db->query($strQuery);
	
	if ($result) {
		$xml_string .= "<status>\n";
		$xml_string .= "-1\n";
		$xml_string .= "</status>\n";
		$xml_string .= "<reason>\n";
		$xml_string .= "Username is unavailable.";
		$xml_string .= "</reason>\n";
	} else {
		$strQuery = "INSERT INTO medmasterusers VALUES ('', '".$firstname."', '".$lastname."', '".$phone."', '".$email."', '".$username."', '".$password."',".$pin.", '".$createDate."')";
		$result = $db->query($strQuery);
		
		if ($result) {
			$xml_string .= "<status>\n";
			$xml_string .= "0\n";
			$xml_string .= "</status>\n";
			$xml_string .= "<reason>\n";
			$xml_string .= "The User has been registered";
			$xml_string .= "</reason>\n";
		} else {
			$xml_string .= "<status>\n";
			$xml_string .= "-1\n";
			$xml_string .= "</status>\n";
			$xml_string .= "<reason>\n";
			$xml_string .= "ERROR: Sorry, there was an error processing your data. Please re-submit the information again.";
			$xml_string .= "</reason>\n";
		}
	}
	
	$xml_string .= "</MedMasterUser>\n";
	echo $xml_string;
?>