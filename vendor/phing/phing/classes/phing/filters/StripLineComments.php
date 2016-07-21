<?php

/*
 *  $Id: 8761015c0828acd83c3eeec2bac42e09125bd410 $
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

include_once 'phing/filters/BaseParamFilterReader.php';
include_once 'phing/filters/ChainableReader.php';

/**
 * This filter strips line comments.
 *
 * Example:
 *
 * <pre><striplinecomments>
 *   <comment value="#"/>
 *   <comment value="--"/>
 *   <comment value="REM "/>
 *   <comment value="rem "/>
 *   <comment value="//"/>
 * </striplinecomments></pre>
 *
 * Or:
 *
 * <pre><filterreader classname="phing.filters.StripLineComments">
 *   <param type="comment" value="#"/>
 *   <param type="comment" value="--"/>
 *   <param type="comment" value="REM "/>
 *   <param type="comment" value="rem "/>
 *   <param type="comment" value="//"/>
 * </filterreader></pre>
 *
 * @author    <a href="mailto:yl@seasonfive.com">Yannick Lecaillez</a>
 * @author    hans lellelid, hans@velum.net
 * @version   $Id: 8761015c0828acd83c3eeec2bac42e09125bd410 $
 * @see       BaseParamFilterReader
 * @package   phing.filters
 */
class StripLineComments extends BaseParamFilterReader implements ChainableReader
{

    /** Parameter name for the comment prefix. */
    const COMMENTS_KEY = "comment";

    /** Array that holds the comment prefixes. */
    private $_comments = array();

    /**
     * Returns stream only including
     * lines from the original stream which don't start with any of the
     * specified comment prefixes.
     *
     * @param null $len
     * @return mixed the resulting stream, or -1
     *               if the end of the resulting stream has been reached.
     *
     */
    public function read($len = null)
    {

        if (!$this->getInitialized()) {
            $this->_initialize();
            $this->setInitialized(true);
        }

        $buffer = $this->in->read($len);

        if ($buffer === -1) {
            return -1;
        }

        $lines = explode("\n", $buffer);
        $filtered = array();

        $commentsSize = count($this->_comments);

        foreach ($lines as $line) {
            for ($i = 0; $i < $commentsSize; $i++) {
                $comment = $this->_comments[$i]->getValue();
                if (StringHelper::startsWith($comment, ltrim($line))) {
                    $line = null;
                    break;
                }
            }
            if ($line !== null) {
                $filtered[] = $line;
            }
        }

        $filtered_buffer = implode("\n", $filtered);

        return $filtered_buffer;
    }

    /*
     * Adds a <code>comment</code> element to the list of prefixes.
     *
     * @return comment The <code>comment</code> element added to the
     *                 list of comment prefixes to strip.
    */
    public function createComment()
    {
        $num = array_push($this->_comments, new Comment());

        return $this->_comments[$num - 1];
    }

    /*
     * Sets the list of comment prefixes to strip.
     *
     * @param comments A list of strings, each of which is a prefix
     *                 for a comment line. Must not be <code>null</code>.
    */
    /**
     * @param $lineBreaks
     * @throws Exception
     */
    public function setComments($lineBreaks)
    {
        if (!is_array($lineBreaks)) {
            throw new Exception("Excpected 'array', got something else");
        }
        $this->_comments = $lineBreaks;
    }

    /*
     * Returns the list of comment prefixes to strip.
     *
     * @return array The list of comment prefixes to strip.
    */
    /**
     * @return array
     */
    public function getComments()
    {
        return $this->_comments;
    }

    /*
     * Creates a new StripLineComments using the passed in
     * Reader for instantiation.
     *
     * @param reader A Reader object providing the underlying stream.
     *               Must not be <code>null</code>.
     *
     * @return a new filter based on this configuration, but filtering
     *           the specified reader
     */
    /**
     * @param Reader $reader
     * @return StripLineComments
     * @throws Exception
     */
    public function chain(Reader $reader)
    {
        $newFilter = new StripLineComments($reader);
        $newFilter->setComments($this->getComments());
        $newFilter->setInitialized(true);
        $newFilter->setProject($this->getProject());

        return $newFilter;
    }

    /*
     * Parses the parameters to set the comment prefixes.
    */
    private function _initialize()
    {
        $params = $this->getParameters();
        if ($params !== null) {
            for ($i = 0; $i < count($params); $i++) {
                if (self::COMMENTS_KEY === $params[$i]->getType()) {
                    $comment = new Comment();
                    $comment->setValue($params[$i]->getValue());
                    array_push($this->_comments, $comment);
                }
            }
        }
    }
}

/**
 * The class that holds a comment representation.
 *
 * @package phing.filters
 */
class Comment
{

    /** The prefix for a line comment. */
    private $_value;

    /*
     * Sets the prefix for this type of line comment.
     *
     * @param string $value The prefix for a line comment of this type.
     *                      Must not be <code>null</code>.
     */
    /**
     * @param $value
     */
    public function setValue($value)
    {
        $this->_value = (string) $value;
    }

    /*
     * Returns the prefix for this type of line comment.
     *
     * @return string The prefix for this type of line comment.
    */
    public function getValue()
    {
        return $this->_value;
    }
}
