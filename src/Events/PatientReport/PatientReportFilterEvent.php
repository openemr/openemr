<?php

/**
 * PatientReportFilterEvent handls the filtering of data in the patient report for both the portal and the patient report.
 *
 * The class hides the implementation details of the underlying data allowing the event to change implementations
 * as needed.
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (C) 2024 Open Plan IT Ltd. <support@openplanit.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Events\PatientReport;

use Symfony\Contracts\EventDispatcher\Event;

class PatientReportFilterEvent extends Event
{
    /**
     * This event fires just before the data elements are sent into the patient portal's patient report twig template.
     * It allows the listener to modify the data elements that are sent in (hiding/showing sections, etc).
     */
    const FILTER_PORTAL_TWIG_DATA = 'patientReport.filter.portal.twig.data';
    const FILTER_PORTAL_HEALTHSNAPSHOT_TWIG_DATA = 'home.filter.portal.healthsnapshot.twig.data';

    /**
     * @var array $data The data elements that are being filtered by this array.
     */
    private array $data;

    public function __construct()
    {
        $this->data = [];
    }

    /**
     * Populates the data elements for this filtered event.
     * @param array $data
     */
    public function populateData(array $data): void
    {
        $this->clearData();
        foreach ($data as $key => $value) {
            $this->setDataElement($key, $value);
        }
    }

    /**
     * Sets the data elements that are being filtered by this array.
     * @param int|string $key
     * @param mixed $data
     */
    public function setDataElement(int|string $key, mixed $data): void
    {
        $this->data[$key] = $data;
    }

    public function getDataElement(int|string $key): mixed
    {
        return $this->data[$key];
    }

    /**
     * Removes a data element from the filtered data.
     * @param int|string $key
     * @return void
     */
    public function removeDataElement(int|string $key)
    {
        unset($this->data[$key]);
    }

    /**
     * Removes the data elements from the filtered data.
     * @return void
     */
    public function clearData()
    {
        $this->data = [];
    }

    public function getDataAsArray(): array
    {
        return $this->data;
    }
}
