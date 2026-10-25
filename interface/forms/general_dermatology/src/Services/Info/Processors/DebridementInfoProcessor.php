<?php
namespace GeneralDermatology\Services\Info\Processors;
use GeneralDermatology\Services\Info\AbstractProcedureInfoProcessor;

class DebridementInfoProcessor extends AbstractProcedureInfoProcessor
{
    public function extractFields(array $treatment): array
    {
        $skinPrepOptions = [
            'debridement-alcohol' => 'Alcohol',
            'debridement-hibiclens' => 'Hibiclens',
            'debridement-iodine' => 'Iodine',
            'debridement-lasercyn' => 'Lasercyn',
        ];
        
        $anestheticOptions = [
            'debridement-1p-lidocaine' => '1% Lidocaine',
            'debridement-1p-lidocaine-with-epinephrine' => '1% Lidocaine with Epinephrine 1:100,000',
            'debridement-2p-lidocaine' => '2% Lidocaine',
            'debridement-2p-lidocaine-wtih-epinephrine' => '2% Lidocaine with Epinephrine 1:100,000',
            'debridement-05p-lidocaine-with-epinephrine' => '0.5% Lidocaine with Epinephrine 1:200,000',
            'debridement-05p-bupivicaine' => '0.5% Bupivacaine',
            'debridement-05p-bupivicaine-wtih-epinephrine' => '0.5% Bupivacaine with Epinephrine 1:200,000',
            'debridement-025p-bupivicaine' => '0.25% Bupivacaine',
            'debridement-025p-bupivicaine-wtih-epinephrine' => '0.25% Bupivacaine with Epinephrine 1:400,000',
            'debridement-025p-marcaine-wtih-epinephrine' => '0.25% Marcaine with Epinephrine 1:200,000',
            'debridement-09p-bacteriostatic-sodium-chloride' => 'Bacteriostatic 0.9% Sodium Chloride',
            'debridement-sterile-water' => 'Sterile Water',
            'debridement-1p-ropivacaine' => 'Ropivacaine 1%',
            'debridement-05p-ropivacaine' => 'Ropivacaine 0.5%',
            'debridement-84p-sodium-bicarbonate' => 'WITH 8.4% Sodium Bicarbonate',
        ];
        
        $anestheticVolumeOptions = [
            'debridement-anesthetic-volume-1cc' => '<1cc',
            'debridement-anesthetic-volume-1-2cc' => '1-2cc',
            'debridement-anesthetic-volume-2-3cc' => '2-3cc',
            'debridement-anesthetic-volume-3-4cc' => '3-4cc',
            'debridement-anesthetic-volume-4-5cc' => '4-5cc',
        ];
        
        $methodOptions = [
            'debridement-mechanical' => 'Mechanical',
            'debridement-surgical' => 'Surgical',
            'debridement-autolytic' => 'Autolytic',
            'debridement-enzymatic' => 'Enzymatic',
        ];
        
        $aftercareOptions = [
            'debridement-mupirocin' => 'Mupirocin',
            'debridement-polysporin' => 'Polysporin',
            'debridement-bacitracin' => 'Bacitracin',
            'debridement-petrolatum' => 'Petrolatum',
        ];
        
        $bandageOptions = [
            'debridement-pressure-dressing-with-telfa-gauze-and-hypafix' => 'Pressure Dressing with Telfa, Gauze, Hypafix',
            'debridement-pressure-dressing-with-telfa-gauze-and-papertape' => 'Pressure Dressing with Telfa, Gauze, Paper Tape',
            'debridement-regular-bandage' => 'Regular Bandage',
            'debridement-telfa' => 'Telfa',
            'debridement-paper-tape' => 'Paper Tape',
            'debridement-hypafix' => 'Hypafix',
            'debridement-hydrocolloid' => 'Hydrocolloid',
        ];
        
        return [
            'skin_prep' => $this->extractCheckboxGroup($treatment, 'debridement', $skinPrepOptions),
            'skin_prep_free_text' => $this->getField($treatment, 'debridement-skin-prep-free-text'),
            'anesthetic' => $this->extractCheckboxGroup($treatment, 'debridement', $anestheticOptions),
            'anesthetic_free_text' => $this->getField($treatment, 'debridement-anesthetic-free-text'),
            'anesthetic_volume' => $this->extractCheckboxGroup($treatment, 'debridement', $anestheticVolumeOptions),
            'anesthetic_volume_free_text' => $this->getField($treatment, 'debridement-anesthetic-volume-free-text'),
            'areas_treated' => $this->getField($treatment, 'areas_debridement'),
            'number_of_lesions' => $this->getField($treatment, 'areas-count-debridement'),
            'method' => $this->extractCheckboxGroup($treatment, 'debridement', $methodOptions),
            'method_free_text' => $this->getField($treatment, 'debridement-method-free-text'),
            'aftercare' => $this->extractCheckboxGroup($treatment, 'debridement', $aftercareOptions),
            'aftercare_free_text' => $this->getField($treatment, 'debridement-aftercare_free_text'),
            'bandage' => $this->extractCheckboxGroup($treatment, 'debridement', $bandageOptions),
            'bandage_free_text' => $this->getField($treatment, 'debridement-bandage-free-text'),
        ];
    }

