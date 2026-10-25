<?php

namespace GeneralDermatology\Services\Info\Processors;

use GeneralDermatology\Services\Info\AbstractProcedureInfoProcessor;

class IncisionInfoProcessor extends AbstractProcedureInfoProcessor
{
    public function extractFields(array $treatment): array
    {
        $fields = [];

        $skinPrepFields = ['incision-alcohol' => 'Alcohol', 'incision-hibiclens' => 'Hibiclens', 'incision-iodine' => 'Iodine', 'incision-lasercyn' => 'Lasercyn'];
        $skinPrep = $this->extractCheckboxGroup($treatment, $skinPrepFields);
        if (!empty($skinPrep)) $fields['skin_prep'] = $skinPrep;
        if (!$this->isFieldEmpty($treatment, 'incision-skin-prep-free-text')) $fields['skin_prep_other'] = $this->getField($treatment, 'incision-skin-prep-free-text');

        $anestheticFields = ['incision-1p-lidocaine' => '1% Lidocaine', 'incision-1p-lidocaine-with-epinephrine' => '1% Lidocaine with Epinephrine 1:100,000', 'incision-2p-lidocaine' => '2% Lidocaine', 'incision-2p-lidocaine-wtih-epinephrine' => '2% Lidocaine with Epinephrine 1:100,000', 'incision-05p-lidocaine-with-epinephrine' => '0.5% Lidocaine with Epinephrine 1:200,000', 'incision-05p-bupivicaine' => '0.5% Bupivicaine', 'incision-05p-bupivicaine-wtih-epinephrine' => '0.5% Bupivicaine with Epinephrine 1:200,000', 'incision-025p-bupivicaine' => '0.25% Bupivicaine', 'incision-025p-bupivicaine-wtih-epinephrine' => '0.25% Bupivicaine with Epinephrine 1:400,000', 'incision-025p-marcaine-wtih-epinephrine' => '0.25% Marcaine with Epinephrine 1:200,000', 'incision-09p-bacteriostatic-sodium-chloride' => 'Bacteriostatic 0.9% Sodium Chloride', 'incision-sterile-water' => 'Sterile Water', 'incision-1p-ropivacaine' => 'Ropivacaine 1%', 'incision-05p-ropivacaine' => 'Ropivacaine HCl 0.5%', 'incision-84p-sodium-bicarbonate' => 'With 8.4% Sodium Bicarbonate in 1:10 ratio'];
        $anesthetics = $this->extractCheckboxGroup($treatment, $anestheticFields);
        if (!empty($anesthetics)) $fields['anesthetics'] = $anesthetics;
        if (!$this->isFieldEmpty($treatment, 'incision-anesthetic-free-text')) $fields['anesthetic_other'] = $this->getField($treatment, 'incision-anesthetic-free-text');

        $volumeFields = ['incision-anesthetic-volume-1cc' => '<1cc', 'incision-anesthetic-volume-1-2cc' => '1-2cc', 'incision-anesthetic-volume-2-3cc' => '2-3cc', 'incision-anesthetic-volume-3-4cc' => '3-4cc', 'incision-anesthetic-volume-4-5cc' => '4-5cc'];
        $volume = $this->extractCheckboxGroup($treatment, $volumeFields);
        if (!empty($volume)) $fields['anesthetic_volume'] = $volume;
        if (!$this->isFieldEmpty($treatment, 'incision-anesthetic-volume-free-text')) $fields['anesthetic_volume_other'] = $this->getField($treatment, 'incision-anesthetic-volume-free-text');

        if (!$this->isFieldEmpty($treatment, 'areas_incision')) $fields['areas_treated'] = $this->getField($treatment, 'areas_incision');
        if (!$this->isFieldEmpty($treatment, 'areas-count-incision')) $fields['lesion_count'] = $this->getField($treatment, 'areas-count-incision');

        $packingFields = ['incision-p-none' => 'None', 'incision-plain-packing-strip-gauze' => 'Plain packing strip gauze', 'incision-iodine-packing-strip-guaze' => 'Iodine packing strip gauze'];
        $packing = $this->extractCheckboxGroup($treatment, $packingFields);
        if (!empty($packing)) $fields['packing'] = $packing;
        if (!$this->isFieldEmpty($treatment, 'incision-packing-free-text')) $fields['packing_other'] = $this->getField($treatment, 'incision-packing-free-text');

        $packingInstructionsFields = ['incision-remove-packing-in-24-hrs' => 'Remove packing in 24 hrs', 'incision-removal-packing-in-48-hours' => 'Remove packing in 48 hours'];
        $packingInstructions = $this->extractCheckboxGroup($treatment, $packingInstructionsFields);
        if (!empty($packingInstructions)) $fields['packing_instructions'] = $packingInstructions;

        $cultureFields = ['incision-culture-none' => 'None', 'incision-bacterial-culture' => 'Bacterial culture'];
        $culture = $this->extractCheckboxGroup($treatment, $cultureFields);
        if (!empty($culture)) $fields['culture'] = $culture;
        if (!$this->isFieldEmpty($treatment, 'incision-culture-free-text')) $fields['culture_other'] = $this->getField($treatment, 'incision-culture-free-text');

        $aftercareFields = ['incision-aftercare-none' => 'None', 'incision-mupirocin' => 'Mupirocin', 'incision-polysporin' => 'Polysporin', 'incision-bacitracin' => 'Bacitracin', 'incision-petrolatum' => 'Petrolatum'];
        $aftercare = $this->extractCheckboxGroup($treatment, $aftercareFields);
        if (!empty($aftercare)) $fields['aftercare'] = $aftercare;
        if (!$this->isFieldEmpty($treatment, 'incision-aftercare_free_text')) $fields['aftercare_other'] = $this->getField($treatment, 'incision-aftercare_free_text');

        $bandageFields = ['incision-dressing-none' => 'None', 'incision-pressure-dressing-with-telfa-gauze-and-hypafix' => 'Pressure dressing with telfa, gauze and hypafix', 'incision-pressure-dressing-with-telfa-gauze-and-papertape' => 'Pressure dressing with telfa, gauze and paper tape', 'incision-regular-bandage' => 'Regular bandage', 'incision-telfa' => 'Telfa', 'incision-paper-tape' => 'Paper tape', 'incision-hypafix' => 'Hypafix', 'incision-hydrocolloid' => 'Hydrocolloid'];
        $bandage = $this->extractCheckboxGroup($treatment, $bandageFields);
        if (!empty($bandage)) $fields['bandage'] = $bandage;
        if (!$this->isFieldEmpty($treatment, 'incision-bandage-free-text')) $fields['bandage_other'] = $this->getField($treatment, 'incision-bandage-free-text');

        return $fields;
    }

