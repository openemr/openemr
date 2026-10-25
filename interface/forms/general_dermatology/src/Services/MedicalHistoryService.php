<?php

namespace GeneralDermatology\Services;

use OpenEMR\Common\Database\QueryUtils;
use GeneralDermatology\Domain\TreatmentDocument;
use GeneralDermatology\Repository\GeneralDermatologyRepository;

use GeneralDermatology\Models\MedicalHistory;

/**
 * Medical History Service Class
 *
 * Handles retrieval and processing of medical history data
 */
class MedicalHistoryService
{
    /**
     * Get medical history data by patient ID
     *
     * @param int $pid The patient ID
     * @param int $encounter The encounter ID
     * @return MedicalHistory The medical history model
     */
    public function getMedicalHistory(int $pid, int $encounter): MedicalHistory
    {
        // Get form data from general_dermatology table
        $formData = (new GeneralDermatologyRepository())->findById((int) ((QueryUtils::querySingleRow("SELECT id FROM general_dermatology WHERE pid = ? AND IFNULL(general_dermatology_form_template_name,'') = '' ORDER BY id DESC LIMIT 1", [$pid]) ?: [])['id'] ?? 0));

        if (empty($formData)) {
            $formData = [];
        }

        // Process medical history data
        $medicalHistory = [
            'skin_disease_history' => $this->getSkinDiseaseHistory($pid),
            'skin_cancer_history' => $this->getSkinCancerHistory($pid, $formData),
            'alerts' => $this->getAlerts($pid, $formData),
            'medications' => $this->getMedications($pid, $formData),
            'review_of_systems' => $this->getReviewOfSystems($encounter),
            'surgical_history' => $this->getSurgicalHistory($pid)
        ];

        return new MedicalHistory($medicalHistory);
    }

    /**
     * Get skin disease history
     *
     * @param int $pid The patient ID
     * @param array $formData Optional form data if already retrieved
     * @return string The skin disease history
     */
    public function getSkinDiseaseHistory(int $pid): string
    {
        $skinDiseasesList = QueryUtils::querySingleRow(
            "SELECT * FROM `history_data` WHERE `pid` = ? ORDER BY id DESC LIMIT 1",
            [$pid]
        ) ?: [];

        $skinDiseasesSelections = (string) ($skinDiseasesList['Check6'] ?? $skinDiseasesList['check6'] ?? '');
        $selectedDiseases = array_values(array_filter(array_map('intval', explode('|', $skinDiseasesSelections))));
        if ($selectedDiseases === []) {
            return 'none';
        }

        $placeholders = implode(',', array_fill(0, count($selectedDiseases), '?'));
        $rows = QueryUtils::fetchRecords(
            "SELECT `title` FROM `lists` WHERE `type` = 'skin_disease' AND `id` IN ($placeholders)",
            $selectedDiseases
        ) ?: [];

        $titles = [];
        foreach ($rows as $row) {
            $title = trim((string) ($row['title'] ?? ''));
            if ($title !== '') {
                $titles[] = $title;
            }
        }

        return $titles !== [] ? implode(', ', $titles) : 'none';
    }

    /**
     * Get skin cancer history
     *
     * @param int $pid The patient ID
     * @param array $formData Optional form data if already retrieved
     * @return string The skin cancer history
     */
    public function getSkinCancerHistory(int $pid, array $formData = []): string
    {
        // Query history_data table for skin cancer history (from original implementation)
        $skinCancerHistory = QueryUtils::querySingleRow(
            "SELECT `check6` FROM `history_data` WHERE `pid` = ? ORDER BY `id` DESC LIMIT 1",
            [$pid]
        );

        if (empty($skinCancerHistory['check6'])) {
            // If no history_data entry exists, try alternative approach
            return $this->getAlternativeSkinCancerHistory($pid, $formData);
        }

        // Process the history data from check6 field
        $skinCancerHistoryOptions = explode('|', $skinCancerHistory['check6']);

        if (!empty($skinCancerHistoryOptions)) {
            $options = array_values(array_filter(array_map('strval', $skinCancerHistoryOptions), static fn($v) => $v !== ''));
            if ($options !== []) {
                $placeholders = implode(',', array_fill(0, count($options), '?'));
                $params = array_merge(['skindisease'], $options);
                $rows = QueryUtils::fetchRecords(
                    "SELECT `title` FROM `list_options` WHERE `list_id` = ? AND `option_id` IN ($placeholders)",
                    $params
                ) ?: [];
                $fullHistoryArray = [];
                foreach ($rows as $row) {
                    $title = trim((string) ($row['title'] ?? ''));
                    if ($title !== '') {
                        $fullHistoryArray[] = $title;
                    }
                }
                if ($fullHistoryArray !== []) {
                    return implode(', ', $fullHistoryArray);
                }
            }
        }

        // If we get here, no valid skin cancer history was found
        return 'None';
    }

