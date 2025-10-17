<?php

/**
 * Exports patient demographics to a custom XML format
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author  Roberto Vasquez <robertogagliotta@gmail.com>
 * @copyright Copyright (c) 2005 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017 Roberto Vasquez <robertogagliotta@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
*/

 require_once("../interface/globals.php");
 require_once("../library/patient.inc.php");

 use OpenEMR\Core\Header;

 $out = "";
 $indent = 0;

 // Add a string to output with some basic sanitizing.
function custom_xml_Add($tag, $text): void
{
    global $out, $indent;
    $text = trim(str_replace(["\r", "\n", "\t"], " ", ($text ?? '')));
    if ($text) {
        for ($i = 0; $i < $indent; ++$i) {
            $out .= "\t";
        }

        $out .= "<$tag>$text</$tag>\n";
    }
}

function OpenTag($tag): void
{
    global $out, $indent;
    for ($i = 0; $i < $indent; ++$i) {
        $out .= "\t";
    }

    ++$indent;
    $out .= "<$tag>\n";
}

function CloseTag($tag): void
{
    global $out, $indent;
    --$indent;
    for ($i = 0; $i < $indent; ++$i) {
        $out .= "\t";
    }

    $out .= "</$tag>\n";
}

 // Remove all non-digits from a string.
function Digits($field)
{
    return preg_replace("/\D/", "", (string) $field);
}

 // Translate sex.
function Sex($field)
{
    $sex = strtoupper(substr(trim((string) $field), 0, 1));
    if ($sex != "M" && $sex != "F") {
        $sex = "U";
    }

    return $sex;
}

 // Translate a date.
function LWDate($field)
{
    return fixDate($field);
}

 // Add an insurance section.
function addInsurance($row, $seq): void
{
    if ($row["name$seq"]) {
        OpenTag("insurance");
        custom_xml_Add("priority", $seq);
        custom_xml_Add("group", $row["group$seq"]);
        custom_xml_Add("policy", $row["policy$seq"]);
        custom_xml_Add("provider", $row["provider$seq"]);
        custom_xml_Add("name", $row["name$seq"]);
        custom_xml_Add("street1", $row["street1$seq"]);
        custom_xml_Add("street2", $row["street2$seq"]);
        custom_xml_Add("city", $row["city$seq"]);
        custom_xml_Add("state", $row["state$seq"]);
        custom_xml_Add("zip", $row["zip$seq"]);
        custom_xml_Add("country", $row["country$seq"]);
        custom_xml_Add("type", $row["instype$seq"]);
        custom_xml_Add("copay", $row["copay$seq"]);
        OpenTag("subscriber");
        custom_xml_Add("relationship", $row["relationship$seq"]);
        custom_xml_Add("lname", $row["lname$seq"]);
        custom_xml_Add("fname", $row["fname$seq"]);
        custom_xml_Add("mname", $row["mname$seq"]);
        custom_xml_Add("street", $row["sstreet$seq"]);
        custom_xml_Add("city", $row["scity$seq"]);
        custom_xml_Add("state", $row["sstate$seq"]);
        custom_xml_Add("zip", $row["szip$seq"]);
        custom_xml_Add("country", $row["scountry$seq"]);
        custom_xml_Add("dob", $row["sdob$seq"]);
        custom_xml_Add("ss", $row["sss$seq"]);
        custom_xml_Add("phone", $row["sphone$seq"]);
        custom_xml_Add("employer", $row["semployer$seq"]);
        custom_xml_Add("sex", $row["ssex$seq"]);
        custom_xml_Add("employer_street", $row["semployer_street$seq"]);
        custom_xml_Add("employer_city", $row["semployer_city$seq"]);
        custom_xml_Add("employer_state", $row["semployer_state$seq"]);
        custom_xml_Add("employer_zip", $row["semployer_zip$seq"]);
        custom_xml_Add("employer_country", $row["semployer_country$seq"]);
        CloseTag("subscriber");
        CloseTag("insurance");
    }
}

 // This mess gets all the info for the patient.
 //~Well, now it does...-Art
 $insrow = [];
