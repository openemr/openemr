<?php

namespace Doctrine\CouchDB\View;

use Doctrine\CouchDB\HTTP\Client;

class LuceneQuery extends AbstractQuery
{   
    /**
     * The CouchDB Lucene Handler name, probably defaults to _fti
     *
     * @var string
     */
    private $handlerName;

    /**
     * @param Client $client
     * @param string $databaseName
     * @param string $handlerName
     * @param string $viewName
     * @param string $designDocName
     * @param DesignDocument $doc
     */
    public function __construct(Client $client, $databaseName, $handlerName, $designDocName, $viewName, DesignDocument $doc = null)
    {
        parent::__construct($client, $databaseName, $designDocName, $viewName, $doc);
        $this->handlerName = $handlerName;
    }

    protected function getHttpQuery()
    {
        return sprintf(
            "/%s/%s/_design/%s/%s?%s",
            $this->databaseName,
            $this->handlerName,
            $this->designDocumentName,
            $this->viewName,
            http_build_query( $this->params )
        );
    }

    public function setAnalyzer($analyzer)
    {
        $this->params['analyzer'] = $analyzer;
        return $this;
    }

    public function getAnalyzer()
    {
        return (isset($this->params['analyzer'])) ? $this->params['analyzer'] : null;
    }

    /**
     * Automatically fetch and include the document which emitted each view entry
     *
     * @param  bool $flag
     * @return Query
     */
    public function setIncludeDocs($flag)
    {
        $this->params['include_docs'] = $flag;
        return $this;
    }

    public function getIncludeDocs()
    {
        return (isset($this->params['include_docs'])) ? $this->params['include_docs'] : null;
    }

    public function setLimit($limit)
    {
        $this->params['limit'] = $limit;
        return $this;
    }

    public function getLimit()
    {
        return (isset($this->params['limit'])) ? $this->params['limit'] : null;
    }

    public function setQuery($query)
    {
        $this->params['q'] = $query;
        return $this;
    }

    public function getQuery()
    {
        return isset($this->params['q']) ? $this->params['q'] : null;
    }

    public function setSkip($skip)
    {
        $this->params['skip'] = $skip;
        return $this;
    }

    public function setSort($sort)
    {
        $this->params['sort'] = $sort;
        return $this;
    }

    public function setStale($bool)
    {
        if ($bool) {
            $this->params['stale'] = 'ok';
        } else {
            unset($this->params['stale']);
        }
        return $this;
    }

    /**
     * @param \Doctrine\CouchDB\HTTP\Response $response
     * @return LuceneResult
     */
    protected function createResult($response)
    {
        return new LuceneResult($response->body);
    }
}
