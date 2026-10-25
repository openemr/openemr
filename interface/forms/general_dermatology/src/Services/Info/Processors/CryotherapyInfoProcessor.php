<?php

namespace GeneralDermatology\Services\Info\Processors;

use GeneralDermatology\Services\Info\AbstractProcedureInfoProcessor;

/**
 * CryotherapyInfoProcessor
 *
 * Extracts and formats cryotherapy (liquid nitrogen) procedure information.
 */
class CryotherapyInfoProcessor extends AbstractProcedureInfoProcessor
{
    public function extractFields(array $treatment): array
    {
        $fields = [];

        // Cryotherapy form type
        $formType = [];
        if ($this->getCheckboxField($treatment, 'cryotherapy_high_pressure_canister')) {
            $formType[] = 'High-Pressure Canister';
        }
        if ($this->getCheckboxField($treatment, 'cryotherapy_cotton_swab')) {
            $formType[] = 'Cotton Swab';
        }
        $fields['form_type'] = $formType;
        $fields['form_type_free_text'] = $this->getField($treatment, 'cryotherapy_cryotherapy_free_text');

        // Areas treated
        $fields['areas_treated'] = $this->getField($treatment, 'areas_cryotherapy');
        $fields['lesion_count'] = $this->getField($treatment, 'areas-count-cryotherapy');

        // Aftercare
        $aftercareOptions = [
            'cryotherapy_spf' => 'SPF',
            'cryotherapy_petrolatum' => 'Petrolatum',
            'cryotherapy_triamcinolone-0-1p-ointment' => 'Triamcinolone 0.1% Ointment',
            'cryotherapy_hydrocortisone-2-5p-ointment' => 'Hydrocortisone 2.5% Ointment',
        ];
        $fields['aftercare'] = $this->extractCheckboxGroup($treatment, $aftercareOptions);
        $fields['aftercare_free_text'] = $this->getField($treatment, 'cryotherapy_aftercare_free_text');

        return $fields;
    }

    public function formatForDisplay(array $fields): string
    {
        $sections = [];

        // Form type section
        $formType = '';
        if (!empty($fields['form_type'] ?? [])) {
            $formType .= $this->formatFieldList('Cryotherapy Form', $fields['form_type']);
        }
        if (!$this->isFieldEmpty($fields['form_type_free_text'] ?? null)) {
            $formType .= $this->formatField('Additional Notes', $fields['form_type_free_text']);
        }
        if ($formType) {
            $sections['Form Type'] = $formType;
        }

        // Areas section
        $areas = '';
        if (!$this->isFieldEmpty($fields['areas_treated'] ?? null)) {
            $areas .= $this->formatField('Areas Treated', $fields['areas_treated']);
        }
        if (!$this->isFieldEmpty($fields['lesion_count'] ?? null)) {
            $areas .= $this->formatField('Number of Lesions Treated', $fields['lesion_count']);
        }
        if ($areas) {
            $sections['Treatment Areas'] = $areas;
        }

        // Aftercare section
        $aftercare = '';
        if (!empty($fields['aftercare'] ?? [])) {
            $aftercare .= $this->formatFieldList('Aftercare (Applied In-Office)', $fields['aftercare']);
        }
        if (!$this->isFieldEmpty($fields['aftercare_free_text'] ?? null)) {
            $aftercare .= $this->formatField('Aftercare Notes', $fields['aftercare_free_text']);
        }
        if ($aftercare) {
            $sections['Aftercare'] = $aftercare;
        }

        $html = $this->combineSections($sections);
        return $this->wrapProcedureDetails($html);
    }
}
