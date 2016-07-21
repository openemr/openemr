<?php
/**
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

// include_once 'phing/system/io/Reader.php'; // really this is unrelated to Reader
include_once 'phing/system/io/IOException.php';
include_once 'phing/filters/ReplaceTokens.php'; // For class Token

/**
 * Abstract class for TokenReaders.
 *
 * @author    Manuel Holtgewe
 *
 * @package   phing.filters.util
 */
abstract class TokenReader
{
    /**
     * Reference to the Project the TokenReader is used in.
     *
     * @var Project
     */
    protected $project;

    /**
     * Constructor.
     *
     * @param Project $project Reference to the project the TokenReader is used in.
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Utility function for logging.
     *
     * @param $level
     * @param $msg
     *
     * @return void
     */
    public function log($level, $msg)
    {
        $this->project->log($level, $msg);
    }

    /**
     * Reads the next token from the Reader.
     *
     * @throws IOException - On error
     *
     * @return string
     */
    abstract public function readToken();

}
