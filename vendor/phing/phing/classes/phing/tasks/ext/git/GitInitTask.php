<?php
/*
 *  $Id: 8e8b0c6caa7260a0bb563b71f8af4291067cbef0 $
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
require_once 'phing/BuildException.php';
require_once 'phing/tasks/ext/git/GitBaseTask.php';

/**
 * Repository initialization task
 *
 * @author Victor Farazdagi <simple.square@gmail.com>
 * @version $Id: 8e8b0c6caa7260a0bb563b71f8af4291067cbef0 $
 * @package phing.tasks.ext.git
 * @see VersionControl_Git
 * @since 2.4.3
 */
class GitInitTask extends GitBaseTask
{

    /**
     * Whether --bare key should be set for git-init
     * @var string
     */
    private $isBare = false;

    /**
     * The main entry point for the task
     */
    public function main()
    {
        if (null === $this->getRepository()) {
            throw new BuildException('"repository" is required parameter');
        }

        $client = $this->getGitClient();
        $client->initRepository($this->isBare());

        $msg = 'git-init: initializing '
            . ($this->isBare() ? '(bare) ' : '')
            . '"' . $this->getRepository() . '" repository';
        $this->log($msg, Project::MSG_INFO);
    }

    /**
     * Alias @see getBare()
     *
     * @return string
     */
    public function isBare()
    {
        return $this->getBare();
    }

    /**
     * @return string
     */
    public function getBare()
    {
        return $this->isBare;
    }

    /**
     * @param $flag
     */
    public function setBare($flag)
    {
        $this->isBare = (bool) $flag;
    }
}
