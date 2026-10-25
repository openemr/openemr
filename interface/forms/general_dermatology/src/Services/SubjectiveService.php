<?php

/*
 * package   OpenEMR
 * link      http://www.open-emr.org
 * author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * All rights reserved
 * Copyright (c) 2025.
 */

namespace GeneralDermatology\Services;

class SubjectiveService
{
    public function buildSubjectiveSection($data): array
    {
        $conditions = [];
        $conditions['Skin Lesion'] = $data['skinlesion'] ?? 0;
        $conditions['Skin Check'] = $data['skincheck2'] ?? 0;
        $conditions['Rash'] = $data['rash2'] ?? 0;
        $conditions['Cosmetic'] = $data['cosmetic2'] ?? 0;
        $conditions['Surgical'] = $data['surgical2'] ?? 0;
        $conditions['Acne'] = $data['acne2'] ?? 0;
        $conditions['Hairloss'] = $data['hairloss2'] ?? 0;
        $conditions['NailDisorder'] = $data['naildisorder2'] ?? 0;
        $conditions['Other'] = $data['other2'] ?? 0;
        $selectedConditions = array_filter($conditions);
        $activeConditions = array_keys($selectedConditions);

        $activeConditionsList = implode(", ", $activeConditions) ?? 'None';

        $onset = "";
        $year = $data['onset_year'] ?? '';
        $month = $data['onset_month'] ?? '';
        $week = $data['onset_week'] ?? '';
        $day = $data['onset_day'] ?? '';
        if ($year != '' && $month != '' && $day != '') {
            $onset .= $year . "-" . $month . "-" . $day;
        }

        $locations = [];
        $locations['Initially'] = $data['location_initially'] ?? '';
        $locations['Later'] = $data['location_later'] ?? '';
        $locations['Custom'] = $data['location_custom'] ?? '';
        $selectedLocations = array_filter($locations);
        $activeLocations = array_keys($selectedLocations);
        $listedLocations = implode(", ", $activeLocations) ?? 'None';

        //Symptoms section
        $symptoms = [];
        $symptoms['Growth']          =     $data['growth'];
        $symptoms['Pain']            =     $data['pain'];
        $symptoms['Itching']         =     $data['itching'];
        $symptoms['Bleeding']        =     $data['bleeding'];
        $symptoms['Color Change']    =     $data['changeincolor'];
        $symptoms['Shape Change']    =     $data['changeinshape'];
        $symptoms['Spreading']       =     $data['spreading'];
        $symptoms['Asymptomatic']    =     $data['asymptomatic'];
        $symptoms['Affecting ADLs']  =     $data['affecting_adls'];
        $symptoms['Affecting Sleep'] =     $data['affecting_sleep'];
        $symptoms['Unrelenting']     =     $data['unrelenting'];
        $selectedSymptoms = array_filter($symptoms);
        $activeSymptoms = array_keys($selectedSymptoms);
        $listedSymptoms = implode(", ", $activeSymptoms) ?? 'None';


        return [
          'activeConditionsList' => $activeConditionsList,
            'onset' => $onset,
            'weeks' => $week,
            'listedLocations' => $listedLocations,
            'priorTreatmentText1' => $data['prior_tx1'] ?? '',
            'priorTreatmentText2' => $data['prior_tx2'] ?? '',
            'priorTreatmentHelped' => $data['prior_1'] ?? '',
            'priorTreatmentHelped2' => $data['prior_2'] ?? '',
            'listedSymptoms' => $listedSymptoms,
            'symptomsSeverity' => $data['severity_text'] ?? '1',
        ];
    }
}
