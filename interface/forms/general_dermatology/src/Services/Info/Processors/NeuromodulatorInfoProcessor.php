<?php

namespace GeneralDermatology\Services\Info\Processors;

use GeneralDermatology\Services\Info\AbstractProcedureInfoProcessor;

class NeuromodulatorInfoProcessor extends AbstractProcedureInfoProcessor
{
    public function extractFields(array $treatment): array
    {
        $fields = [];

        // Injectable type (checkboxes)
        $injectableFields = [
            'neuromodulator-botox' => 'Botox',
            'neuromodulator-dysport' => 'Dysport',
            'neuromodulator-xeomin' => 'Xeomin',
            'neuromodulator-jeuveau' => 'Jeuveau',
            'neuromodulator-daxxify' => 'Daxxify',
        ];
        $injectables = $this->extractCheckboxGroup($treatment, $injectableFields);
        if (!empty($injectables)) {
            $fields['injectables'] = $injectables;
        }
        if (!$this->isFieldEmpty($treatment, 'neuromodulator-injectable-free-text')) {
            $fields['injectable_other'] = $this->getField($treatment, 'neuromodulator-injectable-free-text');
        }

        // Treatment areas with amounts (checkboxes and text inputs)
        $treatmentAreas = [
            'neuromodulator-glabella' => 'Glabella',
            'neuromodulator-frontalis' => 'Frontalis',
            'neuromodulator-crows-feet-orbicularis-oculi' => 'Crow\'s Feet (Orbicularis Oculi)',
            'neuromodulator-bunny-lines-nasalis' => 'Bunny Lines (Nasalis)',
            'neuromodulator-jelly-roll-pretarsal-orbicularis' => 'Jelly Roll (Pretarsal Orbicularis)',
            'neuromodulator-chin-mentalis' => 'Chin (Mentalis)',
            'neuromodulator-masseters' => 'Masseters',
            'neuromodulator-lip-flip-orbicularis-oris' => 'Lip Flip (Orbicularis Oris)',
            'neuromodulator-smokers-lines-orbicularis-oris' => 'Smoker\'s Lines (Orbicularis Oris)',
            'neuromodulator-gummy-smile-llsan' => 'Gummy Smile (LLSAN)',
            'neuromodulator-depressor-anguli-oris' => 'Depressor Anguli Oris',
            'neuromodulator-depressor-septi-nasi' => 'Depressor Septi Nasi',
            'neuromodulator-nefirtiti-lift-platysma' => 'Nefirtiti Lift (Platysma)',
            'neuromodulator-neck-bands-platysma' => 'Neck Bands (Platysma)',
            'neuromodulator-hyperhidrosis-axillae' => 'Hyperhidrosis (Axillae)',
            'neuromodulator-hyperhidrosis-palms' => 'Hyperhidrosis (Palms)',
            'neuromodulator-hyperhidrosis-soles' => 'Hyperhidrosis (Soles)',
            'neuromodulator-hyperhidrosis-scalp' => 'Hyperhidrosis (Scalp)',
            'neuromodulator-temporalis' => 'Temporalis',
            'neuromodulator-occipitalis' => 'Occipitalis',
            'neuromodulator-cervical-paraspinal' => 'Cervical Paraspinal',
        ];

        $treatmentAreasData = [];
        foreach ($treatmentAreas as $checkboxId => $areaLabel) {
            if ($this->getCheckboxField($treatment, $checkboxId)) {
                $amountId = str_replace($checkboxId, 'neuromodulator-inner-' . substr($checkboxId, 18), $checkboxId);
                $amountId = 'neuromodulator-inner-' . substr($checkboxId, 18) . '-free-text';
                $amount = $this->getField($treatment, $amountId);
                $treatmentAreasData[$areaLabel] = $amount ?: 'Selected';
            }
        }

        // Custom treatment areas
        for ($i = 1; $i <= 3; $i++) {
            $titleId = 'neuromodulator-treatment-' . $i . '-free-text';
            $amountId = 'neuromodulator-inner-treatment-' . $i . '-free-text';
            if (!$this->isFieldEmpty($treatment, $titleId)) {
                $title = $this->getField($treatment, $titleId);
                $amount = $this->getField($treatment, $amountId);
                if (!empty($title)) {
                    $treatmentAreasData[$title] = $amount ?: 'Applied';
                }
            }
        }

        if (!empty($treatmentAreasData)) {
            $fields['treatment_areas'] = $treatmentAreasData;
        }

        // Anesthetic (checkboxes)
        $anestheticFields = [
            'neuromodulator-none' => 'None',
            'neuromodulator-topical-5-lidocaine' => 'Topical 5% Lidocaine',
            'neuromodulator-topical-23-lidocaine-7-tetracaine' => 'Topical 23% Lidocaine, 7% Tetracaine',
            'neuromodulator-topical-23-lidocaine-7-tetracaine-1-phenylephrine' => 'Topical 23% Lidocaine, 7% Tetracaine, 1% Phenylephrine',
            'neuromodulator-topical-20-benzocaine-10-lidocaine-4-tetracaine' => 'Topical 20% Benzocaine, 10% Lidocaine, 4% Tetracaine',
            'neuromodulator-topical-30-lidocaine' => 'Topical 30% Lidocaine',
            'neuromodulator-topical-30-lidocaine-7-tetracaine' => 'Topical 30% Lidocaine, 7% Tetracaine',
            'neuromodulator-ice' => 'Ice',
            'neuromodulator-fan' => 'Fan',
            'neuromodulator-vibration' => 'Vibration',
        ];
        $anesthetics = $this->extractCheckboxGroup($treatment, $anestheticFields);
        if (!empty($anesthetics)) {
            $fields['anesthetics'] = $anesthetics;
        }
        if (!$this->isFieldEmpty($treatment, 'neuromodulator-anesthetic-free-text')) {
            $fields['anesthetic_other'] = $this->getField($treatment, 'neuromodulator-anesthetic-free-text');
        }

        // Dilution (checkboxes)
        $dilutionFields = [
            'neuromodulator-1cc' => '1 CC',
            'neuromodulator-2cc' => '2 CC',
            'neuromodulator-2-5cc' => '2.5 CC',
        ];
        $dilution = $this->extractCheckboxGroup($treatment, $dilutionFields);
        if (!empty($dilution)) {
            $fields['dilution'] = $dilution;
        }
        if (!$this->isFieldEmpty($treatment, 'neuromodulator-dilution-free-text')) {
            $fields['dilution_other'] = $this->getField($treatment, 'neuromodulator-dilution-free-text');
        }

        // Diluent (checkboxes)
        $diluentFields = [
            'neuromodulator-09p-bacteriostatic-sodium-chloride' => 'Bacteriostatic 0.9% Sodium Chloride',
            'neuromodulator-09p-sodium-chloride' => '0.9% Sodium Chloride',
            'neuromodulator-sterile-water' => 'Sterile Water',
            'neuromodulator-1p-lidocaine' => '1% Lidocaine',
            'neuromodulator-1p-lidocaine-with-epinephrine' => '1% Lidocaine With Epinephrine 1:100,000',
        ];
        $diluents = $this->extractCheckboxGroup($treatment, $diluentFields);
        if (!empty($diluents)) {
            $fields['diluents'] = $diluents;
        }
        if (!$this->isFieldEmpty($treatment, 'neuromodulator-diluent-free-text')) {
            $fields['diluent_other'] = $this->getField($treatment, 'neuromodulator-diluent-free-text');
        }

        // Total Units
        if (!$this->isFieldEmpty($treatment, 'neuromodulator-units-free-text')) {
            $fields['total_units'] = $this->getField($treatment, 'neuromodulator-units-free-text');
        }

        // Lot #
        if (!$this->isFieldEmpty($treatment, 'neuromodulator-lot-free-text')) {
            $fields['lot_number'] = $this->getField($treatment, 'neuromodulator-lot-free-text');
        }

        // Expiration
        if (!$this->isFieldEmpty($treatment, 'neuromodulator-expiration-free-text')) {
            $fields['expiration'] = $this->getField($treatment, 'neuromodulator-expiration-free-text');
        }

        // Aftercare (checkboxes)
        $aftercareFields = [
            'neuromodulator-spf' => 'SPF',
            'neuromodulator-petrolatum' => 'Petrolatum',
            'neuromodulator-triamcinolone-0-1p-ointment' => 'Triamcinolone 0.1% Ointment',
            'neuromodulator-hydrocortisone-2-5p-ointment' => 'Hydrocortisone 2.5% Ointment',
        ];
        $aftercare = $this->extractCheckboxGroup($treatment, $aftercareFields);
        if (!empty($aftercare)) {
            $fields['aftercare'] = $aftercare;
        }
        if (!$this->isFieldEmpty($treatment, 'neuromodulator-aftercare_free_text')) {
            $fields['aftercare_other'] = $this->getField($treatment, 'neuromodulator-aftercare_free_text');
        }

        return $fields;
    }