    /**
     * Alternative method to get skin cancer history when history_data doesn't have entries
     *
     * @param int $pid The patient ID
     * @param array $formData Optional form data if already retrieved
     * @return string The skin cancer history
     */
    private function getAlternativeSkinCancerHistory(int $pid, array $formData = []): string
    {
        // If form data wasn't provided, get it
        if (empty($formData)) {
            $formData = QueryUtils::querySingleRow(
                "SELECT * FROM `general_dermatology` WHERE `pid` = ? ORDER BY `id` DESC LIMIT 1",
                [$pid]
            );
        }

        // Check if we have treatment data in JSON format
        if (!empty($formData['treatment'])) {
            $treatmentData = TreatmentDocument::findingsForUi(TreatmentDocument::fromStorage($formData['treatment']));

            if (is_array($treatmentData)) {
                $cancerHistory = [];

                // Extract cancer diagnoses from treatment plans
                foreach ($treatmentData as $treatment) {
                    if (!empty($treatment['diagnosis']) &&
                        (stripos($treatment['diagnosis'], 'cancer') !== false ||
                         stripos($treatment['diagnosis'], 'carcinoma') !== false ||
                         stripos($treatment['diagnosis'], 'melanoma') !== false)) {
                        $cancerHistory[] = $treatment['diagnosis'];
                    }
                }

                if (!empty($cancerHistory)) {
                    return implode(', ', $cancerHistory);
                }
            }
        }

        // Fallback to lists table for skin cancer history
        $cancerHistory = QueryUtils::sqlStatementThrowException(
            "SELECT `title` FROM `lists`
             WHERE `type` = 'medical_problem'
             AND (`title` LIKE '%skin cancer%' OR `title` LIKE '%melanoma%' OR `title` LIKE '%carcinoma%')
             AND `pid` = ?",
            [$pid]
        );

        $cancerHistoryArray = [];
        while ($row = QueryUtils::fetchArrayFromResultSet($cancerHistory)) {
            $cancerHistoryArray[] = $row['title'];
        }

        return !empty($cancerHistoryArray) ? implode(', ', $cancerHistoryArray) : 'None';
    }

    /**
     * Get alerts
     *
     * @param int $pid The patient ID
     * @param array $formData Optional form data if already retrieved
     * @return string The alerts
     */
    public function getAlerts(int $pid, array $formData = []): string
    {
        // Query history_data table for allergies/alerts (from original implementation)
        $alertsData = QueryUtils::querySingleRow(
            "SELECT `check3` FROM `history_data` WHERE `pid` = ? ORDER BY `id` DESC LIMIT 1",
            [$pid]
        );

        if (empty($alertsData['check3'])) {
            // Fall back to alternative implementation if no history_data entries
            return "None";
        }

        // Process alert data from check3 field
        $alertArray = array_filter(explode('|', $alertsData['check3']));

        if (empty($alertArray)) {
            return 'None';
        }

        $options = array_values(array_filter(array_map('strval', $alertArray), static fn($v) => $v !== ''));
        $alertDescriptions = [];
        if ($options !== []) {
            $placeholders = implode(',', array_fill(0, count($options), '?'));
            $params = array_merge(['alerts'], $options);
            $rows = QueryUtils::fetchRecords(
                "SELECT `title` FROM `list_options` WHERE `list_id` = ? AND `option_id` IN ($placeholders)",
                $params
            ) ?: [];
            foreach ($rows as $row) {
                $title = trim((string) ($row['title'] ?? ''));
                if ($title !== '') {
                    $alertDescriptions[] = $title;
                }
            }
        }

        // If we have list_options results, return them
        if (!empty($alertDescriptions)) {
            return implode(', ', $alertDescriptions);
        }

        // If no list_options but we have alert array, return those
        if (!empty($alertArray)) {
            return implode(', ', $alertArray);
        }

        // No alerts found
        return 'None';
    }

