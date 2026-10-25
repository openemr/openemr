<?php
namespace GeneralDermatology\Services\Info\Processors;
use GeneralDermatology\Services\Info\AbstractProcedureInfoProcessor;

class DermabrasionInfoProcessor extends AbstractProcedureInfoProcessor
{
    public function extractFields(array $treatment): array
    {
        $skinPrepOptions = [
            'dermabrasion-alcohol' => 'Alcohol',
            'dermabrasion-hibiclens' => 'Hibiclens',
            'dermabrasion-iodine' => 'Iodine',
            'dermabrasion-lasercyn' => 'Lasercyn',
        ];
        
        $anestheticOptions = [
            'dermabrasion-1p-lidocaine' => '1% Lidocaine',
            'dermabrasion-1p-lidocaine-with-epinephrine' => '1% Lidocaine with Epinephrine 1:100,000',
            'dermabrasion-2p-lidocaine' => '2% Lidocaine',
            'dermabrasion-2p-lidocaine-wtih-epinephrine' => '2% Lidocaine with Epinephrine 1:100,000',
            'dermabrasion-05p-lidocaine-with-epinephrine' => '0.5% Lidocaine with Epinephrine 1:200,000',
            'dermabrasion-05p-bupivicaine' => '0.5% Bupivacaine',
            'dermabrasion-05p-bupivicaine-wtih-epinephrine' => '0.5% Bupivacaine with Epinephrine 1:200,000',
            'dermabrasion-025p-bupivicaine' => '0.25% Bupivacaine',
            'dermabrasion-025p-bupivicaine-wtih-epinephrine' => '0.25% Bupivacaine with Epinephrine 1:400,000',
            'dermabrasion-025p-marcaine-wtih-epinephrine' => '0.25% Marcaine with Epinephrine 1:200,000',
            'dermabrasion-09p-bacteriostatic-sodium-chloride' => 'Bacteriostatic 0.9% Sodium Chloride',
            'dermabrasion-sterile-water' => 'Sterile Water',
            'dermabrasion-1p-ropivacaine' => 'Ropivacaine 1%',
            'dermabrasion-05p-ropivacaine' => 'Ropivacaine 0.5%',
            'dermabrasion-84p-sodium-bicarbonate' => 'WITH 8.4% Sodium Bicarbonate',
        ];
        
        $anestheticVolumeOptions = [
            'dermabrasion-anesthetic-volume-1cc' => '<1cc',
            'dermabrasion-anesthetic-volume-1-2cc' => '1-2cc',
            'dermabrasion-anesthetic-volume-2-3cc' => '2-3cc',
            'dermabrasion-anesthetic-volume-3-4cc' => '3-4cc',
            'dermabrasion-anesthetic-volume-4-5cc' => '4-5cc',
        ];
        
        $deviceOptions = [
            'dermabrasion-rotating-motor-device' => 'Rotating Motor Device',
        ];
        
        $fraiseOptions = [
            'dermabrasion-pear' => 'Pear',
            'dermabrasion-cylindrical' => 'Cylindrical',
            'dermabrasion-disc' => 'Disc',
        ];
        
        $aftercareOptions = [
            'dermabrasion-mupirocin' => 'Mupirocin',
            'dermabrasion-polysporin' => 'Polysporin',
            'dermabrasion-bacitracin' => 'Bacitracin',
            'dermabrasion-petrolatum' => 'Petrolatum',
            'dermabrasion-spf' => 'SPF',
            'dermabrasion-triamcinolone-0-1p-ointment' => 'Triamcinolone 0.1% Ointment',
            'dermabrasion-hydrocortisone-2-5p-ointment' => 'Hydrocortisone 2.5% Ointment',
        ];
        
        $bandageOptions = [
            'dermabrasion-pressure-dressing-with-telfa-gauze-and-hypafix' => 'Pressure Dressing with Telfa, Gauze, Hypafix',
            'dermabrasion-pressure-dressing-with-telfa-gauze-and-papertape' => 'Pressure Dressing with Telfa, Gauze, Paper Tape',
            'dermabrasion-regular-bandage' => 'Regular Bandage',
            'dermabrasion-telfa' => 'Telfa',
            'dermabrasion-paper-tape' => 'Paper Tape',
            'dermabrasion-hypafix' => 'Hypafix',
            'dermabrasion-hydrocolloid' => 'Hydrocolloid',
        ];
        
        return [
            'skin_prep' => $this->extractCheckboxGroup($treatment, 'dermabrasion', $skinPrepOptions),
            'skin_prep_free_text' => $this->getField($treatment, 'dermabrasion-skin-prep-free-text'),
            'anesthetic' => $this->extractCheckboxGroup($treatment, 'dermabrasion', $anestheticOptions),
            'anesthetic_free_text' => $this->getField($treatment, 'dermabrasion-anesthetic-free-text'),
            'anesthetic_volume' => $this->extractCheckboxGroup($treatment, 'dermabrasion', $anestheticVolumeOptions),
            'anesthetic_volume_free_text' => $this->getField($treatment, 'dermabrasion-anesthetic-volume-free-text'),
            'areas_treated' => $this->getField($treatment, 'areas_dermabrasion'),
            'number_of_lesions' => $this->getField($treatment, 'areas-count-dermabrasion'),
            'device_name' => $this->extractCheckboxGroup($treatment, 'dermabrasion', $deviceOptions),
            'device_name_free_text' => $this->getField($treatment, 'dermabrasion-Name-of-device-free-text'),
            'fraises' => $this->extractCheckboxGroup($treatment, 'dermabrasion', $fraiseOptions),
            'fraises_free_text' => $this->getField($treatment, 'dermabrasion-Fraises-free-text'),
            'aftercare' => $this->extractCheckboxGroup($treatment, 'dermabrasion', $aftercareOptions),
            'aftercare_free_text' => $this->getField($treatment, 'dermabrasion-aftercare_free_text'),
            'bandage' => $this->extractCheckboxGroup($treatment, 'dermabrasion', $bandageOptions),
            'bandage_free_text' => $this->getField($treatment, 'dermabrasion-bandage-free-text'),
        ];
    }

