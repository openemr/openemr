<?php

/**
 * Dummy Data
 * Contains all of the Visual Dashboard global settings and configuration
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Kofi Appiah <kkappiah@medsov.com>
 * @copyright Copyright (c) 2023 Visual EHR <https://visualehr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

    $data = [
        array("id" => 1,"name" => "Problem List","hint" => "(Sort by system)","data" => loadTimelineData()),
        array("id" => 2,"name" => "Medications","hint" => "","data" => loadMedicationData()),
        array("id" => 3,"name" => "vitals","hint" => "","data" => []),
        array("id" => 4,"name" => "Testing","hint" => "","data" => []),
        array("id" => 5,"name" => "Prevention","hint" => "","data" => []),
        array("id" => 6,"name" => "Demographic","hint" => "","data" => loadDemographicData())
    ];


    function loadTimelineData()
    {
        return array(
                    ["chronic" => true,"prescriptions" => array(["name" => "Epilepsy ||","color" => "#eb5ea8","startDate" => "2022-04-02T16:06:41.850Z"])],
                    ["chronic" => true,"prescriptions" => array(["name" => "Major Depressive Disorder ||","color" => "#9f4ed3","startDate" => "2022-04-02T16:06:41.850Z"])],
                    ["chronic" => true,"prescriptions" => array(["name" => "Asthma ||","color" => "#99b1fc","startDate" => "2022-04-02T16:06:41.850Z"])],
                    ["chronic" => false,"prescriptions" => array(["name" => "Bacterial Pneumonia ||","color" => "#7e83d0","startDate" => "2016-09-02T16:06:41.850Z"],["name" => "Viral URI ||","color" => "#7e83d0","startDate" => "2020-11-02T16:06:41.850Z","endDate" => "2022-05-02T16:06:41.850Z"])],
                    ["chronic" => false,"prescriptions" => array(["name" => "Essential Hypertension ||","color" => "#fb4e4e","startDate" => "2022-05-01T16:06:41.850Z"])],
                    ["chronic" => false,"prescriptions" => array(["name" => "Coronary Astherosclerosis ||","color" => "#32a852","startDate" => "2022-04-02T16:06:41.850Z"])],
                    ["chronic" => false,"prescriptions" => array(["name" => "Coronary Astherosclerosis ||","color" => "#32a852","startDate" => "2022-04-02T16:06:41.850Z"])],
                    ["chronic" => false,"prescriptions" => array(["name" => "Coronary Astherosclerosis ||","color" => "#32a852","startDate" => "2022-04-02T16:06:41.850Z"])],
                    ["chronic" => false,"prescriptions" => array(["name" => "HFpEF (40%) ||","color" => "#fb4e4e","startDate" => "2022-04-02T16:06:41.850Z"])],
                    ["chronic" => true,"prescriptions" => array(["name" => "Hepatitis C ||","color" => "#20c97e","startDate" => "2021-11-02T16:06:41.850Z"])],
                    ["chronic" => false,"prescriptions" => array(["name" => "Hx Cholecystectomy ||","color" => "#20c97e","startDate" => "2022-04-02T16:06:41.850Z"])],
                    ["chronic" => false,"prescriptions" => array(["name" => "AKI ||","color" => "#bd922d","startDate" => "2021-12-02T16:06:41.850Z"],["name" => "CKD 2 ||","color" => "#fbbc2d","startDate" => "2021-12-02T16:06:41.850Z"],["name" => "CKD 3 ||","color" => "#bd922d","startDate" => "2021-12-02T16:06:41.850Z"])],
                    ["chronic" => true,"prescriptions" => array(["name" => "","color" => "#000000","startDate" => "2022-04-02T16:06:41.850Z"])],
                );
    }

    function loadMedicationData()
    {
        return array(
            ["chronic" => true,"prescriptions" => array(["name" => "Levitiracetam ||","color" => "#eb5ea8","startDate" => "2022-04-02T16:06:41.850Z"])],
            ["chronic" => true,"prescriptions" => array(["name" => "Sertraline ||","color" => "#ea9d0e","startDate" => "2022-04-02T16:06:41.850Z"])],
            ["chronic" => false,"prescriptions" => array(["name" => "Sertraline2 ||","color" => "#ea9d0e","startDate" => "2023-04-02T16:06:41.850Z"],["name" => "Sertraline3 ||","color" => "#ea9d0e","startDate" => "2024-12-02T16:06:41.850Z"])],
            ["chronic" => true,"prescriptions" => array(["name" => "Albuterol ||","color" => "#99b1fc","startDate" => "2022-04-02T16:06:41.850Z"])],
            ["chronic" => false,"prescriptions" => array(["name" => "Azithromycin + Augmentin ||","color" => "#7e83d0","startDate" => "2021-06-02T16:06:41.850Z"])],
            ["chronic" => false,"prescriptions" => array(["name" => "Carvedilol 25mg BID ||","color" => "#fb4e4e","startDate" => "2021-11-02T16:06:41.850Z"],["name" => "Carvedilol 25mg BID ||","color" => "#fb4e4e","startDate" => "2021-11-02T16:06:41.850Z"])],
            ["chronic" => false,"prescriptions" => array(["name" => "Lisinorpril ||","color" => "#7e83d0","startDate" => "2021-06-02T16:06:41.850Z"])],
            ["chronic" => false,"prescriptions" => array(["name" => "Tramadol 25mg ||","color" => "#ccd19b","startDate" => "2015-01-02T16:06:41.850Z"],["name" => "Tramadol 25mg ||","color" => "#ccd19b","startDate" => "2020-01-02T16:06:41.850Z"],["name" => "Tramadol 50mg ||","color" => "#ccd19b","startDate" => "2021-10-02T16:06:41.850Z"],["name" => "Tramadol 50mg ||","color" => "#ccd19b","startDate" => "2021-8-02T16:06:41.850Z"])],
            ["chronic" => false,"prescriptions" => array(["name" => "Sofosbuvir ||","color" => "#20c97e","startDate" => "2022-01-02T16:06:41.850Z"])],
            ["chronic" => false,"prescriptions" => array(["name" => "Polyethylene gylcol ||","color" => "#ccd19b","startDate" => "2021-12-02T16:06:41.850Z"])],
            ["chronic" => false,"prescriptions" => array(["name" => "Vitamin D3 ||","color" => "#ccd19b","startDate" => "2021-05-02T16:06:41.850Z"])],
        );
    }

    function loadDemographicData()
    {
        return array(
            ["chronic" => false,"prescriptions" => array(["name" => "Vitamin D3 ||","color" => "#ccd19b","startDate" => "2021-05-02T16:06:41.850Z"])],
        );
    }
    echo json_encode($data, JSON_PRETTY_PRINT);