    public function formatForDisplay(array $fields): string
    {
        $sections = [];
        
        // Skin Prep Section
        $skinPrepHtml = '';
        if (!empty($fields['skin_prep'] ?? [])) {
            $skinPrepHtml .= $this->formatFieldList('Skin Prep Options', $fields['skin_prep']);
        }
        if (!$this->isFieldEmpty($fields['skin_prep_free_text'] ?? null)) {
            $skinPrepHtml .= $this->formatField('Skin Prep Notes', $fields['skin_prep_free_text']);
        }
        if ($skinPrepHtml) {
            $sections['Skin Preparation'] = $skinPrepHtml;
        }
        
        // Anesthetic Section
        $anestheticHtml = '';
        if (!empty($fields['anesthetic'] ?? [])) {
            $anestheticHtml .= $this->formatFieldList('Anesthetic', $fields['anesthetic']);
        }
        if (!$this->isFieldEmpty($fields['anesthetic_free_text'] ?? null)) {
            $anestheticHtml .= $this->formatField('Anesthetic Notes', $fields['anesthetic_free_text']);
        }
        if (!empty($fields['anesthetic_volume'] ?? [])) {
            $anestheticHtml .= $this->formatFieldList('Anesthetic Volume', $fields['anesthetic_volume']);
        }
        if (!$this->isFieldEmpty($fields['anesthetic_volume_free_text'] ?? null)) {
            $anestheticHtml .= $this->formatField('Volume Notes', $fields['anesthetic_volume_free_text']);
        }
        if ($anestheticHtml) {
            $sections['Anesthetic'] = $anestheticHtml;
        }
        
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
        
        // Method Section
        $methodHtml = '';
        if (!empty($fields['method'] ?? [])) {
            $methodHtml .= $this->formatFieldList('Debridement Method', $fields['method']);
        }
        if (!$this->isFieldEmpty($fields['method_free_text'] ?? null)) {
            $methodHtml .= $this->formatField('Method Notes', $fields['method_free_text']);
        }
        if ($methodHtml) {
            $sections['Method'] = $methodHtml;
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
        
        // Bandage Section
        $bandageHtml = '';
        if (!empty($fields['bandage'] ?? [])) {
            $bandageHtml .= $this->formatFieldList('Bandage', $fields['bandage']);
        }
        if (!$this->isFieldEmpty($fields['bandage_free_text'] ?? null)) {
            $bandageHtml .= $this->formatField('Bandage Notes', $fields['bandage_free_text']);
        }
        if ($bandageHtml) {
            $sections['Bandaging'] = $bandageHtml;
        }
        
        $html = $this->combineSections($sections);
        return $this->wrapProcedureDetails($html);
    }
}
