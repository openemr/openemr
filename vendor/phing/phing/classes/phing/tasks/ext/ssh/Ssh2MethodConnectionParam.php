<?php
/*
 *  $Id: 121e04668c7aa65d641eb261fb93d3a9ece1e730 $
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

/**
 * Class that holds parameters for an ssh2_connect $methods parameter
 * This corresponds to the client_to_server and server_to_client keys of the optional $methods parameter
 * for the ssh2_connect function
 * @see http://php.net/ssh2_connect
 *
 * @author Derek Gallo <http://github.com/drock>
 *
 * @package   phing.tasks.ext
 */
class Ssh2MethodConnectionParam
{
    /**
     * @var string
     */
    private $crypt = null;

    /**
     * @var string
     */
    private $comp = null;

    /**
     * @var string
     */
    private $mac = null;

    /**
     * @param string $comp
     */
    public function setComp($comp)
    {
        $this->comp = $comp;
    }

    /**
     * @return string
     */
    public function getComp()
    {
        return $this->comp;
    }

    /**
     * @param string $crypt
     */
    public function setCrypt($crypt)
    {
        $this->crypt = $crypt;
    }

    /**
     * @return string
     */
    public function getCrypt()
    {
        return $this->crypt;
    }

    /**
     * @param string $mac
     */
    public function setMac($mac)
    {
        $this->mac = $mac;
    }

    /**
     * @return string
     */
    public function getMac()
    {
        return $this->mac;
    }

    /**
     * Get the params as an array
     * unset/null params are excluded from the array
     * @return array
     */
    public function toArray()
    {
        return array_filter(
            get_object_vars($this),
            array($this, '_filterParam')
        );
    }

    /**
     * @param $var
     * @return bool
     */
    protected function _filterParam($var)
    {
        return !is_null($var);
    }
}
