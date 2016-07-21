<?php
/**
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

require_once 'phing/Task.php';
require_once 'phing/tasks/ext/svn/SvnBaseTask.php';

/**
 * Parses the output of 'svn info --xml' and
 *
 * @author Michiel Rook <mrook@php.net>
 *
 * @package phing.tasks.ext.svn
 *
 * @see VersionControl_SVN
 * @since 2.4.9
 */
class SvnInfoTask extends SvnBaseTask
{
    private $propertyName = "svn.info";

    private $element = 'url';
    private $subElement = null;

    /**
     * Sets the name of the property to use
     * @param $propertyName
     */
    public function setPropertyName($propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * Returns the name of the property to use
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * Sets the name of the xml element to use.
     *
     * @param string $element
     *
     * @return void
     */
    public function setElement($element)
    {
        $this->element = $element;
    }

    /**
     * Returns the name of the xml element to use.
     *
     * @return string
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * Sets the name of the xml sub element to use.
     *
     * @param $subElement
     *
     * @return void
     */
    public function setSubElement($subElement)
    {
        $this->subElement = $subElement;
    }

    /**
     * Returns the name of the xml sub element to use.
     *
     * @return string
     */
    public function getSubElement()
    {
        return $this->subElement;
    }

    /**
     * The main entry point.
     *
     * @return void
     *
     * @throws BuildException
     */
    public function main()
    {
        $this->setup('info');

        if ($this->oldVersion) {
            $output = $this->run(array('--xml', '--incremental'));

            if (!($xmlObj = @simplexml_load_string($output))) {
                throw new BuildException("Failed to parse the output of 'svn info --xml'.");
            }

            $object = $xmlObj->{$this->element};

            if (!empty($this->subElement)) {
                $object = $object->{$this->subElement};
            }
        } else {
            $output = $this->run();

            if (empty($output) || !isset($output['entry'][0])) {
                throw new BuildException("Failed to parse the output of 'svn info'.");
            }

            $object = $output['entry'][0][$this->element];

            if (!empty($this->subElement)) {
                $object = $object[$this->subElement];
            }
        }

        $this->project->setProperty($this->getPropertyName(), (string) $object);
    }
}
