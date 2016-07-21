<?php
/**
 *  $Id: 63ab78ae0c05fe95b19878434bce454ce8d82a97 $
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

/**
 * Class for holding nested excludes elements (file, class, method).
 *
 * @package phing.types
 * @author  Benjamin Schultz <bschultz@proqrent.de>
 * @version $Id: 63ab78ae0c05fe95b19878434bce454ce8d82a97 $
 * @since   2.4.6
 */
class ExcludesNameEntry
{
    /**
     * Holds the name of a file, class or method or a file pattern
     *
     * @var string
     */
    private $name;

    /**
     * An alias for the setName() method.
     * Set the name of a file pattern.
     *
     * @see setName()
     *
     * @param string $pattern The file pattern
     */
    public function addText($pattern)
    {
        $this->setName($pattern);
    }

    /**
     * Set the name of a file, class or method
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = (string) $name;
    }

    /**
     * Get the name of a file, class or method or the file pattern
     *
     * @return string The name of a file, class or method or the file pattern
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets a string representation of this name or pattern.
     *
     * @return string
     */
    public function toString()
    {
        return $this->name;
    }
}