foreach (['primary','secondary','tertiary'] as $value) {
    $insrow[] = sqlQuery("SELECT id FROM insurance_data WHERE " .
    "pid = ? AND type = ? ORDER BY date DESC LIMIT 1", [$pid, $value]);
}

 $query = "SELECT " .
  "p.*, " .
  "i1.policy_number AS policy1, i1.group_number AS group1, i1.provider as provider1, " .
  "i1.subscriber_fname AS fname1, i1.subscriber_mname AS mname1, i1.subscriber_lname AS lname1, " .
  "i1.subscriber_street AS sstreet1, i1.subscriber_city AS scity1, i1.subscriber_state AS sstate1, " .
  "i1.subscriber_postal_code AS szip1, i1.subscriber_relationship AS relationship1, " .
  "i1.subscriber_DOB AS sdob1, i1.subscriber_ss AS sss1, i1.subscriber_phone AS sphone1, " .
  "i1.subscriber_sex AS ssex1, i1.subscriber_country AS scountry1, " .
  "i1.subscriber_employer AS semployer1, i1.subscriber_employer_street AS semployer_street1, " .
  "i1.subscriber_employer_city AS semployer_city1, i1.subscriber_employer_state AS semployer_state1, " .
  "i1.subscriber_employer_postal_code AS semployer_zip1, " .
  "i1.subscriber_employer_country AS semployer_country1, i1.copay AS copay1, " .
  "c1.name AS name1, c1.ins_type_code AS instype1, " .
  "a1.line1 AS street11, a1.line2 AS street21, a1.city AS city1, a1.state AS state1, " .
  "a1.zip AS zip1, a1.plus_four AS zip41, a1.country AS country1, " .
  "i2.policy_number AS policy2, i2.group_number AS group2, i2.provider as provider2, " .
  "i2.subscriber_fname AS fname2, i2.subscriber_mname AS mname2, i2.subscriber_lname AS lname2, " .
  "i2.subscriber_postal_code AS szip2, i2.subscriber_relationship AS relationship2, " .
  "i2.subscriber_DOB AS sdob2, i2.subscriber_ss AS sss2, i2.subscriber_phone AS sphone2, " .
  "i2.subscriber_sex AS ssex2, i2.subscriber_country AS scountry2, " .
  "i2.subscriber_employer AS semployer2, i2.subscriber_employer_street AS semployer_street2, " .
  "i2.subscriber_employer_city AS semployer_city2, i2.subscriber_employer_state AS semployer_state2, " .
  "i2.subscriber_employer_postal_code AS semployer_zip2, " .
  "i2.subscriber_employer_country AS semployer_country2, i2.copay AS copay2, " .
  "c2.name AS name2, c2.ins_type_code AS instype2, " .
  "a2.line1 AS street12, a2.line2 AS street22, a2.city AS city2, a2.state AS state2, " .
  "a2.zip AS zip2, a2.plus_four AS zip42, a2.country AS country2, " .
  "i3.policy_number AS policy3, i3.group_number AS group3, i3.provider as provider3, " .
  "i3.subscriber_fname AS fname3, i3.subscriber_mname AS mname3, i3.subscriber_lname AS lname3, " .
  "i3.subscriber_postal_code AS szip3, i3.subscriber_relationship AS relationship3, " .
  "i3.subscriber_DOB AS sdob3, i3.subscriber_ss AS sss3, i3.subscriber_phone AS sphone3, " .
  "i3.subscriber_sex AS ssex3, i3.subscriber_country AS scountry3, " .
  "i3.subscriber_employer AS semployer3, i3.subscriber_employer_street AS semployer_street3, " .
  "i3.subscriber_employer_city AS semployer_city3, i3.subscriber_employer_state AS semployer_state3, " .
  "i3.subscriber_employer_postal_code AS semployer_zip3, " .
  "i3.subscriber_employer_country AS semployer_country3, i3.copay AS copay3, " .
  "c3.name AS name3, c3.ins_type_code AS instype3, " .
  "a3.line1 AS street13, a3.line2 AS street23, a3.city AS city3, a3.state AS state3, " .
  "a3.zip AS zip3, a3.plus_four AS zip43, a3.country AS country3 " .
  "FROM patient_data AS p " .
  // "LEFT OUTER JOIN insurance_data AS i1 ON i1.pid = p.pid AND i1.type = 'primary'   " .
  // "LEFT OUTER JOIN insurance_data AS i2 ON i2.pid = p.pid AND i2.type = 'secondary' " .
  // "LEFT OUTER JOIN insurance_data AS i3 ON i3.pid = p.pid AND i3.type = 'tertiary'  " .
  "LEFT OUTER JOIN insurance_data AS i1 ON i1.id = ? " .
  "LEFT OUTER JOIN insurance_data AS i2 ON i2.id = ? " .
  "LEFT OUTER JOIN insurance_data AS i3 ON i3.id = ? " .
  //
  "LEFT OUTER JOIN insurance_companies AS c1 ON c1.id = i1.provider " .
  "LEFT OUTER JOIN insurance_companies AS c2 ON c2.id = i2.provider " .
  "LEFT OUTER JOIN insurance_companies AS c3 ON c3.id = i3.provider " .
  "LEFT OUTER JOIN addresses AS a1 ON a1.foreign_id = c1.id " .
  "LEFT OUTER JOIN addresses AS a2 ON a2.foreign_id = c2.id " .
  "LEFT OUTER JOIN addresses AS a3 ON a3.foreign_id = c3.id " .
  "WHERE p.pid = ? LIMIT 1";

 $row = sqlFetchArray(sqlStatement($query, [($insrow[0]['id'] ?? null), ($insrow[1]['id'] ?? null), ($insrow[2]['id'] ?? null), $pid]));

 $rowed = getEmployerData($pid);

 OpenTag("patient");

 // Patient Section.
 //
 custom_xml_Add("pid", $pid);
 custom_xml_Add("pubpid", $row['pubpid']);
 custom_xml_Add("lname", $row['lname']);
 custom_xml_Add("fname", $row['fname']);
 custom_xml_Add("mname", $row['mname']);
 custom_xml_Add("title", $row['title']);
 custom_xml_Add("ss", Digits($row['ss']));
 custom_xml_Add("dob", LWDate($row['DOB']));
 custom_xml_Add("sex", Sex($row['sex']));
 custom_xml_Add("street", $row['street']);
 custom_xml_Add("city", $row['city']);
 custom_xml_Add("state", $row['state']);
 custom_xml_Add("zip", $row['postal_code']);
 custom_xml_Add("country", $row['country_code']);
 custom_xml_Add("phone_home", Digits($row['phone_home']));
 custom_xml_Add("phone_biz", Digits($row['phone_biz']));
 custom_xml_Add("phone_contact", Digits($row['phone_contact']));
 custom_xml_Add("phone_cell", Digits($row['phone_cell']));
 custom_xml_Add("occupation", $row['occupation']);
 custom_xml_Add("status", $row['status']);
 custom_xml_Add("contact_relationship", $row['contact_relationship']);
 custom_xml_Add("referrer", $row['referrer']);
 custom_xml_Add("referrerID", $row['referrerID']);
 custom_xml_Add("email", $row['email']);
 custom_xml_Add("language", $row['language']);
 custom_xml_Add("ethnoracial", $row['ethnoracial']);
 custom_xml_Add("interpreter", $row['interpretter']);
 custom_xml_Add("migrantseasonal", $row['migrantseasonal']);
 custom_xml_Add("family_size", $row['family_size']);
 custom_xml_Add("monthly_income", $row['monthly_income']);
 custom_xml_Add("homeless", $row['homeless']);
 custom_xml_Add("financial_review", LWDate(substr((string) $row['financial_review'], 0, 10)));
 custom_xml_Add("genericname1", $row['genericname1']);
 custom_xml_Add("genericval1", $row['genericval1']);
 custom_xml_Add("genericname2", $row['genericname2']);
 custom_xml_Add("genericval2", $row['genericval2']);
 custom_xml_Add("billing_note", $row['billing_note']);
 custom_xml_Add("hipaa_mail", $row['hipaa_mail']);
 custom_xml_Add("hipaa_voice", $row['hipaa_voice']);

 // Insurance Sections.
 //
 addInsurance($row, '1');
 addInsurance($row, '2');
 addInsurance($row, '3');

 // Primary Care Physician Section.
 //