    public function getFamilyHistory($pid)
    {
        $sql = "SELECT
                    `Melanoma2`,
                    `SkinCancer2`,
                    `Eczema`,
                    `Rosacea`,
                    `Fungal`,
                    `Vitiligo`,
                    `Disbetes`,
                    `Lupus`,
                    `IBF`,
                    `HBP`,
                    `HD`,
                    `stroke`
                    FROM `history_data` WHERE `pid` = ? ORDER BY `id` DESC LIMIT 1";
        $pid = (int) $pid;
        $result = QueryUtils::querySingleRow($sql, [$pid]);
        $familyHistory = "";
        if (!empty($result['Melanoma2'])) {
        $melanoma = explode('|', $result['Melanoma2']);
        $melanoma = implode(',', $melanoma);
        $familyHistory .= "Melanoma: " . $melanoma . ", \n\r";
        }
        if (!empty($result['SkinCancer2'])) {
            $skinCancer = explode('|', $result['SkinCancer2']);
            $skinCancer = implode(',', $skinCancer);
            $familyHistory .= "Skin Cancer: " . $skinCancer . ", \n\r";
        }
        if (!empty($result['Eczema'])) {
            $eczema = explode('|', $result['Eczema']);
            $eczema = implode(',', $eczema);
            $familyHistory .= "Eczema: " . $eczema . ", \n\r";
        }
        if (!empty($result['Rosacea'])) {
            $rosacea = explode('|', $result['Rosacea']);
            $rosacea = implode(',', $rosacea);
            $familyHistory .= "Rosacea: " . $rosacea . ", \n\r";
        }
        if (!empty($result['Fungal'])) {
            $fungal = explode('|', $result['Fungal']);
            $fungal = implode(',', $fungal);
            $familyHistory .= "Fungal: " . $fungal . ", \n\r";
        }
        if (!empty($result['Vitiligo'])) {
            $vitiligo = explode('|', $result['Vitiligo']);
            $vitiligo = implode(',', $vitiligo);
            $familyHistory .= "Vitiligo: " . $vitiligo . ", \n\r";
        }
        if (!empty($result['Disbetes'])) {
            $diabetes = explode('|', $result['Disbetes']);
            $diabetes = implode(',', $diabetes);
            $familyHistory .= "Diabetes: " . $diabetes . ", \n\r";
        }
        if (!empty($result['Lupus'])) {
            $lupus = explode('|', $result['Lupus']);
            $lupus = implode(',', $lupus);
            $familyHistory .= "Lupus: " . $lupus . ", \n\r";
        }
        if (!empty($result['IBF'])) {
            $ibf = explode('|', $result['IBF']);
            $ibf = implode(',', $ibf);
            $familyHistory .= "IBF: " . $ibf . ", \n\r";
        }
        if (!empty($result['HBP'])) {
            $hbp = explode('|', $result['HBP']);
            $hbp = implode(',', $hbp);
            $familyHistory .= "HBP: " . $hbp . ", \n\r";
        }
        if (!empty($result['HD'])) {
            $hd = explode('|', $result['HD']);
            $hd = implode(',', $hd);
            $familyHistory .= "HD: " . $hd . ", \n\r";
        }
        if (!empty($result['stroke'])) {
            $stroke = explode('|', $result['stroke']);
            $stroke = implode(',', $stroke);
            $familyHistory .= "Stroke: " . $stroke . ", \n\r";
        }
        if (empty($familyHistory)) {
            $familyHistory = "None";
        }
        return $familyHistory;
    }

    /**
     * Alternative method to get alerts when history_data doesn't have entries
     *
     * @param int $pid The patient ID
     * @param array $formData Optional form data if already retrieved
     * @return string The alerts
     */
    public function getAlternativeAlerts(int $pid, array $formData = []): string
    {
        // If form data wasn't provided, get it
        if (empty($formData)) {
            $formData = QueryUtils::querySingleRow(
                "SELECT * FROM `general_dermatology` WHERE `pid` = ? ORDER BY `id` DESC LIMIT 1",
                [$pid]
            );
        }

        $alerts = [];

        // Check for specific alert fields in the form data
        $alertFields = [
            'review_external_notes', 'review_test_results', 'order_tests',
            'obtain_hx', 'independent_test_another_hcp', 'discuss_test_outside_hcp',
            'document_time_hcp', 'consult_family', 'perform_diag_procedure'
        ];

        foreach ($alertFields as $field) {
            if (!empty($formData[$field])) {
                $alerts[] = ucfirst(str_replace('_', ' ', $field));
            }
        }

        // Check general_derm_note for any alerts
//        if (!empty($formData['general_derm_note'])) {
//            $notes = $formData['general_derm_note'];
//            if (stripos($notes, 'alert') !== false || stripos($notes, 'warning') !== false) {
//                $alerts[] = 'Note contains alerts';
//            }
//        }

        // Also check for allergy data in lists table
        $allergyQuery = QueryUtils::sqlStatementThrowException(
            "SELECT `title` FROM `lists` WHERE `type` = 'allergy' AND `pid` = ?",
            [$pid]
        );

        while ($row = QueryUtils::fetchArrayFromResultSet($allergyQuery)) {
            $alerts[] = 'Allergy: ' . $row['title'];
        }

        return !empty($alerts) ? implode(', ', $alerts) : 'None';
    }

