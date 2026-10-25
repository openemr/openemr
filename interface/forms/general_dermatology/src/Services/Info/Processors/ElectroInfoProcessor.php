<?php
namespace GeneralDermatology\Services\Info\Processors;
use GeneralDermatology\Services\Info\AbstractProcedureInfoProcessor;

class ElectroInfoProcessor extends AbstractProcedureInfoProcessor
{
    public function extractFields(array $treatment): array
    {
        $skinPrepFields = [
            'electro-alcohol' => 'Alcohol',
            'electro-hibiclens' => 'Hibiclens',
            'electro-iodine' => 'Iodine',
            'electro-lasercyn' => 'Lasercyn',
        ];
        
        $anestheticFields = [
            'electro-1p-lidocaine' => '1% Lidocaine',
            'electro-1p-lidocaine-with-epinephrine' => '1% Lidocaine with Epinephrine 1:100,000',
            'electro-2p-lidocaine' => '2% Lidocaine',
            'electro-2p-lidocaine-wtih-epinephrine' => '2% Lidocaine with Epinephrine 1:100,000',
            'electro-05p-lidocaine-with-epinephrine' => '0.5% Lidocaine with Epinephrine 1:200,000',
            'electro-05p-bupivicaine' => '0.5% Bupivicaine',
            'electro-05p-bupivicaine-wtih-epinephrine' => '0.5% Bupivicaine with Epinephrine 1:200,000',
            'electro-025p-bupivicaine' => '0.25% Bupivicaine',
            'electro-025p-bupivicaine-wtih-epinephrine' => '0.25% Bupivicaine with Epinephrine 1:400,000',
            'electro-025p-marcaine-wtih-epinephrine' => '0.25% Marcaine with Epinephrine 1:200,000',
            'electro-09p-bacteriostatic-sodium-chloride' => 'Bacteriostatic 0.9% Sodium Chloride',
            'electro-sterile-water' => 'Sterile Water',
            'electro-1p-ropivacaine' => 'Ropivacaine 1%',
            'electro-05p-ropivacaine' => 'Ropivacaine 0.5%',
            'electro-84p-sodium-bicarbonate' => 'With 8.4% Sodium Bicarbonate in 1:10 ratio',
        ];
        
        $volumeFields = [
            'electro-anesthetic-volume-1cc' => '<1cc',
            'electro-anesthetic-volume-1-2cc' => '1-2cc',
            'electro-anesthetic-volume-2-3cc' => '2-3cc',
            'electro-anesthetic-volume-3-4cc' => '3-4cc',
            'electro-anesthetic-volume-4-5cc' => '4-5cc',
        ];
        
        $curetteFields = [
            'electro-2mm' => '2mm',
            'electro-3mm' => '3mm',
            'electro-4mm' => '4mm',
            'electro-5mm' => '5mm',
            'electro-6mm' => '6mm',
            'electro-7mm' => '7mm',
            'electro-8mm' => '8mm',
        ];
        
        $passFields = [
            'electro-1' => '1',
            'electro-2' => '2',
            'electro-3' => '3',
            'electro-4' => '4',
        ];
        
        $aftercareFields = [
            'electro-mupirocin' => 'Mupirocin',
            'electro-polysporin' => 'Polysporin',
            'electro-bacitracin' => 'Bacitracin',
            'electro-petrolatum' => 'Petrolatum',
        ];
        
        $bandageFields = [
            'electro-pressure-dressing-with-telfa-gauze-and-hypafix' => 'Pressure dressing with telfa, gauze and hypafix',
            'electro-pressure-dressing-with-telfa-gauze-and-papertape' => 'Pressure dressing with telfa, gauze and paper tape',
            'electro-regular-bandage' => 'Regular bandage',
            'electro-telfa' => 'Telfa',
            'electro-paper-tape' => 'Paper tape',
            'electro-hypafix' => 'Hypafix',
            'electro-hydrocolloid' => 'Hydrocolloid',
        ];
        
        return [
            'skin_prep' => $this->extractCheckboxGroup($treatment, 'electro', $skinPrepFields),
            'skin_prep_other' => $this->getField($treatment, 'electro-freeS'),
            'anesthetics' => $this->extractCheckboxGroup($treatment, 'electro', $anestheticFields),
            'anesthetic_other' => $this->getField($treatment, 'electro-anesthetic-free-text'),
            'anesthetic_volume' => $this->extractCheckboxGroup($treatment, 'electro', $volumeFields),
            'anesthetic_volume_other' => $this->getField($treatment, 'electro-anesthetic-volume-free-text'),
            'areas_treated' => $this->getField($treatment, 'areas_electro'),
            'curette_size' => $this->extractCheckboxGroup($treatment, 'electro', $curetteFields),
            'curette_size_other' => $this->getField($treatment, 'electro-curette-size-free-text'),
            'passes' => $this->extractCheckboxGroup($treatment, 'electro', $passFields),
            'pre_op_size' => $this->getField($treatment, 'electro-pre-op-size-free-text'),
            'margins' => $this->getField($treatment, 'electro-margins-free-text'),
            'post_op_size' => $this->getField($treatment, 'electro-post-op-size-free-text'),
            'aftercare' => $this->extractCheckboxGroup($treatment, 'electro', $aftercareFields),
            'aftercare_other' => $this->getField($treatment, 'electro-aftercare_free_text'),
            'bandage' => $this->extractCheckboxGroup($treatment, 'electro', $bandageFields),
            'bandage_other' => $this->getField($treatment, 'electro-bandage-free-text'),
        ];
    }

