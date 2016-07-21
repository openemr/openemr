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

include_once 'phing/util/StringHelper.php';

/**
 * A Path tokenizer takes a path and returns the components that make up
 * that path.
 *
 * The path can use path separators of either ':' or ';' and file separators
 * of either '/' or '\'.
 *
 * @author Hans Lellelid <hans@xmpl.org> (Phing)
 * @author Conor MacNeill (Ant)
 * @author Jeff Tulley <jtulley@novell.com>  (Ant)
 *
 * @package phing.util
 */
class PathTokenizer
{
    /**
     * A array of tokens, created by preg_split().
     */
    private $tokens = array();

    /**
     * A string which stores any path components which have been read ahead
     * due to DOS filesystem compensation.
     * @var string
     */
    private $lookahead;

    /**
     * Flag to indicate whether or not we are running on a platform with a
     * DOS style filesystem
     * @var boolean
     */
    private $dosStyleFilesystem;


    /**
     * Constructs a path tokenizer for the specified path.
     *
     * @param string $path The path to tokenize. Must not be <code>null</code>.
     */
    public function __construct($path)
    {

        // on Windows and Unix, we can ignore delimiters and still have

        // enough information to tokenize correctly.

        $this->tokens = preg_split("/[;:]/", $path, -1, PREG_SPLIT_NO_EMPTY);

        $this->dosStyleFilesystem = (PATH_SEPARATOR == ';');

    }


    /**
     * Tests if there are more path elements available from this tokenizer's
     * path. If this method returns <code>true</code>, then a subsequent call
     * to nextToken will successfully return a token.
     *
     * @return bool <code>true</code> if and only if there is at least one token
     *                                in the string after the current position; <code>false</code> otherwise.
     */
    public function hasMoreTokens()
    {

        if ($this->lookahead !== null) {
            return true;

        }

        return !empty($this->tokens);
    }

    /**
     * Returns the next path element from this tokenizer.
     *
     * @return string the next path element from this tokenizer.
     *
     * @throws Exception if there are no more elements in this tokenizer's path.
     */
    public function nextToken()
    {
        if ($this->lookahead !== null) {

            $token = $this->lookahead;

            $this->lookahead = null;

        } else {

            $token = trim(array_shift($this->tokens));

        }


        if (strlen($token) === 1 && Character::isLetter($token{0})

            && $this->dosStyleFilesystem

            && !empty($this->tokens)
        ) {

            // we are on a dos style system so this path could be a drive

            // spec. We look at the next token

            $nextToken = trim(array_shift($this->tokens));

            if (StringHelper::startsWith('\\', $nextToken) || StringHelper::startsWith('/', $nextToken)) {

                // we know we are on a DOS style platform and the next path

                // starts with a slash or backslash, so we know this is a

                // drive spec

                $token .= ':' . $nextToken;

            } else {

                // store the token just read for next time

                $this->lookahead = $nextToken;

            }

        }

        return $token;
    }

    /**
     * Non StringTokenizer function, that indicates whether the specified path is contained in loaded tokens.
     * We can do this easily because in PHP implimentation we're using arrays.
     *
     * @param  string  $path path to search for.
     *
     * @return boolean
     */
    public function contains($path)
    {
        return in_array($path, $this->tokens, true);
    }
}
