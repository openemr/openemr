<?php
/**
 *  @package   OpenEMR
 *  @link      http://www.open-emr.org
 *  @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c )2019. Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 *
 */

namespace OpenEMR\Rx\Weno;

class NewRx
{
    /**
     * @param $list
     * @param $pid
     * @return string | \SimpleXMLElement
     */
    public function creatOrderXMLBody($med)
    {
        /**
         * The gathering of the data for the prescription
         */
        //Here we set the message id - it must be unique of all the prescriptions in entire world sent
        $mId = new GenMessageId();
        $messageid = $mId->getMessageId();

        $scriptData = new NewRxData(); //bring in all info to populate prescription
        $pi = $scriptData->getPatientData();  //Patient data

        /**
         * Pharmacy data and formating the phone numbers.
         */
        $pai = $scriptData->getPharmacyData($pi['pharmacy_id']);

        $pharmacyphone = $pai[0][0]['area_code'].$pai[0][0]['prefix'].$pai[0][0]['number'];
        $pharmacyfax = $pai[0][1]['area_code'].$pai[0][1]['prefix'].$pai[0][1]['number'];

        /**
         * currently logged in prescriber
         */
        $scriptPrescriber = $scriptData->getPrescriberData();

        $wenoid = 157;
        $partnerID = 'DA51A758AC5EBF04A095D0B7A356C50490A828C4';//'9039730E79C1167765840EDABC6AB2A6';

        /**
         * The generation of the prescription XML
         */
        $envelope = '<Message xmlns:xsi=":http://www.w3.org/2001/XMLSchema-instance"' .
                    ' DatatypesVersion="20170715" TransportVersion="20170715" ' .
                    ' TransactionDomain="SCRIPT" TransactionVersion="20170715" ' .
                    ' StructuresVersion="20170715" ECLVersion="20170715"> ' .
                    '</Message>';

        $xml = new \SimpleXMLElement($envelope);


        $mHeader = $xml->addChild('Header');
        $to = $mHeader->addChild('To', $pai['ncpdp']);
        $to->addAttribute('Qualifier', 'P');
        $from = $mHeader->addChild('From', $GLOBALS['weno_provider_id']);
        $from->addAttribute('Qualifier', 'C');
        $mHeader->addChild('MessageID', 'jse'.$messageid);
        $mHeader->addChild('SentTime', gmdate('Y-m-d\TH:i:s.u'));

        $security = $mHeader->addchild('Security');
        $username = $security->addChild('UsernameToken');
        $username->addchild('Username', $wenoid);
        $pass = $username->addChild('Password', $partnerID);
        $pass->addAttribute('Type', 'PasswordDigest');
        $sender = $security->addChild('Sender');
        $sender->addChild('SecondaryIdentification', 'sherwin@openmedpractice.com');

        $software = $mHeader->addChild('SenderSoftware');
        $software->addChild('SenderSoftwareDeveloper', 'Sherwin Gaddis');
        $software->addChild('SenderSoftwareProduct', 'OpenEMR v5.0.2');
        $software->addChild('SenderSoftwareVersionRelease', '2.0.1');

        $signature = $mHeader->addChild('DigitalSignature');
        $signature->addAttribute('Version', 'T');
        $indicator = $signature->addChild('DigitalSignatureIndicator', '1');

        $mBody = $xml->addChild('Body');
        $newrx = $mBody->addChild('NewRx');
        $newrx->addChild('ReturnReceipt', '001');
        $allergy = $newrx->addChild('AllergyOrAdverseEvent');
        $allergy->addChild('NoKnownAllergies', 'Y');

        $benefits = $newrx->addChild('BenefitsCoordination');
        $payer = $benefits->addChild('PayerIdentification');
        $processor = $payer->addChild('ProcessorIdentificationNumber', 'HT');
        $iinn = $payer->addChild('IINNumber', '015284');
        $cardid = $benefits->addChild('CardholderID', 'WENO');
        $groupid = $benefits->addChild('GroupID', 'BSURE');

        $gender = $pi['sex'];
        $patient = $newrx->addChild('Patient');
        $human = $patient->addChild('HumanPatient');
        $name = $human->addChild('Name');
        $name->addChild('LastName', $pi['lname']);
        $name->addChild('FirstName', $pi['fname']);
        $gender = $human->addChild('Gender', $gender[0]);
        $dob = $human->addChild('DateOfBirth');
        $dob->addChild('Date', $pi['DOB']);
        $address = $human->addChild('Address');
        $address->addChild('AddressLine1', $pi['street']);
        $address->addChild('AddressLine2', 'na');
        $address->addChild('City', $pi['city']);
        $address->addChild('StateProvince', $pi['state']);
        $address->addChild('PostalCode', $pi['postal_code']);

        $address->addChild('CountryCode', 'US'/*$pi['country_code']*/);
        $comm = $human->addChild('CommunicationNumbers');
        $phone = $comm->addChild('PrimaryTelephone');
        $phone->addChild('Number', $pi['phone_home']);
        $sms = $pi['hipaa_allowsms'];
        $phone->addChild('SupportsSMS', $sms[0]);

        $pharmacy = $newrx->addChild('Pharmacy');
        $identification = $pharmacy->addChild('Identification');
        $identification->addChild('NCPDPID', $pai['ncpdp']);
        $identification->addChild('NPI', $pai['npi']);
        $specialty = $pharmacy->addChild('Specialty', 'Retail');
        $businessname = $pharmacy->addChild('BusinessName', $pai['name']);
        $addressbn = $pharmacy->addChild('Address');
        $addressbn->addChild('AddressLine1', $pai['line1']);
        $addressbn->addChild('AddressLine2', (isset($pai['line2']) ? $pai['line2'] : 'NA'));
        $addressbn->addChild('City', $pai['city']);
        $addressbn->addChild('StateProvince', $pai['state']);
        $addressbn->addChild('PostalCode', $pai['zip']);

        $commnum = $pharmacy->addChild('CommunicationNumbers');
        $priphone = $commnum->addChild('PrimaryTelephone');
        $priphone->addChild('Number', $pharmacyphone);
        $faxnum = $commnum->addChild('Fax');
        $faxnum->addChild('Number', $pharmacyfax);

        $prescriber = $newrx->addChild('Prescriber');
        $nonvet = $prescriber->addChild('NonVeterinarian');
        $nonvetid = $nonvet->addChild('Identification');
        $nonvetid->addChild('DEANumber', $scriptPrescriber[0]['federaldrugid']);
        $nonvetid->addChild('NPI', $scriptPrescriber[0]['npi']);
        $prename = $nonvet->addChild('Name');
        $prename->addChild('LastName', $scriptPrescriber[0]['lname']);
        $prename->addChild('FirstName', $scriptPrescriber[0]['fname']);
        $preAddress = $nonvet->addChild('Address');
        $preAddress->addChild('AddressLine1', $scriptPrescriber[0]['street']);
        $preAddress->addChild('City', $scriptPrescriber[0]['city']);
        $preAddress->addChild('StateProvince', $scriptPrescriber[0]['state']);
        $preAddress->addChild('PostalCode', $scriptPrescriber[0]['postal_code']);
        $preAddress->addChild('CountryCode', 'US');
        $commnum2 = $nonvet->addChild('CommunicationNumbers');
        $priphone2 = $commnum2->addChild('PrimaryTelephone');
        $priphone2->addChild('Number', $scriptPrescriber[0]['phone']);

        $vitalsData = $scriptData->getCurrentVitals();

        $observation = $newrx->addChild('Observation');
        $measurement = $observation->addChild('Measurement');
        $vitals = $measurement->addChild('VitalSign', 'Weight');
        $loincv = $measurement->addChild('LOINCVersion', '441');
        $values = $measurement->addChild('Value', $vitalsData['weight']);
        $unitof = $measurement->addChild('UnitOfMeasure', 'pounds');
        $ucumv = $measurement->addChild('UCUMVersion', 'string');
        $obdate = $measurement->addChild('ObservationDate');
        $obdate->addChild('DateTime', gmdate('Y-m-d\TH:i:s.u'));
        $measurement2 = $observation->addChild('Measurement');
        $vitals2 = $measurement2->addChild('VitalSign', 'Height');
        $loincv2 = $measurement2->addChild('LOINCVersion', '4415');
        $values2 = $measurement2->addChild('Value', $vitalsData['height']);
        $unitof2 = $measurement2->addChild('UnitOfMeasure', 'inches');
        $ucumv2 = $measurement2->addChild('UCUMVersion', 'string2');
        $obdate2 = $measurement2->addChild('ObservationDate');
        $takenDate = $vitalsData['date'];
        $taken = explode(" ", $takenDate);

        $obdate2->addChild('DateTime', $taken[0].'T'.$taken[1]);
        $obnotes = $observation->addChild('ObservationNotes', ($vitalsData['note'] == "" ? 'NA' : $vitalsData['note']));

        $medData = $scriptData->medicationData($med);

        $medicationpres = $newrx->addChild('MedicationPrescribed');
        $description = $medicationpres->addChild('DrugDescription', $medData['drug']);
        $drugcode = $medicationpres->addChild('DrugCoded');
        $dbcode = $drugcode->addChild('DrugDBCode');
        $dbcode->addChild('Code', $medData['drug_id']);
        $dbcode->addChild('Qualifier', $medData['drug_db_code_qualifier']);
        $deaschedule = $drugcode->addChild('DEASchedule');
        $deaschedule->addChild('Code', $medData['dea_schedule']);
        $quantity = $medicationpres->addChild('Quantity');
        $quantity->addChild('Value', $medData['quantity']);
        $quantity->addChild('CodeListQualifier', $medData['code_list_qualifier']);
        $quantityofunit = $quantity->addChild('QuantityUnitOfMeasure');
        $quantityofunit->addChild('Code', $medData['potency_unit_code']);
        $writtendate = $medicationpres->addChild('WrittenDate');
        $writtendate->addChild('Date', date("Y-m-d"));
        $substitution = $medicationpres->addChild('Substitutions', '0');
        if (empty($medData['refills'])) {
            $refills = 0;
        } else {
            $refills = $medData['refills'];
        }
        $refills = $medicationpres->addChild('NumberOfRefills', $refills);
        $diagnosis = $medicationpres->addChild('Diagnosis');
        $clinical = $diagnosis->addChild('ClinicalInformationQualifier', '1');
        $primary = $diagnosis->addChild('Primary');
        $primary->addChild('Code', $medData['diagnosis']);
        $primary->addChild('Qualifier', 'ABF');
        $primary->addChild('Description', $medData['title']);
        $note = $medicationpres->addChild('Note', 'Patient Rx Savings Card BIN: 015284; PCN: HT: Group: BSURE; ID: Weno');
        $sig = $medicationpres->addChild('Sig');
        $sig->addChild('SigText', $medData['note']);

        return $xml->asXML();
    }
}
