<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Session\SaveHandler;

use MongoDB\BSON\Binary;
use MongoDB\BSON\UTCDatetime;
use MongoDB\Client as MongoClient;
use MongoDB\Collection as MongoCollection;
use Zend\Session\Exception\InvalidArgumentException;

/**
 * MongoDB session save handler
 */
class MongoDB implements SaveHandlerInterface
{
    /**
     * MongoClient instance
     *
     * @var MongoClient
     */
    protected $mongoClient;

    /**
     * MongoCollection instance
     *
     * @var MongoCollection
     */
    protected $mongoCollection;

    /**
     * Session name
     *
     * @var string
     */
    protected $sessionName;

    /**
     * Session lifetime
     *
     * @var int
     */
    protected $lifetime;

    /**
     * MongoDB session save handler options
     * @var MongoDBOptions
     */
    protected $options;

    /**
     * Constructor
     *
     * @param MongoClient $mongoClient
     * @param MongoDBOptions $options
     * @throws InvalidArgumentException
     */
    public function __construct($mongoClient, MongoDBOptions $options)
    {
        if (null === ($database = $options->getDatabase())) {
            throw new InvalidArgumentException('The database option cannot be empty');
        }

        if (null === ($collection = $options->getCollection())) {
            throw new InvalidArgumentException('The collection option cannot be empty');
        }

        $this->mongoClient = $mongoClient;
        $this->options = $options;
    }

    /**
     * Open session
     *
     * @param string $savePath
     * @param string $name
     * @return bool
     */
    public function open($savePath, $name)
    {
        // Note: session save path is not used
        $this->sessionName = $name;
        $this->lifetime    = (int) ini_get('session.gc_maxlifetime');

        $this->mongoCollection = $this->mongoClient->selectCollection(
            $this->options->getDatabase(),
            $this->options->getCollection()
        );

        $this->mongoCollection->createIndex(
            [$this->options->getModifiedField() => 1],
            $this->options->useExpireAfterSecondsIndex() ? ['expireAfterSeconds' => $this->lifetime] : []
        );

        return true;
    }

    /**
     * Close session
     *
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * Read session data
     *
     * @param string $id
     * @return string
     */
    public function read($id)
    {
        $session = $this->mongoCollection->findOne([
            '_id' => $id,
            $this->options->getNameField() => $this->sessionName,
        ]);

        if (null !== $session) {
            // check if session has expired if index is not used
            if (! $this->options->useExpireAfterSecondsIndex()) {
                $timestamp = $session[$this->options->getLifetimeField()];
                $timestamp += floor(((string)$session[$this->options->getModifiedField()]) / 1000);

                // session expired
                if ($timestamp <= time()) {
                    $this->destroy($id);
                    return '';
                }
            }
            return $session[$this->options->getDataField()]->getData();
        }

        return '';
    }

    /**
     * Write session data
     *
     * @param string $id
     * @param string $data
     * @return bool
     */
    public function write($id, $data)
    {
        $saveOptions = array_replace(
            $this->options->getSaveOptions(),
            ['upsert' => true, 'multiple' => false]
        );

        $criteria = [
            '_id' => $id,
            $this->options->getNameField() => $this->sessionName,
        ];

        $newObj = [
            '$set' => [
                $this->options->getDataField() => new Binary((string)$data, Binary::TYPE_GENERIC),
                $this->options->getLifetimeField() => $this->lifetime,
                $this->options->getModifiedField() => new UTCDatetime(floor(microtime(true) * 1000)),
            ],
        ];

        /* Note: a MongoCursorException will be thrown if a record with this ID
         * already exists with a different session name, since the upsert query
         * cannot insert a new document with the same ID and new session name.
         * This should only happen if ID's are not unique or if the session name
         * is altered mid-process.
         */
        $result = $this->mongoCollection->updateOne($criteria, $newObj, $saveOptions);

        return $result->isAcknowledged();
    }

    /**
     * Destroy session
     *
     * @param string $id
     * @return bool
     */
    public function destroy($id)
    {
        $result = $this->mongoCollection->deleteOne(
            [
                '_id' => $id,
                $this->options->getNameField() => $this->sessionName,
            ],
            $this->options->getSaveOptions()
        );

        return $result->isAcknowledged();
    }

    /**
     * Garbage collection
     *
     * Note: MongoDB 2.2+ supports TTL collections, which may be used in place
     * of this method by indexing the "modified" field with an
     * "expireAfterSeconds" option. Regardless of whether TTL collections are
     * used, consider indexing this field to make the remove query more
     * efficient.
     *
     * @see http://docs.mongodb.org/manual/tutorial/expire-data/
     * @param int $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {
        /* Note: unlike DbTableGateway, we do not use the lifetime field in
         * each document. Doing so would require a $where query to work with the
         * computed value (modified + lifetime) and be very inefficient.
         */
        $microseconds = floor(microtime(true) * 1000) - $maxlifetime;

        $result = $this->mongoCollection->deleteMany(
            [
                $this->options->getModifiedField() => ['$lt' => new UTCDateTime($microseconds)],
            ],
            $this->options->getSaveOptions()
        );

        return $result->isAcknowledged();
    }
}
