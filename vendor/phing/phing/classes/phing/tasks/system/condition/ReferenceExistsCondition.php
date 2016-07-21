<?php
/*
 *  $Id: 7321246363e6d75f8a2d47d9dfcd82aaf0038708 $
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

require_once 'phing/ProjectComponent.php';
require_once 'phing/tasks/system/condition/Condition.php';

/**
 * Condition that tests whether a given reference exists.
 *
 * @author Matthias Pigulla <mp@webfactory.de> (Phing)
 * @version $Id: 7321246363e6d75f8a2d47d9dfcd82aaf0038708 $
 * @package phing.tasks.system.condition
 */
class ReferenceExistsCondition extends ProjectComponent implements Condition
{

    private $refid;

    /**
     * @param $id
     */
    public function setRef($id)
    {
        $this->refid = (string) $id;
    }

    /**
     * Check whether the reference exists.
     * @throws BuildException
     */
    public function evaluate()
    {
        if ($this->refid === null) {
            throw new BuildException("No ref attribute specified for reference-exists "
                . "condition");
        }
        $refs = $this->project->getReferences();

        return !($refs[$this->refid] instanceof UnknownElement) && isset($refs[$this->refid]);
    }

}
