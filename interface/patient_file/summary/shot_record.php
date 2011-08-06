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
$sqlstmt = "select date_format(i1.administered_date,'%Y-%m-%d') as '" . xl('Date') . "\n" . xl('Administered') . "' ".
            ",i1.immunization_id as '" . xl('Vaccine') . "' ".
            ",c.code_text_short as cvx_text ".
            ",i1.manufacturer as '" . xl('Manufacturer') . "' ".
            ",i1.lot_number as '" . xl('Lot') . "\n" . xl('Number') . "' ".
            ",concat(u.lname,', ',u.fname) as '" . xl('Administered By') . "' ".
            ",date_format(i1.education_date,'%Y-%m-%d') as '" . xl('Patient') . "\n" . xl('Education') . "\n" . xl('Date') . "' ".
            ",i1.note as '" . xl('Comments') . "'".
            " from immunizations i1 ".
            " left join users u on i1.administered_by_id = u.id ".
            " left join patient_data p on i1.patient_id = p.pid ".
            " left join codes c on i1.cvx_code = c.code ".
            " left join code_types ct on c.code_type = ct.ct_id ".
            " where p.pid = ? ".
            " AND (( i1.cvx_code = '0' ) OR ".
                " ( i1.cvx_code != '0' AND ct.ct_key = 'CVX' )) ";

// sort the results, as they are on the user's screen
$sqlstmt .= " order by ";
if ($_GET['sortby'] == "vacc") { $sqlstmt .= " i1.immunization_id, i1.administered_date DESC"; }
else { $sqlstmt .= " i1.administered_date desc"; }

$res3 = sqlStatement($sqlstmt, array($pid) );

while ($data[] = sqlFetchArray($res3)) {}

for ($i=0;$i<count($data);$i++) {
  // Figure out which name to use (ie. from cvx list or from the custom list)
  if ($GLOBALS['use_custom_immun_list']) {
   $data[$i][xl('Vaccine')] = generate_display_field(array('data_type'=>'1','list_id'=>'immunizations'), $data[$i][xl('Vaccine')]);
  }
  else {
    if (!(empty($data[$i]['cvx_text']))) {
      $data[$i][xl('Vaccine')] = htmlspecialchars( xl($data[$i]['cvx_text']), ENT_NOQUOTES);
    }
    else {
      $data[$i][xl('Vaccine')] = generate_display_field(array('data_type'=>'1','list_id'=>'immunizations'), $data[$i][xl('Vaccine')]);
    }
  }
  unset( $data[$i]['cvx_text'] );
}


$title = xl('Shot Record as of:','','',' ') . date('m/d/Y h:i:s a');


if ($_GET['output'] == "html") { //print html css
    
  //convert end of line characters to html (escape for html output first)
  $patterns = array ('/\n/');
  $replace = array ('<br>');
  $res['facility_address'] = htmlspecialchars( $res['facility_address'], ENT_NOQUOTES);
  $res['facility_address'] = preg_replace($patterns, $replace, $res['facility_address']);
  $res2['patient_address'] = htmlspecialchars( $res2['patient_address'], ENT_NOQUOTES);
  $res2['patient_address'] = preg_replace($patterns, $replace, $res2['patient_address']);
  
  //deal with bug (last array index is empty)
  array_pop($data);
    
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
	echo "<tr>\n";
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
    window.print();
  </script>
  </body>
  </html>
	
  <?php
}

else { //print pdf
require_once ($GLOBALS['fileroot'] . "/library/classes/class.ezpdf.php");

$pdf =& new Cezpdf("LETTER");
$pdf->ezSetMargins(72,30,50,30);
$pdf->selectFont($GLOBALS['fileroot'] . "/library/fonts/Helvetica.afm");

$opts = array('justification' => "center");
$pdf->ezText($res['facility_address'] ,"",$opts);

$pdf->ezText("\n" . $res2['patient_name'] . "\n" . xl('Date of Birth') . ": " . $res2['patient_DOB'] . "\n" . $res2['patient_address']);
$pdf->ezText("\n");

$opts = array('maxWidth' => 504, 'fontSize' => 8);

$pdf->ezTable($data, "", $title, $opts);

$pdf->ezText("\n\n\n\n" . xl('Signature') . ":________________________________","",array('justification' => 'right'));

$pdf->ezStream();

} # end pdf print

?>
