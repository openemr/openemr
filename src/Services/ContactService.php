<?php

/**
 * Contact Service
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    David <  >
 * @copyright Copyright (c) 2022 David <  >
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\ORDataObject\Address;
use OpenEMR\Common\ORDataObject\Contact;
use OpenEMR\Common\ORDataObject\ContactAddress;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\BaseService;
use OpenEMR\Common\Logging\SystemLogger;

class ContactService extends BaseService
{
    public const TABLE_NAME = 'contact';

    private const CONTACT_ADDRESS_TABLE = "contact_address";

    /**
     * Default constructor.
     */
    public function __construct($base_table = null)
    {
        parent::__construct($base_table ?? self::TABLE_NAME);
        //$this->patientValidator = new PatientValidator();
    }

    // TODO: @adunsulag do we want this service so tightly coupled to the interface widget data format?  Should we genericize this more?
    // for now, following KISS, but we may want to add some more complexity, just so this is a more generic service we can use.
    public function saveContactsForPatient($pid, $contactData)
    {
        (new SystemLogger())->debug("Saving contact for patient pid ", ['pid' => $pid, 'contactData' => $contactData]);

        try {
            // due to contention on the sequences table with connection pooling and the sequences table locking everytime
            // a new sequence value is updated we've disabled the transactions for now until we can figure out what is
            // going on.

            // wrap the entire thing in a transaction so we are idempotent.
//            \sqlBeginTrans();

            $preppedData = $this->convertArraysToRecords($pid, $contactData);

            // save off our data
            foreach ($preppedData as $record) {
                $record->persist();
            }

            // grab all of the NEW records and insert them in as address records for the given patient
//            \sqlCommitTrans();
        } catch (\Exception $exception) {
            // TODO: @adunsulag handle exception
//            \sqlRollbackTrans();
        }

        // then we return
    }

    /**
     * @param $pid
     * @param $contactData
     * @return ContactAddress[]
     */
    public function convertArraysToRecords($pid, $contactData)
    {
        $count = count($contactData['data_action'] ?? []);
        for ($i = 0; $i < $count; $i++) {
            // empty data that we don't need to deal with as we can't do anything meaningful without an id
            if ($contactData['data_action'][$i] != 'ADD' && empty($contactData['id'][$i])) {
                continue;
            }
            $contactAddress = new ContactAddress($contactData['id'][$i] ?? null);

            $address = $contactAddress->getAddress();
            $address->set_line1($contactData['line_1'][$i] ?? '');
            $address->set_line2($contactData['line_2'][$i] ?? '');
            $address->set_city($contactData['city'][$i] ?? '');
            $address->set_state($contactData['state'][$i] ?? '');
            $address->set_country($contactData['country'][$i] ?? '');
            $address->set_postalcode($contactData['postalcode'][$i] ?? '');
            $address->set_foreign_id(null);

            $contact = $contactAddress->getContact();
            // then we will create our contacts record as well
            $contact->setPatientPid($pid);

            // here we can handle all of our data actions
            if ($contactData['data_action'][$i] == 'DELETE') {
                $contactAddress->deactivate();
            }

            // now we fill in any of our ContactAddress information if we have it
            $records[] = $contactAddress;
        }
        return $records;
    }


    /**
     * Returns all of our contact address information for a patient
     * @param $pid
     * @return ContactAddress[]
     */
    public function getContactsForPatient($pid): array
    {

        $sql = "SELECT ca.* FROM contact_address ca LEFT JOIN contact c ON ca.contact_id = c.id AND c.type_table_name = 'patient_data' AND c.type_table_id = ?";
        $contactData = QueryUtils::fetchRecords($sql, 'id', [$pid]) ?? [];
//
//       // does an O(n) fetch from the db, could be optimized later to use populate_array on the record.
        // for single patient pid access this is just fine as very few patients have more than 4-5 addresses
        // TODO: if we need bulk export on patients we should rewrite this method.
        $resultSet = [];
        foreach ($contactData as $record) {
            $contactAddress = new ContactAddress();
            $contactAddress->populate_array($record);
            $address = $contactAddress->getAddress();
            $arrAddress = $address->toArray();
            $arrAddress['id'] = $contactAddress->get_id();
            $resultSet[] = $arrAddress;
        }
        return $resultSet;
    }
}
