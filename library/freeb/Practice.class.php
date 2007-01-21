<?php
require_once("DataObjectBase.class.php");
require_once("xmlrpc.inc");

class Practice Extends DataObjectBase {

        function Practice() {
                $this->_addFunc("name",                                 array( "name"   =>      "FreeB.FBPractice.Name",
                                                                               "sig"    =>      array(XMLRPCSTRING,XMLRPCINT),
                                                                               "doc"    =>      ""));
                $this->_addFunc("streetaddress",                array(  "name" =>       "FreeB.FBPractice.StreetAddress",
                                                                               "sig"    =>      array(XMLRPCSTRING,XMLRPCINT),
                                                                               "doc"    =>      ""));
                $this->_addFunc("city",                                 array( "name"   =>      "FreeB.FBPractice.City",
                                                                               "sig"    =>      array(XMLRPCSTRING,XMLRPCINT),
                                                                               "doc"    =>      ""));
                $this->_addFunc("state",                                array( "name"   =>      "FreeB.FBPractice.State",
                                                                               "sig"    =>      array(XMLRPCSTRING,XMLRPCINT),
                                                                               "doc"    =>      ""));
                $this->_addFunc("zipcode",                              array( "name"   =>      "FreeB.FBPractice.Zipcode",
                                                                               "sig"    =>      array(XMLRPCSTRING,XMLRPCINT),
                                                                               "doc"    =>      ""));
                $this->_addFunc("phonecountry",                 array(  "name" =>       "FreeB.FBPractice.PhoneCountry",
                                                                               "sig"    =>      array(XMLRPCSTRING,XMLRPCINT),
                                                                               "doc"    =>      ""));
                $this->_addFunc("phoneextension",               array(  "name" =>       "FreeB.FBPractice.PhoneExtension",
                                                                               "sig"    =>      array(XMLRPCSTRING,XMLRPCINT),
                                                                               "doc"    =>      ""));
                $this->_addFunc("phonearea",                    array(  "name" =>       "FreeB.FBPractice.PhoneArea",
                                                                               "sig"    =>      array(XMLRPCSTRING,XMLRPCINT),
                                                                               "doc"    =>      ""));
                $this->_addFunc("phonenumber",                  array(  "name" =>       "FreeB.FBPractice.PhoneNumber",
                                                                               "sig"    =>      array(XMLRPCSTRING,XMLRPCINT),
                                                                               "doc"    =>      ""));
                $this->_addFunc("isacceptsassignment",  array(  "name"  =>     "FreeB.FBPractice.isAcceptsAssignment",
                                                                               "sig"    =>      array(XMLRPCSTRING,XMLRPCINT),
                                                                               "doc"    =>      ""));
                $this->_addFunc("tin",                  array(  "name"  =>     "FreeB.FBPractice.TIN",
                                                                               "sig"    =>      array(XMLRPCSTRING,XMLRPCINT,XMLRPCINT,XMLRPCINT),
                                                                               "doc"    =>      ""));
                $this->_addFunc("npi",                  array(  "name"  =>     "FreeB.FBPractice.NPI",
                                                                               "sig"    =>      array(XMLRPCSTRING,XMLRPCINT,XMLRPCINT,XMLRPCINT),
                                                                               "doc"    =>      ""));
                $this->_addFunc("practiceid",                   array(  "name" =>       "FreeB.FBPractice.PracticeID",
                                                                               "sig"    =>      array(XMLRPCSTRING,XMLRPCINT,XMLRPCINT,XMLRPCINT),
                                                                               "doc"    =>      ""));
                $this->_addFunc("renderingpracticeid",                  array( "name"   =>      "FreeB.FBPractice.RenderingPracticeID",
                                                                               "sig"    =>      array(XMLRPCSTRING,XMLRPCINT,XMLRPCINT,XMLRPCINT),
                                                                               "doc"    =>      ""));
                $this->_addFunc("groupid",                              array( "name"   =>      "FreeB.FBPractice.GroupID",
                                                                               "sig"    =>      array(XMLRPCSTRING,XMLRPCINT,XMLRPCINT,XMLRPCINT),
                                                                               "doc"    =>      ""));
                $this->_addFunc("providernumx12type",                   array( "name"   =>      "FreeB.FBPractice.ProviderNumX12Type",
                                                                               "sig"    =>      array(XMLRPCSTRING,XMLRPCINT,XMLRPCINT,XMLRPCINT),
                                                                               "doc"    =>      ""));
                $this->_addFunc("renderingnumx12type",                  array( "name"   =>      "FreeB.FBPractice.RenderingNumX12Type",
                                                                               "sig"    =>      array(XMLRPCSTRING,XMLRPCINT,XMLRPCINT,XMLRPCINT),
                                                                               "doc"    =>      ""));
                $this->_addFunc("x12id",                                array( "name"   =>      "FreeB.FBPractice.X12Id",
                                                                               "sig"    =>      array(XMLRPCSTRING,XMLRPCINT,XMLRPCINT),
                                                                               "doc"    =>      ""));

        }