    public function formatForDisplay(array $fields): string
    {
        if (empty($fields)) {
            return '';
        }
        
        $sections = [];
        
        // Skin Prep Section
        if (!empty($fields['skin_prep'] ?? [])) {
            $html = $this->formatFieldList('Skin Prep', $fields['skin_prep']);
            if (!$this->isFieldEmpty($fields['skin_prep_other'] ?? null)) {
                $html .= $this->formatField('Additional Notes', $fields['skin_prep_other']);
            }
            $sections['Skin Preparation'] = $html;
        }
        
        // Anesthetic Section
        if (!empty($fields['anesthetics'] ?? [])) {
            $html = $this->formatFieldList('Anesthetic', $fields['anesthetics']);
            if (!$this->isFieldEmpty($fields['anesthetic_other'] ?? null)) {
                $html .= $this->formatField('Anesthetic Notes', $fields['anesthetic_other']);
            }
            if (!empty($fields['anesthetic_volume'] ?? [])) {
                $html .= $this->formatFieldList('Volume', $fields['anesthetic_volume']);
            }
            if (!$this->isFieldEmpty($fields['anesthetic_volume_other'] ?? null)) {
                $html .= $this->formatField('Volume Notes', $fields['anesthetic_volume_other']);
            }
            $sections['Anesthetic'] = $html;
        }
        
        // Treatment Areas Section
        if (!$this->isFieldEmpty($fields['areas_treated'] ?? null)) {
            $sections['Treatment Areas'] = $this->formatField('Area(s) Treated', $fields['areas_treated']);
        }
        
        // Curette Size Section
        if (!empty($fields['curette_size'] ?? [])) {
            $html = $this->formatFieldList('Curette Size', $fields['curette_size']);
            if (!$this->isFieldEmpty($fields['curette_size_other'] ?? null)) {
                $html .= $this->formatField('Custom Size', $fields['curette_size_other']);
            }
            $sections['Curette'] = $html;
        }
        
        // ED&C Passes Section
        if (!empty($fields['passes'] ?? [])) {
            $sections['ED&C'] = $this->formatFieldList('Number of Passes', $fields['passes']);
        }
        
        // Size Measurements Section
        $sizeHtml = '';
        if (!$this->isFieldEmpty($fields['pre_op_size'] ?? null)) {
            $sizeHtml .= $this->formatField('Pre-op Size', $fields['pre_op_size']);
        }
        if (!$this->isFieldEmpty($fields['margins'] ?? null)) {
            $sizeHtml .= $this->formatField('Margins', $fields['margins']);
        }
        if (!$this->isFieldEmpty($fields['post_op_size'] ?? null)) {
            $sizeHtml .= $this->formatField('Post-op Size', $fields['post_op_size']);
        }
        if ($sizeHtml) {
            $sections['Size Measurements'] = $sizeHtml;
        }
        
        // Aftercare Section
        if (!empty($fields['aftercare'] ?? [])) {
            $html = $this->formatFieldList('Aftercare (Applied In-Office)', $fields['aftercare']);
            if (!$this->isFieldEmpty($fields['aftercare_other'] ?? null)) {
                $html .= $this->formatField('Aftercare Notes', $fields['aftercare_other']);
            }
            $sections['Aftercare'] = $html;
        }
        
        // Bandage Section
        if (!empty($fields['bandage'] ?? [])) {
            $html = $this->formatFieldList('Bandage', $fields['bandage']);
            if (!$this->isFieldEmpty($fields['bandage_other'] ?? null)) {
                $html .= $this->formatField('Bandage Notes', $fields['bandage_other']);
            }
            $sections['Bandaging'] = $html;
        }
        
        $html = $this->combineSections($sections);
        return $this->wrapProcedureDetails($html);
    }
}
