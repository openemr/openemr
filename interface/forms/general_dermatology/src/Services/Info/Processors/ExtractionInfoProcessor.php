<?php
namespace GeneralDermatology\Services\Info\Processors;
use GeneralDermatology\Services\Info\AbstractProcedureInfoProcessor;

class ExtractionInfoProcessor extends AbstractProcedureInfoProcessor
{
    public function extractFields(array $treatment): array
    {
        $skinPrepOptions = [
            'extraction-alcohol' => 'Alcohol',
            'extraction-hibiclens' => 'Hibiclens',
            'extraction-iodine' => 'Iodine',
            'extraction-lasercyn' => 'Lasercyn',
        ];
        
        $anestheticOptions = [
            'extraction-none' => 'None',
            'extraction-topical-5p-lidocaine' => 'Topical 5% Lidocaine',
            'extraction-topical-23p-lidocaine-7p-tetracaine' => 'Topical 23% Lidocaine, 7% Tetracaine',
            'extraction-topical-23p-lidocaine-7p-tetracaine-1p-phenylephrine' => 'Topical 23% Lidocaine, 7% Tetracaine, 1% Phenylephrine',
            'extraction-topical-20p-benzocaine-10p-lidocaine-4p-tetracaine' => 'Topical 20% Benzocaine, 10% Lidocaine, 4% Tetracaine',
            'extraction-topical-30p-lidocaine' => 'Topical 30% Lidocaine',
            'extraction-topical-30-lidocaine-7-tetracaine' => 'Topical 30% Lidocaine, 7% Tetracaine',
            'extraction-ice' => 'Ice',
            'extraction-fan' => 'Fan',
            'extraction-vibration' => 'Vibration',
            'extraction-1p-lidocaine' => '1% Lidocaine',
            'extraction-1p-lidocaine-with-epinephrine' => '1% Lidocaine with Epinephrine 1:100,000',
            'extraction-2p-lidocaine' => '2% Lidocaine',
            'extraction-2p-lidocaine-wtih-epinephrine' => '2% Lidocaine with Epinephrine 1:100,000',
            'extraction-05p-lidocaine-with-epinephrine' => '0.5% Lidocaine with Epinephrine 1:200,000',
            'extraction-05p-bupivicaine' => '0.5% Bupivacaine',
            'extraction-05p-bupivicaine-wtih-epinephrine' => '0.5% Bupivacaine with Epinephrine 1:200,000',
            'extraction-025p-bupivicaine' => '0.25% Bupivacaine',
            'extraction-025p-bupivicaine-wtih-epinephrine' => '0.25% Bupivacaine with Epinephrine 1:400,000',
            'extraction-025p-marcaine-wtih-epinephrine' => '0.25% Marcaine with Epinephrine 1:200,000',
            'extraction-09p-bacteriostatic-sodium-chloride' => 'Bacteriostatic 0.9% Sodium Chloride',
            'extraction-sterile-water' => 'Sterile Water',
            'extraction-1p-ropivacaine' => 'Ropivacaine 1%',
            'extraction-05p-ropivacaine' => 'Ropivacaine 0.5%',
            'extraction-84p-sodium-bicarbonate' => 'WITH 8.4% Sodium Bicarbonate',
        ];
        
        $anestheticVolumeOptions = [
            'extraction-anesthetic-volume-1cc' => '<1cc',
            'extraction-anesthetic-volume-1-2cc' => '1-2cc',
            'extraction-anesthetic-volume-2-3cc' => '2-3cc',
            'extraction-anesthetic-volume-3-4cc' => '3-4cc',
            'extraction-anesthetic-volume-4-5cc' => '4-5cc',
        ];
        
        $methodOptions = [
            'extraction-comedone-extractor' => 'Comedone Extractor',
            'extraction-q-tips' => 'Q-Tips',
            'extraction-power-extraction' => 'Power Extraction',
        ];
        
        $aftercareOptions = [
            'extraction-spf' => 'SPF',
            'extraction-petrolatum' => 'Petrolatum',
            'extraction-triamcinolone-0-1p-ointment' => 'Triamcinolone 0.1% Ointment',
            'extraction-hydrocortisone-2-5p-ointment' => 'Hydrocortisone 2.5% Ointment',
        ];
        
        return [
            'skin_prep' => $this->extractCheckboxGroup($treatment, 'extraction', $skinPrepOptions),
            'skin_prep_free_text' => $this->getField($treatment, 'extraction-skin-prep-free-text'),
            'anesthetic' => $this->extractCheckboxGroup($treatment, 'extraction', $anestheticOptions),
            'anesthetic_free_text' => $this->getField($treatment, 'extraction-anesthetic-free-text'),
            'anesthetic_volume' => $this->extractCheckboxGroup($treatment, 'extraction', $anestheticVolumeOptions),
            'anesthetic_volume_free_text' => $this->getField($treatment, 'extraction-anesthetic-volume-free-text'),
            'areas_treated' => $this->getField($treatment, 'areas_extraction'),
            'number_of_lesions' => $this->getField($treatment, 'areas-count-extraction'),
            'extraction_method' => $this->extractCheckboxGroup($treatment, 'extraction', $methodOptions),
            'extraction_method_free_text' => $this->getField($treatment, 'extraction-extraction-method-free-text'),
            'aftercare' => $this->extractCheckboxGroup($treatment, 'extraction', $aftercareOptions),
            'aftercare_free_text' => $this->getField($treatment, 'extraction-aftercare_free_text'),
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
        
        // Extraction Method Section
        $methodHtml = '';
        if (!empty($fields['extraction_method'] ?? [])) {
            $methodHtml .= $this->formatFieldList('Extraction Method', $fields['extraction_method']);
        }
        if (!$this->isFieldEmpty($fields['extraction_method_free_text'] ?? null)) {
            $methodHtml .= $this->formatField('Method Notes', $fields['extraction_method_free_text']);
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
        
        $html = $this->combineSections($sections);
        return $this->wrapProcedureDetails($html);
    }
}