        function name($m) {

                $err="";

                $obj= $m->getparam(0);
                $key = $obj->getval();

                $sql = "SELECT * FROM facility where billing_location = '1'";
                //echo $sql;
                $db = $GLOBALS['adodb']['db'];
                $results = $db->Execute($sql);

                if (!$results) {
                        $err = $db->ErrorMsg();
                }
                else {
                        if (!$results->EOF) {
                                $retval =       $results->fields['name'];
                        }
                }

                // if we generated an error, create an error return response
                if ($err) {
                        return $this->_handleError($err);
                }
                else {
                        // otherwise, we create the right response
                        // with the state name
                        return new xmlrpcresp(new xmlrpcval($retval,""));
                }
        }

        function streetaddress($m) {

                $err="";

                $obj= $m->getparam(0);
                $key = $obj->getval();

                $sql = "SELECT * FROM facility where billing_location = '1'";
                //echo $sql;
                $db = $GLOBALS['adodb']['db'];
                $results = $db->Execute($sql);

                if (!$results) {
                        $err = $db->ErrorMsg();
                }
                else {
                        if (!$results->EOF) {
                                $retval =       $results->fields['street'];
                        }
                }

                // if we generated an error, create an error return response
                if ($err) {
                        return $this->_handleError($err);
                }
                else {
                        // otherwise, we create the right response
                        // with the state name
                        return new xmlrpcresp(new xmlrpcval($retval,"string"));
                }
        }


        function city($m) {

                $err="";

                $obj= $m->getparam(0);
                $key = $obj->getval();

                $sql = "SELECT * FROM facility where billing_location = '1'";
                //echo $sql;
                $db = $GLOBALS['adodb']['db'];
                $results = $db->Execute($sql);

                if (!$results) {
                        $err = $db->ErrorMsg();
                }
                else {
                        if (!$results->EOF) {
                                $retval =       $results->fields['city'];
                        }
                }

                // if we generated an error, create an error return response
                if ($err) {
                        return $this->_handleError($err);
                }
                else {
                        // otherwise, we create the right response
                        // with the state name
                        return new xmlrpcresp(new xmlrpcval($retval,"string"));
                }
        }

        function state($m) {

                $err="";

                $obj= $m->getparam(0);
                $key = $obj->getval();

                $sql = "SELECT * FROM facility where billing_location = '1'";
                //echo $sql;
                $db = $GLOBALS['adodb']['db'];
                $results = $db->Execute($sql);

                if (!$results) {
                        $err = $db->ErrorMsg();
                }
                else {
                        if (!$results->EOF) {
                                $retval =       $results->fields['state'];
                        }
                }

                // if we generated an error, create an error return response
                if ($err) {
                        return $this->_handleError($err);
                }
                else {
                        // otherwise, we create the right response
                        // with the state name
                        return new xmlrpcresp(new xmlrpcval($retval,"string"));
                }
        }

