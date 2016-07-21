<?php
/**
 * $Id: 7e1ed209fca1c776aa105241110a78a65284f6ae $
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
require_once 'phing/tasks/ext/svn/SvnBaseTask.php';

/**
 * Stores the output of a log command on a workingcopy or repositoryurl in a property.
 * This stems from the SvnLastRevisionTask.
 *
 * @author Anton Stöckl <anton@stoeckl.de>
 * @author Michiel Rook <mrook@php.net> (SvnLastRevisionTask)
 * @version $Id: 7e1ed209fca1c776aa105241110a78a65284f6ae $
 * @package phing.tasks.ext.svn
 * @see VersionControl_SVN
 * @since 2.1.0
 */
class SvnLogTask extends SvnBaseTask
{
    private $propertyName = "svn.log";
    private $limit = null;

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
     * Sets whether to force compatibility with older SVN versions (< 1.2)
     * @deprecated
     * @param $force
     */
    public function setForceCompatible($force)
    {
    }

    /**
     * Sets the max num of log entries to get from svn
     * @param $limit
     */
    public function setLimit($limit)
    {
        $this->limit = (int) $limit;
    }

    /**
     * The main entry point
     *
     * @throws BuildException
     */
    public function main()
    {
        $this->setup('log');

        $switches = array();
        if ($this->limit > 0) {
            $switches['limit'] = $this->limit;
        }

        $output = $this->run(array(), $switches);
        $result = null;

        if ($this->oldVersion) {
            foreach ($output as $line) {
                $result .= (!empty($result)) ? "\n" : '';
                $result .= "{$line['REVISION']} | {$line['AUTHOR']}  | {$line['DATE']}  | {$line['MSG']}";
            }
        } else {
            foreach ($output['logentry'] as $line) {
                $result .= (!empty($result)) ? "\n" : '';
                $result .= "{$line['revision']} | {$line['author']}  | {$line['date']}  | {$line['msg']}";
            }
        }

        if (!empty($result)) {
            $this->project->setProperty($this->getPropertyName(), $result);
        } else {
            throw new BuildException("Failed to parse the output of 'svn log'.");
        }
    }
}
