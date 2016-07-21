<?php
/*
 * $Id: c492370d37df91fd100b00fd3a7c1ba7f628b0e1 $
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

require_once 'phing/tasks/ext/liquibase/AbstractLiquibaseTask.php';

/**
 * Task for running liquibase commands that doesn't have their own
 * commands yet.
 *
 * Parameters can be provided by nested <parameter name='foo' value='bar' /> tags.
 * That will result in --foo='bar' on the command line.
 *
 * @author Joakim Israelsson <joakim.israelsson.86@gmail.com>
 * @version $Id: c492370d37df91fd100b00fd3a7c1ba7f628b0e1 $
 * @package phing.tasks.ext.liquibase
 */
class LiquibaseTask extends AbstractLiquibaseTask
{

    /**
     * What liquibase command you wish to run.
     */
    private $command;

    /**
     * @param $command
     */
    public function setCommand($command)
    {
        $this->command = (string) $command;
    }

    protected function checkParams()
    {
        parent::checkParams();

        if (null === $this->command) {
            throw new BuildException('Please provide a liquibase command.');
        }
    }

    public function main()
    {
        $this->checkParams();
        $this->execute($this->command, '');
    }

}