        function zipcode($m) {

                $err="";

                $obj= $m->getparam(0);
                $key = $obj->getval();

                $sql = "SELECT * FROM facility where billing_location = '1'";
                //echo $sql;
                $db = $GLOBALS['adodb']['db'];
                $results = $db->Execute($sql);

                if (!$results) {
                        $err = $db->ErrorMsg();
                }
                else {
                        if (!$results->EOF) {
                                $retval =       $results->fields['postal_code'];
                        }
                }

                // if we generated an error, create an error return response
                if ($err) {
                        return $this->_handleError($err);
                }
                else {
                        // otherwise, we create the right response
                        // with the state name
                        return new xmlrpcresp(new xmlrpcval($retval,"string"));
                }
        }
        function phonecountry($m) {

                $err="";

                $pkey = "1";

                // if we generated an error, create an error return response
                if ($err) {
                        return $this->_handleError($err);
                }
                else {
                        // otherwise, we create the right response
                        // with the state name
                        return new xmlrpcresp(new xmlrpcval($pkey));
                }
        }

        function phoneextension($m) {

                $err="";

                //unimplemented by OpenEMR
                $pkey = "";

                // if we generated an error, create an error return response
                if ($err) {
                        return $this->_handleError($err);
                }
                else {
                        // otherwise, we create the right response
                        // with the state name
                        return new xmlrpcresp(new xmlrpcval($pkey));
                }
        }

        function phonearea($m) {

                $err="";

                $obj= $m->getparam(0);
                $key = $obj->getval();

                $sql = "SELECT * FROM facility where billing_location = '1'";
                //echo $sql;
                $db = $GLOBALS['adodb']['db'];
                $results = $db->Execute($sql);

                if (!$results) {
                        $err = $db->ErrorMsg();
                }
                else {
                        if (!$results->EOF) {
                                $retval =       $results->fields['phone'];
                        }
                }

                $phone_parts = array();
//      preg_match("/^\((.*?)\)\s(.*?)\-(.*?)$/",$retval,$phone_parts);
                preg_match("/(\d\d\d)\D*(\d\d\d)\D*(\d\d\d\d)/",$retval,$phone_parts);
                $retval = $phone_parts[1];

                // if we generated an error, create an error return response
                if ($err) {
                        return $this->_handleError($err);
                }
                else {
                        // otherwise, we create the right response
                        // with the state name
                        return new xmlrpcresp(new xmlrpcval($retval,"string"));
                }
        }

        function phonenumber($m) {

                $err="";

                $obj= $m->getparam(0);
                $key = $obj->getval();

                $sql = "SELECT * FROM facility where billing_location = '1'";
                //echo $sql;
                $db = $GLOBALS['adodb']['db'];
                $results = $db->Execute($sql);

                if (!$results) {
                        $err = $db->ErrorMsg();
                }
                else {
                        if (!$results->EOF) {
                                $retval =       $results->fields['phone'];
                        }
                }

                $phone_parts = array();
//      preg_match("/^\((.*?)\)\s(.*?)\-(.*?)$/",$retval,$phone_parts);
                preg_match("/(\d\d\d)\D*(\d\d\d)\D*(\d\d\d\d)/",$retval,$phone_parts);
                $retval = $phone_parts[2] . "-" . $phone_parts[3];

                // if we generated an error, create an error return response
                if ($err) {
                        return $this->_handleError($err);
                }
                else {
                        // otherwise, we create the right response
                        // with the state name
                        return new xmlrpcresp(new xmlrpcval($retval,"string"));
                }
        }

        function isacceptsassignment($m) {

                $err="";

                $obj= $m->getparam(0);
                $key = $obj->getval();

                $sql = "SELECT * FROM facility where billing_location = '1'";
                //echo $sql;
                $db = $GLOBALS['adodb']['db'];
                $results = $db->Execute($sql);

                if (!$results) {
                        $err = $db->ErrorMsg();
                }
                else {
                        if (!$results->EOF) {
                                $retval =       $results->fields['accepts_assignment'];
                        }
                }

                // if we generated an error, create an error return response
                if ($err) {
                        return $this->_handleError($err);
                }
                else {
                        // otherwise, we create the right response
                        // with the state name
                        return new xmlrpcresp(new xmlrpcval($retval,"i4"));
                }
        }

