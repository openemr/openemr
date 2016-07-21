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

include_once 'phing/mappers/FileNameMapper.php';

/**
 * A <code>FileNameMapper</code> that contains
 * other <code>FileNameMapper</code>s.
 * @see FileNameMapper
 */
abstract class ContainerMapper implements FileNameMapper
{
    private $mappers = array();

    /**
     * Add a <code>Mapper</code>.
     * @param Mapper $mapper the <code>Mapper</code> to add.
     */
    public function addMapper(Mapper $mapper)
    {
        $this->add($mapper->getImplementation());
    }

    /**
     * An add configured version of the add method.
     * This class used to contain an add method and an
     * addConfiguredMapper method. Dur to ordering,
     * the add method was always called first. This
     * addConfigured method has been added to allow
     * chaining to work correctly.
     * @param FileNameMapper $fileNameMapper a <code>FileNameMapper</code>.
     */
    public function addConfigured(FileNameMapper $fileNameMapper)
    {
        $this->add($fileNameMapper);
    }

    /**
     * Add a <code>FileNameMapper</code>.
     * @param FileNameMapper $fileNameMapper a <code>FileNameMapper</code>.
     * @throws BadMethodCallException if attempting to add this
     *         <code>ContainerMapper</code> to itself, or if the specified
     *         <code>FileNameMapper</code> is itself a <code>ContainerMapper</code>
     *         that contains this <code>ContainerMapper</code>.
     */
    public function add(Mapper $fileNameMapper)
    {
        if ($this == $fileNameMapper || ($fileNameMapper instanceof ContainerMapper && $fileNameMapper->contains($this))) {
            throw new BadMethodCallException("Circular mapper containment condition detected");
        } else {
            $this->mappers[] = $fileNameMapper;
        }
    }

    /**
     * Return <code>true</code> if this <code>ContainerMapper</code> or any of
     * its sub-elements contains the specified <code>FileNameMapper</code>.
     * @param FileNameMapper $fileNameMapper   the <code>FileNameMapper</code> to search for.
     * @return boolean
     */
    protected function contains(FileNameMapper $fileNameMapper)
    {
        $foundit = false;
        for ($iter = new ArrayIterator($this->mappers); $iter->valid() && !$foundit;) {
            $iter->next();
            $next = $iter->current();
            $foundit = ($next == $fileNameMapper || ($next instanceof ContainerMapper && $next->contains($fileNameMapper)));
        }
        return $foundit;
    }

    /**
     * Get the <code>List</code> of <code>FileNameMapper</code>s.
     * @return FileNameMapper[]
     */
    public function getMappers()
    {
        return $this->mappers;
    }

    /**
     * Empty implementation.
     * @param string $ignore ignored.
     */
    public function setFrom($ignore)
    {
        //Empty
    }

    /**
     * Empty implementation.
     * @param string $ignore ignored.
     */
    public function setTo($ignore)
    {
        //Empty
    }
}
