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
 * Task to create a javadoc-like documentation based on current database and
 * changelog.
 *
 * @author Stephan Hochdoerfer <S.Hochdoerfer@bitExpert.de>
 * @version $Id: a8df74bfdcd6fceb45725bbaa7076682223fc8b1 $
 * @since 2.4.10
 * @package phing.tasks.ext.liquibase
 */
class LiquibaseDbDocTask extends AbstractLiquibaseTask
{
    protected $outputDir;

    /**
     * Sets the output directory where the documentation gets generated to.
     *
     * @param string the output directory
     */
    public function setOutputDir($outputDir)
    {
        $this->outputDir = $outputDir;
    }

    /**
     * @see AbstractTask::checkParams()
     */
    protected function checkParams()
    {
        parent::checkParams();

        if ((null === $this->outputDir) or !is_dir($this->outputDir)) {
            if (!mkdir($this->outputDir, 0777, true)) {
                throw new BuildException(
                    sprintf(
                        'The directory "%s" does not exist and could not be created!',
                        $this->outputDir
                    )
                );
            }
        }

        if (!is_writable($this->outputDir)) {
            throw new BuildException(
                sprintf(
                    'The directory "%s" is not writable!',
                    $this->outputDir
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
        $this->execute('dbdoc', escapeshellarg($this->outputDir));
    }
}
