<?php

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

include_once("../../globals.php");
include_once("$srcdir/sql.inc");
include_once("$srcdir/options.inc.php");
include_once("$srcdir/immunization_helper.php");

//collect facility data
$res = sqlQuery("select concat(f.name,'\n',f.street,'\n',f.city,', ',f.state,' ',f.postal_code) as facility_address ".
                " from facility f, users u ".
                " where u.facility = f.name ".
                " and u.id = ?", array($_SESSION['authId'])
                );

//collect patient data
$res2 = sqlQuery("select concat(p.lname,', ',p.fname,' ',p.mname) patient_name ".
                ",date_format(p.DOB,'%c/%e/%Y') as patient_DOB ".
                ",concat(p.street,'\n',p.city,', ',p.state,' ',p.postal_code) as patient_address".
                " from patient_data p where p.pid = ?", array($pid)
                );

//collect immunizations
$res3 = getImmunizationList($pid, $_GET['sortby'], false);
$data_array = convertToDataArray($res3);

$title = xl('Shot Record as of:','','',' ') . date('m/d/Y h:i:s a');

if ($_GET['output'] == "html") { 
	printHTML($res, $res2, $data_array);
}
else {
	printPDF($res, $res2, $data_array);
}


function convertToDataArray($data_array) {	
	$current = 0;
	while ($row = sqlFetchArray($data_array)) {
		//admin date
		$temp_date = new DateTime($row['administered_date']);
		$data[$current][xl('Date') . "\n" . xl('Admin')] = $temp_date->format('Y-m-d H:i'); //->format('%Y-%m-%d %H:%i');

		//Vaccine
        // Figure out which name to use (ie. from cvx list or from the custom list)
        if ($GLOBALS['use_custom_immun_list']) {
    		$vaccine_display = generate_display_field(array('data_type'=>'1','list_id'=>'immunizations'), $row['immunization_id']);
        }
        else {
            if (!empty($row['code_text_short'])) {
        	    $vaccine_display = htmlspecialchars( xl($row['code_text_short']), ENT_NOQUOTES);
            }
            else {
                $vaccine_display = generate_display_field(array('data_type'=>'1','list_id'=>'immunizations'), $row['immunization_id']);
            }
        } 		
		$data[$current][xl('Vaccine')] = $vaccine_display;
		
		//Amount
                if ($row['amount_administered'] > 0) {
		        $data[$current][xl('Amount') . "\n" . xl('Admin')] = $row['amount_administered'] . " " . 
			        generate_display_field(array('data_type'=>'1','list_id'=>'drug_units'), $row['amount_administered_unit']);
                }
                else {
                        $data[$current][xl('Amount') . "\n" . xl('Admin')] = "";
                }
		
		//expiration date fixed by checking for empty value, smw 040214
		if (isset($row['expiration_date'])) {
		$temp_date = new DateTime($row['expiration_date']);
		$data[$current][xl('Expiration') . "\n" . xl('Date')] = $temp_date->format('Y-m-d');
		}
		else{
		$data[$current][xl('Expiration') . "\n" . xl('Date')] = '';//$temp_date->format('Y-m-d');
		}
		
		//Manufacturer
		$data[$current][xl('Manufacturer')] = $row['manufacturer'];
		
		//Lot Number
		$data[$current][xl('Lot') . "\n" . xl('Number')] = $row['lot_number'];

		//Admin By
		$data[$current][xl('Admin') . "\n" . xl('By')] = $row['administered_by'];
		
		//education date
		$temp_date = new DateTime($row['education_date']);
		$data[$current][xl('Patient') . "\n" . xl('Education') . "\n" . xl('Date')] = $temp_date->format('Y-m-d');		

		//Route
		$data[$current][xl('Route')] = generate_display_field(array('data_type'=>'1','list_id'=>'drug_route'), $row['route']); 
		
		//Admin Site
		$data[$current][xl('Admin') . "\n" . xl('Site')] = generate_display_field(array('data_type'=>'1','list_id'=>'proc_body_site'), $row['administration_site']);
		
		//Comments
		$data[$current][xl('Comments')] = $row['note'];
		$current ++;
	}
	return $data;	
}

function printPDF($res, $res2, $data) {
	require_once ($GLOBALS['fileroot'] . "/library/classes/class.ezpdf.php");
	
	$pdf = new Cezpdf("LETTER");
	$pdf->ezSetMargins(72,30,50,30);
	$pdf->selectFont($GLOBALS['fileroot'] . "/library/fonts/Helvetica.afm");
	
	$opts = array('justification' => "center");
	$pdf->ezText($res['facility_address'] ,"",$opts);
	
	$pdf->ezText("\n" . $res2['patient_name'] . "\n" . xl('Date of Birth') . ": " . $res2['patient_DOB'] . "\n" . $res2['patient_address']);
	$pdf->ezText("\n");
	
	$opts = array('maxWidth' => 550, 'fontSize' => 8);
	
	$pdf->ezTable($data, "", $title, $opts);
	$pdf->ezText("\n\n\n\n" . xl('Signature') . ":________________________________","",array('justification' => 'right'));
	$pdf->ezStream();
}

