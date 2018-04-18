<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Dom;

use ErrorException;

/**
 * Extends DOMXpath to throw ErrorExceptions instead of raising errors.
 */
class DOMXPath extends \DOMXPath
{
    /**
     * A stack of ErrorExceptions created via addError()
     *
     * @var array
     */
    protected $errors = [null];

    /**
     * Evaluates an XPath expression; throws an ErrorException instead of
     * raising an error
     *
     * @param string $expression The XPath expression to evaluate.
     * @param \DOMNode $contextNode
     * @return \DOMNodeList
     * @throws ErrorException
     */
    public function queryWithErrorException($expression, \DOMNode $contextNode = null)
    {
        $this->errors = [null];

        if ($contextNode === null) {
            $contextNode = $this->document->documentElement;
        }

        set_error_handler([$this, 'addError'], \E_WARNING);
        $nodeList = $this->query($expression, $contextNode);
        restore_error_handler();

        $exception = array_pop($this->errors);
        if ($exception) {
            throw $exception;
        }

        return $nodeList;
    }

    /**
     * Adds an error to the stack of errors
     *
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile
     * @param int    $errline
     * @return void
     */
    public function addError($errno, $errstr = '', $errfile = '', $errline = 0)
    {
        $last_error = end($this->errors);
        $this->errors[] = new ErrorException(
            $errstr,
            0,
            $errno,
            $errfile,
            $errline,
            $last_error
        );
    }
}