        /**
        * Returns a federal ein based on practice, payer, provider
        * @key int practice identifier used in database, in OpenEMR practices are facilities
        * @payerkey int database id of the insurance company
        * @providekey int database id of the provider
        */

        function tin($m) {

                $err="";

                $obj= $m->getparam(0);
                $key = $obj->getval();

                $obj= $m->getparam(1);
                $payerkey = $obj->getval();

                $obj= $m->getparam(2);
                $providerkey = $obj->getval();

                $sql = "SELECT * FROM facility where billing_location = 1";
                //echo $sql;
                $db = $GLOBALS['adodb']['db'];
                $results = $db->Execute($sql);

                $vals = array();
                if (!$results) {
                        $err = $db->ErrorMsg();
                }
                else {
                        if (!$results->EOF) {
                                $retval = $results->fields['federal_ein'];

                        }
                }

                // if we generated an error, create an error return response
                if ($err) {
                        return $this->_handleError($err);
                }
                else {
                        // otherwise, we create the right response
                        // with the state name
                        return new xmlrpcresp(new xmlrpcval($retval,"string"));
                }
        }


        function practiceid($m) {

                $err="";

                $obj= $m->getparam(0);
                $key = $obj->getval();

                $obj= $m->getparam(1);
                $payerkey = $obj->getval();

                $obj= $m->getparam(2);
                $providerkey = $obj->getval();

                //this query gets the exact match or resorts to the default when no exact is found (default has a NULL value for insurance_company_id)
                $sql = "SELECT * FROM insurance_numbers where (insurance_company_id = '$payerkey' OR insurance_company_id is NULL) AND provider_id = '$providerkey' order by insurance_company_id DESC";
                //echo $sql;
                $db = $GLOBALS['adodb']['db'];
                $results = $db->Execute($sql);

                $vals = array();
                if (!$results) {
                        $err = $db->ErrorMsg();
                }
                else {
                        while (!$results->EOF) {
                                $vals[] = $results->fields['provider_number'];
                                $results->MoveNext();
                        }
                }

                //if there is an exact match or not match and only the default then set it here
                if (!empty($vals[0])) {
                        $retval = $vals[0];
                }
                else {
                        //if the exact match was empty for a provider number reference the default which is 1 because the nifty query and sorting above
                        $retval = $vals[1];
                }

                // if we generated an error, create an error return response
                if ($err) {
                        return $this->_handleError($err);
                }
                else {
                        // otherwise, we create the right response
                        // with the state name
                        return new xmlrpcresp(new xmlrpcval($retval,"string"));
                }
        }

        function renderingpracticeid($m) {

                $err="";

                $obj= $m->getparam(0);
                $key = $obj->getval();

                $obj= $m->getparam(1);
                $payerkey = $obj->getval();

                $obj= $m->getparam(2);
                $providerkey = $obj->getval();

                //this query gets the exact match or resorts to the default when no exact is found (default has a NULL value for insurance_company_id)
                $sql = "SELECT * FROM insurance_numbers where (insurance_company_id = '$payerkey' OR insurance_company_id is NULL) AND provider_id = '$providerkey' order by insurance_company_id DESC";
                //echo $sql;
                $db = $GLOBALS['adodb']['db'];
                $results = $db->Execute($sql);

                $vals = array();
                if (!$results) {
                        $err = $db->ErrorMsg();
                }
                else {
                        while (!$results->EOF) {
                                $vals[] = $results->fields['rendering_provider_number'];
                                $results->MoveNext();
                        }
                }

                //if there is an exact match or not match and only the default then set it here
                if (!empty($vals[0])) {
                        $retval = $vals[0];
                }
                else {
                        //if the exact match was empty for a provider number reference the default which is 1 because the nifty query and sorting above
                        $retval = $vals[1];
                }

                // if we generated an error, create an error return response
                if ($err) {
                        return $this->_handleError($err);
                }
                else {
                        // otherwise, we create the right response
                        // with the state name
                        return new xmlrpcresp(new xmlrpcval($retval,"string"));
                }
        }

