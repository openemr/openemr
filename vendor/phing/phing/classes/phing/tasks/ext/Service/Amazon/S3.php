<?php

/*
 *  $Id: c858af9a61ef05f61b1ae1c818a58ce7b0f3505a $
 *
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
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

require_once dirname(dirname(__FILE__)) . "/Amazon.php";

/**
 * Abstract Service_Amazon_S3 class.
 *
 * Provides common methods and properties to all of the S3 tasks
 *
 * @version $ID$
 * @package phing.tasks.ext
 * @author Andrei Serdeliuc <andrei@serdeliuc.ro>
 */
abstract class Service_Amazon_S3 extends Service_Amazon
{
    /**
     * Services_Amazon_S3 client
     *
     * (default value: null)
     *
     * @var Services_Amazon_S3
     * @see Services_Amazon_S3
     */
    protected $_client = null;

    /**
     * We only instantiate the client once per task call
     *
     * @return Services_Amazon_S3
     */
    public function getClient()
    {
        require_once "Services/Amazon/S3.php";

        if ($this->_client === null) {
            $this->_client = Services_Amazon_S3::getAccount($this->getKey(), $this->getSecret());
        }

        return $this->_client;
    }

    /**
     * @param $bucket
     * @throws BuildException
     */
    public function setBucket($bucket)
    {
        if (empty($bucket) || !is_string($bucket)) {
            throw new BuildException('Bucket must be a non-empty string');
        }

        $this->bucket = (string) $bucket;
    }

    /**
     * @throws BuildException
     */
    public function getBucket()
    {
        if (!($bucket = $this->bucket)) {
            throw new BuildException('Bucket is not set');
        }

        return $this->bucket;
    }

    /**
     * Returns an instance of Services_Amazon_S3_Resource_Object
     *
     * @param  mixed                              $object
     * @return Services_Amazon_S3_Resource_Object
     */
    public function getObjectInstance($object)
    {
        return $this->getBucketInstance()->getObject($object);
    }

    /**
     * Check if the object already exists in the current bucket
     *
     * @param  mixed $object
     * @return bool
     */
    public function isObjectAvailable($object)
    {
        return (bool) $this->getObjectInstance($object)->load(Services_Amazon_S3_Resource_Object::LOAD_METADATA_ONLY);
    }

    /**
     * Returns an instance of Services_Amazon_S3_Resource_Bucket
     *
     * @return Services_Amazon_S3_Resource_Bucket
     */
    public function getBucketInstance()
    {
        return $this->getClient()->getBucket($this->getBucket());
    }

    /**
     * Check if the current bucket is available
     *
     * @return bool
     */
    public function isBucketAvailable()
    {
        return (bool) $this->getBucketInstance($this->getBucket())->load();
    }

    /**
     * Get the contents of an object (by it's name)
     *
     * @param  string $object
     * @throws BuildException
     * @return mixed
     */
    public function getObjectContents($object)
    {
        if (!$this->isBucketAvailable($this->getBucket())) {
            throw new BuildException('Bucket doesn\'t exist or wrong permissions');
        }

        $bucket = $this->getClient()->getBucket($this->getBucket());
        if (!$this->isObjectAvailable($object)) {
            throw new BuildException('Object not available: ' . $object);
        }

        $object = $this->getObjectInstance($object);
        $object->load();

        return $object->data;
    }

    /**
     * Create a bucket
     *
     * @return bool
     */
    public function createBucket()
    {
        $bucket = $this->getBucketInstance();
        $bucket->name = $this->getBucket();
        $bucket->save();

        return $this->isBucketAvailable();
    }

    /**
     * Main entry point, doesn't do anything
     *
     * @return void
     */
    final public function main()
    {
        $this->execute();
    }

    /**
     * Entry point to children tasks
     *
     * @return void
     */
    abstract public function execute();
}
