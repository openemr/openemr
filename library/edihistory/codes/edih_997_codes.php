<?php

/*
 * test_997_codes.php
 *
 * Copyright 2014 Kevin McCormick Longview, Texas
 *
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 3 or later.  You should have
 * received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *  <http://opensource.org/licenses/gpl-license.php>
 *
 *
 *
 * @link: http://www.open-emr.org
 * @package ediHistory
 */



/**
 * error code values in AK or IK segments
 *
 * @param string  the segment field ak304, ak403, ak501
 * @param string  the code
 * @return string
 */
function edih_997_code_text($ak_seg_field, $ak_code)
{
    // the Availity 997 file has codes with certain errors
    // which correspond to the messages in these arrays
    //
    $ret_str = '';

    $aktext['ak304'] = array(
            '1' => 'Unrecognized segment ID',
            '2' => 'Unexpected segment',
            '3' => 'Mandatory segment missing',
            '4' => 'Loop occurs over maximum times',
            '5' => 'Segment exceeds maximum use',
            '6' => 'Segment not in defined transaction set',
            '7' => 'Segment not in proper sequence',
            '8' => 'Segment has field errors',
            'I4' => 'Segment not used in implementation',
            'I6' => 'Implementation dependent segment missing',
            'I7' => 'Implementation loop occurs less than minimum times',
            'I8' => 'Implementation segment below minimum use',
            'I9' => 'Implementation dependent not used segment present'
            );

    $aktext['ak403'] = array(
           '1' => 'Mandatory data element missing',
           '2' => 'Conditional required data element missing',
           '3' => 'Too many data elements',
           '4' => 'Data element too short',
           '5' => 'Data element too long',
           '6' => 'Invalid character in data element',
           '7' => 'Invalid code value',
           '8' => 'Invalid date',
           '9' => 'Invalid time',
           '10' => 'Exclusion condition violated - segment includes two values that should not occur together',
           '12' => 'Too many repetitions',
           '13' => 'Too many components',
           'I10' => 'Implementation not used',
           'I11' => 'Implementation too few repetitions',
           'I12' => 'Implementation pattern match failure',
           'I13' => 'Implementation dependent not used data element present',
           'I6' => 'Code value not used in implimentation',
           'I9' => 'Implementation dependent data element missing'
           );

    $aktext['ak501'] = array(
           'A' => 'Accepted advised',
           'E' => 'Accepted, but errors were noted',
           'M' => 'Rejected, message authentication code (MAC) failed',
           'P' => 'Partially Accepted',
           'R' => 'Rejected advised',
           'W' => 'Rejected, assurance failed validity tests',
           'X' => 'Rejected, content after decryption could not be analyzed'
           );

     $aktext['ak502'] = array(
         '1' => 'Functional Group not supported',
         '2' => 'Functional Group Version not supported',
         '3' => 'Functional Group Trailer missing',
         '4' => 'Group Control Number in the Functional Group Header and Trailer do not agree',
         '5' => 'Number of included Transaction Sets does not match actual count',
         '6' => 'Group Control Number violates syntax',
         '10' => 'Authentication Key Name unknown',
         '11' => 'Encryption Key Name unknown',
         '12' => 'Requested Service (Authentication or Encryption) not available',
         '13' => 'Unknown security recipient',
         '14' => 'Unknown security originator',
         '15' => 'Syntax error in decrypted text',
         '16' => 'Security not supported',
         '17' => 'Incorrect message length (Encryption only)',
         '18' => 'Message authentication code failed',
         '19' => 'Functional Group Control Number not unique within Interchange',
         '23' => 'S3E Security End Segment missing for S3S Security Start Segment',
         '24' => 'S3S Security Start Segment missing for S3E Security End Segment',
         '25' => 'S4E Security End Segment missing for S4S Security Start Segment',
         '26' => 'S4S Security Start Segment missing for S4E Security End Segment',
         'I6' => 'Implementation dependent segment missing',
         );
    // array_key_exists($ak_seg_field, $aktext) && array_key_exists($ak_code, $aktext[$ak_seg_field]) )
     return ( isset($aktext[$ak_seg_field][$ak_code]) ) ? $aktext[$ak_seg_field][$ak_code] : '';
    //if ( isset($aktext[$ak_seg_field][$ak_code]) ) {
    //  return $aktext[$ak_seg_field][$ak_code];
    //} else {
    //  return "";
    //}
}


/**
 * code values for TA1 segment
 *
 * @param string  the code
 * @return string
 */
function edih_997_ta1_code($code)
{
    // codes in TA1 segment elements 4 and 5, since codes are distinct form, all values in one array

    $ta1code = array('A' => 'Interchange accepted with no errors.',
        'R' => 'Interchange rejected because of errors. Sender must resubmit file.',
        'E' => 'Interchange accepted, but errors are noted. Sender must not resubmit file.',
        '000' => 'No error',
        '001' => 'The Interchange Control Number in the header and trailer do not match. Use the value from the header in the acknowledgment.',
        '002' => 'This Standard as noted in the Control Standards Identifier is not supported.',
        '003' => 'This Version of the controls is not supported',
        '004' => 'The Segment Terminator is invalid',
        '005' => 'Invalid Interchange ID Qualifier for sender',
        '006' => 'Invalid Interchange Sender ID',
        '007' => 'Invalid Interchange ID Qualifier for receiver',
        '008' => 'Invalid Interchange Receiver ID',
        '009' => 'Unknown Interchange Receiver ID',
        '010' => 'Invalid Authorization Information Qualifier value',
        '011' => 'Invalid Authorization Information value',
        '012' => 'Invalid Security Information Qualifier value',
        '013' => 'Invalid Security Information value',
        '014' => 'Invalid Interchange Date value',
        '015' => 'Invalid Interchange Time value',
        '016' => 'Invalid Interchange Standards Identifier value',
        '017' => 'Invalid Interchange Version ID value',
        '018' => 'Invalid Interchange Control Number',
        '019' => 'Invalid Acknowledgment Requested value',
        '020' => 'Invalid Test Indicator value',
        '021' => 'Invalid Number of Included Group value',
        '022' => 'Invalid control structure',
        '023' => 'Improper (Premature) end-of-file (Transmission)',
        '024' => 'Invalid Interchange Content (e.g., invalid GS Segment)',
        '025' => 'Duplicate Interchange Control Number',
        '026' => 'Invalid Data Element Separator',
        '027' => 'Invalid Component Element Separator',
        '028' => 'Invalid delivery date in Deferred Delivery Request',
        '029' => 'Invalid delivery time in Deferred Delivery Request',
        '030' => 'Invalid delivery time Code in Deferred Delivery Request',
        '031' => 'Invalid grade of Service Code'
        );
    if (array_key_exists($code, $ta1code)) {
        return  $ta1code[$code];
    } else {
        return "Code $code not found in TA1 codes table. <br />";
    }
}
