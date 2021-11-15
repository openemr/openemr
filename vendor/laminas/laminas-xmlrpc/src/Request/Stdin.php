<?php

/**
 * @see       https://github.com/laminas/laminas-xmlrpc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-xmlrpc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-xmlrpc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\XmlRpc\Request;

use Laminas\XmlRpc\Fault;
use Laminas\XmlRpc\Request as XmlRpcRequest;

/**
 * XmlRpc Request object -- Request via STDIN
 *
 * Extends {@link Laminas\XmlRpc\Request} to accept a request via STDIN. Request is
 * built at construction time using data from STDIN; if no data is available, the
 * request is declared a fault.
 */
class Stdin extends XmlRpcRequest
{
    /**
     * Raw XML as received via request
     * @var string
     */
    protected $xml;

    /**
     * Constructor
     *
     * Attempts to read from php://stdin to get raw POST request; if an error
     * occurs in doing so, or if the XML is invalid, the request is declared a
     * fault.
     *
     */
    public function __construct()
    {
        $fh = fopen('php://stdin', 'r');
        if (! $fh) {
            $this->fault = new Fault(630);
            return;
        }

        $xml = '';
        while (! feof($fh)) {
            $xml .= fgets($fh);
        }
        fclose($fh);

        $this->xml = $xml;

        $this->loadXml($xml);
    }

    /**
     * Retrieve the raw XML request
     *
     * @return string
     */
    public function getRawRequest()
    {
        return $this->xml;
    }
}
