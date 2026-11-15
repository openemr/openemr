<?php

/**
 * Searches and fetches LOINC form definitions from NLM's Clinical Table Search Service.
 *
 * The URLs are fixed and only the search words and a validated LOINC number are sent, so the
 * endpoint cannot be pointed anywhere else and no patient data leaves the system.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Questionnaire\Import;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use JsonException;

class LoincFormClient
{
    private const SEARCH_URL = 'https://clinicaltables.nlm.nih.gov/api/loinc_items/v3/search';
    private const DEFINITION_URL = 'https://clinicaltables.nlm.nih.gov/loinc_form_definitions';
    private const TIMEOUT_SECONDS = 20;
    private const MAX_RESULTS = 25;

    public function __construct(private readonly ClientInterface $http)
    {
    }

    /**
     * @return list<array{code: string, name: string}>
     * @throws LoincFormUnavailable
     */
    public function search(string $terms): array
    {
        $body = $this->get(self::SEARCH_URL, [
            'type' => 'form',
            'available' => 'true',
            'df' => 'text,LOINC_NUM',
            'maxList' => self::MAX_RESULTS,
            'terms' => $terms,
        ]);

        $rows = is_array($body[3] ?? null) ? $body[3] : [];
        $results = [];
        foreach ($rows as $row) {
            if (is_array($row) && is_string($row[0] ?? null) && is_string($row[1] ?? null)) {
                $results[] = ['code' => $row[1], 'name' => $row[0]];
            }
        }

        return $results;
    }

    /**
     * @return array<mixed> the decoded LHC form definition
     * @throws InvalidArgumentException if the number is not shaped like a LOINC code
     * @throws LoincFormUnavailable if NLM cannot supply the definition
     */
    public function fetch(string $loincNumber): array
    {
        if (preg_match('/^\d{1,7}-\d$/', $loincNumber) !== 1) {
            throw new InvalidArgumentException('Not a LOINC number');
        }

        $definition = $this->get(self::DEFINITION_URL, ['loinc_num' => $loincNumber]);
        if (array_key_exists('Error', $definition)) {
            throw new LoincFormUnavailable('NLM has no definition for this LOINC number');
        }

        return $definition;
    }

    /**
     * @param array<string, int|string> $query
     * @return array<mixed>
     * @throws LoincFormUnavailable
     */
    private function get(string $url, array $query): array
    {
        try {
            $response = $this->http->request('GET', $url, ['query' => $query, 'timeout' => self::TIMEOUT_SECONDS]);
            $decoded = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (GuzzleException | JsonException $e) {
            throw new LoincFormUnavailable('NLM LOINC form request failed', 0, $e);
        }
        if (!is_array($decoded)) {
            throw new LoincFormUnavailable('NLM LOINC form response was not understood');
        }

        return $decoded;
    }
}
