<?php
/*	
	batch Email processor, included from batchcom 
*/

// create file header.
// menu for fields could be added in the future

?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<link rel=stylesheet href="batchcom.css" type="text/css">
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<span class="title">Batch Communication Tool</span>
<br><br>

<?
$email_sender=$_POST['email_sender'];

while ($row=sqlFetchArray($res)) {

	// prepare text for ***NAME*** tag
	$pt_name=$row['title'].' '.$row['fname'].' '.$row['lname'];
	$pt_email=$row['email'];
	$email_subject=$_POST['email_subject'];
	$email_body=$_POST['email_body'];
	$email_subject=ereg_replace('\*{3}NAME\*{3}', $pt_name, $email_subject );
	$email_body=   ereg_replace('\*{3}NAME\*{3}', $pt_name, $email_body );

	$headers = "MIME-Version: 1.0\r\n";
	$headers .= "To: $pt_name<$pt_email>\r\n";
	$headers .= "From: <$email_sender>\r\n";
	$headers .= "Reply-to: <$email_sender>\r\n";
	$headers .= "X-Priority: 3\r\n";
	$headers .= "X-Mailer: PHP mailer\r\n";
	
	if ( mail ('',$email_subject,$email_body,$headers)) {

		echo ("<br>Email sent to $pt_name at $pt_email");

	} else {
		$m_error=TRUE;
		$m_error_count++;
	}

}

if ($m_error) {
	echo ("<br>Could not send email due to a server problem, $m_error_count emails not sent<br>");
}

?> 
