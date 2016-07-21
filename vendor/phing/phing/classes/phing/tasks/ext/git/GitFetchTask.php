<?php
/*
 *  $Id: 0de7b4504804e074f617010ef52604d1da1e76b7 $
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
 * Wrapper aroung git-fetch
 *
 * @author Victor Farazdagi <simple.square@gmail.com>
 * @version $Id: 0de7b4504804e074f617010ef52604d1da1e76b7 $
 * @package phing.tasks.ext.git
 * @see VersionControl_Git
 * @since 2.4.3
 */
class GitFetchTask extends GitBaseTask
{
    /**
     * --force, -f key to git-fetch
     * @var boolean
     */
    private $force = false;

    /**
     * --quiet, -q key to git-fetch
     * @var boolean
     */
    private $quiet = false;

    /**
     * Fetch all remotes
     * --all key to git-fetch
     * @var boolean
     */
    private $allRemotes = false;

    /**
     * Keep downloaded pack
     * --keep key to git-fetch
     * @var boolean
     */
    private $keepFiles = false;

    /**
     * After fetching, remove any remote tracking branches which no longer
     * exist on the remote.
     * --prune key to git fetch
     * @var boolean
     */
    private $prune = false;

    /**
     * Disable/enable automatic tag following
     * --no-tags key to git-fetch
     * @var boolean
     */
    private $noTags = false;

    /**
     * Fetch all tags (even not reachable from branch heads)
     * --tags key to git-fetch
     * @var boolean
     */
    private $tags = false;

    /**
     * <group> argument to git-fetch
     * @var string
     */
    private $group;

    /**
     * <repository> argument to git-fetch
     * @var string
     */
    private $source = 'origin';

    /**
     * <refspec> argument to git-fetch
     * @var string
     */
    private $refspec;

    /**
     * The main entry point for the task
     */
    public function main()
    {
        if (null === $this->getRepository()) {
            throw new BuildException('"repository" is required parameter');
        }

        $client = $this->getGitClient(false, $this->getRepository());
        $command = $client->getCommand('fetch');
        $command
            ->setOption('tags', $this->isTags())
            ->setOption('no-tags', $this->isNoTags())
            ->setOption('prune', $this->isPrune())
            ->setOption('keep', $this->isKeepFiles())
            ->setOption('q', $this->isQuiet())
            ->setOption('force', $this->isForce());

        // set operation target
        if ($this->isAllRemotes()) { // --all
            $command->setOption('all', true);
        } elseif ($this->getGroup()) { // <group>
            $command->addArgument($this->getGroup());
        } elseif ($this->getSource()) { // <repository> [<refspec>]
            $command->addArgument($this->getSource());
            if ($this->getRefspec()) {
                $command->addArgument($this->getRefspec());
            }
        } else {
            throw new BuildException('No remote repository specified');
        }

        $this->log('git-fetch command: ' . $command->createCommandString(), Project::MSG_INFO);

        try {
            $output = $command->execute();
        } catch (Exception $e) {
            throw new BuildException('Task execution failed.', $e);
        }

        $this->log(
            sprintf('git-fetch: branch "%s" repository', $this->getRepository()),
            Project::MSG_INFO
        );
        $this->log('git-fetch output: ' . trim($output), Project::MSG_INFO);
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
    public function setKeep($flag)
    {
        $this->keepFiles = $flag;
    }

    /**
     * @return bool
     */
    public function getKeep()
    {
        return $this->keepFiles;
    }

    /**
     * @return bool
     */
    public function isKeepFiles()
    {
        return $this->getKeep();
    }

    /**
     * @param $flag
     */
    public function setPrune($flag)
    {
        $this->prune = $flag;
    }

    /**
     * @return bool
     */
    public function getPrune()
    {
        return $this->prune;
    }

    /**
     * @return bool
     */
    public function isPrune()
    {
        return $this->getPrune();
    }

    /**
     * @param $flag
     */
    public function setNoTags($flag)
    {
        $this->noTags = $flag;
    }

    /**
     * @return bool
     */
    public function getNoTags()
    {
        return $this->noTags;
    }

    /**
     * @return bool
     */
    public function isNoTags()
    {
        return $this->getNoTags();
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
     * @param $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
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
     * @param $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

}
