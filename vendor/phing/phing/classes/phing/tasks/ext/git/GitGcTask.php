<?php
/*
 *  $Id: 5201d8d2dfb0c6ffb371ee85b8aa4bfc731b00c4 $
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
 * Wrapper around git-gc
 *
 * @author Victor Farazdagi <simple.square@gmail.com>
 * @version $Id: 5201d8d2dfb0c6ffb371ee85b8aa4bfc731b00c4 $
 * @package phing.tasks.ext.git
 * @see VersionControl_Git
 * @since 2.4.3
 */
class GitGcTask extends GitBaseTask
{
    /**
     * --aggressive key to git-gc
     * @var boolean
     */
    private $isAggressive = false;

    /**
     * --auto key to git-gc
     * @var boolean
     */
    private $isAuto = false;

    /**
     * --no-prune key to git-gc
     * @var boolean
     */
    private $noPrune = false;

    /**
     * --prune=<date>option of git-gc
     * @var string
     */
    private $prune = '2.weeks.ago';

    /**
     * The main entry point for the task
     */
    public function main()
    {
        if (null === $this->getRepository()) {
            throw new BuildException('"repository" is required parameter');
        }

        $client = $this->getGitClient(false, $this->getRepository());
        $command = $client->getCommand('gc');
        $command
            ->setOption('aggressive', $this->isAggressive())
            ->setOption('auto', $this->isAuto())
            ->setOption('no-prune', $this->isNoPrune());
        if ($this->isNoPrune() == false) {
            $command->setOption('prune', $this->getPrune());
        }

        // suppress output
        $command->setOption('q');

        $this->log('git-gc command: ' . $command->createCommandString(), Project::MSG_INFO);

        try {
            $command->execute();
        } catch (Exception $e) {
            throw new BuildException('Task execution failed', $e);
        }

        $this->log(
            sprintf('git-gc: cleaning up "%s" repository', $this->getRepository()),
            Project::MSG_INFO
        );
    }

    /**
     * @see getAggressive()
     */
    public function isAggressive()
    {
        return $this->getAggressive();
    }

    /**
     * @return bool
     */
    public function getAggressive()
    {
        return $this->isAggressive;
    }

    /**
     * @param $flag
     */
    public function setAggressive($flag)
    {
        $this->isAggressive = (bool) $flag;
    }

    /**
     * @see getAuto()
     */
    public function isAuto()
    {
        return $this->getAuto();
    }

    /**
     * @return bool
     */
    public function getAuto()
    {
        return $this->isAuto;
    }

    /**
     * @param $flag
     */
    public function setAuto($flag)
    {
        $this->isAuto = (bool) $flag;
    }

    /**
     * @see NoPrune()
     */
    public function isNoPrune()
    {
        return $this->getNoPrune();
    }

    /**
     * @return bool
     */
    public function getNoPrune()
    {
        return $this->noPrune;
    }

    /**
     * @param $flag
     */
    public function setNoPrune($flag)
    {
        $this->noPrune = (bool) $flag;
    }

    /**
     * @return string
     */
    public function getPrune()
    {
        return $this->prune;
    }

    /**
     * @param $date
     */
    public function setPrune($date)
    {
        $this->prune = $date;
    }

}
