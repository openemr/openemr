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
require_once 'phing/tasks/ext/git/GitBaseTask.php';

/**
 * Wrapper around git-describe
 *
 * @package phing.tasks.ext.git
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @see     VersionControl_Git
 */
class GitDescribeTask extends GitBaseTask
{
    /**
     * Use any ref found in .git/refs/. See --all of git-describe
     * @var boolean
     */
    private $all = false;

    /**
     * Use any tag found in .git/refs/tags. See --tags of git-describe
     * @var boolean
     */
    private $tags = false;

    /**
     * Find tag that contains the commit. See --contains of git-describe
     * @var boolean
     */
    private $contains = false;

    /**
     * Use <n> digit object name. See --abbrev of git-describe
     * @var integer
     */
    private $abbrev;

    /**
     * Consider up to <n> most recent tags. See --candidates of git-describe
     * @var integer
     */
    private $candidates;

    /**
     * Always output the long format. See --long of git-describe
     * @var boolean
     */
    private $long = false;

    /**
     * Only consider tags matching the given pattern. See --match of git-describe
     * @var string
     */
    private $match;

    /**
     * Show uniquely abbreviated commit object as fallback. See --always of git-describe
     * @var boolean
     */
    private $always = false;

    /**
     * <committish> argument to git-describe
     * @var string
     */
    private $committish;

    /**
     * Property name to set with output value from git-describe
     * @var string
     */
    private $outputProperty;

    /**
     * The main entry point for the task
     */
    public function main()
    {
        if (null === $this->getRepository()) {
            throw new BuildException('"repository" is required parameter');
        }

        $client = $this->getGitClient(false, $this->getRepository());
        $command = $client->getCommand('describe');
        $command
            ->setOption('all', $this->isAll())
            ->setOption('tags', $this->isTags())
            ->setOption('contains', $this->isContains())
            ->setOption('long', $this->isLong())
            ->setOption('always', $this->isAlways());

        if (null !== $this->getAbbrev()) {
            $command->setOption('abbrev', $this->getAbbrev());
        }
        if (null !== $this->getCandidates()) {
            $command->setOption('candidates', $this->getCandidates());
        }
        if (null !== $this->getMatch()) {
            $command->setOption('match', $this->getMatch());
        }
        if (null !== $this->getCommittish()) {
            $command->addArgument($this->getCommittish());
        }

        try {
            $output = $command->execute();
        } catch (Exception $e) {
            throw new BuildException('Task execution failed');
        }

        if (null !== $this->outputProperty) {
            $this->project->setProperty($this->outputProperty, $output);
        }

        $this->log(
            sprintf('git-describe: recent tags for "%s" repository', $this->getRepository()),
            Project::MSG_INFO
        );
        $this->log('git-describe output: ' . trim($output), Project::MSG_INFO);
    }

    /**
     * @param $flag
     */
    public function setAll($flag)
    {
        $this->all = (bool) $flag;
    }

    /**
     * @return bool
     */
    public function getAll()
    {
        return $this->all;
    }

    /**
     * @return bool
     */
    public function isAll()
    {
        return $this->getAll();
    }

    /**
     * @param $flag
     */
    public function setTags($flag)
    {
        $this->tags = (bool) $flag;
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
     * @param $flag
     */
    public function setContains($flag)
    {
        $this->contains = (bool) $flag;
    }

    /**
     * @return bool
     */
    public function getContains()
    {
        return $this->contains;
    }

    /**
     * @return bool
     */
    public function isContains()
    {
        return $this->getContains();
    }

    /**
     * @param $length
     */
    public function setAbbrev($length)
    {
        $this->abbrev = (int) $length;
    }

    /**
     * @return int
     */
    public function getAbbrev()
    {
        return $this->abbrev;
    }

    /**
     * @param $count
     */
    public function setCandidates($count)
    {
        $this->candidates = (int) $count;
    }

    /**
     * @return int
     */
    public function getCandidates()
    {
        return $this->candidates;
    }

    /**
     * @param $flag
     */
    public function setLong($flag)
    {
        $this->long = (bool) $flag;
    }

    /**
     * @return bool
     */
    public function getLong()
    {
        return $this->long;
    }

    /**
     * @return bool
     */
    public function isLong()
    {
        return $this->getLong();
    }

    /**
     * @param $pattern
     */
    public function setMatch($pattern)
    {
        $this->match = $pattern;
    }

    /**
     * @return string
     */
    public function getMatch()
    {
        return $this->match;
    }

    /**
     * @param $flag
     */
    public function setAlways($flag)
    {
        $this->always = (bool) $flag;
    }

    /**
     * @return bool
     */
    public function getAlways()
    {
        return $this->always;
    }

    /**
     * @return bool
     */
    public function isAlways()
    {
        return $this->getAlways();
    }

    /**
     * @param $object
     */
    public function setCommittish($object)
    {
        $this->committish = $object;
    }

    /**
     * @return string
     */
    public function getCommittish()
    {
        return $this->committish;
    }

    /**
     * @param string $prop
     */
    public function setOutputProperty($prop)
    {
        $this->outputProperty = $prop;
    }
}
