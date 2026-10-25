<?php
namespace GeneralDermatology\Services\Info\Processors;
use GeneralDermatology\Services\Info\AbstractProcedureInfoProcessor;

class PhotodynamicInfoProcessor extends AbstractProcedureInfoProcessor
{
    public function extractFields(array $treatment): array
    {
        $eyeProtectionOptions = [
            'photodynamic-goggles-with-gauze' => 'Goggles with Gauze',
            'photodynamic-metal-shields' => 'Metal Shields',
        ];
        
        $incubationTimeOptions = [
            'photodynamic-none' => 'None',
            'photodynamic-30-min' => '30 Min',
            'photodynamic-60-min' => '60 Min',
            'photodynamic-90-min' => '90 Min',
            'photodynamic-120-min' => '120 Min',
        ];
        
        $illuminationTimeOptions = [
            'photodynamic-16-min-40-sec' => '16 Min 40 Sec',
        ];
        
        $pdtAgentOptions = [
            'photodynamic-levulan' => 'Levulan',
            'photodynamic-ameluz' => 'Ameluz',
        ];
        
        $numberUsedOptions = [
            'photodynamic-1' => '1',
            'photodynamic-2' => '2',
        ];
        
        return [
            'areas_treated' => $this->getField($treatment, 'areas_photodynamic'),
            'eye_protection' => $this->extractCheckboxGroup($treatment, 'photodynamic', $eyeProtectionOptions),
            'eye_protection_free_text' => $this->getField($treatment, 'photodynamic-eye-protectoin-free-text'),
            'incubation_time' => $this->extractCheckboxGroup($treatment, 'photodynamic', $incubationTimeOptions),
            'incubation_time_free_text' => $this->getField($treatment, 'photodynamic-Incubation-Time-free-text'),
            'illumination_time' => $this->extractCheckboxGroup($treatment, 'photodynamic', $illuminationTimeOptions),
            'illumination_time_free_text' => $this->getField($treatment, 'photodynamic-Illumination-Time-free-text'),
            'pdt_agent' => $this->extractCheckboxGroup($treatment, 'photodynamic', $pdtAgentOptions),
            'pdt_agent_free_text' => $this->getField($treatment, 'photodynamic-PDT-agent-free-text'),
            'number_used' => $this->extractCheckboxGroup($treatment, 'photodynamic', $numberUsedOptions),
            'number_used_free_text' => $this->getField($treatment, 'photodynamic-Number-Used-free-text'),
            'lot_number' => $this->getField($treatment, 'photodynamic-Lot-free-text'),
            'expiration_date' => $this->getField($treatment, 'photodynamic-Expiration-Date-free-text'),
            'comments' => $this->getField($treatment, 'photodynamic-Comments-free-text'),
        ];
    }

    public function formatForDisplay(array $fields): string
    {
        $sections = [];
        
        // Treatment Areas Section
        $areasHtml = '';
        if (!$this->isFieldEmpty($fields['areas_treated'] ?? null)) {
            $areasHtml .= $this->formatField('Areas Treated', $fields['areas_treated']);
        }
        if ($areasHtml) {
            $sections['Treatment Areas'] = $areasHtml;
        }
        
        // Eye Protection Section
        $eyeProtectionHtml = '';
        if (!empty($fields['eye_protection'] ?? [])) {
            $eyeProtectionHtml .= $this->formatFieldList('Eye Protection', $fields['eye_protection']);
        }
        if (!$this->isFieldEmpty($fields['eye_protection_free_text'] ?? null)) {
            $eyeProtectionHtml .= $this->formatField('Eye Protection Notes', $fields['eye_protection_free_text']);
        }
        if ($eyeProtectionHtml) {
            $sections['Eye Protection'] = $eyeProtectionHtml;
        }
        
        // Incubation Time Section
        $incubationHtml = '';
        if (!empty($fields['incubation_time'] ?? [])) {
            $incubationHtml .= $this->formatFieldList('Incubation Time', $fields['incubation_time']);
        }
        if (!$this->isFieldEmpty($fields['incubation_time_free_text'] ?? null)) {
            $incubationHtml .= $this->formatField('Incubation Time Notes', $fields['incubation_time_free_text']);
        }
        if ($incubationHtml) {
            $sections['Incubation'] = $incubationHtml;
        }
        
        // Illumination Time Section
        $illuminationHtml = '';
        if (!empty($fields['illumination_time'] ?? [])) {
            $illuminationHtml .= $this->formatFieldList('Illumination Time', $fields['illumination_time']);
        }
        if (!$this->isFieldEmpty($fields['illumination_time_free_text'] ?? null)) {
            $illuminationHtml .= $this->formatField('Illumination Time Notes', $fields['illumination_time_free_text']);
        }
        if ($illuminationHtml) {
            $sections['Illumination'] = $illuminationHtml;
        }
        
        // PDT Agent Section
        $agentHtml = '';
        if (!empty($fields['pdt_agent'] ?? [])) {
            $agentHtml .= $this->formatFieldList('PDT Agent', $fields['pdt_agent']);
        }
        if (!$this->isFieldEmpty($fields['pdt_agent_free_text'] ?? null)) {
            $agentHtml .= $this->formatField('Agent Notes', $fields['pdt_agent_free_text']);
        }
        if ($agentHtml) {
            $sections['PDT Agent'] = $agentHtml;
        }
        
        // Number Used and Lot Information Section
        $numberLotHtml = '';
        if (!empty($fields['number_used'] ?? [])) {
            $numberLotHtml .= $this->formatFieldList('Number Used', $fields['number_used']);
        }
        if (!$this->isFieldEmpty($fields['number_used_free_text'] ?? null)) {
            $numberLotHtml .= $this->formatField('Number Used Notes', $fields['number_used_free_text']);
        }
        if (!$this->isFieldEmpty($fields['lot_number'] ?? null)) {
            $numberLotHtml .= $this->formatField('Lot Number', $fields['lot_number']);
        }
        if (!$this->isFieldEmpty($fields['expiration_date'] ?? null)) {
            $numberLotHtml .= $this->formatField('Expiration Date', $fields['expiration_date']);
        }
        if ($numberLotHtml) {
            $sections['Product Information'] = $numberLotHtml;
        }
        
        // Comments Section
        $commentsHtml = '';
        if (!$this->isFieldEmpty($fields['comments'] ?? null)) {
            $commentsHtml .= $this->formatField('Comments', $fields['comments']);
        }
        if ($commentsHtml) {
            $sections['Comments'] = $commentsHtml;
        }
        
        $html = $this->combineSections($sections);
        return $this->wrapProcedureDetails($html);
    }
}
