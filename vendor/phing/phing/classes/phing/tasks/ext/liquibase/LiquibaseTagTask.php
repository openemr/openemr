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
 * Task to tag the current database state. In case you tag the database multiple
 * times without applying a new changelog before, the tags will overwrite each
 * other!
 *
 * @author Stephan Hochdoerfer <S.Hochdoerfer@bitExpert.de>
 * @version $Id: 1a967314c731282d7e026e0e8670b003eaab9802 $
 * @since 2.4.10
 * @package phing.tasks.ext.liquibase
 */
class LiquibaseTagTask extends AbstractLiquibaseTask
{
    protected $tag;

    /**
     * Sets the name of tag which is used to mark the database state for
     * possible future rollback.
     *
     * @param string the name to tag the database with
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    }

    /**
     * @see AbstractTask::checkParams()
     */
    protected function checkParams()
    {
        parent::checkParams();

        if (null === $this->tag) {
            throw new BuildException(
                sprintf(
                    'Please specify the tag!',
                    $this->tag
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
        $this->execute('tag', escapeshellarg($this->tag));
    }
}
