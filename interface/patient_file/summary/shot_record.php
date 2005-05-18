<?php
include_once("../../globals.php");
include_once("$srcdir/sql.inc");
require_once ($GLOBALS['fileroot'] . "/library/classes/class.ezpdf.php");

$pdf =& new Cezpdf("LETTER");
$pdf->ezSetMargins(72,30,50,30);
$pdf->selectFont($GLOBALS['fileroot'] . "/library/fonts/Helvetica.afm");

$res = sqlQuery("select concat(f.name,'\n',f.street,'\n',f.city,', ',f.state,' ',f.postal_code) as facility_address
                   from facility f
                       ,users u
                  where u.facility = f.name
                    and u.id = " . $_SESSION['authId']
                );

$opts = array('justification' => "center");
$pdf->ezText($res['facility_address']
            ,"",$opts
            );

$res = sqlQuery("select concat(p.lname,', ',p.fname,' ',p.mname) patient_name 
                       ,date_format(p.DOB,'%c/%e/%Y') as patient_DOB
                       ,concat(p.street,'\n',p.city,', ',p.state,' ',p.postal_code) as patient_address
                   from patient_data p where p.id = $pid");

$pdf->ezText("\n" . $res['patient_name'] . "\nDate of Birth: " . $res['patient_DOB'] . "\n" . $res['patient_address']);
$pdf->ezText("\n");

$title = 'Shot Record as of: ' . date('m/d/Y h:i:s a');

$res = sqlStatement("select date_format(i1.administered_date,'%Y-%c-%e') as 'Date\nAdministered'
      ,i2.name as 'Vaccine'
      ,i1.manufacturer as 'Manufacturer'
      ,i1.lot_number as 'Lot\nNumber'
      ,concat(u.lname,', ',u.fname) as 'Administered By'
      ,date_format(i1.education_date,'%Y-%c-%e') as 'Patient\nEducation\nDate'
      ,i1.note as 'Comments'
  from immunizations i1
         left join immunization i2 on i1.immunization_id = i2.id
         left join users u on i1.administered_by_id = u.id
         left join patient_data p on i1.patient_id = p.id
where p.id = " . $pid);

while ($data[] = sqlFetchArray($res)) {}

$opts = array('maxWidth' => 504
             ,'fontSize' => 8
             );

$pdf->ezTable($data,"",$title,$opts);

$pdf->ezText("\n\n\n\nSignature:________________________________","",array('justification' => 'right'));

$pdf->ezStream();

?>
