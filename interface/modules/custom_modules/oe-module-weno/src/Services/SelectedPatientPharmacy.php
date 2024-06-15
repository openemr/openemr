<?php

/**
 * @package     OpenEMR
 * @link        https://www.open-emr.org
 * @author      Kofi Appiah <kkappiah@medsov.com>
 * @copyright   Copyright (c) 2024 Omegasystems Group Intl <info@omegasystemsgroup.com>
 * @license     https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\WenoModule\Services;

class SelectedPatientPharmacy
{
    public function __construct()
    {
    }

    public function prepSelectedPharmacy(array $data)
    {
        $newData = array(
            "primary_pharmacy" => $_POST['primary_pharmacy'],
            "alternate_pharmacy" => $_POST['alternate_pharmacy']
        );
        $persist = array(
            'all_day' => $data['24hr'] ?? '',
            'weno_only' => $data['weno_only'] ?? '',
            'weno_coverage' => $data['weno_coverage'] ?: 'Local',
            'weno_zipcode' => $data['weno_zipcode'] ?? '',
            'weno_city' => $data['weno_city'] ?? '',
            'weno_state' => $data['weno_state'] ?? '',
        );
        $newData['search_persist'] = json_encode($persist);

        $pharmacyService = new PharmacyService();
        $pharmacyService->createWenoPharmaciesForPatient($data['pid'], $newData);
    }

    public function prepForUpdatePharmacy($data)
    {
        $updateData = array(
            "primary_pharmacy" => $data['primary_pharmacy'],
            "alternate_pharmacy" => $data['alternate_pharmacy']
        );
        // Persist search data with patient pharmacy.
        $persist = array(
            'all_day' => $data['24hr'] ?? '',
            'weno_only' => $data['weno_only'] ?? '',
            'weno_coverage' => $data['weno_coverage'] ?: 'Local',
            'weno_zipcode' => $data['weno_zipcode'] ?? '',
            'weno_city' => $data['weno_city'] ?? '',
            'weno_state' => $data['weno_state'] ?? '',
        );
        $updateData['search_persist'] = json_encode($persist);

        $pharmacyService = new PharmacyService();
        $pharmacyService->updatePatientWenoPharmacy($data['pid'], $updateData);
    }
}
