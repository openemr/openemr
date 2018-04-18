<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zend-log for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Log\Writer;

use DateTimeInterface;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Manager;
use MongoDB\Driver\WriteConcern;
use MongoDB\BSON\UTCDateTime;
use Traversable;
use Zend\Log\Exception;
use Zend\Log\Formatter\FormatterInterface;
use Zend\Stdlib\ArrayUtils;

/**
 * MongoDB log writer.
 */
class MongoDB extends AbstractWriter
{
    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var string
     */
    protected $database;

    /**
     * @var WriteConcern
     */
    protected $writeConcern;

    /**
     * Constructor
     *
     * @param Manager|array|Traversable $manager
     * @param string $database
     * @param string $collection
     * @param WriteConcern|array|Traversable $writeConcern
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($manager, $database = null, $collection = null, $writeConcern = null)
    {
        if (! extension_loaded('mongodb')) {
            throw new Exception\ExtensionNotLoadedException('Missing ext/mongodb');
        }

        if ($manager instanceof Traversable) {
            // Configuration may be multi-dimensional due to save options
            $manager = ArrayUtils::iteratorToArray($manager);
        }

        if (is_array($manager)) {
            parent::__construct($manager);
            $writeConcern = isset($manager['write_concern']) ? $manager['write_concern'] : new WriteConcern(1);
            $collection   = isset($manager['collection']) ? $manager['collection'] : null;
            $database     = isset($manager['database']) ? $manager['database'] : null;
            $manager      = isset($manager['manager']) ? $manager['manager'] : null;
        }

        if (null === $database) {
            throw new Exception\InvalidArgumentException('The database parameter cannot be empty');
        }

        if (null !== $collection) {
            $database = sprintf('%s.%s', $database, $collection);
        }

        if (! $manager instanceof Manager) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Parameter of type %s is invalid; must be MongoDB\Driver\Manager',
                (is_object($manager) ? get_class($manager) : gettype($manager))
            ));
        }

        if ($writeConcern instanceof Traversable) {
            $writeConcern = iterator_to_array($writeConcern);
        }

        if (is_array($writeConcern)) {
            $wstring      = isset($writeConcern['wstring']) ? $writeConcern['wstring'] : 1;
            $wtimeout     = isset($writeConcern['wtimeout']) ? $writeConcern['wtimeout'] : 0;
            $journal      = isset($writeConcern['journal']) ? $writeConcern['journal'] : false;
            $writeConcern = new WriteConcern($wstring, $wtimeout, $journal);
        }

        $this->manager      = $manager;
        $this->database     = $database;
        $this->writeConcern = $writeConcern;
    }

    /**
     * This writer does not support formatting.
     *
     * @param string|FormatterInterface $formatter
     * @param array|null $options (unused)
     * @return WriterInterface
     */
    public function setFormatter($formatter, array $options = null)
    {
        return $this;
    }

    /**
     * Write a message to the log.
     *
     * @param array $event Event data
     * @return void
     * @throws Exception\RuntimeException
     */
    protected function doWrite(array $event)
    {
        if (null === $this->manager) {
            throw new Exception\RuntimeException('MongoDB\Driver\Manager must be defined');
        }

        if (isset($event['timestamp']) && $event['timestamp'] instanceof DateTimeInterface) {
            $millis = (int) floor((float) $event['timestamp']->format('U.u') * 1000);
            $event['timestamp'] = new UTCDateTime($millis);
        }

        $bulkWrite = new BulkWrite();
        $bulkWrite->insert($event);

        $this->manager->executeBulkWrite($this->database, $bulkWrite, $this->writeConcern);
    }
}
