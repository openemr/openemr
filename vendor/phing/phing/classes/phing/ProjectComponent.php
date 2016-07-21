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

/**
 *  Abstract class providing properties and methods common to all
 *  the project components
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @author    Hans Lellelid <hans@xmpl.org>
 *
 * @package   phing
 */
abstract class ProjectComponent
{
    /**
     * Holds a reference to the project that a project component
     * (a task, a target, etc.) belongs to
     *
     * @var Project $project A reference to the current project instance
     */
    protected $project = null;

    /**
     * References the project to the current component.
     *
     * @param Project $project The reference to the current project
     *
     * @return void
     */
    public function setProject($project)
    {
        $this->project = $project;
    }

    /**
     * Returns a reference to current project
     *
     * @return Project Reference to current porject object
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Logs a message with the given priority.
     *
     * @param string  $msg   The message to be logged.
     * @param integer $level The message's priority at this message should have
     *
     * @return void
     */
    public function log($msg, $level = Project::MSG_INFO)
    {
        if ($this->project !== null) {
            $this->project->log($msg, $level);
        }
    }
}
