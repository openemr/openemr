<?php
/*
 *  $Id: d675b0dcedf7cc852b753abcad05a1cc3ba1e99b $
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

require_once 'phing/Task.php';

/**
 * Abstract class for creating adhoc Phing components in buildfile.
 *
 * By itself this class can be used to declare a single class within your buildfile.
 * You can then reference this class in any task that takes custom classes (selectors,
 * mappers, filters, etc.)
 *
 * Subclasses exist for conveniently declaring and registering tasks and types.
 *
 * @author   Hans Lellelid <hans@xmpl.org>
 * @version  $Id: d675b0dcedf7cc852b753abcad05a1cc3ba1e99b $
 * @package  phing.tasks.system
 */
class AdhocTask extends Task
{

    /**
     * The PHP script
     * @var string
     */
    protected $script;

    protected $newClasses = array();

    /**
     * Main entry point
     */
    public function main()
    {
        $this->execute();
        if ($this->newClasses) {
            foreach ($this->newClasses as $classname) {
                $this->log("Added adhoc class " . $classname, Project::MSG_VERBOSE);
            }
        } else {
            $this->log("Adhoc task executed but did not result in any new classes.", Project::MSG_VERBOSE);
        }
    }

    /**
     * Get array of names of newly defined classes.
     * @return array
     */
    protected function getNewClasses()
    {
        return $this->newClasses;
    }

    /**
     * Load the adhoc class, and perform any core validation.
     * @return string         The classname of the ProjectComponent class.
     * @throws BuildException - if more than one class is defined.
     */
    protected function execute()
    {
        $classes = get_declared_classes();
        eval($this->script);
        $this->newClasses = array_diff(get_declared_classes(), $classes);
    }

    /**
     * Set the script.
     * @param string $script
     */
    public function addText($script)
    {
        $this->script = $script;
    }

}