    /**
     * Get medications
     *
     * @param int $pid The patient ID
     * @param array $formData Optional form data if already retrieved
     * @return string The medications
     */
    public function getMedications(int $pid, array $formData = []): string
    {
        // If form data wasn't provided, get it
        if (empty($formData)) {
            $formData = QueryUtils::querySingleRow(
                "SELECT * FROM `general_dermatology` WHERE `pid` = ? ORDER BY `id` DESC LIMIT 1",
                [$pid]
            );
        }

        // Check if we have treatment data in JSON format
        if (!empty($formData['treatment'])) {
            $treatmentData = json_decode($formData['treatment'], true);

            if (is_array($treatmentData)) {
                $medications = [];

                // Extract medications from treatment plans
                foreach ($treatmentData as $treatment) {
                    if (!empty($treatment['medication'])) {
                        $medications[] = $treatment['medication'];
                    }
                }

                if (!empty($medications)) {
                    return implode(', ', $medications);
                }
            }
        }

        // Fallback to getting medications from other tables
        $medicationsArray = [];
        $medListItems = [];

        // Get prescriptions
        $prescriptionsArray = QueryUtils::sqlStatementThrowException(
            "SELECT `drug` FROM `prescriptions` WHERE `patient_id` = ? ORDER BY `id` DESC LIMIT 10",
            [$pid]
        );

        while ($row = QueryUtils::fetchArrayFromResultSet($prescriptionsArray)) {
            $medicationsArray[] = $row['drug'];
        }

        // Get medication list items
        $medListArray = QueryUtils::sqlStatementThrowException(
            "SELECT `title` FROM `lists` WHERE `type` = 'medication' AND `pid` = ? ORDER BY `id` DESC LIMIT 10",
            [$pid]
        );

        while ($row = QueryUtils::fetchArrayFromResultSet($medListArray)) {
            $medListItems[] = $row['title'];
        }

        // Combine all medications
        $allMedications = array_merge($medicationsArray, $medListItems);

        return !empty($allMedications) ? implode(', ', $allMedications) : 'None';
    }

    /**
     * Get review of systems
     *
     * @param int|null $encounter The encounter ID
     * @return string The review of systems
     */
    public function getReviewOfSystems(?int $encounter = null): string
    {
        // Use passed encounter ID, fall back to session if not provided
        $encounterID = $encounter ?? ($_SESSION['encounter'] ?? null);

        if (empty($encounterID)) {
            return 'None';
        }

        // First check if there's a reviewofs form for the current encounter
        $findRosForm = QueryUtils::querySingleRow(
            "SELECT `form_id` FROM `forms` WHERE `pid` = ? AND `formdir` = 'reviewofs' ORDER BY `id` DESC LIMIT 1",
            array($_SESSION['pid'])
        );

        if (empty($findRosForm['form_id'])) {
            return 'None';
        }

        // Get reviewofs data
        $reviewOfSystemsData = QueryUtils::querySingleRow(
            "SELECT * FROM `form_reviewofs` WHERE `id` = ?",
            array($findRosForm['form_id'])
        );

        $keysArray = [];
        foreach ($reviewOfSystemsData as $key => $value) {
            if ($value == 'on') {
                $keysArray[] = $key;
            }
        }

        $dataArray = [
            "fever" => "No unexplained weight loss or fatigue",
            'chills' => 'No fever, chills, or night sweats',
            "rashes" => 'No new or persistent rashes',
            'infections' => 'No itching or burning sensations',
            'ulcerations' => 'No new or changing moles',
            'pemphigus' => 'No non-healing sores',
            'herpes' => 'No significant changes in skin color',
            'cataracts' => 'No increased sensitivity to sunlight',
            'cataract_surgery' => 'No easy bruising or unexplained bleeding',
            'glaucoma' => 'No history of recurrent skin infections',
            'double_vision' => 'No excessive hair loss or abnormal hair growth',
            'blurred_vision' => 'No nail discoloration, ridging, or brittleness',
            'poor_hearing' => 'No oral ulcers or persistent dryness',
            'headaches' => 'No red or irritated eyes, no new visual changes',
            'ringing_in_ears' => 'No new genital lesions, rashes, or irritation',
        ];

        $foundValue = [];
        foreach ($keysArray as $key) {
            if (isset($dataArray[$key])) {
                $foundValue[] = $dataArray[$key];
            }
        }

        return !empty($foundValue) ? implode("<br>\n", $foundValue) : 'None';
    }

