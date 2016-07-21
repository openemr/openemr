<?php
/**
 * $Id: e97cb62953c381f62ab548dfe073b26071f0eb0a $
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

require_once 'phing/system/io/PhingFile.php';

/**
 * Analyzer element for the PhpDependTask
 *
 * @package phing.tasks.ext.pdepend
 * @author  Benjamin Schultz <bschultz@proqrent.de>
 * @version $Id: e97cb62953c381f62ab548dfe073b26071f0eb0a $
 * @since   2.4.1
 */
class PhpDependAnalyzerElement
{
    /**
     * The type of the analyzer
     *
     * @var string
     */
    protected $type = '';

    /**
     * The value(s) for the analyzer option
     *
     * @var array
     */
    protected $value = array();

    /**
     * Sets the analyzer type
     *
     * @param string $type Type of the analyzer
     *
     * @throws BuildException
     */
    public function setType($type)
    {
        $this->type = $type;

        switch ($this->type) {
            case 'coderank-mode':
                break;

            default:
                throw new BuildException('Analyzer "' . $this->type . '" not implemented');
        }
    }

    /**
     * Get the analyzer type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the value for the analyzer
     *
     * @param string $value Value for the analyzer
     */
    public function setValue($value)
    {
        $this->value = array();

        $token = ' ,;';
        $values = strtok($value, $token);

        while ($values !== false) {
            $this->value[] = $values;
            $values = strtok($token);
        }
    }

    /**
     * Get the analyzer value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
