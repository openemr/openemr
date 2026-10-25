<?php
namespace GeneralDermatology\Services\Info\Processors;
use GeneralDermatology\Services\Info\AbstractProcedureInfoProcessor;

class HyfrecationInfoProcessor extends AbstractProcedureInfoProcessor
{
    public function extractFields(array $treatment): array
    {
        $skinPrepFields = [
            'hyfrecation-alcohol' => 'Alcohol',
            'hyfrecation-hibiclens' => 'Hibiclens',
            'hyfrecation-iodine' => 'Iodine',
            'hyfrecation-lasercyn' => 'Lasercyn',
        ];
        
        $anestheticFields = [
            'hyfrecation-none' => 'None',
            'hyfrecation-topical-5p-lidocaine' => 'Topical 5% Lidocaine',
            'hyfrecation-topical-23p-lidocaine-7p-tetracaine' => 'Topical 23% Lidocaine, 7% Tetracaine',
            'hyfrecation-topical-23p-lidocaine-7p-tetracaine-1p-phenylephrine' => 'Topical 23% Lidocaine, 7% Tetracaine, 1% Phenylephrine',
            'hyfrecation-topical-20p-benzocaine-10p-lidocaine-4p-tetracaine' => 'Topical 20% Benzocaine, 10% Lidocaine, 4% Tetracaine',
            'hyfrecation-topical-30p-lidocaine' => 'Topical 30% Lidocaine',
            'hyfrecation-topical-30-lidocaine-7-tetracaine' => 'Topical 30% Lidocaine, 7% Tetracaine',
            'hyfrecation-ice' => 'Ice',
            'hyfrecation-fan' => 'Fan',
            'hyfrecation-vibration' => 'Vibration',
            'hyfrecation-1p-lidocaine' => '1% Lidocaine',
            'hyfrecation-1p-lidocaine-with-epinephrine' => '1% Lidocaine with Epinephrine 1:100,000',
            'hyfrecation-2p-lidocaine' => '2% Lidocaine',
            'hyfrecation-2p-lidocaine-wtih-epinephrine' => '2% Lidocaine with Epinephrine 1:100,000',
            'hyfrecation-05p-lidocaine-with-epinephrine' => '0.5% Lidocaine with Epinephrine 1:200,000',
            'hyfrecation-05p-bupivicaine' => '0.5% Bupivicaine',
            'hyfrecation-05p-bupivicaine-wtih-epinephrine' => '0.5% Bupivicaine with Epinephrine 1:200,000',
            'hyfrecation-025p-bupivicaine' => '0.25% Bupivicaine',
            'hyfrecation-025p-bupivicaine-wtih-epinephrine' => '0.25% Bupivicaine with Epinephrine 1:400,000',
            'hyfrecation-025p-marcaine-wtih-epinephrine' => '0.25% Marcaine with Epinephrine 1:200,000',
            'hyfrecation-09p-bacteriostatic-sodium-chloride' => 'Bacteriostatic 0.9% Sodium Chloride',
            'hyfrecation-sterile-water' => 'Sterile Water',
            'hyfrecation-1p-ropivacaine' => 'Ropivacaine 1%',
            'hyfrecation-05p-ropivacaine' => 'Ropivacaine 0.5%',
            'hyfrecation-84p-sodium-bicarbonate' => 'With 8.4% Sodium Bicarbonate in 1:10 ratio',
        ];
        
        $volumeFields = [
            'hyfrecation-anesthetic-volume-1cc' => '<1cc',
            'hyfrecation-anesthetic-volume-1-2cc' => '1-2cc',
            'hyfrecation-anesthetic-volume-2-3cc' => '2-3cc',
            'hyfrecation-anesthetic-volume-3-4cc' => '3-4cc',
            'hyfrecation-anesthetic-volume-4-5cc' => '4-5cc',
        ];
        
        $settingFields = [
            'hyfrecation-low-setting' => 'Low-setting',
            'hyfrecation-high-setting' => 'High-setting',
        ];
        
        $lesionFields = [
            'hyfrecation-dermatosis-papulosa-nigra' => 'Dermatosis papulosa nigra',
            'hyfrecation-skin-tags' => 'Skin tags',
            'hyfrecation-cherry-angiomas' => 'Cherry angiomas',
            'hyfrecation-seborrheic-keratoses' => 'Seborrheic keratoses',
            'hyfrecation-syringomas' => 'Syringomas',
            'hyfrecation-sebaceous-hyperplasia' => 'Sebaceous hyperplasia',
            'hyfrecation-milia' => 'Milia',
            'hyfrecation-hidrocystoma' => 'Hidrocystoma',
        ];
        
        $aftercareFields = [
            'hyfrecation-spf' => 'SPF',
            'hyfrecation-petrolatum' => 'Petrolatum',
            'hyfrecation-triamcinolone-0-1p-ointment' => 'Triamcinolone 0.1% Ointment',
            'hyfrecation-hydrocortisone-2-5p-ointment' => 'Hydrocortisone 2.5% Ointment',
        ];
        
        return [
            'skin_prep' => $this->extractCheckboxGroup($treatment, 'hyfrecation', $skinPrepFields),
            'skin_prep_other' => $this->getField($treatment, 'hyfrecation-skin-prep-free-text'),
            'anesthetics' => $this->extractCheckboxGroup($treatment, 'hyfrecation', $anestheticFields),
            'anesthetic_other' => $this->getField($treatment, 'hyfrecation-anesthetic-free-text'),
            'anesthetic_volume' => $this->extractCheckboxGroup($treatment, 'hyfrecation', $volumeFields),
            'anesthetic_volume_other' => $this->getField($treatment, 'hyfrecation-anesthetic-volume-free-text'),
            'areas_treated' => $this->getField($treatment, 'areas_hyfrecation'),
            'lesion_count' => $this->getField($treatment, 'areas-count-hyfrecation'),
            'setting' => $this->extractCheckboxGroup($treatment, 'hyfrecation', $settingFields),
            'setting_other' => $this->getField($treatment, 'hyfrecation-setting-free-text'),
            'lesion_types' => $this->extractCheckboxGroup($treatment, 'hyfrecation', $lesionFields),
            'lesion_types_other' => $this->getField($treatment, 'hyfrecation-lession-free-text'),
            'aftercare' => $this->extractCheckboxGroup($treatment, 'hyfrecation', $aftercareFields),
            'aftercare_other' => $this->getField($treatment, 'hyfrecation-aftercare_free_text'),
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
            $html = $this->formatField('Area(s) Treated', $fields['areas_treated']);
            if (!$this->isFieldEmpty($fields['lesion_count'] ?? null)) {
                $html .= $this->formatField('Number of Lesions Treated', $fields['lesion_count']);
            }
            $sections['Treatment Areas'] = $html;
        }
        
        // Settings Section
        if (!empty($fields['setting'] ?? [])) {
            $html = $this->formatFieldList('Setting', $fields['setting']);
            if (!$this->isFieldEmpty($fields['setting_other'] ?? null)) {
                $html .= $this->formatField('Setting Notes', $fields['setting_other']);
            }
            $sections['Settings'] = $html;
        }
        
        // Lesion Types Section
        if (!empty($fields['lesion_types'] ?? [])) {
            $html = $this->formatFieldList('Lesion Types', $fields['lesion_types']);
            if (!$this->isFieldEmpty($fields['lesion_types_other'] ?? null)) {
                $html .= $this->formatField('Other Types', $fields['lesion_types_other']);
            }
            $sections['Lesion Types'] = $html;
        }
        
        // Aftercare Section
        if (!empty($fields['aftercare'] ?? [])) {
            $html = $this->formatFieldList('Aftercare (Applied In-Office)', $fields['aftercare']);
            if (!$this->isFieldEmpty($fields['aftercare_other'] ?? null)) {
                $html .= $this->formatField('Aftercare Notes', $fields['aftercare_other']);
            }
            $sections['Aftercare'] = $html;
        }
        
        $html = $this->combineSections($sections);
        return $this->wrapProcedureDetails($html);
    }
}