    /**
     * Get surgical history
     *
     * @param int $pid The patient ID
     * @return string The surgical history
     */
    public function getSurgicalHistory(int $pid): string
    {
        // Query history_data table for surgical history (from original implementation)
        $surgicalHistory = QueryUtils::querySingleRow(
            "SELECT `1A` FROM `history_data` WHERE `pid` = ? AND `1A` != '' ORDER BY `date` DESC LIMIT 1",
            [$pid]
        );

        if (empty($surgicalHistory['1A'])) {
            // Fall back to checking lists table for procedures
            return 'None';
        }

        return $surgicalHistory['1A'];
    }

    /**
     * Alternative method to get surgical history when history_data doesn't have entries
     *
     * @param int $pid The patient ID
     * @return string The surgical history
     */
    private function getAlternativeSurgicalHistory(int $pid): string
    {
        // Check for surgical procedures in lists table
        $surgicalItems = [];
        $surgeryQuery = QueryUtils::sqlStatementThrowException(
            "SELECT `title`, `comments`, `date` FROM `lists`
            WHERE `type` = 'surgery' AND `pid` = ?
            ORDER BY `date` DESC",
            [$pid]
        );

        while ($row = QueryUtils::fetchArrayFromResultSet($surgeryQuery)) {
            $surgeryDesc = $row['title'];
            if (!empty($row['comments'])) {
                $surgeryDesc .= ' - ' . $row['comments'];
            }
            if (!empty($row['date'])) {
                $surgeryDesc .= ' (' . date('m/d/Y', strtotime($row['date'])) . ')';
            }
            $surgicalItems[] = $surgeryDesc;
        }

        // Check for procedures in forms
        $procedureQuery = QueryUtils::sqlStatementThrowException(
            "SELECT b.code_text FROM `billing` b
            JOIN `forms` f ON f.encounter = b.encounter
            WHERE b.pid = ? AND b.code_type = 'CPT4' AND
            (b.code LIKE '1%' OR b.code LIKE '2%' OR b.code_text LIKE '%excision%' OR b.code_text LIKE '%biopsy%')
            GROUP BY b.code_text
            ORDER BY f.date DESC",
            [$pid]
        );

        while ($row = QueryUtils::fetchArrayFromResultSet($procedureQuery)) {
            $surgicalItems[] = $row['code_text'];
        }

        return !empty($surgicalItems) ? implode(", ", $surgicalItems) : 'None';
    }

    /**
     * Get diagnosis history
     *
     * @param int $pid The patient ID
     * @param int $encounterId The encounter ID
     * @return string The diagnosis history
     */
    public function getDiagnosisHistory(int $pid, int $encounterId): string
    {
        $diagnosisHistory = "";
        $diagnosis = QueryUtils::sqlStatementThrowException(
            "SELECT code_text FROM `billing` WHERE `encounter` = ? AND `pid` = ? AND code_type = 'ICD10'",
            array($encounterId, $pid)
        );

        if (sqlNumRows($diagnosis) > 0) {
            $diagnosisTexts = [];

            while ($diagnosisRow = QueryUtils::fetchArrayFromResultSet($diagnosis)) {
                $diagnosisTexts[] = $diagnosisRow['code_text'];
            }

            $diagnosisHistory = implode(", ", $diagnosisTexts);
        }

        return !empty($diagnosisHistory) ? $diagnosisHistory : 'None';
    }

