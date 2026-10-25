<?php
namespace GeneralDermatology\Services\Info\Processors;
use GeneralDermatology\Services\Info\AbstractProcedureInfoProcessor;

class PlateletInfoProcessor extends AbstractProcedureInfoProcessor
{
    public function extractFields(array $treatment): array
    {
        $indicationOptions = [
            'platelet-hair-loss' => 'Hair Loss',
            'platelet-skin-rejuvenation' => 'Skin Rejuvenation',
            'platelet-rhytides' => 'Rhytides',
        ];
        
        $anestheticOptions = [
            'platelet-none' => 'None',
            'platelet-topical-5p-lidocaine' => 'Topical 5% Lidocaine',
            'platelet-topical-23p-lidocaine-7p-tetracaine' => 'Topical 23% Lidocaine, 7% Tetracaine',
            'platelet-topical-23p-lidocaine-7p-tetracaine-1p-phenylephrine' => 'Topical 23% Lidocaine, 7% Tetracaine, 1% Phenylephrine',
            'platelet-topical-20p-benzocaine-10p-lidocaine-4p-tetracaine' => 'Topical 20% Benzocaine, 10% Lidocaine, 4% Tetracaine',
            'platelet-topical-30p-lidocaine' => 'Topical 30% Lidocaine',
            'platelet-topical-30-lidocaine-7-tetracaine' => 'Topical 30% Lidocaine, 7% Tetracaine',
            'platelet-ice' => 'Ice',
            'platelet-fan' => 'Fan',
            'platelet-vibration' => 'Vibration',
            'platelet-1p-lidocaine' => '1% Lidocaine',
            'platelet-1p-lidocaine-with-epinephrine' => '1% Lidocaine with Epinephrine 1:100,000',
            'platelet-2p-lidocaine' => '2% Lidocaine',
            'platelet-2p-lidocaine-wtih-epinephrine' => '2% Lidocaine with Epinephrine 1:100,000',
            'platelet-05p-lidocaine-with-epinephrine' => '0.5% Lidocaine with Epinephrine 1:200,000',
            'platelet-05p-bupivicaine' => '0.5% Bupivacaine',
            'platelet-05p-bupivicaine-wtih-epinephrine' => '0.5% Bupivacaine with Epinephrine 1:200,000',
            'platelet-025p-bupivicaine' => '0.25% Bupivacaine',
            'platelet-025p-bupivicaine-wtih-epinephrine' => '0.25% Bupivacaine with Epinephrine 1:400,000',
            'platelet-025p-marcaine-wtih-epinephrine' => '0.25% Marcaine with Epinephrine 1:200,000',
            'platelet-09p-bacteriostatic-sodium-chloride' => 'Bacteriostatic 0.9% Sodium Chloride',
            'platelet-sterile-water' => 'Sterile Water',
            'platelet-1p-ropivacaine' => 'Ropivacaine 1%',
            'platelet-05p-ropivacaine' => 'Ropivacaine 0.5%',
            'platelet-84p-sodium-bicarbonate' => 'WITH 8.4% Sodium Bicarbonate',
        ];
        
        $anestheticVolumeOptions = [
            'platelet-anesthetic-volume-1cc' => '<1cc',
            'platelet-anesthetic-volume-1-2cc' => '1-2cc',
            'platelet-anesthetic-volume-2-3cc' => '2-3cc',
            'platelet-anesthetic-volume-3-4cc' => '3-4cc',
            'platelet-anesthetic-volume-4-5cc' => '4-5cc',
            'platelet-anesthetic-volume-5-6cc' => '5-6cc',
            'platelet-anesthetic-volume-6-7cc' => '6-7cc',
            'platelet-anesthetic-volume-7-8cc' => '7-8cc',
            'platelet-anesthetic-volume-8-9cc' => '8-9cc',
        ];
        
        $numberVialsOptions = [
            'platelet-1' => '1',
            'platelet-2' => '2',
            'platelet-3' => '3',
            'platelet-4' => '4',
        ];
        
        return [
            'indication' => $this->extractCheckboxGroup($treatment, 'platelet', $indicationOptions),
            'indication_free_text' => $this->getField($treatment, 'platelet-Indication-free-text'),
            'areas_treated' => $this->getField($treatment, 'areas_platelet'),
            'anesthetic' => $this->extractCheckboxGroup($treatment, 'platelet', $anestheticOptions),
            'anesthetic_free_text' => $this->getField($treatment, 'platelet-anesthetic-free-text'),
            'anesthetic_volume' => $this->extractCheckboxGroup($treatment, 'platelet', $anestheticVolumeOptions),
            'anesthetic_volume_free_text' => $this->getField($treatment, 'platelet-anesthetic-volume-free-text'),
            'number_of_vials' => $this->extractCheckboxGroup($treatment, 'platelet', $numberVialsOptions),
            'number_of_vials_free_text' => $this->getField($treatment, 'platelet-Number-of-Vials-Injected-free-text'),
            'lot_number' => $this->getField($treatment, 'platelet-Lot-free-text'),
            'expiration_date' => $this->getField($treatment, 'platelet-Expiration-Date-free-text'),
        ];
    }

    public function formatForDisplay(array $fields): string
    {
        $sections = [];
        
        // Indication Section
        $indicationHtml = '';
        if (!empty($fields['indication'] ?? [])) {
            $indicationHtml .= $this->formatFieldList('Indication', $fields['indication']);
        }
        if (!$this->isFieldEmpty($fields['indication_free_text'] ?? null)) {
            $indicationHtml .= $this->formatField('Indication Notes', $fields['indication_free_text']);
        }
        if ($indicationHtml) {
            $sections['Indication'] = $indicationHtml;
        }
        
        // Treatment Areas Section
        $areasHtml = '';
        if (!$this->isFieldEmpty($fields['areas_treated'] ?? null)) {
            $areasHtml .= $this->formatField('Areas Treated', $fields['areas_treated']);
        }
        if ($areasHtml) {
            $sections['Treatment Areas'] = $areasHtml;
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
        
        // Product Information Section
        $productHtml = '';
        if (!empty($fields['number_of_vials'] ?? [])) {
            $productHtml .= $this->formatFieldList('Number of Vials Injected', $fields['number_of_vials']);
        }
        if (!$this->isFieldEmpty($fields['number_of_vials_free_text'] ?? null)) {
            $productHtml .= $this->formatField('Vials Notes', $fields['number_of_vials_free_text']);
        }
        if (!$this->isFieldEmpty($fields['lot_number'] ?? null)) {
            $productHtml .= $this->formatField('Lot Number', $fields['lot_number']);
        }
        if (!$this->isFieldEmpty($fields['expiration_date'] ?? null)) {
            $productHtml .= $this->formatField('Expiration Date', $fields['expiration_date']);
        }
        if ($productHtml) {
            $sections['Product Information'] = $productHtml;
        }
        
        $html = $this->combineSections($sections);
        return $this->wrapProcedureDetails($html);
    }
}
