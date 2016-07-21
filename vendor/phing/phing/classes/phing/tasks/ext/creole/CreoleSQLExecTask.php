<?php
/*
 *  $Id: b0540d6f531fdc2a5103a09c92a54318ebdf8c0b $
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

require_once 'phing/tasks/ext/creole/CreoleTask.php';
include_once 'phing/system/io/StringReader.php';

/**
 * Executes a series of SQL statements on a database using Creole.
 *
 * <p>Statements can
 * either be read in from a text file using the <i>src</i> attribute or from
 * between the enclosing SQL tags.</p>
 *
 * <p>Multiple statements can be provided, separated by semicolons (or the
 * defined <i>delimiter</i>). Individual lines within the statements can be
 * commented using either --, // or REM at the start of the line.</p>
 *
 * <p>The <i>autocommit</i> attribute specifies whether auto-commit should be
 * turned on or off whilst executing the statements. If auto-commit is turned
 * on each statement will be executed and committed. If it is turned off the
 * statements will all be executed as one transaction.</p>
 *
 * <p>The <i>onerror</i> attribute specifies how to proceed when an error occurs
 * during the execution of one of the statements.
 * The possible values are: <b>continue</b> execution, only show the error;
 * <b>stop</b> execution and commit transaction;
 * and <b>abort</b> execution and transaction and fail task.</p>
 *
 * @author    Hans Lellelid <hans@xmpl.org> (Phing)
 * @author    Jeff Martin <jeff@custommonkey.org> (Ant)
 * @author    Michael McCallum <gholam@xtra.co.nz> (Ant)
 * @author    Tim Stephenson <tim.stephenson@sybase.com> (Ant)
 * @package   phing.tasks.ext.creole
 * @version   $Id: b0540d6f531fdc2a5103a09c92a54318ebdf8c0b $
 */
class CreoleSQLExecTask extends CreoleTask
{

    private $goodSql = 0;
    private $totalSql = 0;

    const DELIM_ROW = "row";
    const DELIM_NORMAL = "normal";

    /**
     * Database connection
     */
    private $conn = null;

    /**
     * files to load
     */
    private $filesets = array();

    /**
     * all filterchains objects assigned to this task
     */
    private $filterChains = array();

    /**
     * SQL statement
     */
    private $statement = null;

    /**
     * SQL input file
     */
    private $srcFile = null;

    /**
     * SQL input command
     */
    private $sqlCommand = "";

    /**
     * SQL transactions to perform
     */
    private $transactions = array();

    /**
     * SQL Statement delimiter
     */
    private $delimiter = ";";

    /**
     * The delimiter type indicating whether the delimiter will
     * only be recognized on a line by itself
     */
    private $delimiterType = "normal"; // can't use constant just defined

    /**
     * Print SQL results.
     */
    private $print = false;

    /**
     * Print header columns.
     */
    private $showheaders = true;

    /**
     * Results Output file.
     */
    private $output = null;


    /**
     * Action to perform if an error is found
     **/
    private $onError = "abort";

    /**
     * Encoding to use when reading SQL statements from a file
     */
    private $encoding = null;

    /**
     * Append to an existing file or overwrite it?
     */
    private $append = false;

    /**
     * Set the name of the SQL file to be run.
     * Required unless statements are enclosed in the build file
     * @param PhingFile $srcFile
     */
    public function setSrc(PhingFile $srcFile)
    {
        $this->srcFile = $srcFile;
    }

    /**
     * Set an inline SQL command to execute.
     * NB: Properties are not expanded in this text.
     * @param $sql
     */
    public function addText($sql)
    {
        $this->sqlCommand .= $sql;
    }

    /**
     * Adds a set of files (nested fileset attribute).
     * @param FileSet $set
     */
    public function addFileset(FileSet $set)
    {
        $this->filesets[] = $set;
    }

    /**
     * Creates a filterchain
     *
     * @return object The created filterchain object
     */
    public function createFilterChain()
    {
        $num = array_push($this->filterChains, new FilterChain($this->project));

        return $this->filterChains[$num - 1];
    }

    /**
     * Add a SQL transaction to execute
     */
    public function createTransaction()
    {
        $t = new SQLExecTransaction($this);
        $this->transactions[] = $t;

        return $t;
    }

    /**
     * Set the file encoding to use on the SQL files read in
     *
     * @param the $encoding
     * @internal param the $encoding encoding to use on the files
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

    /**
     * Set the statement delimiter.
     *
     * <p>For example, set this to "go" and delimitertype to "ROW" for
     * Sybase ASE or MS SQL Server.</p>
     *
     * @param delimiter
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
    }

    /**
     * Set the Delimiter type for this sql task. The delimiter type takes two
     * values - normal and row. Normal means that any occurrence of the delimiter
     * terminate the SQL command whereas with row, only a line containing just
     * the delimiter is recognized as the end of the command.
     *
     * @param string $delimiterType
     */
    public function setDelimiterType($delimiterType)
    {
        $this->delimiterType = $delimiterType;
    }

    /**
     * Set the print flag.
     *
     * @param boolean $print
     */
    public function setPrint($print)
    {
        $this->print = (boolean) $print;
    }

