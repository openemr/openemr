<?php

/**
 * AI-powered patient search service using Google Gemma 4 API.
 *
 * Parses natural language patient search queries into structured search
 * parameters by calling the Gemma 4 API hosted on Google AI Studio.
 * Falls back gracefully when the API key is not configured or the
 * API is unreachable.
 *
 * @package OpenEMR
 * @subpackage Services\Search
 * @author  Dhanvi <dhanvi@openemr.org>
 * @link    https://www.open-emr.org
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Search;

use OpenEMR\Core\OEGlobalsBag;
use Psr\Log\LoggerInterface;

/**
 * Calls the Gemma 4 API to extract structured patient identifiers from
 * free-text search queries.
 *
 * Example: "John Smith born 03/15/1985" → ['fname' => 'John', 'lname' => 'Smith', 'DOB' => '1985-03-15']
 */
final class AiPatientSearchService
{
    private const API_BASE_URL = 'https://generativelanguage.googleapis.com/v1beta/models/';
    private const DEFAULT_MODEL = 'gemma-4-4b-it';
    private const TIMEOUT_SECONDS = 5;

    /**
     * The system prompt that instructs Gemma to extract structured patient
     * search fields from natural language input.
     */
    private const SYSTEM_PROMPT = <<<'PROMPT'
You are a medical records search assistant. Given a patient search query, extract structured search parameters.

Return ONLY a valid JSON object with these possible keys (omit keys that are not found in the query):
- "lname": last name (string)
- "fname": first name (string)
- "mname": middle name (string)
- "DOB": date of birth in YYYY-MM-DD format (string)
- "pid": patient ID number (string)
- "pubpid": public patient ID (string)
- "ss": social security number (string)
- "phone_home": home phone number (string)
- "phone_cell": cell phone number (string)

Rules:
1. Return ONLY the JSON object, no explanation or markdown.
2. If the query has a single word, treat it as a last name.
3. If the query has "firstname lastname" pattern (two words, no comma), the first word is fname, second is lname.
4. If the query has "lastname, firstname" pattern (comma separated), parse accordingly.
5. Convert dates to YYYY-MM-DD format. Handle formats like MM/DD/YYYY, Month DD YYYY, etc.
6. If you detect a number that looks like a patient ID (e.g. "patient 12345" or "pid 12345"), put it in "pid".
7. If you detect a social security number pattern (XXX-XX-XXXX), put it in "ss".
8. If a date is referenced with words like "born", "DOB", "birthday", put it in "DOB".
9. If the query mentions "phone" or contains a phone number pattern, put it in "phone_home".
10. If you cannot extract any meaningful fields, return an empty JSON object: {}
PROMPT;

    private LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new \OpenEMR\Common\Logging\SystemLogger();
    }

    /**
     * Check whether the AI search feature is available (API key configured).
     */
    public function isAvailable(): bool
    {
        return $this->getApiKey() !== '';
    }

    /**
     * Parse a free-text search query into structured patient search parameters.
     *
     * @param string $userInput The raw search query from the user
     * @return array<string, string>|null Structured search fields, or null on failure
     */
    public function parseSearchQuery(string $userInput): ?array
    {
        $apiKey = $this->getApiKey();
        if ($apiKey === '') {
            $this->logger->debug('Gemma AI search: no API key configured');
            return null;
        }

        $userInput = trim($userInput);
        if ($userInput === '') {
            return null;
        }

        $model = $this->getModel();
        $url = self::API_BASE_URL . $model . ':generateContent?key=' . $apiKey;

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => self::SYSTEM_PROMPT . "\n\nSearch query: " . $userInput],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.0,
                'maxOutputTokens' => 256,
            ],
        ];

        $jsonPayload = json_encode($payload, JSON_THROW_ON_ERROR);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonPayload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => self::TIMEOUT_SECONDS,
            CURLOPT_CONNECTTIMEOUT => 3,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curlError !== '') {
            $this->logger->warning('Gemma AI search: cURL error', [
                'error' => $curlError,
            ]);
            return null;
        }

        if ($httpCode !== 200) {
            $this->logger->warning('Gemma AI search: API returned non-200 status', [
                'http_code' => $httpCode,
                'response' => mb_substr((string) $response, 0, 500),
            ]);
            return null;
        }

        return $this->parseApiResponse((string) $response);
    }

    /**
     * Extract the structured JSON from the Gemma API response.
     *
     * @return array<string, string>|null
     */
    private function parseApiResponse(string $responseBody): ?array
    {
        try {
            $data = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->logger->warning('Gemma AI search: failed to decode API response', [
                'exception' => $e,
            ]);
            return null;
        }

        // Navigate the Gemma API response structure:
        // { "candidates": [{ "content": { "parts": [{ "text": "..." }] } }] }
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!is_string($text) || $text === '') {
            $this->logger->warning('Gemma AI search: empty response text from API');
            return null;
        }

        // The model may wrap JSON in markdown code fences — strip them
        $text = trim($text);
        if (str_starts_with($text, '```')) {
            $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
            $text = preg_replace('/\s*```\s*$/', '', $text);
        }

        try {
            $parsed = json_decode(trim($text), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->logger->warning('Gemma AI search: model returned non-JSON text', [
                'text' => mb_substr($text, 0, 500),
                'exception' => $e,
            ]);
            return null;
        }

        if (!is_array($parsed)) {
            return null;
        }

        // Whitelist only known patient_data columns
        $allowedKeys = [
            'lname', 'fname', 'mname', 'DOB', 'pid', 'pubpid',
            'ss', 'phone_home', 'phone_cell',
        ];

        $result = [];
        foreach ($allowedKeys as $key) {
            if (isset($parsed[$key]) && is_string($parsed[$key]) && $parsed[$key] !== '') {
                $result[$key] = $parsed[$key];
            }
        }

        if ($result === []) {
            $this->logger->debug('Gemma AI search: no fields extracted from query');
            return null;
        }

        $this->logger->debug('Gemma AI search: extracted fields', [
            'fields' => array_keys($result),
        ]);

        return $result;
    }

    /**
     * Retrieve the Gemma API key from global settings.
     */
    private function getApiKey(): string
    {
        $globals = OEGlobalsBag::getInstance();
        // Try the dedicated Gemma API key setting
        $key = $globals->getString('gemma_api_key');
        if ($key !== '') {
            return $key;
        }

        return '';
    }

    /**
     * Retrieve the configured Gemma model name.
     */
    private function getModel(): string
    {
        $globals = OEGlobalsBag::getInstance();
        $model = $globals->getString('gemma_model_name');
        return $model !== '' ? $model : self::DEFAULT_MODEL;
    }
}
