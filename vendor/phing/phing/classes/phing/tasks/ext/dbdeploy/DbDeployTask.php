<?php
/*
 *  $Id: 2800e23b0fecfddc209dec5c7985e0c8eace0959 $
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
require_once 'phing/tasks/ext/dbdeploy/DbmsSyntaxFactory.php';

/**
 * Generate SQL script for db using dbdeploy schema version table
 * and delta scripts
 *
 * <dbdeploy url="mysql:host=localhost;dbname=test"
 *     userid="dbdeploy" password="dbdeploy" dir="db" outputfile="">
 *
 * @author   Luke Crouch at SourceForge (http://sourceforge.net)
 * @version  $Id: 2800e23b0fecfddc209dec5c7985e0c8eace0959 $
 * @package  phing.tasks.ext.dbdeploy
 */
class DbDeployTask extends Task
{
    /**
     * The tablename to use from the database for storing all changes
     * This cannot be changed
     *
     * @var string
     */
    public static $TABLE_NAME = 'changelog';

    /**
     * Connection string for the database connection
     *
     * @var string
     */
    protected $url;

    /**
     * The userid for the database connection
     *
     * @var string
     */
    protected $userid;

    /**
     * The password of the database user
     *
     * @var string
     */
    protected $password;

    /**
     * Path to the directory that holds the database patch files
     *
     * @var string
     */
    protected $dir;

    /**
     * Output file for performing all database patches of this deployment
     * Contains all the SQL statements that need to be executed
     *
     * @var string
     */
    protected $outputFile = 'dbdeploy_deploy.sql';

    /**
     * Outputfile for undoing the database patches of this deployment
     * Contains all the SQL statements that need to be executed
     *
     * @var string
     */
    protected $undoOutputFile = 'dbdeploy_undo.sql';

    /**
     * The deltaset that's being used
     *
     * @var string
     */
    protected $deltaSet = 'Main';

    /**
     * The number of the last change to apply
     *
     * @var int
     */
    protected $lastChangeToApply = 999;

    /**
     * Contains the object for the DBMS that is used
     *
     * @var object
     */
    protected $dbmsSyntax = null;

    /**
     * Array with all change numbers that are applied already
     *
     * @var array
     */
    protected $appliedChangeNumbers = array();

    /**
     * Checkall attribute
     * False means dbdeploy will only apply patches that have a higher number
     * than the last patchnumber that was applied
     * True means dbdeploy will apply all changes that aren't applied
     * already (in ascending order)
     *
     * @var int
     */
    protected $checkall = false;

    /**
     * The value of the 'applied_by' column for
     * each changelog entry
     *
     * @var string
     */
    protected $appliedBy = 'dbdeploy';

    /**
     * The main function for the task
     *
     * @throws BuildException
     * @return void
     */
    public function main()
    {
        try {
            // get correct DbmsSyntax object
            $dbms = substr($this->url, 0, strpos($this->url, ':'));
            $dbmsSyntaxFactory = new DbmsSyntaxFactory($dbms);
            $this->dbmsSyntax = $dbmsSyntaxFactory->getDbmsSyntax();

            // figure out which revisions are in the db already
            $this->appliedChangeNumbers = $this->getAppliedChangeNumbers();
            $this->log('Current db revision: ' . $this->getLastChangeAppliedInDb());
            $this->log('Checkall: ' . ($this->checkall ? 'On' : 'Off'));

            $this->deploy();

        } catch (Exception $e) {
            throw new BuildException($e);
        }
    }

    /**
     * Get the numbers of all the patches that are already applied according to
     * the changelog table in the database
     *
     * @return array
     */
    protected function getAppliedChangeNumbers()
    {
        if (count($this->appliedChangeNumbers) == 0) {
            $this->log('Getting applied changed numbers from DB: ' . $this->url);
            $appliedChangeNumbers = array();
            $dbh = new PDO($this->url, $this->userid, $this->password);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->dbmsSyntax->applyAttributes($dbh);
            $sql = "SELECT *
                    FROM " . DbDeployTask::$TABLE_NAME . "
                    WHERE delta_set = '$this->deltaSet'
                    ORDER BY change_number";
            foreach ($dbh->query($sql) as $change) {
                $appliedChangeNumbers[] = $change['change_number'];
            }
            $this->appliedChangeNumbers = $appliedChangeNumbers;
        }

        return $this->appliedChangeNumbers;
    }

    /**
     * Get the number of the last patch applied to the database
     *
     * @return int|mixed The highest patch number that is applied in the db
     */
    protected function getLastChangeAppliedInDb()
    {
        return (count($this->appliedChangeNumbers) > 0)
            ? max($this->appliedChangeNumbers) : 0;
    }

    /**
     * Create the deploy and undo deploy outputfiles
     *
     * @return void
     */
    protected function deploy()
    {
        // create deploy outputfile
        $this->createOutputFile($this->outputFile, false);

        // create undo deploy outputfile
        $this->createOutputFile($this->undoOutputFile, true);
    }

    /**
     * Generate the sql for doing/undoing the deployment and write it to a file
     *
     * @param  string $file
     * @param  bool   $undo
     * @return void
     */
    protected function createOutputFile($file, $undo = false)
    {
        $fileHandle = fopen($file, "w+");
        $sql = $this->generateSql($undo);
        fwrite($fileHandle, $sql);
    }

