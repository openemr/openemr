<?php
/*
 *  $Id: 54de7ff3fbe82f553e57cfeb22ab5ccea8c447ac $
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

require_once "phing/tasks/system/condition/Condition.php";

/**
 * Condition that compare versions
 *
 * @author    Tomáš Fejfar (tomas.fejfar@gmail.com)
 * @package   phing.tasks.system.condition
 */
class VersionCompareCondition implements Condition
{

    /**
     * Actual version
     *
     * @var string
     */
    private $version;

    /**
     * Version to be compared to
     *
     * @var string
     */
    private $desiredVersion;

    /**
     * Operator to use (default "greater or equal")
     *
     * @var string operator for possible values @see http://php.net/version%20compare
     */
    private $operator = '>=';

    private $debug = false;

    /**
     * @param $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @param $desiredVersion
     */
    public function setDesiredVersion($desiredVersion)
    {
        $this->desiredVersion = $desiredVersion;
    }

    /**
     * @param $operator
     * @throws BuildException
     */
    public function setOperator($operator)
    {
        $allowed = array('<', 'lt', '<=', 'le', '>', 'gt', '>=', 'ge', '==', '=', 'eq', '!=', '<>', 'ne');
        if (!in_array($operator, $allowed)) { // allowed operators for php's version_comapare()
            require_once 'phing/BuildException.php';
            throw new BuildException(sprintf(
                'Operator "%s" is not supported. Supported operators: %s',
                $operator,
                implode(', ', $allowed)
            ));
        }
        $this->operator = $operator;
    }

    /**
     * @param $debug
     */
    public function setDebug($debug)
    {
        $this->debug = (bool) $debug;
    }

    /**
     * @return mixed
     * @throws BuildException
     */
    public function evaluate()
    {
        if ($this->version === null || $this->desiredVersion === null) {
            require_once 'phing/BuildException.php';
            throw new BuildException("Missing one version parameter for version compare");
        }
        $isValid = version_compare($this->version, $this->desiredVersion, $this->operator);
        if ($this->debug) {
            echo sprintf(
                'Assertion that %s %s %s failed' . PHP_EOL,
                $this->version,
                $this->operator,
                $this->desiredVersion
             );
        }
        return $isValid;
    }
}
