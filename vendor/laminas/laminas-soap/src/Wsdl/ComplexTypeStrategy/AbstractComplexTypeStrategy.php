<?php

/**
 * @see       https://github.com/laminas/laminas-soap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-soap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-soap/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Soap\Wsdl\ComplexTypeStrategy;

use Laminas\Soap\Wsdl;
use Laminas\Soap\Wsdl\DocumentationStrategy\DocumentationStrategyInterface;

/**
 * Abstract class for Laminas\Soap\Wsdl\Strategy.
 */
abstract class AbstractComplexTypeStrategy implements ComplexTypeStrategyInterface
{
    /**
     * Context object
     *
     * @var Wsdl
     */
    protected $context;

    /**
     * @var DocumentationStrategyInterface
     */
    protected $documentationStrategy;

    /**
     * Set the WSDL Context object this strategy resides in.
     *
     * @param Wsdl $context
     */
    public function setContext(Wsdl $context)
    {
        $this->context = $context;
    }

    /**
     * Return the current WSDL context object
     *
     * @return Wsdl
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Look through registered types
     *
     * @param string $phpType
     * @return null|string
     */
    public function scanRegisteredTypes($phpType)
    {
        if (array_key_exists($phpType, $this->getContext()->getTypes())) {
            $soapTypes = $this->getContext()->getTypes();
            return $soapTypes[$phpType];
        }
        return;
    }

    /**
     * Sets the strategy for generating complex type documentation
     *
     * @param DocumentationStrategyInterface $documentationStrategy
     * @return void
     */
    public function setDocumentationStrategy(DocumentationStrategyInterface $documentationStrategy)
    {
        $this->documentationStrategy = $documentationStrategy;
    }
}
