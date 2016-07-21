<?php
/**
 * $Id: bb6bda1d445fc14e863a2c88b2d11acaebe757c0 $
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
 * Stores the output of a list command on a workingcopy or repositoryurl in a property.
 * This stems from the SvnLastRevisionTask.
 *
 * @author Anton Stöckl <anton@stoeckl.de>
 * @author Michiel Rook <mrook@php.net> (SvnLastRevisionTask)
 * @version $Id: bb6bda1d445fc14e863a2c88b2d11acaebe757c0 $
 * @package phing.tasks.ext.svn
 * @see VersionControl_SVN
 * @since 2.1.0
 */
class SvnListTask extends SvnBaseTask
{
    private $propertyName = "svn.list";
    private $limit = null;
    private $orderDescending = false;

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
     * Sets the max num of tags to display
     * @param $limit
     */
    public function setLimit($limit)
    {
        $this->limit = (int) $limit;
    }

    /**
     * Sets whether to sort tags in descending order
     * @param $orderDescending
     */
    public function setOrderDescending($orderDescending)
    {
        $this->orderDescending = (bool) $orderDescending;
    }

    /**
     * The main entry point
     *
     * @throws BuildException
     */
    public function main()
    {
        $this->setup('list');

        if ($this->oldVersion) {
            $this->svn->setOptions(array('fetchmode' => VERSIONCONTROL_SVN_FETCHMODE_XML));
            $output = $this->run(array('--xml'));

            if (!($xmlObj = @simplexml_load_string($output))) {
                throw new BuildException("Failed to parse the output of 'svn list --xml'.");
            }

            $objects = $xmlObj->list->entry;
            $entries = array();

            foreach ($objects as $object) {
                $entries[] = array(
                    'commit' => array(
                        'revision' => (string) $object->commit['revision'],
                        'author' => (string) $object->commit->author,
                        'date' => (string) $object->commit->date
                    ),
                    'name' => (string) $object->name
                );
            }
        } else {
            $output = $this->run(array());
            $entries = $output['list'][0]['entry'];
        }

        if ($this->orderDescending) {
            $entries = array_reverse($entries);
        }

        $result = null;
        $count = 0;

        foreach ($entries as $entry) {
            if ($this->limit > 0 && $count >= $this->limit) {
                break;
            }

            $result .= (!empty($result)) ? "\n" : '';
            $result .= $entry['commit']['revision'] . ' | ' . $entry['commit']['author'] . ' | ' . $entry['commit']['date'] . ' | ' . $entry['name'];
            $count++;
        }

        if (!empty($result)) {
            $this->project->setProperty($this->getPropertyName(), $result);
        } else {
            throw new BuildException("Failed to parse the output of 'svn list'.");
        }
    }
}
