<?php

namespace Doctrine\CouchDB\View;

class LuceneResult extends Result
{
    public function getETag()
    {
        return $this->result['etag'];
    }

    public function getFetchDuration()
    {
        return $this->result['fetch_duration'];
    }

    public function getLimit()
    {
        return $this->result['limit'];
    }

    public function getExecutedQuery()
    {
        return $this->result['q'];
    }

    public function getRows()
    {
        return $this->result['rows'];
    }

    public function getSearchDuration()
    {
        return $this->result['search_duration'];
    }

    public function getSkip()
    {
        return $this->result['skip'];
    }

    public function getTotalRows()
    {
        return $this->result['total_rows'];
    }
}