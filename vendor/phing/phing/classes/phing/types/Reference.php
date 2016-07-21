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

/**
 * Class to hold a reference to another object in the project.
 *
 * @package phing.types
 */
class Reference
{
    /** @var string $refid */
    protected $refid;

    /**
     * @param string $id
     */
    public function __construct($id = null)
    {
        if ($id !== null) {
            $this->setRefId($id);
        }
    }

    /**
     * @param $id
     */
    public function setRefId($id)
    {
        $this->refid = (string) $id;
    }

    /**
     * @return string
     */
    public function getRefId()
    {
        return $this->refid;
    }

    /**
     * returns reference to object in references container of project
     *
     * @param Project $project
     *
     * @throws BuildException
     *
     * @return Reference
     */
    public function getReferencedObject(Project $project)
    {
        if ($this->refid === null) {
            throw new BuildException("No reference specified");
        }
        $refs = $project->getReferences();
        $o = @$refs[$this->refid];
        if (!is_object($o)) {
            throw new BuildException("Reference {$this->refid} not found.");
        }

        return $o;
    }
}
