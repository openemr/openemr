<?php
namespace GeneralDermatology\Services\Info\Processors;
use GeneralDermatology\Services\Info\AbstractProcedureInfoProcessor;

class SilverInfoProcessor extends AbstractProcedureInfoProcessor
{
    public function extractFields(array $treatment): array
    {
        $skinPrepOptions = [
            'silver-alcohol' => 'Alcohol',
            'silver-hibiclens' => 'Hibiclens',
            'silver-iodine' => 'Iodine',
            'silver-lasercyn' => 'Lasercyn',
        ];
        
        $postOpEffectsOptions = [
            'silver-no-change' => 'No Change',
            'silver-erythema' => 'Erythema',
            'silver-bleeding' => 'Bleeding',
            'silver-epidermal-damage' => 'Epidermal Damage',
            'silver-swelling' => 'Swelling',
            'silver-blisters' => 'Blisters',
            'silver-pain' => 'Pain',
        ];
        
        $aftercareOptions = [
            'silver-mupirocin' => 'Mupirocin',
            'silver-polysporin' => 'Polysporin',
            'silver-bacitracin' => 'Bacitracin',
            'silver-petrolatum' => 'Petrolatum',
        ];
        
        $bandageOptions = [
            'silver-pressure-dressing-with-telfa-gauze-and-hypafix' => 'Pressure dressing with telfa, gauze and hypafix',
            'silver-pressure-dressing-with-telfa-gauze-and-papertape' => 'Pressure dressing with telfa, gauze and paper tape',
            'silver-regular-bandage' => 'Regular bandage',
            'silver-telfa' => 'Telfa',
            'silver-paper-tape' => 'Paper tape',
            'silver-hypafix' => 'Hypafix',
            'silver-hydrocolloid' => 'Hydrocolloid',
        ];
        
        return [
            'areas_treated' => $this->getField($treatment, 'areas_silver'),
            'number_of_lesions' => $this->getField($treatment, 'areas-count-silver'),
            'skin_prep' => $this->extractCheckboxGroup($treatment, 'silver', $skinPrepOptions),
            'skin_prep_free_text' => $this->getField($treatment, 'silver-skin-prep-free-text'),
            'post_op_effects' => $this->extractCheckboxGroup($treatment, 'silver', $postOpEffectsOptions),
            'post_op_effects_free_text' => $this->getField($treatment, 'silver-post-op-effects-free-text'),
            'aftercare' => $this->extractCheckboxGroup($treatment, 'silver', $aftercareOptions),
            'aftercare_free_text' => $this->getField($treatment, 'silver-aftercare_free_text'),
            'bandage' => $this->extractCheckboxGroup($treatment, 'silver', $bandageOptions),
            'bandage_free_text' => $this->getField($treatment, 'silver-bandage-free-text'),
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
        if (!$this->isFieldEmpty($fields['number_of_lesions'] ?? null)) {
            $areasHtml .= $this->formatField('Number of Lesions', $fields['number_of_lesions']);
        }
        if ($areasHtml) {
            $sections['Treatment Areas'] = $areasHtml;
        }
        
        // Skin Preparation Section
        $skinPrepHtml = '';
        if (!empty($fields['skin_prep'] ?? [])) {
            $skinPrepHtml .= $this->formatFieldList('Skin Prep', $fields['skin_prep']);
        }
        if (!$this->isFieldEmpty($fields['skin_prep_free_text'] ?? null)) {
            $skinPrepHtml .= $this->formatField('Notes', $fields['skin_prep_free_text']);
        }
        if ($skinPrepHtml) {
            $sections['Skin Preparation'] = $skinPrepHtml;
        }
        
        // Post-operative Effects Section
        $postOpHtml = '';
        if (!empty($fields['post_op_effects'] ?? [])) {
            $postOpHtml .= $this->formatFieldList('Effects Observed', $fields['post_op_effects']);
        }
        if (!$this->isFieldEmpty($fields['post_op_effects_free_text'] ?? null)) {
            $postOpHtml .= $this->formatField('Notes', $fields['post_op_effects_free_text']);
        }
        if ($postOpHtml) {
            $sections['Post-operative Effects'] = $postOpHtml;
        }
        
        // Aftercare Section
        $aftercareHtml = '';
        if (!empty($fields['aftercare'] ?? [])) {
            $aftercareHtml .= $this->formatFieldList('Aftercare (Applied In-Office)', $fields['aftercare']);
        }
        if (!$this->isFieldEmpty($fields['aftercare_free_text'] ?? null)) {
            $aftercareHtml .= $this->formatField('Notes', $fields['aftercare_free_text']);
        }
        if ($aftercareHtml) {
            $sections['Aftercare'] = $aftercareHtml;
        }
        
        // Bandage Section
        $bandageHtml = '';
        if (!empty($fields['bandage'] ?? [])) {
            $bandageHtml .= $this->formatFieldList('Bandage Type', $fields['bandage']);
        }
        if (!$this->isFieldEmpty($fields['bandage_free_text'] ?? null)) {
            $bandageHtml .= $this->formatField('Notes', $fields['bandage_free_text']);
        }
        if ($bandageHtml) {
            $sections['Bandage'] = $bandageHtml;
        }
        
        $html = $this->combineSections($sections);
        return $this->wrapProcedureDetails($html);
    }
}