    public function buildExaminationSection($examinationData, $data): array
    {

        //Exam section
        $columns = [
            'scalp' => 'Scalp',
            'hair' => 'Hair',
            'face' => 'Face',
            'externalears'  => 'Ears',
            'eyelids' => 'Eyelids',
            'lips' => 'Lips',
            'nose' => 'Nose',
            'neck' => 'Neck',
            'chest' => 'Chest',
            'shoulders' => 'Shoulders',
            'back' => 'Back',
            'arms' => 'Arms',
            'forearms' => 'Forearms',
            'hands' => 'Hands',
            'fingernails' => 'Fingernails',
            'legs' => 'Legs',
            'feet' => 'Feet',
            'toenails' => 'Toenails',
            'groin' => 'Groin',
            'externalgenitalia' => 'External Genitalia',
            'genitalmucosa' => 'Genital Mucosa',
            'conjunctivalmucosa' => 'Conjunctival Mucosa',
            'oralmucosa' => 'Oral Mucosa',
            'buttocks' => 'Buttocks',
            'axillae' => 'Axillae',
            'ptdeclinedfullbodyskinexam' => "Declined full body skin exam",
            'ptdeclinedtoremoveunderwear' => "Declined to remove underwear",
            'ptdeclinedtoremovebra' => "Declined to remove bra",
            'ptwearingfingernailpolish' => "Wearing fingernail polish",
            'ptwearingtoenailpolish' => "Wearing toenail polish",
        ];

        $exams = [];
        foreach ($columns as $column => $value) {
            if (($data[$column] ?? 0) == 1) {
                $exams[] = $value;
            }
        }
        if (!empty($data['sites_examined_text'])) {
            $exams[] = $data['sites_examined_text'];
        }
        $exams = implode(', ', $exams);

        // Initialise observation strings — each is conditionally populated below.
        $exams_observation_1 = '';
        $exams_observation_2 = '';
        $exams_observation_3 = '';

        if (($data['well_developed'] ?? 0) == 1 && ($data['well_nourished'] ?? 0) == 1) {
            $exams_observation_1 = 'General Appearance of the patient is well developed and well nourished.';
        }

        if (($data['obese'] ?? 0) == 1) {
            $exams_observation_2 = 'The patient is obese.';
        }

        if (($data['anorexic'] ?? 0) == 1) {
            $exams_observation_3 = 'The patient is anorexic.';
        }

        $exams_observation_text = $examinationData['exams_observation_text'] ?? '';

        $lymphNodes = "";
        if ($data['cervical'] == 1) {
            $lymphNodes .= "<p>Cervical</p>";
        }
        if (($data['postauricular'] ?? 0) == 1) {
            $lymphNodes .= "<p>Postauricular</p>";
        }

        if ($data['occipital'] == 1) {
            $lymphNodes .= "<p>Occipital</p>";
        }

        if ($data['submandibular'] == 1) {
            $lymphNodes .= "<p>Submandibular</p>";
        }

        if ($data['supraclavicular'] == 1) {
            $lymphNodes .= "<p>Supraclavicular</p>";
        }

        if ($data['axillary'] == 1) {
            $lymphNodes .= "<p>Axillary</p>";
        }

        if ($data['inguinal'] == '1') {
            $lymphNodes .= "<p>Inguinal</p>";
        }

        if ($data['popliteal'] == '1') {
            $lymphNodes .= "<p>Popliteal</p>";
        }

        $orientation = "";
        if ($data['time'] == 1) {
            $orientation .= "Time, ";
        }
        if ($data['place'] =='1') {
            $orientation .= "Place, ";
        }
        if ($data['person'] == 1) {
            $orientation .= "Person";
        }
        $mood = "";
        if ($data['alert'] == 1) {
            $mood .= "Alert, ";
        }
        if ($data['uncomfortable'] == 1) {
            $mood .= "Uncomfortable, ";
        }
        if ($data['anxious'] == 1) {
            $mood .= "Anxious, ";
        }
        if ($data['flat'] == 1) {
            $mood .= "Flat ";
        }

        $vitals = "";
        if ($data['vital_bp']) {
            $vitals .= "<p>BP: " . $data['vital_bp'] . "</p>";
        }
        if ($data['vital_hr']) {
            $vitals .= "<p>HR: " . $data['vital_hr'] . "</p>";
        }
        if ($data['vital_rr']) {
            $vitals .= "<p>RR: " . $data['vital_rr'] . "</p>";
        }

        if (empty($vitals)) {
            $vitals = "None <br><br>";
        }

//end of Exam section
        return [
            'exams_observation_text' => $exams_observation_text,
            'exams' => $exams,
            'exams_observation_1' => $exams_observation_1,
            'exams_observation_2' => $exams_observation_2,
            'exams_observation_3' => $exams_observation_3,
            'orientation' => $orientation,
            'mood' => $mood,
            'lymph_nodes' => $lymphNodes,
            'vitals' => $vitals
            ];
    }
}
