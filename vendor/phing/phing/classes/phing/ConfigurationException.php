<?php
/*
 *  $Id: f5672973a0e8f94bc824729aed7285130e4322a3 $
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
 * ConfigurationException is thrown by Phing during the configuration and setup phase of the project.
 *
 * @author   Hans Lellelid <hans@xmpl.org>
 * @version  $Id: f5672973a0e8f94bc824729aed7285130e4322a3 $
 * @package  phing
 */
class ConfigurationException extends Exception
{

    /**
     * Location in the xml file.
     * @var Location
     */
    protected $location;

    /**
     * The nested "cause" exception.
     * @var Exception
     */
    protected $cause;

    /**
     * Construct a ConfigurationException.
     * Supported signatures:
     *         throw new BuildException($causeExc);
     *         throw new BuildException($msg);
     *         throw new BuildException($msg, $causeExc);
     * @param Exception|string $p1
     * @param Exception|null   $p2
     */
    public function __construct($p1, $p2 = null)
    {

        $cause = null;
        $msg = "";

        if ($p2 !== null) {
            if ($p2 instanceof Exception) {
                $cause = $p2;
                $msg = $p1;
            }
        } elseif ($p1 instanceof Exception) {
            $cause = $p1;
        } else {
            $msg = $p1;
        }

        parent::__construct($msg);

        if ($cause !== null) {
            $this->cause = $cause;
            $this->message .= " [wrapped: " . $cause->getMessage() . "]";
        }
    }

    /**
     * Gets the cause exception.
     *
     * @return Exception
     */
    public function getCause()
    {
        return $this->cause;
    }

}
