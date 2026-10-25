<?php
namespace GeneralDermatology\Services\Info\Processors;
use GeneralDermatology\Services\Info\AbstractProcedureInfoProcessor;

class UnnaBootInfoProcessor extends AbstractProcedureInfoProcessor
{
    public function extractFields(array $treatment): array
    {
        $skinPrepOptions = [
            'unna-alcohol' => 'Alcohol',
            'unna-hibiclens' => 'Hibiclens',
            'unna-iodine' => 'Iodine',
            'unna-lasercyn' => 'Lasercyn',
        ];
        
        $locationOptions = [
            'unna-both-legs' => 'Both Legs',
            'unna-left-leg' => 'Left Leg',
            'unna-right-leg' => 'Right Leg',
            'unna-both-arms' => 'Both Arms',
            'unna-left-arm' => 'Left Arm',
            'unna-right-arm' => 'Right Arm',
        ];
        
        $indicationOptions = [
            'unna-lymphedema' => 'Lymphedema',
            'unna-chronic-ulcer' => 'Chronic Ulcer',
            'unna-venous-insufficiency' => 'Venous Insufficiency',
            'unna-venous-ulcer' => 'Venous Ulcer',
            'unna-stasis-ulcer' => 'Stasis Ulcer',
            'unna-post-op-immobilization' => 'Post-op Immobilization',
            'unna-thrombophlebitis' => 'Thrombophlebitis',
            'unna-graft-dressing' => 'Graft Dressing',
            'unna-extensive-actinic-keratoses' => 'Extensive Actinic Keratoses',
            'unna-numerous-sccis' => 'Numerous SCCiS',
        ];
        
        $medicationOptions = [
            'unna-5-fluorouracil-cream' => '5-Fluorouracil Cream',
            'unna-aquaphor' => 'Aquaphor',
            'unna-petrolatum' => 'Petrolatum',
            'unna-bactroban' => 'Bactroban',
            'unna-triamcinolone-0-1-ointment' => 'Triamcinolone 0.1% Ointment',
            'unna-gentamicin' => 'Gentamicin',
            'unna-mupirocin' => 'Mupirocin',
            'unna-polysporin' => 'Polysporin',
        ];
        
        $priorBootOptions = [
            'unna-yes' => 'Yes',
            'unna-no' => 'No',
        ];
        
        $wrapOptions = [
            'unna-coban' => 'Coban',
            'unna-tubular-compression-stockings' => 'Tubular Compression Stockings',
            'unna-compression-stockings' => 'Compression Stockings',
        ];
        
        return [
            'skin_prep' => $this->extractCheckboxGroup($treatment, 'unna', $skinPrepOptions),
            'skin_prep_free_text' => $this->getField($treatment, 'unna-freeS'),
            'location_applied' => $this->extractCheckboxGroup($treatment, 'unna', $locationOptions),
            'location_applied_free_text' => $this->getField($treatment, 'unna-location-applied-free-text'),
            'indication' => $this->extractCheckboxGroup($treatment, 'unna', $indicationOptions),
            'indication_free_text' => $this->getField($treatment, 'unna-Indication-free-text'),
            'medication_occluded' => $this->extractCheckboxGroup($treatment, 'unna', $medicationOptions),
            'medication_occluded_free_text' => $this->getField($treatment, 'unna-Medication-occluded-free-text'),
            'prior_boot_removed' => $this->extractCheckboxGroup($treatment, 'unna', $priorBootOptions),
            'prior_boot_removed_free_text' => $this->getField($treatment, 'unna-Prior-Unna-boot-removed-free-text'),
            'wrap_type' => $this->extractCheckboxGroup($treatment, 'unna', $wrapOptions),
            'wrap_type_free_text' => $this->getField($treatment, 'unna-Wrap-of-Unna-boot-free-text'),
        ];
    }

    public function formatForDisplay(array $fields): string
    {
        $sections = [];
        
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
        
        // Location Applied Section
        $locationHtml = '';
        if (!empty($fields['location_applied'] ?? [])) {
            $locationHtml .= $this->formatFieldList('Location Applied', $fields['location_applied']);
        }
        if (!$this->isFieldEmpty($fields['location_applied_free_text'] ?? null)) {
            $locationHtml .= $this->formatField('Additional Details', $fields['location_applied_free_text']);
        }
        if ($locationHtml) {
            $sections['Location Applied'] = $locationHtml;
        }
        
        // Indication Section
        $indicationHtml = '';
        if (!empty($fields['indication'] ?? [])) {
            $indicationHtml .= $this->formatFieldList('Indication', $fields['indication']);
        }
        if (!$this->isFieldEmpty($fields['indication_free_text'] ?? null)) {
            $indicationHtml .= $this->formatField('Other Indications', $fields['indication_free_text']);
        }
        if ($indicationHtml) {
            $sections['Indication'] = $indicationHtml;
        }
        
        // Medication Occluded Section
        $medicationHtml = '';
        if (!empty($fields['medication_occluded'] ?? [])) {
            $medicationHtml .= $this->formatFieldList('Medication Occluded', $fields['medication_occluded']);
        }
        if (!$this->isFieldEmpty($fields['medication_occluded_free_text'] ?? null)) {
            $medicationHtml .= $this->formatField('Medication Notes', $fields['medication_occluded_free_text']);
        }
        if ($medicationHtml) {
            $sections['Medication Occluded'] = $medicationHtml;
        }
        
        // Prior Boot Removal Section
        $priorBootHtml = '';
        if (!empty($fields['prior_boot_removed'] ?? [])) {
            $priorBootHtml .= $this->formatFieldList('Prior Unna Boot Removed', $fields['prior_boot_removed']);
        }
        if (!$this->isFieldEmpty($fields['prior_boot_removed_free_text'] ?? null)) {
            $priorBootHtml .= $this->formatField('Additional Details', $fields['prior_boot_removed_free_text']);
        }
        if ($priorBootHtml) {
            $sections['Prior Boot Removal'] = $priorBootHtml;
        }
        
        // Wrap Type Section
        $wrapHtml = '';
        if (!empty($fields['wrap_type'] ?? [])) {
            $wrapHtml .= $this->formatFieldList('Wrap Type', $fields['wrap_type']);
        }
        if (!$this->isFieldEmpty($fields['wrap_type_free_text'] ?? null)) {
            $wrapHtml .= $this->formatField('Wrap Details', $fields['wrap_type_free_text']);
        }
        if ($wrapHtml) {
            $sections['Wrap Type'] = $wrapHtml;
        }
        
        $html = $this->combineSections($sections);
        return $this->wrapProcedureDetails($html);
    }
}
