<?php
/*
 *  $Id: 1d817a30c44e1f87a0f51ecebdc9fefe3177790c $
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
require_once 'phing/tasks/ext/dbdeploy/DbmsSyntax.php';

/**
 * Factory for generating dbms-specific syntax-generating objects
 *
 * @author   Luke Crouch at SourceForge (http://sourceforge.net)
 * @version  $Id: 1d817a30c44e1f87a0f51ecebdc9fefe3177790c $
 * @package  phing.tasks.ext.dbdeploy
 */
class DbmsSyntaxFactory
{
    private $dbms;

    /**
     * @param $dbms
     */
    public function __construct($dbms)
    {
        $this->dbms = $dbms;
    }

    public function getDbmsSyntax()
    {
        switch ($this->dbms) {
            case('sqlite') :
                require_once 'phing/tasks/ext/dbdeploy/DbmsSyntaxSQLite.php';

                return new DbmsSyntaxSQLite();
            case('mysql'):
                require_once 'phing/tasks/ext/dbdeploy/DbmsSyntaxMysql.php';

                return new DbmsSyntaxMysql();
            case 'odbc':
            case('mssql'):
            case 'dblib':
                require_once 'phing/tasks/ext/dbdeploy/DbmsSyntaxMsSql.php';

                return new DbmsSyntaxMsSql();
            case('pgsql'):
                require_once 'phing/tasks/ext/dbdeploy/DbmsSyntaxPgSQL.php';

                return new DbmsSyntaxPgSQL();
            case 'oci':
                require_once 'phing/tasks/ext/dbdeploy/DbmsSyntaxOracle.php';

                return new DbmsSyntaxOracle();
            default:
                throw new Exception($this->dbms . ' is not supported by dbdeploy task.');
        }
    }
}
