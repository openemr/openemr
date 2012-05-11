<?php
/*    
    batch Email processor, included from batchcom 
*/

// create file header.
// menu for fields could be added in the future

?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="batchcom.css" type="text/css">
</head>
<body class="body_top">
<span class="title"><?php xl('Batch Communication Tool','e')?></span>
<br><br>
<span class="title" ><?php xl('Email Notification Report','e')?></span>
<br><br>


<?php
$email_sender=$_POST['email_sender'];
$sent_by=$_SESSION["authId"];
$msg_type=xl('Email from Batchcom');

while ($row=sqlFetchArray($res)) {

    // prepare text for ***NAME*** tag
    $pt_name=$row['title'].' '.$row['fname'].' '.$row['lname'];
    $pt_email=$row['email'];
	
	$email_subject=$_POST['email_subject'];
    $email_body=$_POST['email_body'];
    $email_subject=ereg_replace('\*{3}NAME\*{3}', $pt_name, $email_subject );
    $email_body=   ereg_replace('\*{3}NAME\*{3}', $pt_name, $email_body );

    $headers = "MIME-Version: 1.0\r\n";
	$headers .= "To: $pt_name<".$pt_email.">\r\n";
    $headers .= "From: <".$email_sender.">\r\n";
    $headers .= "Reply-to: <".$email_sender.">\r\n";
    $headers .= "X-Priority: 3\r\n";
    $headers .= "X-Mailer: PHP mailer\r\n";
    if ( mail($pt_email,$email_subject,$email_body,$headers)) {

        echo ("<br>" . xl('Email sent to') . ": " . text($pt_name) . " , " . text($pt_email) . "<br>");

    } else {
        $m_error=TRUE;
        $m_error_count++;
    }

}

if ($m_error) {
   echo (xl('Could not send email due to a server problem. ','','<br>'). $m_error_count . xl(' emails not sent','',''));
}

?> 