    public function formatForDisplay(array $fields): string
    {
        if (empty($fields)) {
            return '';
        }

        $sections = [];

        // Injectable
        if (isset($fields['injectables'])) {
            $injectable = $this->formatFieldList($fields['injectables']);
            if (isset($fields['injectable_other'])) {
                $injectable .= ', ' . $fields['injectable_other'];
            }
            $sections[] = $this->formatSection('Injectable', $injectable);
        }

        // Treatment Areas
        if (isset($fields['treatment_areas'])) {
            $areaList = [];
            foreach ($fields['treatment_areas'] as $area => $amount) {
                $areaList[] = $area . ': ' . $this->escapeText($amount);
            }
            $sections[] = $this->formatSection('Treatment Areas', implode('; ', $areaList));
        }

        // Anesthetic
        if (isset($fields['anesthetics'])) {
            $anesthetic = $this->formatFieldList($fields['anesthetics']);
            if (isset($fields['anesthetic_other'])) {
                $anesthetic .= ', ' . $fields['anesthetic_other'];
            }
            $sections[] = $this->formatSection('Anesthetic', $anesthetic);
        }

        // Dilution
        if (isset($fields['dilution'])) {
            $dilution = $this->formatFieldList($fields['dilution']);
            if (isset($fields['dilution_other'])) {
                $dilution .= ', ' . $fields['dilution_other'];
            }
            $sections[] = $this->formatSection('Dilution', $dilution);
        }

        // Diluent
        if (isset($fields['diluents'])) {
            $diluent = $this->formatFieldList($fields['diluents']);
            if (isset($fields['diluent_other'])) {
                $diluent .= ', ' . $fields['diluent_other'];
            }
            $sections[] = $this->formatSection('Diluent', $diluent);
        }

        // Total Units
        if (isset($fields['total_units'])) {
            $sections[] = $this->formatSection('Total Units', $fields['total_units']);
        }

        // Lot #
        if (isset($fields['lot_number'])) {
            $sections[] = $this->formatSection('Lot #', $fields['lot_number']);
        }

        // Expiration
        if (isset($fields['expiration'])) {
            $sections[] = $this->formatSection('Expiration', $fields['expiration']);
        }

        // Aftercare
        if (isset($fields['aftercare'])) {
            $aftercare = $this->formatFieldList($fields['aftercare']);
            if (isset($fields['aftercare_other'])) {
                $aftercare .= ', ' . $fields['aftercare_other'];
            }
            $sections[] = $this->formatSection('Aftercare (Applied In-Office)', $aftercare);
        }

        return $this->wrapProcedureDetails($this->combineSections($sections));
    }
}