    public function formatForDisplay(array $fields): string
    {
        $sections = [];
        
        // Skin Prep Section
        $skinPrepHtml = '';
        if (!empty($fields['skin_prep'] ?? [])) {
            $skinPrepHtml .= $this->formatFieldList('Skin Prep Options', $fields['skin_prep']);
        }
        if (!$this->isFieldEmpty($fields['skin_prep_free_text'] ?? null)) {
            $skinPrepHtml .= $this->formatField('Skin Prep Notes', $fields['skin_prep_free_text']);
        }
        if ($skinPrepHtml) {
            $sections['Skin Preparation'] = $skinPrepHtml;
        }
        
        // Anesthetic Section
        $anestheticHtml = '';
        if (!empty($fields['anesthetic'] ?? [])) {
            $anestheticHtml .= $this->formatFieldList('Anesthetic', $fields['anesthetic']);
        }
        if (!$this->isFieldEmpty($fields['anesthetic_free_text'] ?? null)) {
            $anestheticHtml .= $this->formatField('Anesthetic Notes', $fields['anesthetic_free_text']);
        }
        if (!empty($fields['anesthetic_volume'] ?? [])) {
            $anestheticHtml .= $this->formatFieldList('Anesthetic Volume', $fields['anesthetic_volume']);
        }
        if (!$this->isFieldEmpty($fields['anesthetic_volume_free_text'] ?? null)) {
            $anestheticHtml .= $this->formatField('Volume Notes', $fields['anesthetic_volume_free_text']);
        }
        if ($anestheticHtml) {
            $sections['Anesthetic'] = $anestheticHtml;
        }
        
        // Treatment Areas Section
        $areasHtml = '';
        if (!$this->isFieldEmpty($fields['areas_treated'] ?? null)) {
            $areasHtml .= $this->formatField('Areas Treated', $fields['areas_treated']);
        }
        if (!$this->isFieldEmpty($fields['number_of_lesions'] ?? null)) {
            $areasHtml .= $this->formatField('Number of Lesions', $fields['number_of_lesions']);
        }
        if ($areasHtml) {
            $sections['Treatment Areas'] = $areasHtml;
        }
        
        // Device Section
        $deviceHtml = '';
        if (!empty($fields['device_name'] ?? [])) {
            $deviceHtml .= $this->formatFieldList('Device', $fields['device_name']);
        }
        if (!$this->isFieldEmpty($fields['device_name_free_text'] ?? null)) {
            $deviceHtml .= $this->formatField('Device Name', $fields['device_name_free_text']);
        }
        if ($deviceHtml) {
            $sections['Device'] = $deviceHtml;
        }
        
        // Fraises Section
        $fraisesHtml = '';
        if (!empty($fields['fraises'] ?? [])) {
            $fraisesHtml .= $this->formatFieldList('Fraises', $fields['fraises']);
        }
        if (!$this->isFieldEmpty($fields['fraises_free_text'] ?? null)) {
            $fraisesHtml .= $this->formatField('Fraise Notes', $fields['fraises_free_text']);
        }
        if ($fraisesHtml) {
            $sections['Fraises'] = $fraisesHtml;
        }
        
        // Aftercare Section
        $aftercareHtml = '';
        if (!empty($fields['aftercare'] ?? [])) {
            $aftercareHtml .= $this->formatFieldList('Aftercare (Applied In-Office)', $fields['aftercare']);
        }
        if (!$this->isFieldEmpty($fields['aftercare_free_text'] ?? null)) {
            $aftercareHtml .= $this->formatField('Aftercare Notes', $fields['aftercare_free_text']);
        }
        if ($aftercareHtml) {
            $sections['Aftercare'] = $aftercareHtml;
        }
        
        // Bandage Section
        $bandageHtml = '';
        if (!empty($fields['bandage'] ?? [])) {
            $bandageHtml .= $this->formatFieldList('Bandage', $fields['bandage']);
        }
        if (!$this->isFieldEmpty($fields['bandage_free_text'] ?? null)) {
            $bandageHtml .= $this->formatField('Bandage Notes', $fields['bandage_free_text']);
        }
        if ($bandageHtml) {
            $sections['Bandaging'] = $bandageHtml;
        }
        
        $html = $this->combineSections($sections);
        return $this->wrapProcedureDetails($html);
    }
}
