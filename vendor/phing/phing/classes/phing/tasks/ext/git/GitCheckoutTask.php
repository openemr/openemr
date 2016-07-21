<?php
/*
 *  $Id: 0eb5ad0732bc3acd74df7f9256b37952ddce3212 $
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
 * Wrapper around git-checkout
 *
 * @author Victor Farazdagi <simple.square@gmail.com>
 * @version $Id: 0eb5ad0732bc3acd74df7f9256b37952ddce3212 $
 * @package phing.tasks.ext.git
 * @see VersionControl_Git
 * @since 2.4.3
 */
class GitCheckoutTask extends GitBaseTask
{
    /**
     * Branch name
     * @var string
     */
    private $branchname;

    /**
     * If not HEAD, specify starting point
     * @var string
     */
    private $startPoint;

    /**
     * --force, -f key to git-checkout
     * @var boolean
     */
    private $force = false;

    /**
     * --quiet, -q key to git-checkout
     * @var boolean
     */
    private $quiet = false;

    /**
     * When creating a new branch, set up "upstream" configuration.
     * --track key to git-checkout
     * @var boolean
     */
    private $track = false;

    /**
     * Do not set up "upstream" configuration
     * --no-track key to git-checkout
     * @var boolean
     */
    private $noTrack = false;

    /**
     * -b, -B, -m  options to git-checkout
     * Respective task options:
     * create, forceCreate, merge
     * @var array
     */
    private $extraOptions = array(
        'b' => false,
        'B' => false,
        'm' => false,
    );

    /**
     * The main entry point for the task
     */
    public function main()
    {
        if (null === $this->getRepository()) {
            throw new BuildException('"repository" is required parameter');
        }
        if (null === $this->getBranchname()) {
            throw new BuildException('"branchname" is required parameter');
        }

        $client = $this->getGitClient(false, $this->getRepository());
        $command = $client->getCommand('checkout');
        $command
            ->setOption('no-track', $this->isNoTrack())
            ->setOption('q', $this->isQuiet())
            ->setOption('force', $this->isForce())
            ->setOption('b', $this->isCreate())
            ->setOption('B', $this->isForceCreate())
            ->setOption('m', $this->isMerge());
        if ($this->isNoTrack()) {
            $command->setOption('track', $this->isTrack());
        }

        $command->addArgument($this->getBranchname());

        if (null !== $this->getStartPoint()) {
            $command->addArgument($this->getStartPoint());
        }

        $this->log('git-checkout command: ' . $command->createCommandString(), Project::MSG_INFO);

        try {
            $output = $command->execute();
        } catch (Exception $e) {
            throw new BuildException('Task execution failed.', $e);
        }

        $this->log(
            sprintf('git-checkout: checkout "%s" repository', $this->getRepository()),
            Project::MSG_INFO
        );
        $this->log('git-checkout output: ' . trim($output), Project::MSG_INFO);
    }

    /**
     * @param $branchname
     */
    public function setBranchname($branchname)
    {
        $this->branchname = $branchname;
    }

    /**
     * @return string
     */
    public function getBranchname()
    {
        return $this->branchname;
    }

    /**
     * @param $startPoint
     */
    public function setStartPoint($startPoint)
    {
        $this->startPoint = $startPoint;
    }

    /**
     * @return string
     */
    public function getStartPoint()
    {
        return $this->startPoint;
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
    public function setTrack($flag)
    {
        $this->track = $flag;
    }

    /**
     * @return bool
     */
    public function getTrack()
    {
        return $this->track;
    }

    /**
     * @return bool
     */
    public function isTrack()
    {
        return $this->getTrack();
    }

    /**
     * @param $flag
     */
    public function setNoTrack($flag)
    {
        $this->noTrack = $flag;
    }

    /**
     * @return bool
     */
    public function getNoTrack()
    {
        return $this->noTrack;
    }

    /**
     * @return bool
     */
    public function isNoTrack()
    {
        return $this->getNoTrack();
    }

    /**
     * @param $flag
     */
    public function setCreate($flag)
    {
        $this->extraOptions['b'] = $flag;
    }

    public function getCreate()
    {
        return $this->extraOptions['b'];
    }

    public function isCreate()
    {
        return $this->getCreate();
    }

    // -B flag is not found in all versions of git
    // --force is present everywhere
    /**
     * @param $flag
     */
    public function setForceCreate($flag)
    {
        $this->setForce($flag);
    }

    public function getForceCreate()
    {
        return $this->extraOptions['B'];
    }

    public function isForceCreate()
    {
        return $this->getForceCreate();
    }

    /**
     * @param $flag
     */
    public function setMerge($flag)
    {
        $this->extraOptions['m'] = $flag;
    }

    public function getMerge()
    {
        return $this->extraOptions['m'];
    }

    public function isMerge()
    {
        return $this->getMerge();
    }
}
