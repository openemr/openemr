<?php

namespace GeneralDermatology\Services\Info\Processors;

use GeneralDermatology\Services\Info\AbstractProcedureInfoProcessor;

class SclerotherapyInfoProcessor extends AbstractProcedureInfoProcessor
{
    public function extractFields(array $treatment): array
    {
        $fields = [];

        if (!$this->isFieldEmpty($treatment, 'areas_sclerotherapy')) {
            $fields['areas_treated'] = $this->getField($treatment, 'areas_sclerotherapy');
        }

        $skinPrepFields = [
            'sclerotherapy-alcohol' => 'Alcohol',
            'sclerotherapy-hibiclens' => 'Hibiclens',
            'sclerotherapy-iodine' => 'Iodine',
            'sclerotherapy-lasercyn' => 'Lasercyn',
        ];
        $skinPrep = $this->extractCheckboxGroup($treatment, $skinPrepFields);
        if (!empty($skinPrep)) {
            $fields['skin_prep'] = $skinPrep;
        }
        if (!$this->isFieldEmpty($treatment, 'sclerotherapy-skin-prep-free-text')) {
            $fields['skin_prep_other'] = $this->getField($treatment, 'sclerotherapy-skin-prep-free-text');
        }

        $solutionFields = [
            'sclerotherapy-0-25-polidocanol' => '0.25% Polidocanol',
            'sclerotherapy-0-5-polidocanol' => '0.5% Polidocanol',
            'sclerotherapy-1-polidocanol' => '1% Polidocanol',
            'sclerotherapy-2-polidocanol' => '2% Polidocanol',
            'sclerotherapy-3-polidocanol' => '3% Polidocanol',
            'sclerotherapy-4-polidocanol' => '4% Polidocanol',
            'sclerotherapy-0-2-sts' => '0.2% Sodium Tetradecyl Sulfate',
            'sclerotherapy-0-5-sts' => '0.5% Sodium Tetradecyl Sulfate',
            'sclerotherapy-1-sts' => '1% Sodium Tetradecyl Sulfate',
            'sclerotherapy-3-sts' => '3% Sodium Tetradecyl Sulfate',
            'sclerotherapy-hypertonic-saline' => 'Hypertonic Saline',
        ];
        $solutions = $this->extractCheckboxGroup($treatment, $solutionFields);
        if (!empty($solutions)) {
            $fields['solution_used'] = $solutions;
        }
        if (!$this->isFieldEmpty($treatment, 'sclerotherapy-Solution-Used-free-text')) {
            $fields['solution_other'] = $this->getField($treatment, 'sclerotherapy-Solution-Used-free-text');
        }

        $foamRatioFields = [
            'sclerotherapy-1-2' => '1:2',
            'sclerotherapy-1-3' => '1:3',
            'sclerotherapy-1-4' => '1:4',
            'sclerotherapy-1-5' => '1:5',
        ];
        $foamRatio = $this->extractCheckboxGroup($treatment, $foamRatioFields);
        if (!empty($foamRatio)) {
            $fields['foam_ratio'] = $foamRatio;
        }
        if (!$this->isFieldEmpty($treatment, 'sclerotherapy-Foam-Ratio-free-text')) {
            $fields['foam_ratio_other'] = $this->getField($treatment, 'sclerotherapy-Foam-Ratio-free-text');
        }

        if (!$this->isFieldEmpty($treatment, 'sclerotherapy-cc-used-free-text')) {
            $fields['cc_used'] = $this->getField($treatment, 'sclerotherapy-cc-used-free-text');
        }
        if (!$this->isFieldEmpty($treatment, 'sclerotherapy-Lot-number-free-text')) {
            $fields['lot_number'] = $this->getField($treatment, 'sclerotherapy-Lot-number-free-text');
        }
        if (!$this->isFieldEmpty($treatment, 'sclerotherapy-Expiration-free-text')) {
            $fields['expiration'] = $this->getField($treatment, 'sclerotherapy-Expiration-free-text');
        }

        $bandageFields = [
            'sclerotherapy-paper-tape-and-cotton-balls' => 'Paper Tape and Cotton Balls',
            'sclerotherapy-compression-stockings' => 'Compression Stockings',
        ];
        $bandage = $this->extractCheckboxGroup($treatment, $bandageFields);
        if (!empty($bandage)) {
            $fields['bandage'] = $bandage;
        }
        if (!$this->isFieldEmpty($treatment, 'sclerotherapy-Bandage-free-text')) {
            $fields['bandage_other'] = $this->getField($treatment, 'sclerotherapy-Bandage-free-text');
        }

        return $fields;
    }

    public function formatForDisplay(array $fields): string
    {
        if (empty($fields)) {
            return '';
        }

        $sections = [];

        if (isset($fields['areas_treated'])) {
            $sections[] = $this->formatSection('Area(s) Treated', $fields['areas_treated']);
        }

        if (isset($fields['skin_prep'])) {
            $prep = $this->formatFieldList($fields['skin_prep']);
            if (isset($fields['skin_prep_other'])) {
                $prep .= ', ' . $fields['skin_prep_other'];
            }
            $sections[] = $this->formatSection('Skin Prep', $prep);
        }

        if (isset($fields['solution_used'])) {
            $solution = $this->formatFieldList($fields['solution_used']);
            if (isset($fields['solution_other'])) {
                $solution .= ', ' . $fields['solution_other'];
            }
            $sections[] = $this->formatSection('Solution Used', $solution);
        }

        if (isset($fields['foam_ratio'])) {
            $ratio = $this->formatFieldList($fields['foam_ratio']);
            if (isset($fields['foam_ratio_other'])) {
                $ratio .= ', ' . $fields['foam_ratio_other'];
            }
            $sections[] = $this->formatSection('Foam Ratio (liquid:air)', $ratio);
        }

        if (isset($fields['cc_used'])) {
            $sections[] = $this->formatSection('CC\'s Used', $fields['cc_used']);
        }
        if (isset($fields['lot_number'])) {
            $sections[] = $this->formatSection('Lot Number', $fields['lot_number']);
        }
        if (isset($fields['expiration'])) {
            $sections[] = $this->formatSection('Expiration', $fields['expiration']);
        }

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