    /**
     * Print headers for result sets from the
     * statements; optional, default true.
     * @param boolean $showheaders
     */
    public function setShowheaders($showheaders)
    {
        $this->showheaders = (boolean) $showheaders;
    }

    /**
     * Set the output file;
     * optional, defaults to the console.
     * @param PhingFile $output
     */
    public function setOutput(PhingFile $output)
    {
        $this->output = $output;
    }

    /**
     * whether output should be appended to or overwrite
     * an existing file.  Defaults to false.
     * @param $append
     */
    public function setAppend($append)
    {
        $this->append = (boolean) $append;
    }


    /**
     * Action to perform when statement fails: continue, stop, or abort
     * optional; default &quot;abort&quot;
     * @param $action
     */
    public function setOnerror($action)
    {
        $this->onError = $action;
    }

    /**
     * Load the sql file and then execute it
     * @throws BuildException
     */
    public function main()
    {

        $savedTransaction = array();
        for ($i = 0, $size = count($this->transactions); $i < $size; $i++) {
            $savedTransaction[] = clone $this->transactions[$i];
        }

        $savedSqlCommand = $this->sqlCommand;

        $this->sqlCommand = trim($this->sqlCommand);

        try {
            if ($this->srcFile === null && $this->sqlCommand === ""
                && empty($this->filesets)
            ) {
                if (count($this->transactions) === 0) {
                    throw new BuildException("Source file or fileset, "
                        . "transactions or sql statement "
                        . "must be set!", $this->location);
                }
            }

            if ($this->srcFile !== null && !$this->srcFile->exists()) {
                throw new BuildException("Source file does not exist!", $this->location);
            }

            // deal with the filesets
            for ($i = 0, $size = count($this->filesets); $i < $size; $i++) {
                $fs = $this->filesets[$i];
                $ds = $fs->getDirectoryScanner($this->project);
                $srcDir = $fs->getDir($this->project);

                $srcFiles = $ds->getIncludedFiles();

                // Make a transaction for each file
                for ($j = 0, $size = count($srcFiles); $j < $size; $j++) {
                    $t = $this->createTransaction();
                    $t->setSrc(new PhingFile($srcDir, $srcFiles[$j]));
                }
            }

            // Make a transaction group for the outer command
            $t = $this->createTransaction();
            if ($this->srcFile) {
                $t->setSrc($this->srcFile);
            }
            $t->addText($this->sqlCommand);
            $this->conn = $this->getConnection();

            try {

                $this->statement = $this->conn->createStatement();

                $out = null;

                try {

                    if ($this->output !== null) {
                        $this->log("Opening output file " . $this->output, Project::MSG_VERBOSE);
                        $out = new BufferedWriter(new FileWriter($this->output->getAbsolutePath(), $this->append));
                    }

                    // Process all transactions
                    for ($i = 0, $size = count($this->transactions); $i < $size; $i++) {
                        $this->transactions[$i]->runTransaction($out);
                        if (!$this->isAutocommit()) {
                            $this->log("Committing transaction", Project::MSG_VERBOSE);
                            $this->conn->commit();
                        }
                    }
                    if ($out) {
                        $out->close();
                    }
                } catch (Exception $e) {
                    if ($out) {
                        $out->close();
                    }
                    throw $e;
                }
            } catch (IOException $e) {
                if (!$this->isAutocommit() && $this->conn !== null && $this->onError == "abort") {
                    try {
                        $this->conn->rollback();
                    } catch (SQLException $ex) {
                    }
                }
                throw new BuildException($e->getMessage(), $this->location);
            } catch (SQLException $e) {
                if (!$this->isAutocommit() && $this->conn !== null && $this->onError == "abort") {
                    try {
                        $this->conn->rollback();
                    } catch (SQLException $ex) {
                    }
                }
                throw new BuildException($e->getMessage(), $this->location);
            }

            $this->log(
                $this->goodSql . " of " . $this->totalSql .
                " SQL statements executed successfully"
            );
        } catch (Exception $e) {
            $this->transactions = $savedTransaction;
            $this->sqlCommand = $savedSqlCommand;
            throw $e;
        }
        // finally {
        $this->transactions = $savedTransaction;
        $this->sqlCommand = $savedSqlCommand;

    }


