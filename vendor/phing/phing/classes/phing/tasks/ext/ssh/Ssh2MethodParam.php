<?php
/*
 *  $Id: 9356d19c55fef22aba2dcda0fff929690af41a94 $
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

require_once 'phing/types/DataType.php';
require_once 'Ssh2MethodConnectionParam.php';

/**
 * Class that holds parameters for an ssh2_connect $methods parameter
 * This corresponds to the optional $methods parameter
 * for the ssh2_connect function
 * @see http://php.net/ssh2_connect
 *
 * @author Derek Gallo <http://github.com/drock>
 *
 * @package   phing.tasks.ext
 */
class Ssh2MethodParam extends DataType
{
    /**
     * @var string
     */
    private $kex;

    /**
     * @var string
     */
    private $hostkey;

    /**
     * @var Ssh2MethodConnectionParam
     */
    private $client_to_server;

    /**
     * @var Ssh2MethodConnectionParam
     */
    private $server_to_client;

    /**
     * @param string $hostkey
     */
    public function setHostkey($hostkey)
    {
        $this->hostkey = $hostkey;
    }

    /**
     * @param Project $p
     * @throws BuildException
     * @return string
     */
    public function getHostkey(Project $p)
    {
        if ($this->isReference()) {
            return $this->getRef($p)->getHostkey($p);
        }

        return $this->hostkey;
    }

    /**
     * @param string $kex
     */
    public function setKex($kex)
    {
        $this->kex = $kex;
    }

    /**
     * @param Project $p
     * @throws BuildException
     * @return string
     */
    public function getKex(Project $p)
    {
        if ($this->isReference()) {
            return $this->getRef($p)->getKex($p);
        }

        return $this->kex;
    }

    /**
     * @param Project $p
     * @throws BuildException
     * @return \Ssh2MethodConnectionParam
     */
    public function getClientToServer(Project $p)
    {
        if ($this->isReference()) {
            return $this->getRef($p)->getClientToServer($p);
        }

        return $this->client_to_server;
    }

    /**
     * @param Project $p
     * @throws BuildException
     * @return \Ssh2MethodConnectionParam
     */
    public function getServerToClient(Project $p)
    {
        if ($this->isReference()) {
            return $this->getRef($p)->getServerToClient($p);
        }

        return $this->server_to_client;
    }

    /**
     * Handles the <client /> nested element
     * @return Ssh2MethodConnectionParam
     */
    public function createClient()
    {
        $this->client_to_server = new Ssh2MethodConnectionParam();

        return $this->client_to_server;
    }

    /**
     * Handles the <server /> nested element
     * @return Ssh2MethodConnectionParam
     */
    public function createServer()
    {
        $this->server_to_client = new Ssh2MethodConnectionParam();

        return $this->server_to_client;
    }

    /**
     * Convert the params to an array that is suitable to be passed in the ssh2_connect $methods parameter
     * @param Project $p
     * @return array
     */
    public function toArray(Project $p)
    {
        $client_to_server = $this->getClientToServer($p);
        $server_to_client = $this->getServerToClient($p);

        $array = array(
            'kex' => $this->getKex($p),
            'hostkey' => $this->getHostkey($p),
            'client_to_server' => !is_null($client_to_server) ? $client_to_server->toArray() : null,
            'server_to_client' => !is_null($server_to_client) ? $server_to_client->toArray() : null
        );

        return array_filter($array, array($this, '_filterParam'));
    }

    /**
     * @param $var
     * @return boolean
     */
    protected function _filterParam($var)
    {
        if (is_array($var)) {
            return !empty($var);
        }

        return !is_null($var);
    }

    /**
     *
     * @param Project $p
     * @throws BuildException
     * @return Ssh2MethodParam
     */
    public function getRef(Project $p)
    {
        if (!$this->checked) {
            $stk = array();
            array_push($stk, $this);
            $this->dieOnCircularReference($stk, $p);
        }
        $o = $this->ref->getReferencedObject($p);
        if (!($o instanceof Ssh2MethodParam)) {
            throw new BuildException($this->ref->getRefId() . " doesn't denote a Ssh2MethodParam");
        } else {
            return $o;
        }
    }

}
