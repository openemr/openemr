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


namespace Doctrine\CouchDB\Utils;

use Doctrine\CouchDB\HTTP\Client;

/**
 * Bulk updater class
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.com
 * @since       1.0
 * @author      Benjamin Eberlei <kontakt@beberlei.de>
 */
class BulkUpdater
{
    private $data = array('docs' => array());

    private $requestHeaders = array();

    private $httpClient;

    private $databaseName;

    public function __construct(Client $httpClient, $databaseName)
    {
        $this->httpClient = $httpClient;
        $this->databaseName = $databaseName;
    }

    public function setAllOrNothing($allOrNothing)
    {
        $this->data['all_or_nothing'] = (bool)$allOrNothing;
    }

    public function updateDocument($data)
    {
        $this->data['docs'][] = $data;
    }

    public function updateDocuments(array $docs)
    {
        foreach ($docs as $doc) {
            $this->data['docs'][] = (is_array($doc) ? $doc : json_decode($doc, true));
        }
    }

    public function deleteDocument($id, $rev)
    {
        $this->data['docs'][] = array('_id' => $id, '_rev' => $rev, '_deleted' => true);
    }

    public function setNewEdits($newEdits)
    {
        $this->data["new_edits"] = (bool)$newEdits;
    }

    public function setFullCommitHeader($commit)
    {
        $this->requestHeaders['X-Couch-Full-Commit'] = (bool)$commit;
    }

    public function execute()
    {
        return $this->httpClient->request('POST', $this->getPath(), json_encode($this->data), false, $this->requestHeaders);
    }

    public function getPath()
    {
        return '/' . $this->databaseName . '/_bulk_docs';
    }
}