        function groupid($m) {

                $err="";

                $obj= $m->getparam(0);
                $key = $obj->getval();

                $obj= $m->getparam(1);
                $payerkey = $obj->getval();

                $obj= $m->getparam(2);
                $providerkey = $obj->getval();

                //this query gets the exact match or resorts to the default when no exact is found (default has a NULL value for insurance_company_id)
                $sql = "SELECT * FROM insurance_numbers where (insurance_company_id = '$payerkey' OR insurance_company_id is NULL) AND provider_id = '$providerkey' order by insurance_company_id DESC";
                //echo $sql;
                $db = $GLOBALS['adodb']['db'];
                $results = $db->Execute($sql);

                $vals = array();
                if (!$results) {
                        $err = $db->ErrorMsg();
                }
                else {
                        while (!$results->EOF) {
                                $vals[] = $results->fields['group_number'];
                                $results->MoveNext();
                        }
                }

                if (!empty($vals[0])) {
                        $retval = $vals[0];
                }
                else {
                        //if the exact match was empty for a group number reference the default which is 1 because the nifty query and sorting above
                        $retval = $vals[1];
                }


                // if we generated an error, create an error return response
                if ($err) {
                        return $this->_handleError($err);
                }
                else {
                        // otherwise, we create the right response
                        // with the state name
                        return new xmlrpcresp(new xmlrpcval($retval,"string"));
                }
        }

        function providernumx12type($m) {

                $err="";

                $obj= $m->getparam(0);
                $key = $obj->getval();

                $obj= $m->getparam(1);
                $payerkey = $obj->getval();

                $obj= $m->getparam(2);
                $providerkey = $obj->getval();

                //this query gets the exact match or resorts to the default when no exact is found (default has a NULL value for insurance_company_id)
                $sql = "SELECT * FROM insurance_numbers where (insurance_company_id = '$payerkey' OR insurance_company_id is NULL) AND provider_id = '$providerkey' order by insurance_company_id DESC";
                //echo $sql;
                $db = $GLOBALS['adodb']['db'];
                $results = $db->Execute($sql);

                $vals = array();
                if (!$results) {
                        $err = $db->ErrorMsg();
                }
                else {
                        while (!$results->EOF) {
                                $vals[] = $results->fields['provider_number_type'];
                                $results->MoveNext();
                        }
                }

                //if there is an exact match or not match and only the default then set it here
                if (!empty($vals[0])) {
                        $retval = $vals[0];
                }
                else {
                        //if the exact match was empty for a provider number reference the default which is 1 because the nifty query and sorting above
                        $retval = $vals[1];
                }

                // if we generated an error, create an error return response
                if ($err) {
                        return $this->_handleError($err);
                }
                else {
                        // otherwise, we create the right response
                        // with the state name
                        return new xmlrpcresp(new xmlrpcval($retval));
                }
        }

        function renderingnumx12type($m) {

                $err="";

                $obj= $m->getparam(0);
                $key = $obj->getval();

                $obj= $m->getparam(1);
                $payerkey = $obj->getval();

                $obj= $m->getparam(2);
                $providerkey = $obj->getval();

                //this query gets the exact match or resorts to the default when no exact is found (default has a NULL value for insurance_company_id)
                $sql = "SELECT * FROM insurance_numbers where (insurance_company_id = '$payerkey' OR insurance_company_id is NULL) AND provider_id = '$providerkey' order by insurance_company_id DESC";
                //echo $sql;
                $db = $GLOBALS['adodb']['db'];
                $results = $db->Execute($sql);

                $vals = array();
                if (!$results) {
                        $err = $db->ErrorMsg();
                }
                else {
                        while (!$results->EOF) {
                                $vals[] = $results->fields['rendering_provider_number_type'];
                                $results->MoveNext();
                        }
                }

                //if there is an exact match or not match and only the default then set it here
                if (!empty($vals[0])) {
                        $retval = $vals[0];
                }
                else {
                        //if the exact match was empty for a provider number reference the default which is 1 because the nifty query and sorting above
                        $retval = $vals[1];
                }

                // if we generated an error, create an error return response
                if ($err) {
                        return $this->_handleError($err);
                }
                else {
                        // otherwise, we create the right response
                        // with the state name
                        return new xmlrpcresp(new xmlrpcval($retval));
                }
        }

