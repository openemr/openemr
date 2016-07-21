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
 * Task to update the database to latest version of the changelog file.
 *
 * @author Stephan Hochdoerfer <S.Hochdoerfer@bitExpert.de>
 * @version $Id: 9bc3829efb98aff78729b2da69cfde7d9c1204ff $
 * @since 2.4.10
 * @package phing.tasks.ext.liquibase
 */
class LiquibaseUpdateTask extends AbstractLiquibaseTask
{
    /**
     * @see Task::main()
     */
    public function main()
    {
        $this->checkParams();
        $this->execute('update');
    }
}
