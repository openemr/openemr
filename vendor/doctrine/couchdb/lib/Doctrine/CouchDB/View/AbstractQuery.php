<?php

namespace Doctrine\CouchDB\View;

use Doctrine\CouchDB\HTTP\Client;
use Doctrine\CouchDB\HTTP\ErrorResponse;
use Doctrine\CouchDB\HTTP\HTTPException;

abstract class AbstractQuery
{
    /**
     * @var DesignDocument
     */
    protected $doc;

    /**
     * @var string
     */
    protected $designDocumentName;

    /**
     * @var string
     */
    protected $viewName;

    /**
     * @var string
     */
    protected $databaseName;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $params = array();

    /**
     * @param Client $client
     * @param string $designDocName
     * @param string $viewName
     * @param DesignDocument $doc
     */
    public function __construct(Client $client, $databaseName, $designDocName, $viewName, DesignDocument $doc = null)
    {
        $this->client = $client;
        $this->databaseName = $databaseName;
        $this->designDocumentName = $designDocName;
        $this->viewName = $viewName;
        $this->doc = $doc;
    }

    /**
     * @param  string $key
     * @return mixed
     */
    public function getParameter($key)
    {
        if (isset($this->params[$key])) {
            return $this->params[$key];
        }
        return null;
    }

    abstract protected function getHttpQuery();

    /**
     * Query the view with the current params.
     *
     * @return Result
     */
    public function execute()
    {
        return $this->createResult($this->doExecute());
    }

    protected function doExecute()
    {
        $path = $this->getHttpQuery();
        $response = $this->client->request("GET", $path);

        if ( $response instanceof ErrorResponse ) {
            // Create view, if it does not exist yet
            $this->createDesignDocument();
            $response = $this->client->request( "GET", $path );
        }

        if ($response->status >= 400) {
            throw HTTPException::fromResponse($path, $response);
        }
        return $response;
    }

    /**
     * @param $response
     * @return Result
     */
    abstract protected function createResult($response);

    /**
     * Create non existing view
     *
     * @return void
     * @throws \Doctrine\CouchDB\JsonDecodeException
     * @throws \Exception
     */
    public function createDesignDocument()
    {
        if (!$this->doc) {
            throw new \Exception("No DesignDocument Class is connected to this view query, cannot create the design document with its corresponding view automatically!");
        }

        $data = $this->doc->getData();
        if ($data === null) {
            throw \Doctrine\CouchDB\JsonDecodeException::fromLastJsonError();
        }
        $data['_id'] = '_design/' . $this->designDocumentName;

        $response = $this->client->request(
            "PUT",
            sprintf(
                "/%s/_design/%s",
                $this->databaseName,
                $this->designDocumentName
            ),
            json_encode($data)
        );
    }
}
