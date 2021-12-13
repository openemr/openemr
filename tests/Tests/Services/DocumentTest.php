<?php

/**
 * DocumentTest tests the \Document object
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services;

use Document;
use Monolog\Test\TestCase;
use OpenEMR\Services\UserService;
use Ramsey\Uuid\Uuid;

class DocumentTest extends TestCase
{
    /**
     * Checks that a document can be created and the file exists at the location the document says it saves at
     */
    public function testCreateDocument()
    {
        $userService = new UserService();
        $apiSystemUser = $userService->getSystemUser();

        $jobId = Uuid::uuid4();
        $document = new \Document();
        $folder = 'services-test-file';
        $categoryId = null;
        $fileName = "Patient-" . $jobId . ".ndjson";
        $fullPath =  $folder . DIRECTORY_SEPARATOR . $fileName;

        $data = json_encode(['id' => $jobId]);

        $mimeType = "application/fhir+ndjson";
        $higherLevelPath = "";
        $pathDepth = 1;
        $owner = $apiSystemUser['id'];  // userID, if we didn't have one it would default to the session
        $document->createDocument($jobId, $categoryId, $fullPath, $mimeType, $data, $higherLevelPath, $pathDepth, $owner);

        $this->assertNotEmpty($document->get_id(), "database id should be populated");
        $this->assertEquals($folder . '/' . $fileName, $document->get_name(), "Saved document should have a matching name");
        $this->assertEquals($mimeType, $document->get_mimetype());

        $url = $document->get_url();
        if ($document->get_storagemethod() === Document::STORAGE_METHOD_FILESYSTEM) {
            // not sure how the couch db tests should work but we are going to verify if the file is stored on the file
            // system that we did indeed write a file here.
            if (strpos($url, 'file:') !== false) {
                $this->assertTrue(file_exists($document->get_url_filepath(), "File should exist at document location"));
            }
        }
    }
}