    /**
     * read in lines and execute them
     * @param Reader $reader
     * @param null $out
     * @throws BuildException
     */
    public function runStatements(Reader $reader, $out = null)
    {
        $sql = "";
        $line = "";

        $buffer = '';

        if ((is_array($this->filterChains)) && (!empty($this->filterChains))) {
            $in = FileUtils::getChainedReader(new BufferedReader($reader), $this->filterChains, $this->getProject());
            while (-1 !== ($read = $in->read())) { // -1 indicates EOF
                $buffer .= $read;
            }
            $lines = explode("\n", $buffer);
        } else {
            $in = new BufferedReader($reader);

            while (($line = $in->readLine()) !== null) {
                $lines[] = $line;
            }
        }

        try {
            foreach ($lines as $line) {
                $line = trim($line);
                $line = ProjectConfigurator::replaceProperties(
                    $this->project,
                    $line,
                    $this->project->getProperties()
                );

                if (StringHelper::startsWith("//", $line) ||
                    StringHelper::startsWith("--", $line) ||
                    StringHelper::startsWith("#", $line)
                ) {
                    continue;
                }

                if (strlen($line) > 4
                    && strtoupper(substr($line, 0, 4)) == "REM "
                ) {
                    continue;
                }

                $sql .= " " . $line;
                $sql = trim($sql);

                // SQL defines "--" as a comment to EOL
                // and in Oracle it may contain a hint
                // so we cannot just remove it, instead we must end it
                if (strpos($line, "--") !== false) {
                    $sql .= "\n";
                }

                if ($this->delimiterType == self::DELIM_NORMAL
                    && StringHelper::endsWith($this->delimiter, $sql)
                    || $this->delimiterType == self::DELIM_ROW
                    && $line == $this->delimiter
                ) {
                    $this->log("SQL: " . $sql, Project::MSG_VERBOSE);
                    $this->execSQL(StringHelper::substring($sql, 0, strlen($sql) - strlen($this->delimiter)), $out);
                    $sql = "";
                }
            }

            // Catch any statements not followed by ;
            if ($sql !== "") {
                $this->execSQL($sql, $out);
            }
        } catch (SQLException $e) {
            throw new BuildException("Error running statements", $e);
        }
    }

    /**
     * Exec the sql statement.
     * @param $sql
     * @param null $out
     * @throws BuildException
     */
    protected function execSQL($sql, $out = null)
    {
        // Check and ignore empty statements
        if (trim($sql) == "") {
            return;
        }

        try {
            $this->totalSql++;
            if (!$this->statement->execute($sql)) {
                $this->log($this->statement->getUpdateCount() . " rows affected", Project::MSG_VERBOSE);
            } else {
                if ($this->print) {
                    $this->printResults($out);
                }
            }

            $this->goodSql++;

        } catch (SQLException $e) {
            $this->log("Failed to execute: " . $sql, Project::MSG_ERR);
            if ($this->onError != "continue") {
                throw new BuildException("Failed to execute SQL", $e);
            }
            $this->log($e->getMessage(), Project::MSG_ERR);
        }
    }

    /**
     * print any results in the statement.
     * @param null $out
     */
    protected function printResults($out = null)
    {

        $rs = null;
        do {
            $rs = $this->statement->getResultSet();

            if ($rs !== null) {

                $this->log("Processing new result set.", Project::MSG_VERBOSE);

                $line = "";

                $colsprinted = false;

                while ($rs->next()) {
                    $fields = $rs->getRow();

                    if (!$colsprinted && $this->showheaders) {
                        $first = true;
                        foreach ($fields as $fieldName => $ignore) {
                            if ($first) {
                                $first = false;
                            } else {
                                $line .= ",";
                            }
                            $line .= $fieldName;
                        }
                        if ($out !== null) {
                            $out->write($line);
                            $out->newLine();
                        } else {
                            print($line . PHP_EOL);
                        }
                        $line = "";
                        $colsprinted = true;
                    } // if show headers

                    $first = true;
                    foreach ($fields as $columnValue) {

                        if ($columnValue != null) {
                            $columnValue = trim($columnValue);
                        }

                        if ($first) {
                            $first = false;
                        } else {
                            $line .= ",";
                        }
                        $line .= $columnValue;
                    }

                    if ($out !== null) {
                        $out->write($line);
                        $out->newLine();
                    } else {
                        print($line . PHP_EOL);
                    }
                    $line = "";

                } // while rs->next()
            }
        } while ($this->statement->getMoreResults());
        print(PHP_EOL);
        if ($out !== null) {
            $out->newLine();
        }
    }
}

/**
 * "Inner" class that contains the definition of a new transaction element.
 * Transactions allow several files or blocks of statements
 * to be executed using the same JDBC connection and commit
 * operation in between.
 *
 * @package   phing.tasks.ext.creole
 */
class SQLExecTransaction
{

    private $tSrcFile = null;
    private $tSqlCommand = "";
    private $parent;

    /**
     * @param $parent
     */
    public function __construct($parent)
    {
        // Parent is required so that we can log things ...
        $this->parent = $parent;
    }

    /**
     * @param PhingFile $src
     */
    public function setSrc(PhingFile $src)
    {
        $this->tSrcFile = $src;
    }

    /**
     * @param $sql
     */
    public function addText($sql)
    {
        $this->tSqlCommand .= $sql;
    }

    /**
     * @param null $out
     */
    public function runTransaction($out = null)
    {
        if (!empty($this->tSqlCommand)) {
            $this->parent->log("Executing commands", Project::MSG_INFO);
            $this->parent->runStatements(new StringReader($this->tSqlCommand), $out);
        }

        if ($this->tSrcFile !== null) {
            $this->parent->log(
                "Executing file: " . $this->tSrcFile->getAbsolutePath(),
                Project::MSG_INFO
            );

            $reader = new FileReader($this->tSrcFile);

            $this->parent->runStatements($reader, $out);
            $reader->close();
        }
    }
}
