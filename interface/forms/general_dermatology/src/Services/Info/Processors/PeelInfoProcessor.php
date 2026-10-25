<?php
namespace GeneralDermatology\Services\Info\Processors;
use GeneralDermatology\Services\Info\AbstractProcedureInfoProcessor;

class PeelInfoProcessor extends AbstractProcedureInfoProcessor
{
    public function extractFields(array $treatment): array
    {
        $anestheticOptions = [
            'peel-none' => 'None',
            'peel-topical-5-lidocaine' => 'Topical 5% Lidocaine',
            'peel-topical-23-lidocaine-7-tetracaine' => 'Topical 23% Lidocaine 7% Tetracaine',
            'peel-topical-23-lidocaine-7-tetracaine-1-phenylephrine' => 'Topical 23% Lidocaine 7% Tetracaine 1% Phenylephrine',
            'peel-topical-20-benzocaine-10-lidocaine-4-tetracaine' => 'Topical 20% Benzocaine 10% Lidocaine 4% Tetracaine',
            'peel-topical-30-lidocaine' => 'Topical 30% Lidocaine',
            'peel-topical-30-lidocaine-7-tetracaine' => 'Topical 30% Lidocaine 7% Tetracaine',
            'peel-ice' => 'Ice',
            'peel-fan' => 'Fan',
            'peel-vibration' => 'Vibration',
        ];
        
        $preOpMedsOptions = [
            'peel-none-meds' => 'None',
        ];
        
        $prepOptions = [
            'peel-acetone' => 'Acetone',
            'peel-petrolatum-for-protection-of-mucous-membranes' => 'Petrolatum For Protection of Mucous Membranes',
        ];
        
        $endpointOptions = [
            'peel-none-ep' => 'None',
            'peel-frosting' => 'Frosting',
            'peel-erythema' => 'Erythema',
        ];
        
        $aftercareOptions = [
            'peel-spf' => 'SPF',
            'peel-petrolatum' => 'Petrolatum',
            'peel-triamcinolone-0-1p-ointment' => 'Triamcinolone 0.1% Ointment',
            'peel-hydrocortisone-2-5p-ointment' => 'Hydrocortisone 2.5% Ointment',
        ];
        
        return [
            'peel_type' => $this->getField($treatment, 'peel-Peel-free-text'),
            'areas_treated' => $this->getField($treatment, 'areas_peel'),
            'treatment_number' => $this->getField($treatment, 'peel-Treatment-free-text'),
            'anesthetic' => $this->extractCheckboxGroup($treatment, 'peel', $anestheticOptions),
            'anesthetic_free_text' => $this->getField($treatment, 'peel-anesthetic-free-text'),
            'pre_op_meds' => $this->extractCheckboxGroup($treatment, 'peel', $preOpMedsOptions),
            'pre_op_meds_free_text' => $this->getField($treatment, 'peel-pre-op-meds-free-text'),
            'prep' => $this->extractCheckboxGroup($treatment, 'peel', $prepOptions),
            'prep_free_text' => $this->getField($treatment, 'peel-Prep-free-text'),
            'passes' => $this->getField($treatment, 'peel-Passes-free-text'),
            'treatment_time' => $this->getField($treatment, 'peel-Treatment-time-free-text'),
            'endpoint' => $this->extractCheckboxGroup($treatment, 'peel', $endpointOptions),
            'endpoint_free_text' => $this->getField($treatment, 'peel-end-point-free-text'),
            'aftercare' => $this->extractCheckboxGroup($treatment, 'peel', $aftercareOptions),
            'aftercare_free_text' => $this->getField($treatment, 'peel-aftercare_free_text'),
            'notes' => $this->getField($treatment, 'peel-Notes-free-text'),
        ];
    }

    public function formatForDisplay(array $fields): string
    {
        $sections = [];
        
        // Peel Information Section
        $peelHtml = '';
        if (!$this->isFieldEmpty($fields['peel_type'] ?? null)) {
            $peelHtml .= $this->formatField('Peel Type', $fields['peel_type']);
        }
        if (!$this->isFieldEmpty($fields['treatment_number'] ?? null)) {
            $peelHtml .= $this->formatField('Treatment Number', $fields['treatment_number']);
        }
        if ($peelHtml) {
            $sections['Peel Information'] = $peelHtml;
        }
        
        // Treatment Areas Section
        $areasHtml = '';
        if (!$this->isFieldEmpty($fields['areas_treated'] ?? null)) {
            $areasHtml .= $this->formatField('Areas Treated', $fields['areas_treated']);
        }
        if ($areasHtml) {
            $sections['Treatment Areas'] = $areasHtml;
        }
        
        // Anesthetic Section
        $anestheticHtml = '';
        if (!empty($fields['anesthetic'] ?? [])) {
            $anestheticHtml .= $this->formatFieldList('Anesthetic', $fields['anesthetic']);
        }
        if (!$this->isFieldEmpty($fields['anesthetic_free_text'] ?? null)) {
            $anestheticHtml .= $this->formatField('Anesthetic Notes', $fields['anesthetic_free_text']);
        }
        if ($anestheticHtml) {
            $sections['Anesthetic'] = $anestheticHtml;
        }
        
        // Pre-operative Section
        $preOpHtml = '';
        if (!empty($fields['pre_op_meds'] ?? [])) {
            $preOpHtml .= $this->formatFieldList('Pre-op Medications', $fields['pre_op_meds']);
        }
        if (!$this->isFieldEmpty($fields['pre_op_meds_free_text'] ?? null)) {
            $preOpHtml .= $this->formatField('Pre-op Med Notes', $fields['pre_op_meds_free_text']);
        }
        if (!empty($fields['prep'] ?? [])) {
            $preOpHtml .= $this->formatFieldList('Prep', $fields['prep']);
        }
        if (!$this->isFieldEmpty($fields['prep_free_text'] ?? null)) {
            $preOpHtml .= $this->formatField('Prep Notes', $fields['prep_free_text']);
        }
        if ($preOpHtml) {
            $sections['Pre-operative'] = $preOpHtml;
        }
        
        // Treatment Parameters Section
        $parametersHtml = '';
        if (!$this->isFieldEmpty($fields['passes'] ?? null)) {
            $parametersHtml .= $this->formatField('Number of Passes', $fields['passes']);
        }
        if (!$this->isFieldEmpty($fields['treatment_time'] ?? null)) {
            $parametersHtml .= $this->formatField('Treatment Time', $fields['treatment_time']);
        }
        if ($parametersHtml) {
            $sections['Treatment Parameters'] = $parametersHtml;
        }
        
        // Endpoint Section
        $endpointHtml = '';
        if (!empty($fields['endpoint'] ?? [])) {
            $endpointHtml .= $this->formatFieldList('Endpoint', $fields['endpoint']);
        }
        if (!$this->isFieldEmpty($fields['endpoint_free_text'] ?? null)) {
            $endpointHtml .= $this->formatField('Endpoint Notes', $fields['endpoint_free_text']);
        }
        if ($endpointHtml) {
            $sections['Endpoint'] = $endpointHtml;
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
        
        // Notes Section
        $notesHtml = '';
        if (!$this->isFieldEmpty($fields['notes'] ?? null)) {
            $notesHtml .= $this->formatField('Notes', $fields['notes']);
        }
        if ($notesHtml) {
            $sections['Notes'] = $notesHtml;
        }
        
        $html = $this->combineSections($sections);
        return $this->wrapProcedureDetails($html);
    }
}
