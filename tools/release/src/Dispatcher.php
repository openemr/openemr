<?php

/**
 * Send `repository_dispatch` events to consumer repos. Validates the
 * envelope against the vendored dispatch.schema.json before any HTTP
 * call so a malformed payload fails fast with a clear error.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

use JsonSchema\Validator;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class Dispatcher
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $schemaPath,
        private string $apiBaseUrl = 'https://api.github.com',
    ) {
    }

    /**
     * @param list<string> $targetRepos owner/name strings
     * @return list<DispatchResult>
     */
    public function dispatch(DispatchRequest $request, array $targetRepos): array
    {
        if (!$request->probe) {
            $this->validateAgainstSchema($request);
        }
        if ($targetRepos === []) {
            throw new \InvalidArgumentException('targetRepos must not be empty');
        }

        $payload = $request->toEnvelope();
        $results = [];
        foreach ($targetRepos as $repo) {
            $response = $this->httpClient->request('POST', $this->endpoint($repo), [
                'headers' => [
                    'Accept' => 'application/vnd.github+json',
                    'Authorization' => 'Bearer ' . $request->appToken,
                    'X-GitHub-Api-Version' => '2022-11-28',
                ],
                'json' => [
                    'event_type' => $request->event,
                    'client_payload' => $payload,
                ],
            ]);
            $status = $response->getStatusCode();
            $results[] = new DispatchResult($repo, $status, $status === 204);
            if ($status !== 204) {
                throw new \RuntimeException(sprintf(
                    'repository_dispatch to %s failed (HTTP %d): %s',
                    $repo,
                    $status,
                    $response->getContent(false),
                ));
            }
        }
        return $results;
    }

    private function validateAgainstSchema(DispatchRequest $request): void
    {
        $contents = file_get_contents($this->schemaPath);
        if ($contents === false) {
            throw new \RuntimeException('Cannot read schema at ' . $this->schemaPath);
        }
        $schema = json_decode($contents, false, 512, JSON_THROW_ON_ERROR);
        $payload = json_decode(
            json_encode($request->toEnvelope(), JSON_THROW_ON_ERROR),
            false,
            512,
            JSON_THROW_ON_ERROR,
        );

        $validator = new Validator();
        $validator->validate($payload, $schema);
        if ($validator->isValid()) {
            return;
        }
        $errors = [];
        foreach ($validator->getErrors() as $err) {
            if (!is_array($err)) {
                continue;
            }
            $property = is_string($err['property'] ?? null) ? $err['property'] : '';
            $message = is_string($err['message'] ?? null) ? $err['message'] : '';
            $errors[] = sprintf('%s: %s', $property, $message);
        }
        throw new \RuntimeException('Dispatch payload failed schema validation: ' . implode('; ', $errors));
    }

    private function endpoint(string $repo): string
    {
        return $this->apiBaseUrl . '/repos/' . $repo . '/dispatches';
    }
}
