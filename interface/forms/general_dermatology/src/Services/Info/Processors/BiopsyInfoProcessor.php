<?php

namespace GeneralDermatology\Services\Info\Processors;

use GeneralDermatology\Services\Info\AbstractProcedureInfoProcessor;

class BiopsyInfoProcessor extends AbstractProcedureInfoProcessor
{
    public function extractFields(array $treatment): array
    {
        $fields = [];

        // Biopsy location (areas treated)
        if (!$this->isFieldEmpty($treatment, 'biopsy-areas')) {
            $fields['areas'] = $this->getField($treatment, 'biopsy-areas');
        }

        // Rule out
        if (!$this->isFieldEmpty($treatment, 'biopsy-Rule-out-free-text')) {
            $fields['rule_out'] = $this->getField($treatment, 'biopsy-Rule-out-free-text');
        }

        // Biopsy Method (checkboxes)
        $methodFields = [
            'biopsy-shave' => 'Shave',
            'biopsy-punch' => 'Punch',
            'biopsy-incisional' => 'Incisional',
            'biopsy-excisional' => 'Excisional',
            'biopsy-nail-bed' => 'Nail Bed',
            'biopsy-nail-matrix' => 'Nail Matrix',
            'biopsy-nail' => 'Nail',
            'biopsy-ear' => 'Ear',
            'biopsy-penis' => 'Penis',
            'biopsy-vulva' => 'Vulva',
            'biopsy-anus' => 'Anus',
            'biopsy-eyelid' => 'Eyelid',
            'biopsy-lip' => 'Lip',
        ];
        $methods = $this->extractCheckboxGroup($treatment, $methodFields);
        if (!empty($methods)) {
            $fields['methods'] = $methods;
        }
        if (!$this->isFieldEmpty($treatment, 'biopsy-bio-method-free-text')) {
            $fields['method_other'] = $this->getField($treatment, 'biopsy-bio-method-free-text');
        }

        // Biopsy Type (checkboxes)
        $typeFields = [
            'biopsy-h-and-e' => 'H and E',
            'biopsy-dif' => 'DIF',
            'biopsy-frozen-section' => 'Frozen Section',
            'biopsy-pas' => 'PAS',
            'biopsy-flow-cytometry' => 'Flow Cytometry',
            'biopsy-t-cell-receptor-gene-rearrangement' => 'T-cell Receptor Gene Rearrangement',
        ];
        $types = $this->extractCheckboxGroup($treatment, $typeFields);
        if (!empty($types)) {
            $fields['types'] = $types;
        }
        if (!$this->isFieldEmpty($treatment, 'biopsy-bio-type-free-text')) {
            $fields['type_other'] = $this->getField($treatment, 'biopsy-bio-type-free-text');
        }

        // Differential diagnosis
        if (!$this->isFieldEmpty($treatment, 'biopsy-Differential-diagnosis-free-text')) {
            $fields['differential_diagnosis'] = $this->getField($treatment, 'biopsy-Differential-diagnosis-free-text');
        }

        // Skin Prep (checkboxes)
        $skinPrepFields = [
            'biopsy-alcohol' => 'Alcohol',
            'biopsy-hibiclens' => 'Hibiclens',
            'biopsy-iodine' => 'Iodine',
            'biopsy-lasercyn' => 'Lasercyn',
        ];
        $skinPrep = $this->extractCheckboxGroup($treatment, $skinPrepFields);
        if (!empty($skinPrep)) {
            $fields['skin_prep'] = $skinPrep;
        }
        if (!$this->isFieldEmpty($treatment, 'biopsy-skin-prep-free-text')) {
            $fields['skin_prep_other'] = $this->getField($treatment, 'biopsy-skin-prep-free-text');
        }

        // Anesthetic (extensive list - both topical and injectable)
        $anestheticFields = [
            'biopsy-none' => 'None',
            'biopsy-topical-5p-lidocaine' => 'Topical 5% Lidocaine',
            'biopsy-topical-23p-lidocaine-7p-tetracaine' => 'Topical 23% Lidocaine, 7% Tetracaine',
            'biopsy-topical-23p-lidocaine-7p-tetracaine-1p-phenylephrine' => 'Topical 23% Lidocaine, 7% Tetracaine, 1% Phenylephrine',
            'biopsy-topical-20p-benzocaine-10p-lidocaine-4p-tetracaine' => 'Topical 20% Benzocaine, 10% Lidocaine, 4% Tetracaine',
            'biopsy-topical-30p-lidocaine' => 'Topical 30% Lidocaine',
            'biopsy-topical-30-lidocaine-7-tetracaine' => 'Topical 30% Lidocaine, 7% Tetracaine',
            'biopsy-ice' => 'Ice',
            'biopsy-fan' => 'Fan',
            'biopsy-vibration' => 'Vibration',
            'biopsy-1p-lidocaine' => '1% Lidocaine',
            'biopsy-1p-lidocaine-with-epinephrine' => '1% Lidocaine with Epinephrine 1:100,000',
            'biopsy-2p-lidocaine' => '2% Lidocaine',
            'biopsy-2p-lidocaine-wtih-epinephrine' => '2% Lidocaine with Epinephrine 1:100,000',
            'biopsy-05p-lidocaine-with-epinephrine' => '0.5% Lidocaine with Epinephrine 1:200,000',
            'biopsy-05p-bupivicaine' => '0.5% Bupivicaine',
            'biopsy-05p-bupivicaine-wtih-epinephrine' => '0.5% Bupivicaine with Epinephrine 1:200,000',
            'biopsy-025p-bupivicaine' => '0.25% Bupivicaine',
            'biopsy-025p-bupivicaine-wtih-epinephrine' => '0.25% Bupivicaine with Epinephrine 1:400,000',
            'biopsy-025p-marcaine-wtih-epinephrine' => '0.25% Marcaine with Epinephrine 1:200,000',
            'biopsy-09p-bacteriostatic-sodium-chloride' => 'Bacteriostatic 0.9% Sodium Chloride',
            'biopsy-sterile-water' => 'Sterile Water',
            'biopsy-1p-ropivacaine' => 'Ropivacaine 1%',
            'biopsy-05p-ropivacaine' => 'Ropivacaine 0.5%',
            'biopsy-84p-sodium-bicarbonate' => 'With 8.4% Sodium Bicarbonate in 1:10 ratio',
        ];
        $anesthetics = $this->extractCheckboxGroup($treatment, $anestheticFields);
        if (!empty($anesthetics)) {
            $fields['anesthetics'] = $anesthetics;
        }
        if (!$this->isFieldEmpty($treatment, 'biopsy-anesthetic-free-text')) {
            $fields['anesthetic_other'] = $this->getField($treatment, 'biopsy-anesthetic-free-text');
        }

        // Anesthetic Volume (checkboxes)
        $volumeFields = [
            'biopsy-anesthetic-volume-1cc' => '<1cc',
            'biopsy-anesthetic-volume-1-2cc' => '1-2cc',
            'biopsy-anesthetic-volume-2-3cc' => '2-3cc',
            'biopsy-anesthetic-volume-3-4cc' => '3-4cc',
            'biopsy-anesthetic-volume-4-5cc' => '4-5cc',
        ];
        $volume = $this->extractCheckboxGroup($treatment, $volumeFields);
        if (!empty($volume)) {
            $fields['anesthetic_volume'] = $volume;
        }
        if (!$this->isFieldEmpty($treatment, 'biopsy-anesthetic-volume-free-text')) {
            $fields['anesthetic_volume_other'] = $this->getField($treatment, 'biopsy-anesthetic-volume-free-text');
        }

        // Hemostasis (checkboxes)
        $hemostasisFields = [
            'biopsy-aluminum-chloride' => 'Aluminum Chloride',
            'biopsy-electrodessication' => 'Electrodessication',
            'biopsy-electrofulguration' => 'Electrofulguration',
            'biopsy-electrocoagulation' => 'Electrocoagulation',
            'biopsy-electrocautery' => 'Electrocautery',
            'biopsy-hemostasis-none' => 'None',
        ];
        $hemostasis = $this->extractCheckboxGroup($treatment, $hemostasisFields);
        if (!empty($hemostasis)) {
            $fields['hemostasis'] = $hemostasis;
        }
        if (!$this->isFieldEmpty($treatment, 'biopsy-hemostasis-free-text')) {
            $fields['hemostasis_other'] = $this->getField($treatment, 'biopsy-hemostasis-free-text');
        }

        // Sutures (checkboxes)
        $sutureFields = [
            'biopsy-no-sutures-were-needed' => 'No Sutures Were Needed',
            'biopsy-3-0-nylon' => '3-0 Nylon',
            'biopsy-4-0-nylon' => '4-0 Nylon',
            'biopsy-5-0-nylon' => '5-0 Nylon',
            'biopsy-6-0-nylon' => '6-0 Nylon',
            'biopsy-3-0-prolene' => '3-0 Prolene',
            'biopsy-4-0-prolene' => '4-0 Prolene',
            'biopsy-5-0-prolene' => '5-0 Prolene',
            'biopsy-6-0-prolene' => '6-0 Prolene',
        ];
        $sutures = $this->extractCheckboxGroup($treatment, $sutureFields);
        if (!empty($sutures)) {
            $fields['sutures'] = $sutures;
        }
        if (!$this->isFieldEmpty($treatment, 'biopsy-Sutures-free-text')) {
            $fields['sutures_other'] = $this->getField($treatment, 'biopsy-Sutures-free-text');
        }

        // Aftercare Applied In-Office (checkboxes)
        $aftercareFields = [
            'biopsy-mupirocin' => 'Mupirocin',
            'biopsy-polysporin' => 'Polysporin',
            'biopsy-bacitracin' => 'Bacitracin',
            'biopsy-petrolatum' => 'Petrolatum',
        ];
        $aftercare = $this->extractCheckboxGroup($treatment, $aftercareFields);
        if (!empty($aftercare)) {
            $fields['aftercare'] = $aftercare;
        }
        if (!$this->isFieldEmpty($treatment, 'biopsy-aftercare_free_text')) {
            $fields['aftercare_other'] = $this->getField($treatment, 'biopsy-aftercare_free_text');
        }

        // Bandage (checkboxes)
        $bandageFields = [
            'biopsy-pressure-dressing-with-telfa-gauze-and-hypafix' => 'Pressure dressing with telfa, gauze and hypafix',
            'biopsy-pressure-dressing-with-telfa-gauze-and-papertape' => 'Pressure dressing with telfa, gauze and paper tape',
            'biopsy-regular-bandage' => 'Regular bandage',
            'biopsy-telfa' => 'Telfa',
            'biopsy-paper-tape' => 'Paper tape',
            'biopsy-hypafix' => 'Hypafix',
            'biopsy-hydrocolloid' => 'Hydrocolloid',
        ];
        $bandage = $this->extractCheckboxGroup($treatment, $bandageFields);
        if (!empty($bandage)) {
            $fields['bandage'] = $bandage;
        }
        if (!$this->isFieldEmpty($treatment, 'biopsy-bandage-free-text')) {
            $fields['bandage_other'] = $this->getField($treatment, 'biopsy-bandage-free-text');
        }

        return $fields;
    }

