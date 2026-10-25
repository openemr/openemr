<?php

namespace GeneralDermatology\Services\Info\Processors;

use GeneralDermatology\Services\Info\AbstractProcedureInfoProcessor;

class FillerInfoProcessor extends AbstractProcedureInfoProcessor
{
    public function extractFields(array $treatment): array
    {
        $fields = [];

        // Injectable type (checkboxes)
        $injectableFields = [
            'filler-restylane' => 'Restylane',
            'filler-restylane-silk' => 'Restylane Silk',
            'filler-restylane-eyelight' => 'Restylane Eyelight',
            'filler-restylane-refyne' => 'Restylane Refyne',
            'filler-restylane-defyne' => 'Restylane Defyne',
            'filler-restylane-contour' => 'Restylane Contour',
            'filler-restylane-lyft' => 'Restylane Lyft',
            'filler-restylane-kysse' => 'Restylane Kysse',
            'filler-juvederm-ultra-xc' => 'Juvederm Ultra XC',
            'filler-juvederm-ultra-plus-xc' => 'Juvederm Ultra Plus XC',
            'filler-juvederm-voluma-xc' => 'Juvederm Voluma XC',
            'filler-juvederm-volbella-xc' => 'Juvederm Volbella XC',
            'filler-juvederm-vollure-xc' => 'Juvederm Vollure XC',
            'filler-juvederm-volux-xc' => 'Juvederm Volux XC',
            'filler-sculptra' => 'Sculptra',
        ];
        $injectables = $this->extractCheckboxGroup($treatment, $injectableFields);
        if (!empty($injectables)) {
            $fields['injectables'] = $injectables;
        }
        if (!$this->isFieldEmpty($treatment, 'filler-injectable-free-text')) {
            $fields['injectable_other'] = $this->getField($treatment, 'filler-injectable-free-text');
        }

        // Treatment areas (checkboxes)
        $treatmentFields = [
            'filler-cheek' => 'Cheek',
            'filler-lips' => 'Lips',
            'filler-jawline' => 'Jawline',
            'filler-nasolabial-folds' => 'Nasolabial Folds',
            'filler-temples' => 'Temples',
            'filler-chin' => 'Chin',
            'filler-tear-troughs' => 'Tear Troughs',
            'filler-nose' => 'Nose',
            'filler-marionette-lines' => 'Marionette Lines',
            'filler-earlobes' => 'Earlobes',
            'filler-hands' => 'Hands',
            'filler-chest' => 'Chest',
            'filler-brows' => 'Brows',
            'filler-buttocks' => 'Buttocks',
            'filler-thighs' => 'Thighs',
            'filler-scars' => 'Scars',
        ];
        $treatments = $this->extractCheckboxGroup($treatment, $treatmentFields);
        if (!empty($treatments)) {
            $fields['treatment_areas'] = $treatments;
        }
        if (!$this->isFieldEmpty($treatment, 'filler-treatment-free-text')) {
            $fields['treatment_area_other'] = $this->getField($treatment, 'filler-treatment-free-text');
        }

        // Anesthetic (checkboxes)
        $anestheticFields = [
            'filler-none' => 'None',
            'filler-topical-5-lidocaine' => 'Topical 5% Lidocaine',
            'filler-topical-23-lidocaine-7-tetracaine' => 'Topical 23% Lidocaine, 7% Tetracaine',
            'filler-topical-23-lidocaine-7-tetracaine-1-phenylephrine' => 'Topical 23% Lidocaine, 7% Tetracaine, 1% Phenylephrine',
            'filler-topical-20-benzocaine-10-lidocaine-4-tetracaine' => 'Topical 20% Benzocaine, 10% Lidocaine, 4% Tetracaine',
            'filler-topical-30-lidocaine' => 'Topical 30% Lidocaine',
            'filler-topical-30-lidocaine-7-tetracaine' => 'Topical 30% Lidocaine, 7% Tetracaine',
            'filler-1-percent-lidocaine' => '1% Lidocaine',
            'filler-1-percent-lidocaine-with-epinephrine-1-100-000' => '1% Lidocaine With Epinephrine 1:100,000',
            'filler-2-percent-lidocaine' => '2% Lidocaine',
            'filler-2-percent-lidocaine-with-epinephrine-1-100-000' => '2% Lidocaine With Epinephrine 1:100,000',
            'filler-ice' => 'Ice',
            'filler-fan' => 'Fan',
            'filler-vibration' => 'Vibration',
        ];
        $anesthetics = $this->extractCheckboxGroup($treatment, $anestheticFields);
        if (!empty($anesthetics)) {
            $fields['anesthetics'] = $anesthetics;
        }
        if (!$this->isFieldEmpty($treatment, 'filler-anesthetic-free-text')) {
            $fields['anesthetic_other'] = $this->getField($treatment, 'filler-anesthetic-free-text');
        }

        // Dilution (standard filler - checkboxes)
        $dilutionFields = [
            'filler-dilution-1-1' => '1:1',
            'filler-dilution-1-2' => '1:2',
            'filler-dilution-1-3' => '1:3',
            'filler-dilution-1-4' => '1:4',
        ];
        $dilution = $this->extractCheckboxGroup($treatment, $dilutionFields);
        if (!empty($dilution)) {
            $fields['dilution'] = $dilution;
        }
        if (!$this->isFieldEmpty($treatment, 'filler-dilution-free-text')) {
            $fields['dilution_other'] = $this->getField($treatment, 'filler-dilution-free-text');
        }

        // Sculptra Dilution (special Sculptra options)
        $sculptraDilutionFields = [
            'filler-8-cc-sterile-water-1-cc-2-percent-lidocaine' => '8cc Sterile Water, 1cc 2% Lidocaine',
            'filler-8-cc-sterile-water-1-cc-1-percent-lidocaine' => '8cc Sterile Water, 1cc 1% Lidocaine',
            'filler-7-cc-sterile-water-2-cc-2-percent-lidocaine' => '7cc Sterile Water, 2cc 2% Lidocaine',
            'filler-7-cc-sterile-water-2-cc-2-percent-lidocaine-duplicate' => '7cc Sterile Water, 2cc 1% Lidocaine',
        ];
        $sculptraDilution = $this->extractCheckboxGroup($treatment, $sculptraDilutionFields);
        if (!empty($sculptraDilution)) {
            $fields['sculptra_dilution'] = $sculptraDilution;
        }
        if (!$this->isFieldEmpty($treatment, 'filler-Sculptra-Dilution-free-text')) {
            $fields['sculptra_dilution_other'] = $this->getField($treatment, 'filler-Sculptra-Dilution-free-text');
        }

        // Diluent (checkboxes)
        $diluentFields = [
            'filler-09p-bacteriostatic-sodium-chloride' => 'Bacteriostatic 0.9% Sodium Chloride',
            'filler-09p-sodium-chloride' => '0.9% Sodium Chloride',
            'filler-sterile-water' => 'Sterile Water',
            'filler-1p-lidocaine' => '1% Lidocaine',
            'filler-1p-lidocaine-with-epinephrine' => '1% Lidocaine With Epinephrine 1:100,000',
            'filler-2p-lidocaine' => '2% Lidocaine',
            'filler-2p-lidocaine-with-epinephrine-1-100-000' => '2% Lidocaine With Epinephrine 1:100,000',
        ];
        $diluents = $this->extractCheckboxGroup($treatment, $diluentFields);
        if (!empty($diluents)) {
            $fields['diluents'] = $diluents;
        }
        if (!$this->isFieldEmpty($treatment, 'filler-diluent-free-text')) {
            $fields['diluent_other'] = $this->getField($treatment, 'filler-diluent-free-text');
        }

        // CC Amount (checkboxes)
        $ccFields = [
            'filler-05cc' => '0.5 CC',
            'filler-1cc' => '1 CC',
            'filler-2cc' => '2 CC',
            'filler-3cc' => '3 CC',
            'filler-4cc' => '4 CC',
            'filler-5cc' => '5 CC',
            'filler-9cc' => '9 CC',
        ];
        $cc = $this->extractCheckboxGroup($treatment, $ccFields);
        if (!empty($cc)) {
            $fields['cc_amount'] = $cc;
        }
        if (!$this->isFieldEmpty($treatment, 'filler-cc-free-text')) {
            $fields['cc_amount_other'] = $this->getField($treatment, 'filler-cc-free-text');
        }

        // Cannula size (checkboxes)
        $cannulaFields = [
            'filler-cannula-18' => '18',
            'filler-cannula-20' => '20',
            'filler-cannula-22' => '22',
            'filler-cannula-25' => '25',
            'filler-cannula-27' => '27',
        ];
        $cannula = $this->extractCheckboxGroup($treatment, $cannulaFields);
        if (!empty($cannula)) {
            $fields['cannula_size'] = $cannula;
        }

        // Lot #
        if (!$this->isFieldEmpty($treatment, 'filler-lot-free-text')) {
            $fields['lot_number'] = $this->getField($treatment, 'filler-lot-free-text');
        }

        // Expiration
        if (!$this->isFieldEmpty($treatment, 'filler-expiration-free-text')) {
            $fields['expiration'] = $this->getField($treatment, 'filler-expiration-free-text');
        }

        // Aftercare (checkboxes)
        $aftercareFields = [
            'filler-spf' => 'SPF',
            'filler-petrolatum' => 'Petrolatum',
            'filler-triamcinolone-0-1p-ointment' => 'Triamcinolone 0.1% Ointment',
            'filler-hydrocortisone-2-5p-ointment' => 'Hydrocortisone 2.5% Ointment',
        ];
        $aftercare = $this->extractCheckboxGroup($treatment, $aftercareFields);
        if (!empty($aftercare)) {
            $fields['aftercare'] = $aftercare;
        }
        if (!$this->isFieldEmpty($treatment, 'filler-aftercare_free_text')) {
            $fields['aftercare_other'] = $this->getField($treatment, 'filler-aftercare_free_text');
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
            $area = $this->formatFieldList($fields['treatment_areas']);
            if (isset($fields['treatment_area_other'])) {
                $area .= ', ' . $fields['treatment_area_other'];
            }
            $sections[] = $this->formatSection('Treatment Areas', $area);
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
            $sections[] = $this->formatSection('Dilution (Filler: Diluent)', $dilution);
        }

        // Sculptra Dilution
        if (isset($fields['sculptra_dilution'])) {
            $sculptraDilution = $this->formatFieldList($fields['sculptra_dilution']);
            if (isset($fields['sculptra_dilution_other'])) {
                $sculptraDilution .= ', ' . $fields['sculptra_dilution_other'];
            }
            $sections[] = $this->formatSection('Sculptra Dilution', $sculptraDilution);
        }

        // Diluent
        if (isset($fields['diluents'])) {
            $diluent = $this->formatFieldList($fields['diluents']);
            if (isset($fields['diluent_other'])) {
                $diluent .= ', ' . $fields['diluent_other'];
            }
            $sections[] = $this->formatSection('Diluent', $diluent);
        }

        // CC Amount
        if (isset($fields['cc_amount'])) {
            $cc = $this->formatFieldList($fields['cc_amount']);
            if (isset($fields['cc_amount_other'])) {
                $cc .= ', ' . $fields['cc_amount_other'];
            }
            $sections[] = $this->formatSection('CC Amount', $cc);
        }

        // Cannula Size
        if (isset($fields['cannula_size'])) {
            $sections[] = $this->formatSection('Cannula Size', $this->formatFieldList($fields['cannula_size']));
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
