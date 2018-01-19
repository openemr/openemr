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
use Doctrine\CouchDB\HTTP\HTTPException;

/**
 * An attachment is a special embedded document that exists inside CouchDB.
 * It is created inside the "Attachments" object for each attachment that is found.
 *
 * TODO: This is a very inefficient first version implementation that saves both
 * binary and base64 data of everything if possible to ease the API.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.com
 * @since       1.0
 * @author      Benjamin Eberlei <kontakt@beberlei.de>
 */
class Attachment
{
    /**
     * Content-Type of the Attachment
     *
     * If this is false on putting a new attachment into the database the
     * generic "application/octet-stream" type will be used.
     *
     * @var string
     */
    private $contentType = false;

    /**
     * Base64 Encoded tring of the Data.
     *
     * @var string
     */
    private $data;

    /**
     * @var string
     */
    private $binaryData;

    /**
     * This attachment only represented as stub, which means the attachment is standalone and not inline.
     *
     * WARNING: Never change this variable from true to false if you don't provide data.
     * CouchDB otherwise quits with an error: {"error":"unknown_error","reason":"function_clause"}
     *
     * @var bool
     */
    private $stub = true;

    /**
     * Size of the attachment.
     *
     * @var int
     */
    private $length = 0;

    /**
     * Revision Position field of this Attachment.
     *
     * @var int
     */
    private $revpos = 0;

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var string
     */
    private $path;

    /**
     * Get the content-type of this attachment.
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Get the length of the base64 encoded representation of this attachment.
     *
     * @return int
     */
    public function getLength()
    {
        if (!$this->stub && !is_int($this->length)) {
            $this->length = strlen($this->data);
        }
        return $this->length;
    }

    /**
     * Get the raw data of this attachment.
     *
     * @return string
     */
    public function getRawData()
    {
        $this->lazyLoad();

        return $this->binaryData;
    }

    /**
     * @return string
     */
    public function getBase64EncodedData()
    {
        $this->lazyLoad();

        return $this->data;
    }

    /**
     * Lazy Load Data from CouchDB if necessary
     *
     * @return void
     */
    private function lazyLoad()
    {
        if ($this->stub) {
            $response = $this->httpClient->request('GET', $this->path, null, true); // raw request
            if ($response->status != 200) {
                throw HTTPException::fromResponse($this->path, $response);
            }
            $this->stub = false;
            $this->binaryData = $response->body;
            $this->data = \base64_encode($this->binaryData);
        }
    }

    public function isLoaded()
    {
        return !$this->stub;
    }

    /**
     * Number of times an attachment was alreaady saved with the document, indicating in which revision it was added.
     *
     * @return int
     */
    public function getRevPos()
    {
        return $this->revpos;
    }

    /**
     * Attachments are special in how they need to be persisted depending on stub or not.
     *
     * TODO: Is this really necessary with all this special logic? Having attahments as special
     * case without special code would be really awesome.
     *
     * @return string
     */
    public function toArray()
    {
        if ($this->stub) {
            $json = array('stub' => true);
        } else {
            $json = array('data' => $this->getBase64EncodedData());
            if ($this->contentType) {
                $json['content_type'] = $this->contentType;
            }
        }
        return $json;
    }

    /**
     * @param string $binaryData
     * @param string $base64Data
     * @param string $contentType
     * @param int $length
     * @param int $revPos
     * @param Client $httpClient
     * @param string $path
     */
    final private function __construct($binaryData = null, $base64Data = null, $contentType = false, $length = false, $revPos = false, $httpClient = null, $path = null)
    {
        if ($binaryData || $base64Data) {
            $this->binaryData = $binaryData;
            $this->data = $base64Data;
            $this->stub =  false;
        } else {
            $this->stub = true;
        }
        $this->contentType = $contentType;
        $this->length = $length;
        $this->revpos = $revPos;
        $this->httpClient = $httpClient;
        $this->path = $path;
    }

    /**
     * Create an Attachment from a string or resource of binary data.
     *
     * WARNING: Changes to the file handle after calling this method will *NOT* be recognized anymore.
     *
     * @param string|resource $data
     * @param string $contentType
     * @return Attachment
     */
    static public function createFromBinaryData($data, $contentType = false)
    {
        if (\is_resource($data)) {
            $data = \stream_get_contents($data);
        }

        return new self($data, \base64_encode($data), $contentType);
    }

    /**
     * Create an attachment from base64 data.
     *
     * @param string $data
     * @param string $contentType
     * @param int $revpos
     * @return Attachment
     */
    static public function createFromBase64Data($data, $contentType = false, $revpos = false)
    {
        return new self(\base64_decode($data), $data, $contentType, false, $revpos);
    }

    /**
     * Create a stub attachment that has lazy loading capabilities.
     *
     * @param string $contentType
     * @param int $length
     * @param int $revPos
     * @param Client $httpClient
     * @param string $path
     * @return Attachment
     */
    static public function createStub($contentType, $length, $revPos, Client $httpClient, $path)
    {
        return new self(null, null, $contentType, $length, $revPos, $httpClient, $path);
    }
}
