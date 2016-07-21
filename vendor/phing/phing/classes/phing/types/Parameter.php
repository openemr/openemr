<?php
/*
 *  $Id: 4206aed7478704fa7ef5d066ba183159ae0ea7f9 $
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

include_once 'phing/types/DataType.php';

/**
 * A parameter is composed of a name, type and value. Nested
 * Parameters are also possible, but the using task/type has
 * to support them
 *
 * @author    Manuel Holtgrewe
 * @author    <a href="mailto:yl@seasonfive.com">Yannick Lecaillez</a>
 * @package   phing.types
 */
class Parameter extends DataType
{

    /** Parameter name */
    protected $name;

    /** Parameter type */
    protected $type;

    /** Parameter value */
    protected $value;

    /** Nested parameters */
    protected $parameters = array();

    /**
     * @param $name
     */
    public function setName($name)
    {
        $this->name = (string) $name;
    }

    /**
     * @param $type
     */
    public function setType($type)
    {
        $this->type = (string) $type;
    }

    /**
     * Sets value to dynamic register slot.
     * @param RegisterSlot $value
     */
    public function setListeningValue(RegisterSlot $value)
    {
        $this->value = $value;
    }

    /**
     * @param $value
     */
    public function setValue($value)
    {
        $this->value = (string) $value;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        if ($this->value instanceof RegisterSlot) {
            return $this->value->getValue();
        } else {
            return $this->value;
        }
    }

    /**
     * @return Parameter
     */
    public function createParam()
    {
        $num = array_push($this->parameters, new Parameter());

        return $this->parameters[$num - 1];
    }

    /**
     * @return array Nested parameters.
     */
    public function getParams()
    {
        return $this->parameters;
    }
}
