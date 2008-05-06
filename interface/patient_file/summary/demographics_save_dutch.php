<?
 include_once("../../globals.php");
 include_once("$srcdir/patient.inc");
 include_once("$srcdir/acl.inc");

 // Check authorization.
 $thisauth = acl_check('patients', 'demo');
 if ($pid) {
  if ($thisauth != 'write')
   die("Updating demographics is not authorized.");
  $tmp = getPatientData($pid, "squad");
  if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
   die("You are not authorized to access this squad.");
 } else {
  if ($thisauth != 'write' && $thisauth != 'addonly')
   die("Adding demographics is not authorized.");
 }

foreach ($_POST as $key => $val) {
  if ($val == "MM/DD/YYYY") {
    $_POST[$key] = "";
  }
}

// prepare the sex value for the table
switch ( $_POST["df_sex"]) {
    case 0: $var_sex = ''; break;
    case 1: $var_sex = 'Male'; break;
    case 2: $var_sex = 'Female'; break;
    default: $var_sex = '';
}

// prepare the voice and email dropdown value
$dfvoice = ( $_POST['df_allowvoice'] ) ? xl('Yes') : xl('No');
$dfemail = ( $_POST['df_allowemail'] ) ? xl('Yes') : xl('No');


$provider_data = array (
    'pro_referer'   => $_POST['df_pcp_rfr'],
    'pro_company'   => $_POST['df_pcp_company'],
    'pro_initials'  => $_POST['df_pcp_initials'],
    'pro_prefix'    => $_POST['df_pcp_prefix'],
    'pro_lname'     => $_POST['df_pcp_lname'],
    'pro_street'    => $_POST['df_pcp_street'],
    'pro_number'    => $_POST['df_pcp_number'],
    'pro_addition'  => $_POST['df_pcp_addition'],
    'pro_city'      => $_POST['df_pcp_city'],
    'pro_zipcode'   => $_POST['df_pcp_zipcode'],
    'pro_phone'     => $_POST['df_pcp_phone'],
    'pro_fax'       => $_POST['df_pcp_fax'],
    'pro_email'     => $_POST['df_pcp_email']
    );
if ( $_POST['df_pcp_rfr'] == '1' ) {
    // the referer is the provider itself
    $provider_data['pro_referer'] = 1;
} else {
    $code = ( $_POST['df_rfr_type'] ) ? $_POST['df_rfr_type'] : $_POST['df_rfr_code'];
    $referer_data = array(
        'ref_code'      => $code,
        'ref_company'   => $_POST['df_rfr_company'],
        'ref_initials'  => $_POST['df_rfr_initials'],
        'ref_prefix'    => $_POST['df_rfr_prefix'],
        'ref_lname'     => $_POST['df_rfr_lname'],
        'ref_street'    => $_POST['df_rfr_street'],
        'ref_number'    => $_POST['df_rfr_number'],
        'ref_addition'  => $_POST['df_rfr_addition'],
        'ref_city'      => $_POST['df_rfr_city'],
        'ref_zipcode'   => $_POST['df_rfr_zipcode'],
        'ref_phone'     => $_POST['df_rfr_phone'],
        'ref_fax'       => $_POST['df_rfr_fax'],
        'ref_email'     => $_POST['df_rfr_email']
    );
}

// filtreat the variables
$obf = new Filtreatment();
foreach ( $provider_data as $pd => $pdv) {
    $provider_data_clean[$pd] = $obf->ft_dbsql($pdv);
}

foreach ( $referer_data as $rf => $rfv) {
    $referer_data_clean[$rf] = $obf->ft_dbsql($rfv);
}

$finrev = fixDate($_POST["financial_review"]);

$nstreet    = ( isset($_POST['df_straat']) ) ? $_POST['df_straat'] : '' ;
$nnr        = ( isset($_POST['df_nummer']) ) ? $_POST['df_nummer'] : '' ;
$nadd       = ( isset($_POST['df_toevoe']) ) ? $_POST['df_toevoe'] : '' ;

$street = $nstreet .' '. $nnr .' '. $nadd;

$nstreet_clean  = $obf->ft_dbsql($nstreet, 'SQL');
$nnr_clean      = $obf->ft_integer($nnr);
$nadd_clean     = $obf->ft_dbsql($nadd, 'SQL');
$street_clean   = $obf->ft_dbsql($street);

