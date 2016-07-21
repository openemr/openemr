<?php
/*
 *  $Id: 8406c9cfd560b995db09e9888334355641cd3975 $
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
require_once 'phing/tasks/ext/git/GitBaseTask.php';

/**
 * Wrapper aroung git-push
 *
 * @author Victor Farazdagi <simple.square@gmail.com>
 * @version $Id: 8406c9cfd560b995db09e9888334355641cd3975 $
 * @package phing.tasks.ext.git
 * @see VersionControl_Git
 * @since 2.4.3
 * @link http://www.kernel.org/pub/software/scm/git/docs/git-push.html
 */
class GitPushTask extends GitBaseTask
{
    /**
     * Instead of naming each ref to push, specifies that all refs
     * --all key to git-push
     * @var boolean
     */
    private $allRemotes = false;

    /**
     * Mirror to remote repository
     * --mirror key to git-push
     * @var boolean
     */
    private $mirror = false;

    /**
     * Same as prefixing repos with colon
     * --delete argument to git-push
     * @var string
     */
    private $delete = false;

    /**
     * Push all refs under refs/tags
     * --tags key to git-fetch
     * @var boolean
     */
    private $tags = false;

    /**
     * <repository> argument to git-push
     * @var string
     */
    private $destination = 'origin';

    /**
     * <refspec> argument to git-push
     * @var string
     */
    private $refspec;

    /**
     * --force, -f key to git-push
     * @var boolean
     */
    private $force = false;

    /**
     * --quiet, -q key to git-push
     * @var boolean
     */
    private $quiet = true;

    /**
     * The main entry point for the task
     */
    public function main()
    {
        if (null === $this->getRepository()) {
            throw new BuildException('"repository" is required parameter');
        }

        $client = $this->getGitClient(false, $this->getRepository());
        $command = $client->getCommand('push');
        $command
            ->setOption('tags', $this->isTags())
            ->setOption('mirror', $this->isMirror())
            ->setOption('delete', $this->isDelete())
            ->setOption('q', $this->isQuiet())
            ->setOption('force', $this->isForce());

        // set operation target
        if ($this->isAllRemotes()) { // --all
            $command->setOption('all', true);
            $this->log('git-push: push to all refs', Project::MSG_INFO);
        } elseif ($this->isMirror()) { // <repository> [<refspec>]
            $command->setOption('mirror', true);
            $this->log('git-push: mirror all refs', Project::MSG_INFO);
        } elseif ($this->getDestination()) { // <repository> [<refspec>]
            $command->addArgument($this->getDestination());
            if ($this->getRefspec()) {
                $command->addArgument($this->getRefspec());
            }
            $this->log(
                sprintf(
                    'git-push: pushing to %s %s',
                    $this->getDestination(),
                    $this->getRefspec()
                ),
                Project::MSG_INFO
            );
        } else {
            throw new BuildException('At least one destination must be provided');
        }

        $this->log('git-push command: ' . $command->createCommandString(), Project::MSG_INFO);

        try {
            $output = $command->execute();
        } catch (Exception $e) {
            throw new BuildException('Task execution failed.', $e);
        }

        $this->log('git-push: complete', Project::MSG_INFO);
        if ($this->isDelete()) {
            $this->log('git-push: branch delete requested', Project::MSG_INFO);
        }
        $this->log('git-push output: ' . trim($output), Project::MSG_INFO);
    }

    /**
     * @param $flag
     */
    public function setAll($flag)
    {
        $this->allRemotes = $flag;
    }

    /**
     * @return bool
     */
    public function getAll()
    {
        return $this->allRemotes;
    }

    /**
     * @return bool
     */
    public function isAllRemotes()
    {
        return $this->getAll();
    }

    /**
     * @param $flag
     */
    public function setMirror($flag)
    {
        $this->mirror = (boolean) $flag;
    }

    /**
     * @return bool
     */
    public function getMirror()
    {
        return $this->mirror;
    }

    /**
     * @return bool
     */
    public function isMirror()
    {
        return $this->getMirror();
    }

    /**
     * @param $flag
     */
    public function setDelete($flag)
    {
        $this->delete = (boolean) $flag;
    }

    /**
     * @return string
     */
    public function getDelete()
    {
        return $this->delete;
    }

    /**
     * @return string
     */
    public function isDelete()
    {
        return $this->getDelete();
    }

    /**
     * @param $flag
     */
    public function setTags($flag)
    {
        $this->tags = $flag;
    }

    /**
     * @return bool
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @return bool
     */
    public function isTags()
    {
        return $this->getTags();
    }

    /**
     * @param $destination
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
    }

    /**
     * @return string
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * @param $spec
     */
    public function setRefspec($spec)
    {
        $this->refspec = $spec;
    }

    /**
     * @return string
     */
    public function getRefspec()
    {
        return $this->refspec;
    }

    /**
     * @param $flag
     */
    public function setForce($flag)
    {
        $this->force = $flag;
    }

    /**
     * @return bool
     */
    public function getForce()
    {
        return $this->force;
    }

    /**
     * @return bool
     */
    public function isForce()
    {
        return $this->getForce();
    }

    /**
     * @param $flag
     */
    public function setQuiet($flag)
    {
        $this->quiet = $flag;
    }

    /**
     * @return bool
     */
    public function getQuiet()
    {
        return $this->quiet;
    }

    /**
     * @return bool
     */
    public function isQuiet()
    {
        return $this->getQuiet();
    }

}