        function x12id($m) {

                $err="";


                $obj= $m->getparam(0);
                $key = $obj->getval();

                $sql = "SELECT * FROM facility where billing_location = 1";
                //echo $sql;
                $db = $GLOBALS['adodb']['db'];
                $results = $db->Execute($sql);

                $vals = array();
                if (!$results) {
                        $err = $db->ErrorMsg();
                }
                else {
                        if (!$results->EOF) {
                                $retval = $results->fields['federal_ein'];

                        }
                }

                // if we generated an error, create an error return response
                if ($err) {
                        return $this->_handleError($err);
                }
                else {
                        // otherwise, we create the right response
                        // with the state name
                        return new xmlrpcresp(new xmlrpcval($retval));
                }
        }

        function npi($m) {

                $err="";

                $obj= $m->getparam(0);
                $key = $obj->getval();

                $obj= $m->getparam(1);
                $payerkey = $obj->getval();

                $obj= $m->getparam(2);
                $providerkey = $obj->getval();

                $sql = "SELECT * FROM facility where billing_location = 1";
                //echo $sql;
                $db = $GLOBALS['adodb']['db'];
                $results = $db->Execute($sql);

                $vals = array();
                if (!$results) {
                        $err = $db->ErrorMsg();
                }
                else {
                        if (!$results->EOF) {
                                $retval = $results->fields['facility_npi'];

                        }
                }

                // if we generated an error, create an error return response
                if ($err) {
                        return $this->_handleError($err);
                }
                else {
                        // otherwise, we create the right response
                        // with the state name
                        return new xmlrpcresp(new xmlrpcval($retval,"string"));
                }
        }


}


//'FreeB.FBPractice.Name'                       => \&FreeB_FBPractice_Name,
//'FreeB.FBPractice.StreetAddress'              => \&FreeB_FBPractice_StreetAddress,
//'FreeB.FBPractice.City'                       => \&FreeB_FBPractice_City,
//'FreeB.FBPractice.State'                      => \&FreeB_FBPractice_State,
//'FreeB.FBPractice.Zipcode'                    => \&FreeB_FBPractice_Zipcode,
//'FreeB.FBPractice.PhoneCountry'               => \&FreeB_FBPractice_PhoneCountry,
//'FreeB.FBPractice.PhoneExtension'             => \&FreeB_FBPractice_PhoneExtension,
//'FreeB.FBPractice.PhoneNumber'                        => \&FreeB_FBPractice_PhoneNumber,
//'FreeB.FBPractice.PhoneArea'                  => \&FreeB_FBPractice_PhoneArea,
//'FreeB.FBPractice.isAcceptsAssignment'                => \&FreeB_FBPractice_isAcceptsAssignment,
//'FreeB.FBPractice.PracticeID'                         => \&FreeB_FBPractice_PracticeID,
//'FreeB.FBPractice.RenderingPracticeID'                => \&FreeB_FBPractice_RenderingPracticeID,
//'FreeB.FBPractice.GroupID'                    => \&FreeB_FBPractice_GroupID,
//'FreeB.FBPractice.TIN'                                => \&FreeB_FBPractice_TIN,
//'FreeB.FBPractice.NPI'                                => \&FreeB_FBPractice_NPI,
//'FreeB.FBPractice.ProviderNumX12Type'                         => \&FreeB_FBPractice_ProviderNumX12Type,
//'FreeB.FBPractice.RenderingNumX12Type'                        => \&FreeB_FBPractice_RenderingNumX12Type,
//'FreeB.FBPractice.X12Id'                      => \&FreeB_FBPractice_X12Id,



?>
