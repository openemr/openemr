<?php

include_once("../globals.php");
set_time_limit(0);
$format = $_GET['format'];
$billkey = escapeshellarg($_GET['billkey']);

$command = "perl /usr/share/freeb/formatbin/" . $format . ".pl";
$command = escapeshellcmd($command);


$logfile = "/tmp/log-" . date("Ymd") . "-" . rand() . ".log";

$execstring = $command . " " . $billkey . " " . $logfile;
echo "Running Command: $execstring\n<br>";
echo "<p>";
echo "Please note that this can take a very long time, up to several minutes, your web browser may not appear very active during this time but generating a bill is a 
complicated process and your web browser is merely waiting for more information.";
echo "</p>";
echo "<p>";
echo "You should be running this test if this claim appeared to generate successfully but the actual claim file does not contain any data or only an unfinished portion
of the amount of data it is supposed to contain. It is obvious with HCFA claims because they are human readable, with X12 claims it is a more difficult process to determine
if the claim is properly complete.";
echo " Pecularities in many browsers may mean that the output below enters your screen in sudden jerks and that there are long pauses of several seconds where it 
appears as though things may have crashed. That is not the case, you will eventually see output coming out line by line. There may be sequential numbers appearing 
below, this is to indicate that even though nothing else may be displaying there is activity going on. These numbers will be interspersed with the content of the 
billing and that is normal.";
echo "</p>";
echo "<p>";
echo "Depending on the type of bill you are testing you will see HCFA like output on a blank page for HCFA bills, you will see many lines of somewhat garbled text 
and information if you are testing an X12 claim. That garbled text is the X12 EDI 4010A format. Occasionally you will see odd characters that look like dominoes or 
squiglies, these are control characters such as page feeds and are normal.";
echo "</p>";
echo "<p>";
echo "Please do NOT use your browsers stop or reload button while this page is running unless more than 10 minutes have elapsed, this will not cause the process to 
stop on the server and will consume uneccesary resources.";
echo "</p>";
flush();
ob_flush;
$descriptorspec = array(
   0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
   1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
   2 => array("pipe", "w") // stderr is a file to write to
);

$process = proc_open($execstring, $descriptorspec, $pipes);
if (is_resource($process)) {
    // $pipes now looks like this:
    // 0 => writeable handle connected to child stdin
    // 1 => readable handle connected to child stdout
    // Any error output will be appended to /tmp/error-output.txt
	stream_set_blocking($pipes[1],false);
	stream_set_blocking($pipes[2],false);
    
	echo "<pre>";
	$loop = true;
	$p1 = true;
	$p2 = true;
	$counter = 0;
    while($loop) {	
    	echo sprintf("%04d",$counter) . " ";
    	flush();
    	
    	if (!feof($pipes[1])) {
    		fflush($pipes[1]); 
    		$val =  fgets($pipes[1], 1024);
    		if (!empty($val)) {
    			echo $val;
    		 	flush();
    		}
    	}
    	else {
    		$p1 = false;	
    	}
    	if (!feof($pipes[2])) {
    		fflush($pipes[2]);
    		$val = fgets($pipes[2], 1024);
    		if (!empty($val)) {
    			echo $val;
    	 		flush();
    		}
    	}
    	else {
    		$p2 = false;	
    	}
    	if (!$p1 && !$p2) {
    		$loop = false;
    	}
        $counter++;
        usleep(1500000);
    }	
    fclose($pipes[0]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    echo "</pre>";
    // It is important that you close any pipes before calling
    // proc_close in order to avoid a deadlock
    $return_value = proc_close($process);

    echo "<br>Claim test has completed running\n";
}

?>