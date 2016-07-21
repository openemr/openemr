<?php
/**
 * $Id: 8a0e0d7afe9ac9416d6ec9ea82563f6f738cb709 $
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

abstract class AbstractPropertySetterTask extends Task
{
    private $property;
    private $override = false;

    public function setOverride($override)
    {
        $this->override = $override;
    }

    public function setProperty($property)
    {
        $this->property = $property;
    }

    protected function validate()
    {
        if ($this->property == null) {
            throw new BuildException("You must specify a property to set.");
        }
    }

    protected function setPropertyValue($value) {
        if ($value !== null) {
            if ($this->override) {
                if ($this->getProject()->getUserProperty($this->property) == null) {
                    $this->getProject()->setProperty($this->property, $value);
                } else {
                    $this->getProject()->setUserProperty($this->property, $value);
                }
            } else {
                $p = $this->project->createTask("property");
                $p->setName($this->property);
                $p->setValue($value);
                $p->main();
            }
        }
    }
}