function printHTML($res, $res2, $data) {
//print html css
    
  //convert end of line characters to html (escape for html output first)
  $patterns = array ('/\n/');
  $replace = array ('<br>');
  $res['facility_address'] = htmlspecialchars( $res['facility_address'], ENT_NOQUOTES);
  $res['facility_address'] = preg_replace($patterns, $replace, $res['facility_address']);
  $res2['patient_address'] = htmlspecialchars( $res2['patient_address'], ENT_NOQUOTES);
  $res2['patient_address'] = preg_replace($patterns, $replace, $res2['patient_address']);
  
  //deal with bug (last array index is empty)
  //array_pop($data);
    
  ?>  
	
  <html>
  <head>
  <style>
    body {
      font-family: sans-serif;
      font-weight: normal;
      font-size: 10pt;
      background: white;
      color: black;
    }
    div {
      padding: 0;
      margin: 0;
    }	
    div.paddingdiv {
      width: 524pt;
      height: 668pt;
      page-break-after: always;
    }
    div.patientAddress {
      margin: 20pt 0 10pt 0;
      font-size:  10pt;
    }
    div.clinicAddress {
      text-align: center;
      width: 100%;
      font-size: 10pt;
    }	
    div.sign {
      margin: 30pt 0 0 20pt;
    }
    div.tabletitle {
      font-size: 12pt;
      text-align: center;
      width: 100%;
    }	
    table {
      margin: 0 20pt 0 20pt;
      border-collapse: collapse;
      border: 1pt solid black;
    }	
    td {
      font-size: 10pt;
      padding: 2pt 3pt 2pt 3pt;
      border-right: 1pt solid black;
      border-left: 1pt solid black;
    }
    td.odd {
      background-color: #D8D8D8; 	
    }	
    th {
      font-size: 10pt;
      border: 1pt solid black;
      padding: 2pt 3pt 2pt 3pt;
    }	
    div.pageNumber {
      margin-top: 15pt;
      font-size: 8pt;
      text-align: center;
      width: 100%;
    }
  </style>
  <title><?php xl ('Shot Record','e'); ?></title>
  </head>
  <body>
	
  <?php
  //plan 15 lines per page
  $linesPerPage=15;
  $countTotalPages = (ceil((count($data))/$linesPerPage));
  for ($i=0;$i<$countTotalPages;$i++) {
    echo "<div class='paddingdiv'>\n";
      
    //display facility information (Note it is already escaped)
    echo "<div class='clinicAddress'>" . $res['facility_address'] . "</div>\n";
    
    //display patient information (Note patient address is already escaped)
    echo "<div class='patientAddress'>" . htmlspecialchars( $res2['patient_name'], ENT_NOQUOTES) . "<br>" .
      htmlspecialchars( xl('Date of Birth') . ": " . $res2['patient_DOB'], ENT_NOQUOTES) . "<br>" .
      $res2['patient_address'] . "</div>\n";

    //display table title
    echo "<div class='tabletitle'>" . htmlspecialchars( $title, ENT_NOQUOTES) . "</div>\n";
      
    echo "<table cellspacing='0' cellpadding='0'>\n";
      
    //display header
    echo "<tr>\n";
    foreach ($data[0] as $key => $value) {

	//convert end of line characters to space
	$patterns = array ('/\n/');
	$replace = array (' ');
	$key = preg_replace($patterns, $replace, $key);
      	echo "<th>".htmlspecialchars( $key, ENT_NOQUOTES)."</th>\n";
    }
    echo "</tr>\n";
    
    //display shot data
    for ($j=0;$j<$linesPerPage;$j++) {
      if ($rowData = array_shift($data)) {
	echo "<tr>";
	foreach ($rowData as $key => $value) {

	  //shading of cells
	  if ($j==0) {
	    echo "<td>";
	  }
	  elseif ($j%2) {
	    echo "<td class ='odd'>";
	  }
	  else {
	    echo "<td>";   
	  }
	    
	  // output data of cell
	    echo ($value == "") ? "&nbsp;" : htmlspecialchars($value, ENT_NOQUOTES);
	  echo "</td>";
	}
      }
      else {
	//done displaying shot data, so leave loop
        break;	    
      }
    }
  
    echo "</table>\n";
    
    //display signature line
    echo "<div class='sign'>" . htmlspecialchars( xl('Signature'), ENT_NOQUOTES) .
      ":________________________________" . "</div>\n";
  
    if ($countTotalPages > 1) {
      //display page number if greater than one page
      echo "<div class='pageNumber'>" .
        htmlspecialchars( xl('Page') . " " . ($i+1) . "/" . $countTotalPages, ENT_NOQUOTES) .
	"</div>\n";
    }
      
    echo "</div>\n";    
  }
  
  ?>
    
  <script language='JavaScript'>
    opener.top.printLogPrint(window);
  </script>
  </body>
  </html>
  <?php
}


?>
