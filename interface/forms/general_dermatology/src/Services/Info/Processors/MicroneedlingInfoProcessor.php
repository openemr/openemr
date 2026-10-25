<?php
namespace GeneralDermatology\Services\Info\Processors;
use GeneralDermatology\Services\Info\AbstractProcedureInfoProcessor;

class MicroneedlingInfoProcessor extends AbstractProcedureInfoProcessor
{
    public function extractFields(array $treatment): array
    {
        $skinPrepOptions = [
            'microneedling-alcohol' => 'Alcohol',
            'microneedling-hibiclens' => 'Hibiclens',
            'microneedling-iodine' => 'Iodine',
            'microneedling-lasercyn' => 'Lasercyn',
        ];
        
        $anestheticOptions = [
            'microneedling-none' => 'None',
            'microneedling-topical-5-lidocaine' => 'Topical 5% Lidocaine',
            'microneedling-topical-23-lidocaine-7-tetracaine' => 'Topical 23% Lidocaine, 7% Tetracaine',
            'microneedling-topical-23-lidocaine-7-tetracaine-1-phenylephrine' => 'Topical 23% Lidocaine, 7% Tetracaine, 1% Phenylephrine',
            'microneedling-topical-20-benzocaine-10-lidocaine-4-tetracaine' => 'Topical 20% Benzocaine, 10% Lidocaine, 4% Tetracaine',
            'microneedling-topical-30-lidocaine' => 'Topical 30% Lidocaine',
            'microneedling-topical-30-lidocaine-7-tetracaine' => 'Topical 30% Lidocaine, 7% Tetracaine',
            'microneedling-ice' => 'Ice',
            'microneedling-fan' => 'Fan',
            'microneedling-vibration' => 'Vibration',
        ];
        
        $infusionOptions = [
            'microneedling-prp' => 'PRP',
        ];
        
        $aftercareOptions = [
            'microneedling-spf' => 'SPF',
            'microneedling-petrolatum' => 'Petrolatum',
            'microneedling-triamcinolone-0-1p-ointment' => 'Triamcinolone 0.1% Ointment',
            'microneedling-hydrocortisone-2-5p-ointment' => 'Hydrocortisone 2.5% Ointment',
        ];
        
        $fields = [
            'skin_prep' => $this->extractCheckboxGroup($treatment, 'microneedling', $skinPrepOptions),
            'skin_prep_free_text' => $this->getField($treatment, 'microneedling-freeS'),
            'anesthetic' => $this->extractCheckboxGroup($treatment, 'microneedling', $anestheticOptions),
            'anesthetic_free_text' => $this->getField($treatment, 'microneedling-anesthetic-free-text'),
            'areas_treated' => $this->getField($treatment, 'areas_microneedling'),
            'infusions' => $this->extractCheckboxGroup($treatment, 'microneedling', $infusionOptions),
            'infusions_free_text' => $this->getField($treatment, 'microneedling-Infusions-free-text'),
            'additional_info' => $this->getField($treatment, 'microneedling-additional-info-free-text'),
            'aftercare' => $this->extractCheckboxGroup($treatment, 'microneedling', $aftercareOptions),
            'aftercare_free_text' => $this->getField($treatment, 'microneedling_aftercare_free_text'),
        ];
        
        // Extract 6 location/depth pairs
        for ($i = 1; $i <= 6; $i++) {
            $fields["location_$i"] = $this->getField($treatment, "microneedling-Location-$i-free-text");
            $fields["depth_$i"] = $this->getField($treatment, "microneedling-Depth-$i-free-text");
        }
        
        return $fields;
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
        if ($anestheticHtml) {
            $sections['Anesthetic'] = $anestheticHtml;
        }
        
        // Treatment Areas Section
        $areasHtml = '';
        if (!$this->isFieldEmpty($fields['areas_treated'] ?? null)) {
            $areasHtml .= $this->formatField('Areas Treated', $fields['areas_treated']);
        }
        if ($areasHtml) {
            $sections['Treatment Areas'] = $areasHtml;
        }
        
        // Infusions Section
        $infusionsHtml = '';
        if (!empty($fields['infusions'] ?? [])) {
            $infusionsHtml .= $this->formatFieldList('Infusions', $fields['infusions']);
        }
        if (!$this->isFieldEmpty($fields['infusions_free_text'] ?? null)) {
            $infusionsHtml .= $this->formatField('Infusion Notes', $fields['infusions_free_text']);
        }
        if ($infusionsHtml) {
            $sections['Infusions'] = $infusionsHtml;
        }
        
        // Treatment Locations Section
        $locationsHtml = '';
        for ($i = 1; $i <= 6; $i++) {
            $location = $fields["location_$i"] ?? null;
            $depth = $fields["depth_$i"] ?? null;
            
            if (!$this->isFieldEmpty($location) || !$this->isFieldEmpty($depth)) {
                if (!$this->isFieldEmpty($location)) {
                    $locationsHtml .= $this->formatField("Location #$i", $location);
                }
                if (!$this->isFieldEmpty($depth)) {
                    $locationsHtml .= $this->formatField("Depth (mm) #$i", $depth);
                }
            }
        }
        if ($locationsHtml) {
            $sections['Treatment Locations'] = $locationsHtml;
        }
        
        // Additional Info Section
        $additionalHtml = '';
        if (!$this->isFieldEmpty($fields['additional_info'] ?? null)) {
            $additionalHtml .= $this->formatField('Additional Information', $fields['additional_info']);
        }
        if ($additionalHtml) {
            $sections['Additional Information'] = $additionalHtml;
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