$initials_clean = $obf->ft_dbsql($_POST['df_voorletters']); 
$achternaam     = $obf->ft_dbsql($_POST['df_achternaam']);
$roepnaam       = $obf->ft_dbsql($_POST['df_roepnaam']);
$dob            = $obf->ft_validdate($_POST['df_geboorte']);

$postal_clean   = $obf->ft_dbsql($_POST['df_postal']);
$plaats_clean   = $obf->ft_dbsql($_POST['df_plaats']);

$insurance_clean= (int)$_POST['df_insurance'];
$policy_clean   = $obf->ft_dbsql($_POST['df_policy']);
$insdate_clean  = $obf->ft_validdate($_POST['df_insdatum']);

// ================================================
//                VALIDATION STUFF

$valid = TRUE;

// required fields
$valid = ( !empty($nstreet_clean) && !empty($nnr_clean) && !empty($initials_clean) && !empty($postal_clean) && !empty($plaats_clean));

if ( !$valid ) {
    $_SESSION['errormsg'] = 'Eén of meer verplichte velden zijn niet ingevuld!<br />';
    reload_form();
}

// date of birth validation
if ( !$dob ) {
    $_SESSION['errormsg'] = 'Date must be in the form YYYY-MM-DD and must be a valid date!<br />';
    reload_form(); 
}

// if we have an insurer, we must have also a policy number
// insurance = 1 means UNINSURED so we don't need the policy
if ( $insurance_clean ) {
    if ( ($insurance_clean != 1) && (!$policy_clean) ) {
        $_SESSION['errormsg']  = 'The policy number is mandatory for a choosen insurer!<br />';
    }
    if ( !$insdate_clean ) {
        $_SESSION['errormsg'] .= 'Date must be in the form YYYY-MM-DD and must be a valid date!<br />';
    }
    if ( $_SESSION['errormsg'] ) reload_form(); 
}

// ================================================

// call the functions to save the infos
newPatientData(
  $_SESSION['pid'],
  '', // title
  $roepnaam, // first name
  $achternaam, // last name
  '', // middle name
  $var_sex,
  $dob,
  $street_clean,  //initialy $_POST["street"], DBC Addition
  $nstreet_clean,
  $nnr_clean,
  $nadd_clean, // EOS DBC
  $postal_clean,
  $plaats_clean,
  '', // state
  $_POST["df_land"],
  $_POST["df_burgerservicenummer"],
  '', //$_POST["occupation"],
  $_POST["df_telh"],
  $_POST["df_telw"],
  $_POST["df_emtel"],
  $_POST["df_huwe"],
  $_POST["df_emcon"],
  '', //$_POST["referrer"],
  '', //$_POST["referrerID"],
  $_POST["df_emailcon"],
  strtolower($_POST["language"]),
  '', //$_POST["ethnoracial"],
  '', //$_POST["interpretter"],
  '', //$_POST["migrantseasonal"],
  '', //$_POST["family_size"],
  '', //$_POST["monthly_income"],
  '', //$_POST["homeless"],
  '', //$finrev,
  $pid, // pubpid is the same with the pid for us
  $pid,
  '', //$_POST["providerID"],
  '', //$_POST["genericname1"],
  '', //$_POST["genericval1"],
  '', //$_POST["genericname2"],
  '', //$_POST["genericval2"],
  $_POST["df_telm"],
  $dfemail,
  $dfvoice,
  '', //$_POST["squad"],
  '', //$_POST["pharmacy_id"],
  $_POST["df_licid"],
  '', //$_POST["hipaa_notice"],
  '', //$_POST["hipaa_message"],
  $_POST["df_allowmsg"], // $regdate
  $_POST["df_voorvoegsel"],
  $_POST["df_par_voorvoegsel"],
  $_POST["df_par_achternaam"],
  $initials_clean,
  $provider_data_clean,
  $referer_data_clean
);


$i1dob = fixDate($_POST["i1subscriber_DOB"]);

// insurance stuff
if ( $insurance_clean ) {
    set_insurer_nl($_SESSION['pid'], $insurance_clean, $insdate_clean, $policy_clean);
}

$i2dob = fixDate($_POST["i2subscriber_DOB"]);

if ($GLOBALS['concurrent_layout']) {
 include_once("demographics.php");
} else {
 include_once("patient_summary.php");
}

/**
RELOAD THE FORM

*/
function reload_form() {
    header('location:demographics_full_dutch.php');
}
?>
