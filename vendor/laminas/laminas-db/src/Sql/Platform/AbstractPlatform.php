<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Sql\Platform;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Platform\PlatformInterface;
use Laminas\Db\Adapter\StatementContainerInterface;
use Laminas\Db\Sql\Exception;
use Laminas\Db\Sql\PreparableSqlInterface;
use Laminas\Db\Sql\SqlInterface;

class AbstractPlatform implements PlatformDecoratorInterface, PreparableSqlInterface, SqlInterface
{
    /**
     * @var object|null
     */
    protected $subject;

    /**
     * @var PlatformDecoratorInterface[]
     */
    protected $decorators = [];

    /**
     * {@inheritDoc}
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @param string                     $type
     * @param PlatformDecoratorInterface $decorator
     *
     * @return void
     */
    public function setTypeDecorator($type, PlatformDecoratorInterface $decorator)
    {
        $this->decorators[$type] = $decorator;
    }

    /**
     * @param PreparableSqlInterface|SqlInterface $subject
     * @return PlatformDecoratorInterface|PreparableSqlInterface|SqlInterface
     */
    public function getTypeDecorator($subject)
    {
        foreach ($this->decorators as $type => $decorator) {
            if ($subject instanceof $type) {
                $decorator->setSubject($subject);

                return $decorator;
            }
        }

        return $subject;
    }

    /**
     * @return array|PlatformDecoratorInterface[]
     */
    public function getDecorators()
    {
        return $this->decorators;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\RuntimeException
     */
    public function prepareStatement(AdapterInterface $adapter, StatementContainerInterface $statementContainer)
    {
        if (! $this->subject instanceof PreparableSqlInterface) {
            throw new Exception\RuntimeException(
                'The subject does not appear to implement Laminas\Db\Sql\PreparableSqlInterface, thus calling '
                . 'prepareStatement() has no effect'
            );
        }

        $this->getTypeDecorator($this->subject)->prepareStatement($adapter, $statementContainer);

        return $statementContainer;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\RuntimeException
     */
    public function getSqlString(PlatformInterface $adapterPlatform = null)
    {
        if (! $this->subject instanceof SqlInterface) {
            throw new Exception\RuntimeException(
                'The subject does not appear to implement Laminas\Db\Sql\SqlInterface, thus calling '
                . 'prepareStatement() has no effect'
            );
        }

        return $this->getTypeDecorator($this->subject)->getSqlString($adapterPlatform);
    }
}
