<?php

/**
 * Create an annotated release tag via the GitHub API. The conductor
 * workflow runs this on merge of the release-prep PR, then verifies
 * the resulting tag with TagVerifier.
 *
 * Uses the Tag Object endpoint plus a refs/tags reference, not a
 * lightweight ref, so the tag carries author/date/message metadata
 * required by openemr/openemr-devops#664.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Release;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class TagCreator
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiBaseUrl = 'https://api.github.com',
    ) {
    }

    public function create(TagCreationRequest $request): TagCreationResult
    {
        $tagName = $request->tagName();
        $message = $request->renderMessage();

        $tagPayload = [
            'tag' => $tagName,
            'message' => $message,
            'object' => $request->commitSha,
            'type' => 'commit',
            'tagger' => [
                'name' => $request->taggerName,
                'email' => $request->taggerEmail,
                'date' => $request->date . 'T00:00:00Z',
            ],
        ];

        $tagResponse = $this->httpClient->request('POST', $this->endpoint($request->repo, '/git/tags'), [
            'headers' => $this->headers($request->appToken),
            'json' => $tagPayload,
        ]);
        if ($tagResponse->getStatusCode() !== 201) {
            throw new \RuntimeException(sprintf(
                'Tag object creation failed (HTTP %d): %s',
                $tagResponse->getStatusCode(),
                $tagResponse->getContent(false),
            ));
        }

        /** @var array{sha: string} $tagBody */
        $tagBody = $tagResponse->toArray();
        $tagSha = $tagBody['sha'];

        $refResponse = $this->httpClient->request('POST', $this->endpoint($request->repo, '/git/refs'), [
            'headers' => $this->headers($request->appToken),
            'json' => [
                'ref' => 'refs/tags/' . $tagName,
                'sha' => $tagSha,
            ],
        ]);
        if ($refResponse->getStatusCode() !== 201) {
            throw new \RuntimeException(sprintf(
                'Tag ref creation failed (HTTP %d): %s',
                $refResponse->getStatusCode(),
                $refResponse->getContent(false),
            ));
        }

        return new TagCreationResult($tagName, $tagSha);
    }

    private function endpoint(string $repo, string $path): string
    {
        return $this->apiBaseUrl . '/repos/' . $repo . $path;
    }

    /**
     * @return array<string, string>
     */
    private function headers(string $appToken): array
    {
        return [
            'Accept' => 'application/vnd.github+json',
            'Authorization' => 'Bearer ' . $appToken,
            'X-GitHub-Api-Version' => '2022-11-28',
        ];
    }
}
