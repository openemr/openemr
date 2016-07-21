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

include_once 'phing/BuildException.php';
include_once 'phing/Task.php';
include_once 'phing/tasks/system/condition/Condition.php';

/**
 * An phing version condition/task.
 *
 * @author    Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package   phing.tasks.system
 */
class PhingVersion extends Task implements Condition
{
    private $atLeast = null;
    private $exactly = null;
    private $propertyname = null;

    /**
     * Run as a task.
     * @throws BuildException if an error occurs.
     */
    public function main()
    {
        if ($this->propertyname == null) {
            throw new BuildException("'property' must be set.");
        }
        if ($this->atLeast != null || $this->exactly != null) {
            // If condition values are set, evaluate the condition
            if ($this->evaluate()) {
                $this->getProject()->setNewProperty($this->propertyname, $this->getVersion());
            }
        } else {
            // Raw task
            $this->getProject()->setNewProperty($this->propertyname, $this->getVersion());
        }
    }

    /**
     * Evaluate the condition.
     * @return true if the condition is true.
     * @throws BuildException if an error occurs.
     */
    public function evaluate()
    {
        $this->validate();
        $actual = $this->getVersion();
        if (null != $this->atLeast) {
            return version_compare($actual, $this->atLeast, '>=');
        }

        if (null != $this->exactly) {
            return version_compare($actual, $this->exactly, '=');
        }

        return false;
    }

    private function validate()
    {
        if ($this->atLeast != null && $this->exactly != null) {
            throw new BuildException("Only one of atleast or exactly may be set.");
        }
        if (null == $this->atLeast && null == $this->exactly) {
            throw new BuildException("One of atleast or exactly must be set.");
        }
    }

    private function getVersion()
    {
        $p = new Project();

        return $p->getPhingVersion();
    }

    /**
     * Get the atleast attribute.
     * @return string the atleast attribute.
     */
    public function getAtLeast()
    {
        return $this->atLeast;
    }

    /**
     * Set the atleast attribute.
     * This is of the form major.minor.point.
     * For example 1.7.0.
     * @param string $atLeast the version to check against.
     */
    public function setAtLeast($atLeast)
    {
        $this->atLeast = $atLeast;
    }

    /**
     * Get the exactly attribute.
     * @return string the exactly attribute.
     */
    public function getExactly()
    {
        return $this->exactly;
    }

    /**
     * Set the exactly attribute.
     * This is of the form major.minor.point.
     * For example 1.7.0.
     * @param string $exactly the version to check against.
     */
    public function setExactly($exactly) {
        $this->exactly = $exactly;
    }

    /**
     * Get the name of the property to hold the phing version.
     * @return string the name of the property.
     */
    public function getProperty()
    {
        return $this->propertyname;
    }

    /**
     * Set the name of the property to hold the phing version.
     * @param string $propertyname the name of the property.
     */
    public function setProperty($propertyname)
    {
        $this->propertyname = $propertyname;
    }
}
