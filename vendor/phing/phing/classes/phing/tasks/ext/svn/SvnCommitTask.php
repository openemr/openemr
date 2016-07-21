<?php
/**
 * $Id: eec7114955b928683994705ad4dc68fe246ef23f $
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
 * Commits changes in a local working copy to the repository
 *
 * @author Johan Persson <johanp@aditus.nu>
 * @version $Id: eec7114955b928683994705ad4dc68fe246ef23f $
 * @package phing.tasks.ext.svn
 * @since 2.4.0
 */
class SvnCommitTask extends SvnBaseTask
{
    /**
     * Commit message
     */
    private $message = '';

    /**
     * Property name where we store the revision number of the just
     * committed version.
     */
    private $propertyName = "svn.committedrevision";

    /**
     * Sets the commit message
     * @param $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Gets the commit message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Sets the name of the property to use for returned revision
     * @param $propertyName
     */
    public function setPropertyName($propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * Returns the name of the property to use for returned revision
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * The main entry point
     *
     * @throws BuildException
     */
    public function main()
    {
        if (trim($this->message) === '') {
            throw new BuildException('SVN Commit message can not be empty.');
        }

        $this->setup('commit');

        $this->log(
            "Committing SVN working copy at '" . $this->getWorkingCopy() . "' with message '" . $this->GetMessage() . "'"
        );

        $output = $this->run(array(), array('message' => $this->GetMessage()));

        if (preg_match('/[\s]*Committed revision[\s]+([\d]+)/', $output, $matches)) {
            $this->project->setProperty($this->getPropertyName(), $matches[1]);
        } else {
            /**
             * If no new revision was committed set revision to "empty". Remember that
             * this is not necessarily an error. It could be that the specified working
             * copy is identical to the copy in the repository and in that case
             * there will be no update and no new revision number.
             */
            $this->project->setProperty($this->getPropertyName(), '');
        }

    }
}
