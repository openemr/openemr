<?php

/**
 * class X12Partner
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Daniel Pflieger <daniel@mi-squared.com>, <daniel@growlingflea.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Daniel Pflieger <daniel@mi-squared.com>, <daniel@growlingflea.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\ORDataObject\ORDataObject;

class X12Partner extends ORDataObject
{
    public $name;
    public $x12_submitter_id;
    public $x12_submitter_name;
    public $x12_submitter_array;
    public $id_number;
    public $x12_isa01; //
    public $x12_isa02; //
    public $x12_isa03; //
    public $x12_isa04; //
    public $x12_isa05; // Sender Interchange ID Qualifier. ZZ = mutually defined, 01 = Duns, etc.
    public $x12_sender_id;   // ISA06
    public $x12_isa07; // Receiver Interchange ID Qualifier.
    public $x12_receiver_id; // ISA08
    public $x12_isa14; // Acknowledgment Requested. 0 = No, 1 = Yes.
    public $x12_isa15; // Usage Indicator. T = testing, P = production.
    public $x12_gs02;  // Application Sender's Code. Default to ISA06.
    public $x12_dtp03; // 270 2100C DTP03 service date for eligibility.
    public $x12_per06; // The submitter's EDI Access Number, if any.
    public $x12_version;
    public $processing_format;
    public $processing_format_array;
    public $x12_gs03; // Application Sender's Code. If this isn't set then we will use the $x12_receiver_id(ISA08).

    //for submitting claims via sftp
    public $x12_sftp_login;
    public $x12_sftp_pass;
    public $x12_sftp_host;
    public $x12_sftp_port;
    public $x12_sftp_local_dir;
    public $x12_sftp_remote_dir;
    public $x12_client_id;
    public $x12_client_secret;
    public $x12_token_endpoint;
    public $x12_eligibility_endpoint;
    public $x12_claim_status_endpoint;
    public $x12_attachment_endpoint;

    /**
     * Constructor sets all Insurance attributes to their default value
     */

    function __construct(public $id = "", $prefix = "")
    {
        parent::__construct();
        $this->_table = "x12_partners";
        $this->processing_format_array = $this->_load_enum("processing_format", false);
        $this->processing_format = $this->processing_format_array[0] ?? null;
        //most recent x12 version mandated by HIPAA and CMS
        // $this->x12_version = "004010X098A1";
        $this->x12_version = "005010X222A1";
        $this->x12_isa05 = "ZZ";
        $this->x12_isa07 = "ZZ";
        $this->x12_isa14 = "0";
        $this->x12_dtp03 = "A";
        if ($this->id != "") {
            $this->populate();
        }
    }

    function x12_partner_factory()
    {
        $partners = [];
        $x = new X12Partner();
        $sql = "SELECT id FROM "  . $x->_table . " order by name";
        $result = $x->_db->Execute($sql);
        while ($result && !$result->EOF) {
            $partners[] = new X12Partner($result->fields['id']);
            $result->MoveNext();
        }

        return $partners;
    }

    function get_id()
    {
        return $this->id;
    }

    function set_id($id)
    {
        if (is_numeric($id)) {
            $this->id = $id;
        }
    }

    function get_name()
    {
        return $this->name;
    }

    function get_x12_submitter_array()
    {
        $query = "SELECT id, organization FROM users WHERE abook_type = 'bill_svc'";
        $res = sqlStatement($query);
        $x12_submitter_array[0] = null;
        while ($row = sqlFetchArray($res)) {
            $x12_submitter_array[$row['id']] = $row['organization'];
        }

        return  $x12_submitter_array;
    }

    function set_x12_submitter_id($id)
    {
        $this->x12_submitter_id = $id;
    }

    function get_x12_submitter_id()
    {
        return $this->x12_submitter_id;
    }

    function get_x12_submitter_name()
    {
        $xa = $this->get_x12_submitter_array();
        return $xa[$this->get_x12_submitter_id()] ?? null;
    }

    /**
     * SFTP credentials for direct submit to x-12 partners.
     *
     * @param $string
     */
    function set_x12_sftp_login($string)
    {
        $this->x12_sftp_login = $string;
    }

    function get_x12_sftp_login()
    {
        return $this->x12_sftp_login;
    }

    function set_x12_sftp_pass($string)
    {
        $this->x12_sftp_pass = $string;
    }

    function get_x12_sftp_pass()
    {
        return $this->x12_sftp_pass;
    }

    function set_x12_sftp_host($string)
    {
        $this->x12_sftp_host = $string;
    }

    function get_x12_sftp_host()
    {
        return $this->x12_sftp_host;
    }

    function set_x12_sftp_port($string)
    {
        $this->x12_sftp_port = $string;
    }

    function get_x12_sftp_port()
    {
        return $this->x12_sftp_port;
    }

    function set_x12_sftp_local_dir($string)
    {
        $this->x12_sftp_local_dir = $string;
    }

    function get_x12_sftp_local_dir()
    {
        return $this->x12_sftp_local_dir;
    }

    function set_x12_sftp_remote_dir($string)
    {
        $this->x12_sftp_remote_dir = $string;
    }

    function get_x12_sftp_remote_dir()
    {
        return $this->x12_sftp_remote_dir;
    }

    function set_name($string)
    {
            $this->name = $string;
    }

    function get_id_number()
    {
        return $this->id_number;
    }

    function set_id_number($string)
    {
            $this->id_number = $string;
    }

    function get_x12_sender_id()
    {
        return $this->x12_sender_id;
    }

    function set_x12_sender_id($string)
    {
        $this->x12_sender_id = $string;
    }

    function get_x12_receiver_id()
    {
        return $this->x12_receiver_id;
    }

    function set_x12_receiver_id($string)
    {
        $this->x12_receiver_id = $string;
    }

    function get_x12_version()
    {
        return $this->x12_version;
    }

    function set_x12_version($string)
    {
        $this->x12_version = $string;
    }

    function get_x12_isa01()
    {
        return $this->x12_isa01;
    }

    function set_x12_isa01($string)
    {
        $this->x12_isa01 = $string;
    }

    function get_x12_isa02()
    {
        return $this->x12_isa02;
    }

    function set_x12_isa02($string)
    {
        $this->x12_isa02 = str_pad((string) $string, 10);
    }

    function get_x12_isa03()
    {
        return $this->x12_isa03;
    }

    function set_x12_isa03($string)
    {
        $this->x12_isa03 = $string;
    }

    function get_x12_isa04()
    {
        return $this->x12_isa04;
    }

    function set_x12_isa04($string)
    {
        $this->x12_isa04 = str_pad((string) $string, 10);
    }

    function get_x12_isa05()
    {
        return $this->x12_isa05;
    }

    function set_x12_isa05($string)
    {
        $this->x12_isa05 = $string;
    }

    function get_x12_isa07()
    {
        return $this->x12_isa07;
    }

    function set_x12_isa07($string)
    {
        $this->x12_isa07 = $string;
    }

    function get_x12_isa14()
    {
        return $this->x12_isa14;
    }

    function set_x12_isa14($string)
    {
        $this->x12_isa14 = $string;
    }

    function get_x12_isa15()
    {
        return $this->x12_isa15;
    }

    function set_x12_isa15($string)
    {
        $this->x12_isa15 = $string;
    }

    function get_x12_gs02()
    {
        return $this->x12_gs02;
    }

    function set_x12_gs02($string)
    {
        $this->x12_gs02 = $string;
    }

    function get_x12_dtp03()
    {
        return $this->x12_dtp03;
    }

    function set_x12_dtp03($string)
    {
        $this->x12_dtp03 = $string;
    }

    function get_x12_per06()
    {
        return $this->x12_per06;
    }

    function set_x12_per06($string)
    {
        $this->x12_per06 = $string;
    }

    function get_processing_format()
    {
        //this is enum so it can be string or int
        if (!is_numeric($this->processing_format)) {
            $ta = $this->processing_format_array;
            return ($ta[$this->processing_format] ?? null);
        }

        return $this->processing_format;
    }

    function get_processing_format_array()
    {
        //flip it because normally it is an id to name lookup, for templates it needs to be a name to id lookup
        return array_flip($this->processing_format_array);
    }

    function set_processing_format($string)
    {
        $this->processing_format = $string;
    }

    function get_x12_gs03()
    {
        return $this->x12_gs03;
    }

    function set_x12_gs03($string)
    {
        $this->x12_gs03 = $string;
    }

    function get_x12_isa14_array()
    {
        return [
        '0' => 'No',
        '1' => 'Yes',
        ];
    }

    function get_x12_isa15_array()
    {
        return [
        'T' => 'Testing',
        'P' => 'Production',
        ];
    }

    function get_idqual_array()
    {
        return [
        '01' => 'Duns (Dun & Bradstreet)',
        '14' => 'Duns Plus Suffix',
        '20' => 'Health Industry Number (HIN)',
        '27' => 'Carrier ID from HCFA',
        '28' => 'Fiscal Intermediary ID from HCFA',
        '29' => 'Medicare ID from HCFA',
        '30' => 'U.S. Federal Tax ID Number',
        '33' => 'NAIC Company Code',
        'ZZ' => 'Mutually Defined',
        ];
    }

    function get_x12_version_array()
    {
        return [
        '005010X222A1' => '005010X222A1',
        '004010X098A1' => '004010X098A1',
        ];
    }

    function get_x12_dtp03_type_array()
    {
        return [
            'C' => 'Current Date',
            'A' => 'Appointment Date',
            'E' => 'Subscriber Effective Date',
        ];
    }

    function set_x12_client_id($string)
    {
        $this->x12_client_id = $string;
    }

    function get_x12_client_id()
    {
        return $this->x12_client_id;
    }

    function set_x12_client_secret($string)
    {
        $this->x12_client_secret = $string;
    }

    function get_x12_client_secret()
    {
        return $this->x12_client_secret;
    }

    function set_x12_token_endpoint($string)
    {
        $this->x12_token_endpoint = $string;
    }

    function get_x12_token_endpoint()
    {
        return $this->x12_token_endpoint;
    }

    function set_x12_eligibility_endpoint($string)
    {
        $this->x12_eligibility_endpoint = $string;
    }

    function get_x12_eligibility_endpoint()
    {
        return $this->x12_eligibility_endpoint;
    }

    function set_x12_claim_status_endpoint($string)
    {
        $this->x12_claim_status_endpoint = $string;
    }

    function get_x12_claim_status_endpoint()
    {
        return $this->x12_claim_status_endpoint;
    }

    function set_x12_attachment_endpoint($string)
    {
        $this->x12_attachment_endpoint = $string;
    }

    function get_x12_attachment_endpoint()
    {
        return $this->x12_attachment_endpoint;
    }
}