    /**
     * Generate the sql for doing/undoing this deployment
     *
     * @param  bool   $undo
     * @return string The sql
     */
    protected function generateSql($undo = false)
    {
        $sql = '';
        $lastChangeAppliedInDb = $this->getLastChangeAppliedInDb();
        $files = $this->getDeltasFilesArray();
        $this->sortFiles($files, $undo);

        foreach ($files as $fileChangeNumber => $fileName) {
            if ($this->fileNeedsToBeRead($fileChangeNumber, $lastChangeAppliedInDb)) {
                $sql .= '-- Fragment begins: ' . $fileChangeNumber . ' --' . "\n";

                if (!$undo) {
                    $sql .= 'INSERT INTO ' . DbDeployTask::$TABLE_NAME . '
                                (change_number, delta_set, start_dt, applied_by, description)' .
                        ' VALUES (' . $fileChangeNumber . ', \'' . $this->deltaSet . '\', ' .
                        $this->dbmsSyntax->generateTimestamp() .
                        ', \'' . $this->appliedBy . '\', \'' . $fileName . '\');' . "\n";
                }

                // read the file
                $fullFileName = $this->dir . '/' . $fileName;
                $fh = fopen($fullFileName, 'r');
                $contents = fread($fh, filesize($fullFileName));
                // allow construct with and without space added
                $split = strpos($contents, '-- //@UNDO');
                if ($split === false) {
                    $split = strpos($contents, '--//@UNDO');
                }
                if ($split === false) {
                    $split = strlen($contents);
                }

                if ($undo) {
                    $sql .= substr($contents, $split + 10) . "\n";
                    $sql .= 'DELETE FROM ' . DbDeployTask::$TABLE_NAME . '
	                         WHERE change_number = ' . $fileChangeNumber . '
	                         AND delta_set = \'' . $this->deltaSet . '\';' . "\n";
                } else {
                    $sql .= substr($contents, 0, $split);
                    // Ensuring there's a newline after the final -- //
                    $sql .= PHP_EOL;
                    $sql .= 'UPDATE ' . DbDeployTask::$TABLE_NAME . '
	                         SET complete_dt = ' . $this->dbmsSyntax->generateTimestamp() . '
	                         WHERE change_number = ' . $fileChangeNumber . '
	                         AND delta_set = \'' . $this->deltaSet . '\';' . "\n";
                }

                $sql .= '-- Fragment ends: ' . $fileChangeNumber . ' --' . "\n";
            }
        }

        return $sql;
    }

    /**
     * Get a list of all the patch files in the patch file directory
     *
     * @return array
     */
    protected function getDeltasFilesArray()
    {
        $files = array();

        $baseDir = realpath($this->dir);
        $dh = opendir($baseDir);

        if ($dh === false) {
            return $files;
        }

        $fileChangeNumberPrefix = '';
        while (($file = readdir($dh)) !== false) {
            if (preg_match('[\d+]', $file, $fileChangeNumberPrefix)) {
                $files[intval($fileChangeNumberPrefix[0])] = $file;
            }
        }

        return $files;
    }

    /**
     * Sort files in the patch files directory (ascending or descending depending on $undo boolean)
     *
     * @param  array $files
     * @param  bool  $undo
     * @return void
     */
    protected function sortFiles(&$files, $undo)
    {
        if ($undo) {
            krsort($files);
        } else {
            ksort($files);
        }
    }

    /**
     * Determine if this patch file need to be deployed
     * (using fileChangeNumber, lastChangeAppliedInDb and $this->checkall)
     *
     * @param  int    $fileChangeNumber
     * @param  string $lastChangeAppliedInDb
     * @return bool   True or false if patch file needs to be deployed
     */
    protected function fileNeedsToBeRead($fileChangeNumber, $lastChangeAppliedInDb)
    {
        if ($this->checkall) {
            return (!in_array($fileChangeNumber, $this->appliedChangeNumbers));
        } else {
            return ($fileChangeNumber > $lastChangeAppliedInDb && $fileChangeNumber <= $this->lastChangeToApply);
        }
    }

    /**
     * Set the url for the database connection
     *
     * @param  string $url
     * @return void
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Set the userid for the database connection
     *
     * @param  string $userid
     * @return void
     */
    public function setUserId($userid)
    {
        $this->userid = $userid;
    }

    /**
     * Set the password for the database connection
     *
     * @param  string $password
     * @return void
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Set the directory where to find the patchfiles
     *
     * @param  string $dir
     * @return void
     */
    public function setDir($dir)
    {
        $this->dir = $dir;
    }

    /**
     * Set the outputfile which contains all patch sql statements for this deployment
     *
     * @param  string $outputFile
     * @return void
     */
    public function setOutputFile($outputFile)
    {
        $this->outputFile = $outputFile;
    }

    /**
     * Set the undo outputfile which contains all undo statements for this deployment
     *
     * @param  string $undoOutputFile
     * @return void
     */
    public function setUndoOutputFile($undoOutputFile)
    {
        $this->undoOutputFile = $undoOutputFile;
    }

    /**
     * Set the lastchangetoapply property
     *
     * @param  int  $lastChangeToApply
     * @return void
     */
    public function setLastChangeToApply($lastChangeToApply)
    {
        $this->lastChangeToApply = $lastChangeToApply;
    }

    /**
     * Set the deltaset property
     *
     * @param  string $deltaSet
     * @return void
     */
    public function setDeltaSet($deltaSet)
    {
        $this->deltaSet = $deltaSet;
    }

    /**
     * Set the checkall property
     *
     * @param  bool $checkall
     * @return void
     */
    public function setCheckAll($checkall)
    {
        $this->checkall = (int) $checkall;
    }

    /**
     * Set the appliedBy property
     *
     * @param  string $appliedBy
     * @return void
     */
    public function setAppliedBy($appliedBy)
    {
        $this->appliedBy = $appliedBy;
    }

    /**
     * Nested adder, adds a set of files (nested fileset attribute).
     *
     * @param FileSet $fs
     * @return void
     */
    public function addFileSet(FileSet $fs)
    {
        $this->filesets[] = $fs;
    }
}
