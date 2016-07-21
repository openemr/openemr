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

require_once 'phing/tasks/system/condition/ConditionBase.php';
require_once 'phing/tasks/system/condition/Condition.php';

/**
 * Checks the value of a specified property.
 *
 * Returns true if the property evaluates to true.
 *
 * @author    Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package   phing.tasks.system.condition
 */
class IsPropertyTrueCondition extends ConditionBase implements Condition
{
    /** @var string|null $name */
    private $name = null;

    /**
     * @param $name
     *
     * @return void
     */
    public function  setProperty($name)
    {
        $this->name = $name;
    }

    /**
     * @return bool
     *
     * @throws BuildException
     */
    public function evaluate()
    {
        if ($this->name === null) {
            throw new BuildException("Property name must be set.");
        }

        return (bool) $this->getProject()->getProperty($this->name);
    }
}
