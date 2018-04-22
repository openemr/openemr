<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Db\Sql\Platform\Sqlite;

use Zend\Db\Adapter\Driver\DriverInterface;
use Zend\Db\Adapter\ParameterContainer;
use Zend\Db\Adapter\Platform\PlatformInterface;
use Zend\Db\Sql\Platform\PlatformDecoratorInterface;
use Zend\Db\Sql\Select;

class SelectDecorator extends Select implements PlatformDecoratorInterface
{
    /**
     * @var Select
     */
    protected $subject = null;

    /**
     * Set Subject
     *
     * @param Select $select
     * @return self Provides a fluent interface
     */
    public function setSubject($select)
    {
        $this->subject = $select;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function localizeVariables()
    {
        parent::localizeVariables();
        $this->specifications[self::COMBINE] = '%1$s %2$s';
    }

    /**
     * {@inheritDoc}
     */
    protected function processStatementStart(
        PlatformInterface $platform,
        DriverInterface $driver = null,
        ParameterContainer $parameterContainer = null
    ) {
        return '';
    }

    protected function processLimit(
        PlatformInterface $platform,
        DriverInterface $driver = null,
        ParameterContainer $parameterContainer = null
    ) {
        if ($this->limit === null && $this->offset !== null) {
            return [''];
        }
        if ($this->limit === null) {
            return;
        }
        if ($parameterContainer) {
            $paramPrefix = $this->processInfo['paramPrefix'];
            $parameterContainer->offsetSet($paramPrefix . 'limit', $this->limit, ParameterContainer::TYPE_INTEGER);
            return [$driver->formatParameterName('limit')];
        }

        return [$this->limit];
    }

    protected function processOffset(
        PlatformInterface $platform,
        DriverInterface $driver = null,
        ParameterContainer $parameterContainer = null
    ) {
        if ($this->offset === null) {
            return;
        }
        if ($parameterContainer) {
            $paramPrefix = $this->processInfo['paramPrefix'];
            $parameterContainer->offsetSet($paramPrefix . 'offset', $this->offset, ParameterContainer::TYPE_INTEGER);
            return [$driver->formatParameterName('offset')];
        }

        return [$this->offset];
    }

    /**
     * {@inheritDoc}
     */
    protected function processStatementEnd(
        PlatformInterface $platform,
        DriverInterface $driver = null,
        ParameterContainer $parameterContainer = null
    ) {
        return '';
    }
}
