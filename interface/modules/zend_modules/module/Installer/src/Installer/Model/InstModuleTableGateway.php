<?php
namespace Installer\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSetInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\TableIdentifier;

class InstModuleTableGateway extends TableGateway
{

    /**
     * Constructor
     *
     * @param string|TableIdentifier|array                                              $table
     * @param AdapterInterface                                                          $adapter
     * @param Feature\AbstractFeature|Feature\FeatureSet|Feature\AbstractFeature[]|null $features
     * @param ResultSetInterface|null                                                   $resultSetPrototype
     * @param Sql|null                                                                  $sql
     *
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(
        $table,
        AdapterInterface $adapter,
        $features = null,
        ResultSetInterface $resultSetPrototype = null,
        Sql $sql = null
    ) {
        parent::__construct($table, $adapter, $features, $resultSetPrototype, $sql);
    }
}