    public function formatForDisplay(array $fields): string
    {
        if (empty($fields)) {
            return '';
        }

        $sections = [];

        // Areas Treated
        if (isset($fields['areas'])) {
            $sections[] = $this->formatSection('Biopsy Location', $fields['areas']);
        }

        // Rule Out
        if (isset($fields['rule_out'])) {
            $sections[] = $this->formatSection('Rule Out', $fields['rule_out']);
        }

        // Biopsy Method
        if (isset($fields['methods'])) {
            $method = $this->formatFieldList($fields['methods']);
            if (isset($fields['method_other'])) {
                $method .= ', ' . $fields['method_other'];
            }
            $sections[] = $this->formatSection('Biopsy Method', $method);
        }

        // Biopsy Type
        if (isset($fields['types'])) {
            $type = $this->formatFieldList($fields['types']);
            if (isset($fields['type_other'])) {
                $type .= ', ' . $fields['type_other'];
            }
            $sections[] = $this->formatSection('Biopsy Type', $type);
        }

        // Differential Diagnosis
        if (isset($fields['differential_diagnosis'])) {
            $sections[] = $this->formatSection('Differential Diagnosis', $fields['differential_diagnosis']);
        }

        // Skin Prep
        if (isset($fields['skin_prep'])) {
            $prep = $this->formatFieldList($fields['skin_prep']);
            if (isset($fields['skin_prep_other'])) {
                $prep .= ', ' . $fields['skin_prep_other'];
            }
            $sections[] = $this->formatSection('Skin Prep', $prep);
        }

        // Anesthetic
        if (isset($fields['anesthetics'])) {
            $anesthetic = $this->formatFieldList($fields['anesthetics']);
            if (isset($fields['anesthetic_other'])) {
                $anesthetic .= ', ' . $fields['anesthetic_other'];
            }
            $sections[] = $this->formatSection('Anesthetic', $anesthetic);
        }

        // Anesthetic Volume
        if (isset($fields['anesthetic_volume'])) {
            $volume = $this->formatFieldList($fields['anesthetic_volume']);
            if (isset($fields['anesthetic_volume_other'])) {
                $volume .= ', ' . $fields['anesthetic_volume_other'];
            }
            $sections[] = $this->formatSection('Anesthetic Volume', $volume);
        }

        // Hemostasis
        if (isset($fields['hemostasis'])) {
            $hemostasis = $this->formatFieldList($fields['hemostasis']);
            if (isset($fields['hemostasis_other'])) {
                $hemostasis .= ', ' . $fields['hemostasis_other'];
            }
            $sections[] = $this->formatSection('Hemostasis', $hemostasis);
        }

        // Sutures
        if (isset($fields['sutures'])) {
            $sutures = $this->formatFieldList($fields['sutures']);
            if (isset($fields['sutures_other'])) {
                $sutures .= ', ' . $fields['sutures_other'];
            }
            $sections[] = $this->formatSection('Sutures', $sutures);
        }

        // Aftercare Applied In-Office
        if (isset($fields['aftercare'])) {
            $aftercare = $this->formatFieldList($fields['aftercare']);
            if (isset($fields['aftercare_other'])) {
                $aftercare .= ', ' . $fields['aftercare_other'];
            }
            $sections[] = $this->formatSection('Aftercare (Applied In-Office)', $aftercare);
        }

        // Bandage
        if (isset($fields['bandage'])) {
            $bandage = $this->formatFieldList($fields['bandage']);
            if (isset($fields['bandage_other'])) {
                $bandage .= ', ' . $fields['bandage_other'];
            }
            $sections[] = $this->formatSection('Bandage', $bandage);
        }

        return $this->wrapProcedureDetails($this->combineSections($sections));
    }
}
