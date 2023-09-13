<?php

/**
 * QueryPagination is used for tracking and handling the pagination of a search query.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2023 Discover and Change, Inc <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Database;

use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle\FHIRBundleLink;

class QueryPagination implements \JsonSerializable
{
    // TODO: @adunsulag do we want to maintain backwards compatability and let ALL data be retrieved with 0? or should we impose a limit which will break backwards compatability?
    const DEFAULT_LIMIT = 0;

    const MAX_LIMIT = 200;

    private int|null $firstOffsetId = null;
    private int|null $nextOffsetId = null;

    private int|null $previousOffsetId = null;

    private int $limit;

    private int|string $currentOffsetId;

    private int|null $totalCount;


    private bool $hasMore = false;

    private string $searchUri = '';

    public function __construct(int $limit = self::DEFAULT_LIMIT, int $offsetId = 0)
    {
        if ($offsetId < 0) {
            throw new \InvalidArgumentException("Offset Id must be greater than or equal to 0");
        }
        $limit = min($limit, self::MAX_LIMIT);
        $this->firstOffsetId = 0;
        $this->totalCount = 0;
        $this->nextOffsetId = $offsetId + $limit;
        $this->previousOffsetId = max(0, $offsetId - $limit);
        $this->setCurrentOffsetId($offsetId);
        $this->setLimit($limit);
    }

    public function getSearchUri(): string
    {
        return $this->searchUri;
    }

    public function setSearchUri($searchUri)
    {
        $this->searchUri = $searchUri;
    }

    public function copy(): QueryPagination
    {
        $copy = clone $this;
        return $copy;
    }

    /**
     * @param int|null $totalCount
     */
    public function setTotalCount(?int $totalCount): void
    {
        $this->totalCount = $totalCount;
    }

    /**
     * @return int|null
     */
    public function getTotalCount(): ?int
    {
        return $this->totalCount;
    }

    /**
     * @return int|string|null
     */
    public function getNextOffsetId(): int|string|null
    {
        return $this->nextOffsetId;
    }

    public function hasMoreData(): bool
    {
        return $this->hasMore;
    }

    public function setHasMoreData(bool $hasMore): void
    {
        $this->hasMore = $hasMore;
    }

    public function setCurrentOffsetId(int|string $currentOffsetId): void
    {
        $this->currentOffsetId = $currentOffsetId;
    }

    public function setLimit(int $limit): void
    {
        if ($limit < 0) {
            throw new \InvalidArgumentException("Limit must be greater than or equal to 0");
        }
        $this->limit = $limit;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
    public function getCurrentOffsetId(): int|string
    {
        return $this->currentOffsetId;
    }

    public function getLinks()
    {
        $searchQueryUri = $this->searchUri;
        $links = [
            'first' => $searchQueryUri . "&_offset=" . $this->firstOffsetId . "&_count=" . $this->limit
        ];
        if ($this->currentOffsetId > 0) {
            $links['previous'] = $searchQueryUri . "&_offset=" . $this->previousOffsetId . "&_count=" . $this->limit;
        }
        if ($this->hasMore) {
            $links['next'] = $searchQueryUri . "&_offset=" . $this->nextOffsetId . "&_count=" . $this->limit;
        }
        // if we ever figure out performance wise to handle last we would do that here, but for now we will not
        return $links;
    }

    /**
     * Returns the fhir pagination for this query pagination result.
     * @return FHIRBundleLink[]
     */
    public function getFhirLinks(): array
    {
        $links = [];
        $pagination = $this->getLinks();
        foreach ($pagination as $key => $uri) {
            $link = new FHIRBundleLink();
            $link->setId($key);
            $link->setUrl(new FHIRUri($uri));
            $links[] = $link;
        }
        return $links;
    }

    public function jsonSerialize(): mixed
    {
        return $this->getLinks();
    }
}
