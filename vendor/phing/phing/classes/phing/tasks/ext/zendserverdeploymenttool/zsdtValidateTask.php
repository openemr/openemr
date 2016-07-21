<?php
/**
 * $Id: 53fe9d2cbd622bf129a44c1e06c9bae8fe14fc89 $
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

require_once 'phing/tasks/ext/zendserverdeploymenttool/zsdtBaseTask.php';

/**
 * Class ZendServerDeploymentToolTask
 *
 * @author Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.ext.zendserverdevelopmenttools
 */
class zsdtValidateTask extends zsdtBaseTask
{
    /**
     * @inheritdoc}
     *
     * @return void
     */
    public function init()
    {
        $this->action = 'validate';
    }

    /**
     * {@inheritdoc}
     *
     * @throws BuildException
     *
     * @return void
     */
    protected function validate()
    {
        parent::validate();

        if ($this->descriptor === null) {
            throw new BuildException('The package descriptor file have to be set.');
        }

        $this->arguments .= $this->descriptor;
    }
}
