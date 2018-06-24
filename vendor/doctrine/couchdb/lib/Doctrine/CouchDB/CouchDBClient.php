<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\CouchDB;

use Doctrine\CouchDB\HTTP\Client;
use Doctrine\CouchDB\HTTP\Response;
use Doctrine\CouchDB\HTTP\HTTPException;
use Doctrine\CouchDB\HTTP\MultipartParserAndSender;
use Doctrine\CouchDB\HTTP\StreamClient;
use Doctrine\CouchDB\Utils\BulkUpdater;
use Doctrine\CouchDB\View\DesignDocument;

/**
 * CouchDB client class
 *
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 * @link        www.doctrine-project.com
 * @since       1.0
 * @author      Benjamin Eberlei <kontakt@beberlei.de>
 * @author      Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class CouchDBClient
{
    /** string \ufff0 */
    const COLLATION_END = "\xEF\xBF\xB0";

    /**
     * Name of the CouchDB database
     *
     * @string
     */
    protected $databaseName;

    /**
     * The underlying HTTP Connection of the used DocumentManager.
     *
     * @var Client
     */
    private $httpClient;

    /**
     * CouchDB Version
     *
     * @var string
     */
    private $version = null;

    static private $clients = array(
        'socket' => 'Doctrine\CouchDB\HTTP\SocketClient',
        'stream' => 'Doctrine\CouchDB\HTTP\StreamClient',
    );

    /**
     * Factory method for CouchDBClients
     *
     * @param array $options
     * @return CouchDBClient
     * @throws \InvalidArgumentException
     */
    static public function create(array $options)
    {
        if (isset($options['url'])) {
            $urlParts = parse_url($options['url']);
            $urlOptions = ['user' => 'user', 'pass' => 'password', 'host' => 'host', 'path' => 'dbname', 'port' => 'port'];
            foreach ($urlParts as $part => $value) {
                switch($part) {
                    case 'host':
                    case 'user':
                    case 'port':
                        $options[$part] = $value;
                        break;

                    case 'path':
                        $path = explode('/', $value);
                        $options['dbname'] = array_pop($path);
                        $options['path'] = trim(implode('/', $path), '/');
                        break;

                    case 'pass':
                        $options['password'] = $value;
                        break;

                    case 'scheme':
                        $options['ssl'] = ($value === 'https');
                        break;

                    default:
                        break;
                }
            }
        }

        if (!isset($options['dbname'])) {
            throw new \InvalidArgumentException("'dbname' is a required option to create a CouchDBClient");
        }

        $defaults = array(
            'type' => 'socket',
            'host' => 'localhost',
            'port' => 5984,
            'user' => null,
            'password' => null,
            'ip' => null,
            'ssl' => false,
            'path' => null,
            'logging' => false,
            'timeout' => 0.01,
        );
        $options = array_merge($defaults, $options);

        if (!isset(self::$clients[$options['type']])) {
            throw new \InvalidArgumentException(sprintf('There is no client implementation registered for %s, valid options are: %s',
                $options['type'], implode(", ", array_keys(self::$clients))
            ));
        }
        $connectionClass = self::$clients[$options['type']];
        $connection = new $connectionClass(
            $options['host'],
            $options['port'],
            $options['user'],
            $options['password'],
            $options['ip'],
            $options['ssl'],
            $options['path'],
            $options['timeout']
        );
        if ($options['logging'] === true) {
            $connection = new HTTP\LoggingClient($connection);
        }
        return new static($connection, $options['dbname']);
    }

    /**
     * @param Client $client
     * @param string $databaseName
     */
    public function __construct(Client $client, $databaseName)
    {
        $this->httpClient = $client;
        $this->databaseName = $databaseName;
    }

    public function setHttpClient(Client $client)
    {
        $this->httpClient = $client;
    }

    /**
     * @return Client
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    public function getDatabase()
    {
        return $this->databaseName;
    }

    /**
     * Let CouchDB generate an array of UUIDs.
     *
     * @param  int $count
     * @return array
     * @throws CouchDBException
     */
    public function getUuids($count = 1)
    {
        $count = (int)$count;
        $response = $this->httpClient->request('GET', '/_uuids?count=' . $count);

        if ($response->status != 200) {
            throw new CouchDBException("Could not retrieve UUIDs from CouchDB.");
        }

        return $response->body['uuids'];
    }

    /**
     * Find a document by ID and return the HTTP response.
     *
     * @param string $id
     * @return HTTP\Response
     */
    public function findDocument($id)
    {
        $documentPath = '/' . $this->databaseName . '/' . urlencode($id);
        return $this->httpClient->request('GET', $documentPath);
    }

    /**
     * Find documents of all or the specified revisions.
     *
     * If $revisions is an array containing the revisions to be fetched, only
     * the documents of those revisions are fetched. Else document of all
     * leaf revisions are fetched.
     *
     * @param string $docId
     * @param mixed $revisions
     * @return HTTP\Response
     */
    public function findRevisions($docId, $revisions = null)
    {
        $path = '/' . $this->databaseName . '/' . urlencode($docId);
        if (is_array($revisions)) {
            // Fetch documents of only the specified leaf revisions.
            $path .= '?open_revs=' . json_encode($revisions);
        } else {
            // Fetch documents of all leaf revisions.
            $path .= '?open_revs=all';
        }
        // Set the Accept header to application/json to get a JSON array in the
        // response's body. Without this the response is multipart/mixed stream.
        return $this->httpClient->request(
            'GET',
            $path,
            null,
            false,
            array('Accept' => 'application/json')
        );
    }

    /**
     * Find many documents by passing their ids and return the HTTP response.
     *
     * @param array $ids
     * @param null $limit
     * @param null $offset
     * @return HTTP\Response
     */
    public function findDocuments(array $ids, $limit = null, $offset = null)
    {
        $allDocsPath = '/' . $this->databaseName . '/_all_docs?include_docs=true';
        if ($limit) {
            $allDocsPath .= '&limit=' . (int)$limit;
        }
        if ($offset) {
            $allDocsPath .= '&skip=' . (int)$offset;
        }

        return $this->httpClient->request('POST', $allDocsPath, json_encode(
            array('keys' => array_values($ids)))
        );
    }

    /**
     * Get all documents
     *
     * @param int|null $limit
     * @param string|null $startKey
     * @param string|null $endKey
     * @param int|null $skip
     * @param bool $descending
     * @return HTTP\Response
     */
    public function allDocs($limit = null, $startKey = null, $endKey = null, $skip = null, $descending = false)
    {
        $allDocsPath = '/' . $this->databaseName . '/_all_docs?include_docs=true';
        if ($limit) {
            $allDocsPath .= '&limit=' . (int)$limit;
        }
        if ($startKey) {
            $allDocsPath .= '&startkey="' . (string)$startKey.'"';
        }
        if (!is_null($endKey)) {
            $allDocsPath .= '&endkey="' . (string)$endKey.'"';
        }
        if (!is_null($skip) && (int)$skip > 0) {
            $allDocsPath .= '&skip=' . (int)$skip;
        }
        if (!is_null($descending) && (bool)$descending === true) {
            $allDocsPath .= '&descending=true';
        }
        return $this->httpClient->request('GET', $allDocsPath);
    }

    /**
     * Get the current version of CouchDB.
     *
     * @throws HTTPException
     * @return string
     */
    public function getVersion()
    {
        if ($this->version === null) {
            $response = $this->httpClient->request('GET', '/');
            if ($response->status != 200) {
                throw HTTPException::fromResponse('/', $response);
            }

            $this->version = $response->body['version'];
        }
        return $this->version;
    }

    /**
     * Get all databases
     *
     * @throws HTTPException
     * @return array
     */
    public function getAllDatabases()
    {
        $response = $this->httpClient->request('GET', '/_all_dbs');
        if ($response->status != 200) {
            throw HTTPException::fromResponse('/_all_dbs', $response);
        }

        return $response->body;
    }

    /**
     * Create a new database
     *
     * @throws HTTPException
     * @param string $name
     * @return void
     */
    public function createDatabase($name)
    {
        $response = $this->httpClient->request('PUT', '/' . urlencode($name));

        if ($response->status != 201) {
            throw HTTPException::fromResponse('/' . urlencode($name), $response);
        }
    }

    /**
     * Drop a database
     *
     * @throws HTTPException
     * @param string $name
     * @return void
     */
    public function deleteDatabase($name)
    {
        $response = $this->httpClient->request('DELETE', '/' . urlencode($name));

        if ($response->status != 200 && $response->status != 404) {
            throw HTTPException::fromResponse('/' . urlencode($name), $response);
        }
    }

    /**
     * Get Information about a database.
     *
     * @param  string $name
     * @return array
     * @throws HTTPException
     */
    public function getDatabaseInfo($name = null)
    {
        $response = $this->httpClient->request('GET', '/' . ($name ? urlencode($name) : $this->databaseName));

        if ($response->status != 200) {
            throw HTTPException::fromResponse('/' . urlencode($name), $response);
        }

        return $response->body;
    }

    /**
     * Get changes.
     *
     * @param array $params
     * @return array
     * @throws HTTPException
     */
    public function getChanges(array $params = array())
    {
        $path = '/' . $this->databaseName . '/_changes';

        $method = ((!isset($params['doc_ids']) || $params['doc_ids'] == null) ? "GET" : "POST");
        $response = '';

        if ($method == "GET") {

            foreach ($params as $key => $value) {
                if (isset($params[$key]) === true && is_bool($value) === true) {
                    $params[$key] = ($value) ? 'true' : 'false';
                }
            }
            if (count($params) > 0) {
                $query = http_build_query($params);
                $path = $path.'?'.$query;
            }
            $response = $this->httpClient->request('GET', $path);

        } else {
            $path .= '?filter=_doc_ids';
            $response = $this->httpClient->request('POST', $path, json_encode($params));
        }
        if ($response->status != 200) {
            throw HTTPException::fromResponse($path, $response);
        }

        return $response->body;
    }

    /**
     * Create a bulk updater instance.
     *
     * @return BulkUpdater
     */
    public function createBulkUpdater()
    {
        return new BulkUpdater($this->httpClient, $this->databaseName);
    }

    /**
     * Execute a POST request against CouchDB inserting a new document, leaving the server to generate a uuid.
     *
     * @param  array $data
     * @return array<id, rev>
     * @throws HTTPException
     */
    public function postDocument(array $data)
    {
        $path = '/' . $this->databaseName;
        $response = $this->httpClient->request('POST', $path, json_encode($data));

        if ($response->status != 201) {
            throw HTTPException::fromResponse($path, $response);
        }

        return array($response->body['id'], $response->body['rev']);
    }

    /**
     * Execute a PUT request against CouchDB inserting or updating a document.
     *
     * @param array $data
     * @param string $id
     * @param string|null $rev
     * @return array<id, rev>
     * @throws HTTPException
     */
    public function putDocument($data, $id, $rev = null)
    {
        $data['_id'] = $id;
        if ($rev) {
            $data['_rev'] = $rev;
        }

        $path = '/' . $this->databaseName . '/' . urlencode($id);
        $response = $this->httpClient->request('PUT', $path, json_encode($data));

        if ($response->status != 201) {
            throw HTTPException::fromResponse($path, $response);
        }

        return array($response->body['id'], $response->body['rev']);
    }

    /**
     * Delete a document.
     *
     * @param  string $id
     * @param  string $rev
     * @return void
     * @throws HTTPException
     */
    public function deleteDocument($id, $rev)
    {
        $path = '/' . $this->databaseName . '/' . urlencode($id) . '?rev=' . $rev;
        $response = $this->httpClient->request('DELETE', $path);

        if ($response->status != 200) {
            throw HTTPException::fromResponse($path, $response);
        }
    }

    /**
     * @param string $designDocName
     * @param string $viewName
     * @param DesignDocument $designDoc
     * @return View\Query
     */
    public function createViewQuery($designDocName, $viewName, DesignDocument $designDoc = null)
    {
        return new View\Query($this->httpClient, $this->databaseName, $designDocName, $viewName, $designDoc);
    }

    /**
     * Create or update a design document from the given in memory definition.
     *
     * @param string $designDocName
     * @param DesignDocument $designDoc
     * @return HTTP\Response
     */
    public function createDesignDocument($designDocName, DesignDocument $designDoc)
    {
        $data        = $designDoc->getData();
        $data['_id'] = '_design/' . $designDocName;

        $documentPath = '/' . $this->databaseName . '/' . $data['_id'];
        $response     = $this->httpClient->request( 'GET', $documentPath );

        if ($response->status == 200) {
            $docData = $response->body;
            $data['_rev'] = $docData['_rev'];
        }

        return $this->httpClient->request(
            "PUT",
            sprintf("/%s/_design/%s", $this->databaseName, $designDocName),
            json_encode($data)
        );
    }

    /**
     * GET /db/_compact
     *
     * Return array of data about compaction status.
     *
     * @return array
     * @throws HTTPException
     */
    public function getCompactInfo()
    {
        $path = sprintf('/%s/_compact', $this->databaseName);
        $response = $this->httpClient->request('GET', $path);
        if ($response->status >= 400) {
            throw HTTPException::fromResponse($path, $response);
        }
        return $response->body;
    }

    /**
     * POST /db/_compact
     *
     * @return array
     * @throws HTTPException
     */
    public function compactDatabase()
    {
        $path = sprintf('/%s/_compact', $this->databaseName);
        $response = $this->httpClient->request('POST', $path);
        if ($response->status >= 400) {
            throw HTTPException::fromResponse($path, $response);
        }
        return $response->body;
    }

    /**
     * POST /db/_compact/designDoc
     *
     * @param string $designDoc
     * @return array
     * @throws HTTPException
     */
    public function compactView($designDoc)
    {
        $path = sprintf('/%s/_compact/%s', $this->databaseName, $designDoc);
        $response = $this->httpClient->request('POST', $path);
        if ($response->status >= 400) {
            throw HTTPException::fromResponse($path, $response);
        }
        return $response->body;
    }

    /**
     * POST /db/_view_cleanup
     *
     * @return array
     * @throws HTTPException
     */
    public function viewCleanup()
    {
        $path = sprintf('/%s/_view_cleanup', $this->databaseName);
        $response = $this->httpClient->request('POST', $path);
        if ($response->status >= 400) {
            throw HTTPException::fromResponse($path, $response);
        }
        return $response->body;
    }

    /**
     * POST /db/_replicate
     *
     * @param string $source
     * @param string $target
     * @param bool|null $cancel
     * @param bool|null $continuous
     * @param string|null $filter
     * @param array|null $ids
     * @param string|null $proxy
     * @return array
     * @throws HTTPException
     */
    public function replicate($source, $target, $cancel = null, $continuous = null, $filter = null, array $ids = null, $proxy = null)
    {
        $params = array('target' => $target, 'source' => $source);
        if ($cancel !== null) {
            $params['cancel'] = (bool)$cancel;
        }
        if ($continuous !== null) {
            $params['continuous'] = (bool)$continuous;
        }
        if ($filter !== null) {
            $params['filter'] = $filter;
        }
        if ($ids !== null) {
            $params['doc_ids'] = $ids;
        }
        if ($proxy !== null) {
            $params['proxy'] = $proxy;
        }
        $path = '/_replicate';
        $response = $this->httpClient->request('POST', $path, json_encode($params));
        if ($response->status >= 400) {
            throw HTTPException::fromResponse($path, $response);
        }
        return $response->body;
    }

    /**
     * GET /_active_tasks
     *
     * @return array
     * @throws HTTPException
     */
    public function getActiveTasks()
    {
        $response = $this->httpClient->request('GET', '/_active_tasks');
        if ($response->status != 200) {
            throw HTTPException::fromResponse('/_active_tasks', $response);
        }
        return $response->body;
    }

    /**
     * Get revision difference.
     *
     * @param  array $data
     * @return array
     * @throws HTTPException
     */
    public function getRevisionDifference($data)
    {
        $path = '/' . $this->databaseName . '/_revs_diff';
        $response = $this->httpClient->request('POST', $path, json_encode($data));
        if ($response->status != 200) {
            throw HTTPException::fromResponse($path, $response);
        }
        return $response->body;
    }

    /**
     * Transfer missing revisions to the target. The Content-Type of response
     * from the source should be multipart/mixed.
     *
     * @param string $docId
     * @param array $missingRevs
     * @param CouchDBClient $target
     * @return array|HTTP\ErrorResponse|string
     * @throws HTTPException
     */
    public function transferChangedDocuments($docId, $missingRevs, CouchDBClient $target)
    {
        $path = '/' . $this->getDatabase() . '/' . $docId;
        $params = array('revs' => true, 'latest' => true, 'open_revs' => json_encode($missingRevs));
        $query = http_build_query($params);
        $path .= '?' . $query;

        $targetPath = '/' . $target->getDatabase() . '/' . $docId . '?new_edits=false';

        $mutltipartHandler = new MultipartParserAndSender($this->getHttpClient(), $target->getHttpClient());
        return $mutltipartHandler->request(
            'GET',
            $path,
            $targetPath,
            null,
            array('Accept' => 'multipart/mixed')
        );

    }

    /**
     * Get changes as a stream.
     *
     * This method similar to the getChanges() method. But instead of returning
     * the set of changes, it returns the connection stream from which the response
     * can be read line by line. This is useful when you want to continuously get changes
     * as they occur. Filtered changes feed is not supported by this method.
     *
     * @param array $params
     * @param bool $raw
     * @return resource
     * @throws HTTPException
     */
    public function getChangesAsStream(array $params = array())
    {
        // Set feed to continuous.
        if (!isset($params['feed']) || $params['feed'] != 'continuous') {
            $params['feed'] = 'continuous';
        }
        $path = '/' . $this->databaseName . '/_changes';
        $connectionOptions = $this->getHttpClient()->getOptions();
        $streamClient = new StreamClient(
            $connectionOptions['host'],
            $connectionOptions['port'],
            $connectionOptions['username'],
            $connectionOptions['password'],
            $connectionOptions['ip'],
            $connectionOptions['ssl'],
            $connectionOptions['path']
        );

        foreach ($params as $key => $value) {
            if (isset($params[$key]) === true && is_bool($value) === true) {
                $params[$key] = ($value) ? 'true' : 'false';
            }
        }
        if (count($params) > 0) {
            $query = http_build_query($params);
            $path = $path . '?' . $query;
        }
        $stream = $streamClient->getConnection('GET', $path, null);

        $headers = $streamClient->getStreamHeaders($stream);
        if (empty($headers['status'])) {
            throw HTTPException::readFailure(
                $connectionOptions['ip'],
                $connectionOptions['port'],
                'Received an empty response or not status code',
                0
            );
        } elseif ($headers['status'] != 200) {
            $body = '';
            while (!feof($stream)) {
                $body .= fgets($stream);
            }
            throw HTTPException::fromResponse($path, new Response($headers['status'], $headers, $body));
        }
        // Everything seems okay. Return the connection resource.
        return $stream;

    }

    /**
     * Commit any recent changes to the specified database to disk.
     *
     * @return array
     * @throws HTTPException
     */
    public function ensureFullCommit()
    {
        $path = '/' . $this->databaseName . '/_ensure_full_commit';
        $response = $this->httpClient->request('POST', $path);
        if ($response->status != 201) {
            throw HTTPException::fromResponse($path, $response);
        }
        return $response->body;
    }
}
