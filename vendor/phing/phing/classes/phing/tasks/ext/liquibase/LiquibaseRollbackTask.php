<?php

/**
 * Copyright (c) 2007-2011 bitExpert AG
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

require_once 'phing/tasks/ext/liquibase/AbstractLiquibaseTask.php';

/**
 * Rollbacks the database changes.
 *
 * @author Stephan Hochdoerfer <S.Hochdoerfer@bitExpert.de>
 * @version $Id: 1915fb3b50b9c646643a44df4cf4457a2c79f6f1 $
 * @since 2.4.10
 * @package phing.tasks.ext.liquibase
 */
class LiquibaseRollbackTask extends AbstractLiquibaseTask
{
    protected $rollbackTag;

    /**
     * Sets the name of the tag to roll back to.
     *
     * @param string the name to roll back to
     */
    public function setRollbackTag($rollbackTag)
    {
        $this->rollbackTag = $rollbackTag;
    }

    /**
     * @see AbstractTask::checkParams()
     */
    protected function checkParams()
    {
        parent::checkParams();

        if (null === $this->rollbackTag) {
            throw new BuildException(
                sprintf(
                    'Please specify the tag to rollback to!',
                    $this->rollbackTag
                )
            );
        }
    }

    /**
     * @see Task::main()
     */
    public function main()
    {
        $this->checkParams();
        $this->execute('rollback', escapeshellarg($this->rollbackTag));
    }
}
