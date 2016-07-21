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
 * Task to create the diff between two databases. Will output the changes needed
 * to convert the reference database to the database.
 *
 * @author Stephan Hochdoerfer <S.Hochdoerfer@bitExpert.de>
 * @version $Id: d678839b8d9ff66611eac44926d340bc8fe97128 $
 * @since 2.4.10
 * @package phing.tasks.ext.liquibase
 */
class LiquibaseDiffTask extends AbstractLiquibaseTask
{
    protected $referenceUsername;
    protected $referencePassword;
    protected $referenceUrl;

    /**
     * Sets the username to connect to the reference database.
     *
     * @param string the username
     */
    public function setReferenceUsername($username)
    {
        $this->referenceUsername = $username;
    }

    /**
     * Sets the password to connect to the reference database.
     *
     * @param string the password
     */
    public function setReferencePassword($password)
    {
        $this->referencePassword = $password;
    }

    /**
     * Sets the url to connect to the reference database in jdbc style, e.g.
     * <code>
     * jdbc:postgresql://psqlhost/myrefdatabase
     * </code>
     *
     * @param string jdbc connection string
     */
    public function setReferenceUrl($url)
    {
        $this->referenceUrl = $url;
    }

    /**
     * @see AbstractTask::checkParams()
     */
    protected function checkParams()
    {
        parent::checkParams();

        if (null === $this->referenceUsername) {
            throw new BuildException('Please provide a username for the reference database acccess!');
        }

        if (null === $this->referencePassword) {
            throw new BuildException('Please provide a password for the reference database acccess!');
        }

        if (null === $this->referenceUrl) {
            throw new BuildException('Please provide a url for the reference database acccess!');
        }
    }

    /**
     * @see Task::main()
     */
    public function main()
    {
        $this->checkParams();

        $refparams = sprintf(
            '--referenceUsername=%s --referencePassword=%s --referenceUrl=%s',
            escapeshellarg($this->referenceUsername),
            escapeshellarg($this->referencePassword),
            escapeshellarg($this->referenceUrl)
        );

        // save main changelog file
        $changelogFile = $this->changeLogFile;

        // set the name of the new generated changelog file
        $this->setChangeLogFile(dirname($changelogFile) . '/diffs/' . date('YmdHis') . '.xml');
        if (!is_dir(dirname($changelogFile) . '/diffs/')) {
            mkdir(dirname($changelogFile) . '/diffs/', 0777, true);
        }
        $this->execute('diffChangeLog', $refparams);

        $xmlFile = new DOMDocument();
        $xmlFile->load($changelogFile);

        // create the new node
        $rootNode = $xmlFile->getElementsByTagName('databaseChangeLog')->item(0);
        $includeNode = $rootNode->appendChild($xmlFile->createElement('include'));

        // set the attributes for the new node
        $includeNode->setAttribute('file', str_replace(dirname($changelogFile) . '/', '', $this->changeLogFile));
        $includeNode->setAttribute('relativeToChangelogFile', 'true');
        file_put_contents($changelogFile, $xmlFile->saveXML());

        $this->setChangeLogFile($changelogFile);
        $this->execute('markNextChangeSetRan');
    }
}
