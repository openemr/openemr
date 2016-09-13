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
namespace Doctrine\CouchDB\Tools\Migrations;

use Doctrine\CouchDB\CouchDBClient;

/**
 * Migration base class
 */
abstract class AbstractMigration
{
    private $client;

    public function __construct(CouchDBClient $client)
    {
        $this->client = $client;
    }

    /**
     * Execute migration by iterating over all documents in batches of 100.
     *
     * @return void
     * @throws \RuntimeException
     */
    public function execute()
    {
        $response = $this->client->allDocs(100);
        $lastKey = null;

        do {
            if ($response->status !== 200) {
                throw new \RuntimeException("Error while migrating at offset " . $offset);
            }

            $bulkUpdater = $this->client->createBulkUpdater();
            foreach ($response->body['rows'] AS $row) {
                $doc = $this->migrate($row['doc']);
                if ($doc) {
                    $bulkUpdater->updateDocument($doc);
                }
                $lastKey = $row['key'];
            }

            $bulkUpdater->execute();
            $response = $this->client->allDocs(100, $lastKey);
        } while (count($response->body['rows']) > 1);
    }

    /**
     * Return an array of to migrate to document data or null if this document should not be migrated.
     *
     * @param array $docData
     * @return array|bool|null $docData
     */
    abstract protected function migrate(array $docData);
}
