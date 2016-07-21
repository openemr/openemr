<?php

/*
 * $Id: 50fe104e0e864bee411dd746e332279173d215fd $
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

include_once 'phing/util/StringHelper.php';

/**
 * Selector that selects files by forwarding the request on to other classes.
 *
 * TODO - Consider adding Path (phing.types.Path) support to this class
 *         and to the Mappers class.  See Ant versions for implimentation details.
 *
 * @author Hans Lellelid <hans@xmpl.org> (Phing)
 * @author Bruce Atherton <bruce@callenish.com> (Ant)
 * @package phing.types.selectors
 */
class ExtendSelector extends BaseSelector
{

    private $classname;
    private $dynselector;
    private $parameters = array();

    /**
     * Sets the classname of the custom selector.
     *
     * @param string $classname is the class which implements this selector
     */
    public function setClassname($classname)
    {
        $this->classname = $classname;
    }

    /**
     * Instantiates the identified custom selector class.
     */
    public function selectorCreate()
    {
        if ($this->classname !== null && $this->classname !== "") {
            try {
                // assume it's fully qualified, import it
                $cls = Phing::import($this->classname);

                // make sure class exists
                if (class_exists($cls)) {
                    $this->dynselector = new $cls();
                } else {
                    $this->setError("Selector " . $this->classname . " not initialized, no such class");
                }
            } catch (Exception $e) {
                $this->setError(
                    "Selector " . $this->classname . " not initialized, could not create class: " . $e->getMessage()
                );
            }
        } else {
            $this->setError("There is no classname specified");
        }
    }

    /**
     * Create new parameters to pass to custom selector.
     *
     * @param Parameter $p The new Parameter object
     * @return void
     */
    public function addParam(Parameter $p)
    {
        $this->parameters[] = $p;
    }

    /**
     * These are errors specific to ExtendSelector only. If there are
     * errors in the custom selector, it should throw a BuildException
     * when isSelected() is called.
     */
    public function verifySettings()
    {
        // Creation is done here rather than in isSelected() because some
        // containers may do a validation pass before running isSelected(),
        // but we need to check for the existence of the created class.
        if ($this->dynselector === null) {
            $this->selectorCreate();
        }

        if (empty($this->classname)) {
            $this->setError("The classname attribute is required");
        } elseif ($this->dynselector === null) {
            $this->setError("Internal Error: The custom selector was not created");
        } elseif (!($this->dynselector instanceof ExtendFileSelector) && (count($this->parameters) > 0)) {
            $this->setError(
                "Cannot set parameters on custom selector that does not "
                . "implement ExtendFileSelector."
            );
        }
    }

    /**
     * Allows the custom selector to choose whether to select a file. This
     * is also where the Parameters are passed to the custom selector.
     *
     * @param PhingFile $basedir
     * @param string $filename The filename
     * @param PhingFile $file
     * @return \whether
     * @throws BuildException
     */
    public function isSelected(PhingFile $basedir, $filename, PhingFile $file)
    {

        $this->validate();

        if (count($this->parameters) > 0 && $this->dynselector instanceof ExtendFileSelector) {
            // We know that dynselector must be non-null if no error message
            $this->dynselector->setParameters($this->parameters);
        }

        return $this->dynselector->isSelected($basedir, $filename, $file);
    }

}
