<?php

/**
 * @see       https://github.com/laminas/laminas-soap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-soap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-soap/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Soap\Server;

use Laminas\Soap\Exception;
use ReflectionObject;

/**
 * Wraps WSDL Document/Literal Style service objects to hide SOAP request
 * message abstraction from the actual service object.
 *
 * When using the document/literal SOAP message pattern you end up with one
 * object passed to your service methods that contains all the parameters of
 * the method. This obviously leads to a problem since Laminas\Soap\Wsdl tightly
 * couples method parameters to request message parameters.
 *
 * Example:
 *
 * <code>
 * class MyCalculatorService
 * {
 *     /**
 *      * @param int $x
 *      * @param int $y
 *      * @return int
 *      *
 *     public function add($x, $y)
 *     {
 *     }
 * }
 * </code>
 *
 * The document/literal wrapper pattern would lead php ext/soap to generate a
 * single "request" object that contains $x and $y properties. To solve this a
 * wrapper service is needed that extracts the properties and delegates a
 * proper call to the underlying service.
 *
 * The input variable from a document/literal SOAP-call to the client
 * MyCalculatorServiceClient#add(10, 20) would lead PHP ext/soap to create
 * the following request object:
 *
 * <code>
 * $addRequest = new \stdClass;
 * $addRequest->x = 10;
 * $addRequest->y = 20;
 * </code>
 *
 * This object does not match the signature of the server-side
 * MyCalculatorService and lead to failure.
 *
 * Also the response object in this case is supposed to be an array
 * or object with a property "addResult":
 *
 * <code>
 * $addResponse = new \stdClass;
 * $addResponse->addResult = 30;
 * </code>
 *
 * To keep your service object code free from this implementation detail
 * of SOAP this wrapper service handles the parsing between the formats.
 *
 * @example
 * <code>
 *  $service = new MyCalculatorService();
 *  $soap = new \Laminas\Soap\Server($wsdlFile);
 *  $soap->setObject(new \Laminas\Soap\Server\DocumentLiteralWrapper($service));
 *  $soap->handle();
 * </code>
 */
class DocumentLiteralWrapper
{
    /**
     * @var object
     */
    protected $object;

    /**
     * @var ReflectionObject
     */
    protected $reflection;

    /**
     * Pass Service object to the constructor
     *
     * @param object $object
     */
    public function __construct($object)
    {
        $this->object = $object;
        $this->reflection = new ReflectionObject($this->object);
    }

    /**
     * Proxy method that does the heavy document/literal decomposing.
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        $this->assertOnlyOneArgument($args);
        $this->assertServiceDelegateHasMethod($method);

        $delegateArgs = $this->parseArguments($method, $args[0]);
        $ret          = call_user_func_array([$this->object, $method], $delegateArgs);
        return $this->getResultMessage($method, $ret);
    }

    /**
     * Parse the document/literal wrapper into arguments to call the real
     * service.
     *
     * @param  string $method
     * @param  object $document
     * @return array
     * @throws Exception\UnexpectedValueException
     */
    protected function parseArguments($method, $document)
    {
        $reflMethod = $this->reflection->getMethod($method);
        $params = [];
        foreach ($reflMethod->getParameters() as $param) {
            $params[$param->getName()] = $param;
        }

        $delegateArgs = [];
        foreach (get_object_vars($document) as $argName => $argValue) {
            if (! isset($params[$argName])) {
                throw new Exception\UnexpectedValueException(sprintf(
                    "Received unknown argument %s which is not an argument to %s::%s",
                    $argName,
                    get_class($this->object),
                    $method
                ));
            }
            $delegateArgs[$params[$argName]->getPosition()] = $argValue;
        }

        return $delegateArgs;
    }

    /**
     * Returns result message content
     *
     * @param  string $method
     * @param  mixed $ret
     * @return array
     */
    protected function getResultMessage($method, $ret)
    {
        return [$method . 'Result' => $ret];
    }

    /**
     * @param  string $method
     * @throws Exception\BadMethodCallException
     */
    protected function assertServiceDelegateHasMethod($method)
    {
        if (! $this->reflection->hasMethod($method)) {
            throw new Exception\BadMethodCallException(sprintf(
                "Method %s does not exist on delegate object %s",
                $method,
                get_class($this->object)
            ));
        }
    }

    /**
     * @param  array $args
     * @throws Exception\UnexpectedValueException
     */
    protected function assertOnlyOneArgument(array $args)
    {
        if (count($args) != 1) {
            throw new Exception\UnexpectedValueException(sprintf(
                "Expecting exactly one argument that is the document/literal wrapper, got %d",
                count($args)
            ));
        }
    }
}
