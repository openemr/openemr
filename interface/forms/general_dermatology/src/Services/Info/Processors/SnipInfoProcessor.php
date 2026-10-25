<?php
namespace GeneralDermatology\Services\Info\Processors;
use GeneralDermatology\Services\Info\AbstractProcedureInfoProcessor;

class SnipInfoProcessor extends AbstractProcedureInfoProcessor
{
    public function extractFields(array $treatment): array
    {
        $skinPrepOptions = [
            'snip-alcohol' => 'Alcohol',
            'snip-hibiclens' => 'Hibiclens',
            'snip-iodine' => 'Iodine',
            'snip-lasercyn' => 'Lasercyn',
        ];
        
        $anestheticOptions = [
            'snip-none' => 'None',
            'snip-topical-5p-lidocaine' => 'Topical 5% Lidocaine',
            'snip-topical-23p-lidocaine-7p-tetracaine' => 'Topical 23% Lidocaine, 7% Tetracaine',
            'snip-topical-23p-lidocaine-7p-tetracaine-1p-phenylephrine' => 'Topical 23% Lidocaine, 7% Tetracaine, 1% Phenylephrine',
            'snip-topical-20p-benzocaine-10p-lidocaine-4p-tetracaine' => 'Topical 20% Benzocaine, 10% Lidocaine, 4% Tetracaine',
            'snip-topical-30p-lidocaine' => 'Topical 30% Lidocaine',
            'snip-topical-30-lidocaine-7-tetracaine' => 'Topical 30% Lidocaine, 7% Tetracaine',
            'snip-ice' => 'Ice',
            'snip-fan' => 'Fan',
            'snip-vibration' => 'Vibration',
            'snip-1p-lidocaine' => '1% Lidocaine',
            'snip-1p-lidocaine-with-epinephrine' => '1% Lidocaine with Epinephrine 1:100,000',
            'snip-2p-lidocaine' => '2% Lidocaine',
            'snip-2p-lidocaine-wtih-epinephrine' => '2% Lidocaine with Epinephrine 1:100,000',
            'snip-05p-lidocaine-with-epinephrine' => '0.5% Lidocaine with Epinephrine 1:200,000',
            'snip-05p-bupivicaine' => '0.5% Bupivacaine',
            'snip-05p-bupivicaine-wtih-epinephrine' => '0.5% Bupivacaine with Epinephrine 1:200,000',
            'snip-025p-bupivicaine' => '0.25% Bupivacaine',
            'snip-025p-bupivicaine-wtih-epinephrine' => '0.25% Bupivacaine with Epinephrine 1:400,000',
            'snip-025p-marcaine-wtih-epinephrine' => '0.25% Marcaine with Epinephrine 1:200,000',
            'snip-09p-bacteriostatic-sodium-chloride' => 'Bacteriostatic 0.9% Sodium Chloride',
            'snip-sterile-water' => 'Sterile Water',
            'snip-1p-ropivacaine' => 'Ropivacaine 1%',
            'snip-05p-ropivacaine' => 'Ropivacaine 0.5%',
            'snip-84p-sodium-bicarbonate' => 'WITH 8.4% Sodium Bicarbonate in 1:10 ratio',
        ];
        
        $volumeOptions = [
            'snip-anesthetic-volume-1cc' => '<1cc',
            'snip-anesthetic-volume-1-2cc' => '1-2cc',
            'snip-anesthetic-volume-2-3cc' => '2-3cc',
            'snip-anesthetic-volume-3-4cc' => '3-4cc',
            'snip-anesthetic-volume-4-5cc' => '4-5cc',
        ];
        
        $methodOptions = [
            'snip-pickups-and-tissue-scissors' => 'Pickups and Tissue Scissors',
        ];
        
        $lesionTypeOptions = [
            'snip-skin-tags' => 'Skin Tags',
            'snip-dermatosis-papulosa-nigra' => 'Dermatosis Papulosa Nigra',
            'snip-cherry-angiomas' => 'Cherry Angiomas',
        ];
        
        $aftercareOptions = [
            'snip-spf' => 'SPF',
            'snip-petrolatum' => 'Petrolatum',
            'snip-triamcinolone-0-1p-ointment' => 'Triamcinolone 0.1% Ointment',
            'snip-hydrocortisone-2-5p-ointment' => 'Hydrocortisone 2.5% Ointment',
        ];
        
        return [
            'areas_treated' => $this->getField($treatment, 'areas_snip'),
            'number_of_lesions' => $this->getField($treatment, 'areas-count-snip'),
            'treatment_number' => $this->getField($treatment, 'snip-Treatment-free-text'),
            'skin_prep' => $this->extractCheckboxGroup($treatment, 'snip', $skinPrepOptions),
            'skin_prep_free_text' => $this->getField($treatment, 'snip-skin-prep-free-text'),
            'anesthetic' => $this->extractCheckboxGroup($treatment, 'snip', $anestheticOptions),
            'anesthetic_free_text' => $this->getField($treatment, 'snip-anesthetic-free-text'),
            'anesthetic_volume' => $this->extractCheckboxGroup($treatment, 'snip', $volumeOptions),
            'anesthetic_volume_free_text' => $this->getField($treatment, 'snip-anesthetic-volume-free-text'),
            'method' => $this->extractCheckboxGroup($treatment, 'snip', $methodOptions),
            'method_free_text' => $this->getField($treatment, 'snip-method-free-text'),
            'lesion_type' => $this->extractCheckboxGroup($treatment, 'snip', $lesionTypeOptions),
            'lesion_type_free_text' => $this->getField($treatment, 'snip-type-of-lesion-treated-free-text'),
            'aftercare' => $this->extractCheckboxGroup($treatment, 'snip', $aftercareOptions),
            'aftercare_free_text' => $this->getField($treatment, 'snip-aftercare_free_text'),
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
        if (!$this->isFieldEmpty($fields['treatment_number'] ?? null)) {
            $areasHtml .= $this->formatField('Treatment Number', $fields['treatment_number']);
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
        
        // Anesthetic Section
        $anestheticHtml = '';
        if (!empty($fields['anesthetic'] ?? [])) {
            $anestheticHtml .= $this->formatFieldList('Anesthetic', $fields['anesthetic']);
        }
        if (!$this->isFieldEmpty($fields['anesthetic_free_text'] ?? null)) {
            $anestheticHtml .= $this->formatField('Notes', $fields['anesthetic_free_text']);
        }
        if (!empty($fields['anesthetic_volume'] ?? [])) {
            $anestheticHtml .= $this->formatFieldList('Volume', $fields['anesthetic_volume']);
        }
        if (!$this->isFieldEmpty($fields['anesthetic_volume_free_text'] ?? null)) {
            $anestheticHtml .= $this->formatField('Volume Notes', $fields['anesthetic_volume_free_text']);
        }
        if ($anestheticHtml) {
            $sections['Anesthetic'] = $anestheticHtml;
        }
        
        // Method Section
        $methodHtml = '';
        if (!empty($fields['method'] ?? [])) {
            $methodHtml .= $this->formatFieldList('Method', $fields['method']);
        }
        if (!$this->isFieldEmpty($fields['method_free_text'] ?? null)) {
            $methodHtml .= $this->formatField('Method Details', $fields['method_free_text']);
        }
        if ($methodHtml) {
            $sections['Method'] = $methodHtml;
        }
        
        // Lesion Type Section
        $lesionHtml = '';
        if (!empty($fields['lesion_type'] ?? [])) {
            $lesionHtml .= $this->formatFieldList('Lesion Type', $fields['lesion_type']);
        }
        if (!$this->isFieldEmpty($fields['lesion_type_free_text'] ?? null)) {
            $lesionHtml .= $this->formatField('Other Types', $fields['lesion_type_free_text']);
        }
        if ($lesionHtml) {
            $sections['Lesion Type'] = $lesionHtml;
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
        
        $html = $this->combineSections($sections);
        return $this->wrapProcedureDetails($html);
    }
}