if ($row['providerID']) {
    $query = "select id, fname, mname, lname from users where authorized = 1";
    $query .= " AND id = ?";
    $prow = sqlFetchArray(sqlStatement($query, [$row['providerID']]));
    OpenTag("pcp");
    custom_xml_Add("id", $prow['id']);
    custom_xml_Add("lname", $prow['lname']);
    custom_xml_Add("fname", $prow['fname']);
    custom_xml_Add("mname", $prow['mname']);
    CloseTag("pcp");
}

 // Employer Section.
 //
if (!empty($rowed['id'])) {
    OpenTag("employer");
    custom_xml_Add("name", $rowed['name']);
    custom_xml_Add("street", $rowed['street']);
    custom_xml_Add("zip", $rowed['postal_code']);
    custom_xml_Add("city", $rowed['city']);
    custom_xml_Add("state", $rowed['state']);
    custom_xml_Add("country", $rowed['country']);
    CloseTag("employer");
}

 // All done.
 CloseTag("patient");

 // header('Content-type: text/xml');
 // header('Content-Disposition: attachment; filename="pid' . $pid . '.xml"');
 // echo $out;
?>
<html>
<head>
<?php Header::setupHeader(); ?>
<title><?php echo xlt('Export Patient Demographics XML'); ?></title>
</head>
<body class="body_top">
  <div class="container">
     <div class="row">
        <div class="col-12">
           <div class="form-group"></div>
           <div class="form-group">
              <textarea name="export_data" class="form-control" rows="18" readonly><?php echo text($out) ?></textarea>
           </div>
           <div class="form-group">
              <div class="col-12 text-right">
                 <div class="btn-group" role="group">
                    <button type="button" class="btn btn-secondary btn-cancel" onclick="dlgclose()"><?php echo xlt("Close"); ?></button>
                 </div>
              </div>
           </div>
        </div>
     </div>
  </div>

</body>
</html>
