<?php

/* +-----------------------------------------------------------------------------+
* Copyright 2016 matrix israel
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 3
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program. If not, see
* http://www.gnu.org/licenses/licenses.html#GPL
*    @author  Dror Golan <drorgo@matrix.co.il>
* +------------------------------------------------------------------------------+
 *
 */

namespace Patientvalidation\Model;

use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;

class PatientData implements InputFilterAwareInterface
{
    /*
'id', 'bigint(20)', 'NO', 'MUL', NULL, 'auto_increment'
'title', 'varchar(255)', 'NO', '', '', ''
'language', 'varchar(255)', 'NO', '', '', ''
'financial', 'varchar(255)', 'NO', '', '', ''
'fname', 'varchar(255)', 'NO', '', '', ''
'lname', 'varchar(255)', 'NO', '', '', ''
'mname', 'varchar(255)', 'NO', '', '', ''
'DOB', 'date', 'YES', '', NULL, ''
'street', 'varchar(255)', 'NO', '', '', ''
'postal_code', 'varchar(255)', 'NO', '', '', ''
'city', 'varchar(255)', 'NO', '', '', ''
'state', 'varchar(255)', 'NO', '', '', ''
'country_code', 'varchar(255)', 'NO', '', '', ''
'drivers_license', 'varchar(255)', 'NO', '', '', ''
'ss', 'varchar(255)', 'NO', '', '', ''
'occupation', 'longtext', 'YES', '', NULL, ''
'phone_home', 'varchar(255)', 'NO', '', '', ''
'phone_biz', 'varchar(255)', 'NO', '', '', ''
'phone_contact', 'varchar(255)', 'NO', '', '', ''
'phone_cell', 'varchar(255)', 'NO', '', '', ''
'pharmacy_id', 'int(11)', 'NO', '', '0', ''
'status', 'varchar(255)', 'NO', '', '', ''
'contact_relationship', 'varchar(255)', 'NO', '', '', ''
'date', 'datetime', 'YES', '', NULL, ''
'sex', 'varchar(255)', 'NO', '', '', ''
'referrer', 'varchar(255)', 'NO', '', '', ''
'referrerID', 'varchar(255)', 'NO', '', '', ''
'providerID', 'int(11)', 'YES', '', NULL, ''
'ref_providerID', 'int(11)', 'YES', '', NULL, ''
'email', 'varchar(255)', 'NO', '', '', ''
'email_direct', 'varchar(255)', 'NO', '', '', ''
'ethnoracial', 'varchar(255)', 'NO', '', '', ''
'race', 'varchar(255)', 'NO', '', '', ''
'ethnicity', 'varchar(255)', 'NO', '', '', ''
'religion', 'varchar(40)', 'NO', '', '', ''
'interpretter', 'varchar(255)', 'NO', '', '', ''
'migrantseasonal', 'varchar(255)', 'NO', '', '', ''
'family_size', 'varchar(255)', 'NO', '', '', ''
'monthly_income', 'varchar(255)', 'NO', '', '', ''
'billing_note', 'text', 'YES', '', NULL, ''
'homeless', 'varchar(255)', 'NO', '', '', ''
'financial_review', 'datetime', 'YES', '', NULL, ''
'pubpid', 'varchar(255)', 'NO', '', '', ''
'pid', 'bigint(20)', 'NO', 'PRI', '0', ''
'genericname1', 'varchar(255)', 'NO', '', '', ''
'genericval1', 'varchar(255)', 'NO', '', '', ''
'genericname2', 'varchar(255)', 'NO', '', '', ''
'genericval2', 'varchar(255)', 'NO', '', '', ''
'hipaa_mail', 'varchar(3)', 'NO', '', '', ''
'hipaa_voice', 'varchar(3)', 'NO', '', '', ''
'hipaa_notice', 'varchar(3)', 'NO', '', '', ''
'hipaa_message', 'varchar(20)', 'NO', '', '', ''
'hipaa_allowsms', 'varchar(3)', 'NO', '', 'NO', ''
'hipaa_allowemail', 'varchar(3)', 'NO', '', 'NO', ''
'squad', 'varchar(32)', 'NO', '', '', ''
'fitness', 'int(11)', 'NO', '', '0', ''
'referral_source', 'varchar(30)', 'NO', '', '', ''
'usertext1', 'varchar(255)', 'NO', '', '', ''
'usertext2', 'varchar(255)', 'NO', '', '', ''
'usertext3', 'varchar(255)', 'NO', '', '', ''
'usertext4', 'varchar(255)', 'NO', '', '', ''
'usertext5', 'varchar(255)', 'NO', '', '', ''
'usertext6', 'varchar(255)', 'NO', '', '', ''
'usertext7', 'varchar(255)', 'NO', '', '', ''
'usertext8', 'varchar(255)', 'NO', '', '', ''
'userlist1', 'varchar(255)', 'NO', '', '', ''
'userlist2', 'varchar(255)', 'NO', '', '', ''
'userlist3', 'varchar(255)', 'NO', '', '', ''
'userlist4', 'varchar(255)', 'NO', '', '', ''
'userlist5', 'varchar(255)', 'NO', '', '', ''
'userlist6', 'varchar(255)', 'NO', '', '', ''
'userlist7', 'varchar(255)', 'NO', '', '', ''
'pricelevel', 'varchar(255)', 'NO', '', 'standard', ''
'regdate', 'date', 'YES', '', NULL, ''
'contrastart', 'date', 'YES', '', NULL, ''
'completed_ad', 'varchar(3)', 'NO', '', 'NO', ''
'ad_reviewed', 'date', 'YES', '', NULL, ''
'vfc', 'varchar(255)', 'NO', '', '', ''
'mothersname', 'varchar(255)', 'NO', '', '', ''
'guardiansname', 'text', 'YES', '', NULL, ''
'allow_imm_reg_use', 'varchar(255)', 'NO', '', '', ''
'allow_imm_info_share', 'varchar(255)', 'NO', '', '', ''
'allow_health_info_ex', 'varchar(255)', 'NO', '', '', ''
'allow_patient_portal', 'varchar(31)', 'NO', '', '', ''
'deceased_date', 'datetime', 'YES', '', NULL, ''
'deceased_reason', 'varchar(255)', 'NO', '', '', ''
'soap_import_status', 'tinyint(4)', 'YES', '', NULL, ''
'cmsportal_login', 'varchar(60)', 'NO', '', '', ''
'county', 'varchar(40)', 'NO', '', '', ''
'industry', 'text', 'YES', '', NULL, ''
'imm_reg_status', 'text', 'YES', '', NULL, ''
'imm_reg_stat_effdate', 'text', 'YES', '', NULL, ''
'publicity_code', 'text', 'YES', '', NULL, ''
'publ_code_eff_date', 'text', 'YES', '', NULL, ''
'protect_indicator', 'text', 'YES', '', NULL, ''
'prot_indi_effdate', 'text', 'YES', '', NULL, ''
'guardianrelationship', 'text', 'YES', '', NULL, ''
'guardiansex', 'text', 'YES', '', NULL, ''
'guardianaddress', 'text', 'YES', '', NULL, ''
'guardiancity', 'text', 'YES', '', NULL, ''
'guardianstate', 'text', 'YES', '', NULL, ''
'guardianpostalcode', 'text', 'YES', '', NULL, ''
'guardiancountry', 'text', 'YES', '', NULL, ''
'guardianphone', 'text', 'YES', '', NULL, ''
'guardianworkphone', 'text', 'YES', '', NULL, ''
'guardianemail', 'text', 'YES', '', NULL, ''
'care_team_provider', 'text', 'YES', '', NULL, ''
'care_team_facility', 'text', 'YES', '', NULL, ''
    */
    const FIELD_ID = "id";
    protected $inputFilter;
    public $id;
    public $fname;
    public $lname;
    public $DOB;
    public $sex;

    public function exchangeArray($data)
    {

        $this->id     = (!empty($data['id'])) ? $data['id'] : null;
        $this->fname = (!empty($data['fname'])) ? $data['fname'] : null;
        $this->lname = (!empty($data['lname'])) ? $data['lname'] : null;
        $this->sex = (!empty($data['sex'])) ? $data['sex'] : null;
        $this->DOB = (!empty($data['DOB'])) ? $data['DOB'] : null;
    }



    public static $inputsValidations = array(
        array(
            'name'     => 'id',
            'required' => true,
            'filters'  => array(
                array('name' => 'Int'),
            ),
        ),
        array(
            'name'     => 'fname',
            'required' => true,

        ),
        array(
            'name'     => 'lname',
            'required' => true,

        ),

        array(
            'name'     => 'sex',
            'required' => true,
        ),
        array(
            'name'     => 'DOB',
            'required' => true,
        )
    );


    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            foreach (self::$inputsValidations as $input) {
                $inputFilter->add($input);
            }

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
