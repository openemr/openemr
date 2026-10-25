<?php
namespace GeneralDermatology\Services\Info\Processors;
use GeneralDermatology\Services\Info\AbstractProcedureInfoProcessor;

class CanThardinInfoProcessor extends AbstractProcedureInfoProcessor
{
    public function extractFields(array $treatment): array
    {
        return [
            'areas_treated' => $this->getField($treatment, 'areas_cantharidin'),
            'number_of_lesions' => $this->getField($treatment, 'areas-count-cantharidin'),
            'dressing' => $this->extractCheckboxGroup($treatment, 'cantharidin', ['cantharidin_none' => 'None', 'cantharidin_paper' => 'Paper Tape']),
            'dressing_free_text' => $this->getField($treatment, 'cantharidin-dressing-free-text'),
            'wash_time' => $this->extractCheckboxGroup($treatment, 'cantharidin', ['cantharidin_4h' => '4 Hours', 'cantharidin_6h' => '6 Hours']),
            'wash_time_free_text' => $this->getField($treatment, 'cantharidin_time_free_text'),
        ];
    }

    public function formatForDisplay(array $fields): string
    {
        $sections = [];
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
        
        $dressingHtml = '';
        if (!empty($fields['dressing'] ?? [])) {
            $dressingHtml .= $this->formatFieldList('Dressing', $fields['dressing']);
        }
        if (!$this->isFieldEmpty($fields['dressing_free_text'] ?? null)) {
            $dressingHtml .= $this->formatField('Dressing Notes', $fields['dressing_free_text']);
        }
        if ($dressingHtml) {
            $sections['Dressing'] = $dressingHtml;
        }
        
        $washHtml = '';
        if (!empty($fields['wash_time'] ?? [])) {
            $washHtml .= $this->formatFieldList('Wash Time', $fields['wash_time']);
        }
        if (!$this->isFieldEmpty($fields['wash_time_free_text'] ?? null)) {
            $washHtml .= $this->formatField('Wash Instructions', $fields['wash_time_free_text']);
        }
        if ($washHtml) {
            $sections['Aftercare Instructions'] = $washHtml;
        }
        
        $html = $this->combineSections($sections);
        return $this->wrapProcedureDetails($html);
    }
}
