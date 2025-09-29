<?php
namespace OpenEMR\Services\VietnamesePT;

class VietnameseTranslationService
{
    private $medicalTermsService;

    public function __construct()
    {
        $this->medicalTermsService = new VietnameseMedicalTermsService();
    }

    public function translateToVietnamese($englishText): string
    {
        $result = $this->medicalTermsService->translate($englishText, 'en');
        return $result['vietnamese_term'] ?? $englishText;
    }

    public function translateToEnglish($vietnameseText): string
    {
        $result = $this->medicalTermsService->translate($vietnameseText, 'vi');
        return $result['english_term'] ?? $vietnameseText;
    }

    public function translateBatch(array $terms, $fromLanguage = 'en'): array
    {
        $translations = [];
        foreach ($terms as $term) {
            if ($fromLanguage === 'en') {
                $translations[$term] = $this->translateToVietnamese($term);
            } else {
                $translations[$term] = $this->translateToEnglish($term);
            }
        }
        return $translations;
    }
}