    public function formatForDisplay(array $fields): string
    {
        if (empty($fields)) return '';
        $sections = [];
        if (isset($fields['skin_prep'])) { $prep = $this->formatFieldList($fields['skin_prep']); if (isset($fields['skin_prep_other'])) $prep .= ', ' . $fields['skin_prep_other']; $sections[] = $this->formatSection('Skin Prep', $prep); }
        if (isset($fields['anesthetics'])) { $anesthetic = $this->formatFieldList($fields['anesthetics']); if (isset($fields['anesthetic_other'])) $anesthetic .= ', ' . $fields['anesthetic_other']; $sections[] = $this->formatSection('Anesthetic', $anesthetic); }
        if (isset($fields['anesthetic_volume'])) { $volume = $this->formatFieldList($fields['anesthetic_volume']); if (isset($fields['anesthetic_volume_other'])) $volume .= ', ' . $fields['anesthetic_volume_other']; $sections[] = $this->formatSection('Anesthetic Volume', $volume); }
        if (isset($fields['areas_treated'])) $sections[] = $this->formatSection('Area(s) Treated', $fields['areas_treated']);
        if (isset($fields['lesion_count'])) $sections[] = $this->formatSection('Number of Lesions Treated', $fields['lesion_count']);
        if (isset($fields['packing'])) { $packing = $this->formatFieldList($fields['packing']); if (isset($fields['packing_other'])) $packing .= ', ' . $fields['packing_other']; $sections[] = $this->formatSection('Packing', $packing); }
        if (isset($fields['packing_instructions'])) $sections[] = $this->formatSection('Packing Instructions', $this->formatFieldList($fields['packing_instructions']));
        if (isset($fields['culture'])) { $culture = $this->formatFieldList($fields['culture']); if (isset($fields['culture_other'])) $culture .= ', ' . $fields['culture_other']; $sections[] = $this->formatSection('Culture', $culture); }
        if (isset($fields['aftercare'])) { $aftercare = $this->formatFieldList($fields['aftercare']); if (isset($fields['aftercare_other'])) $aftercare .= ', ' . $fields['aftercare_other']; $sections[] = $this->formatSection('Aftercare (Applied In-Office)', $aftercare); }
        if (isset($fields['bandage'])) { $bandage = $this->formatFieldList($fields['bandage']); if (isset($fields['bandage_other'])) $bandage .= ', ' . $fields['bandage_other']; $sections[] = $this->formatSection('Bandage', $bandage); }
        return $this->wrapProcedureDetails($this->combineSections($sections));
    }
}
