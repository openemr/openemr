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

require_once 'phing/tasks/system/condition/Condition.php';

/**
 * Condition returns true if selected partition has the requested space, false otherwise.
 *
 * @author Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system.condition
 */
class HasFreeSpaceCondition implements Condition
{
    /** @var string $partition */
    private $partition;

    /** @var string $needed */
    private $needed;

    /**
     * {@inheritdoc}
     *
     * @throws BuildException
     *
     * @return boolean
     */
    public function evaluate()
    {
        $this->validate();

        $free = disk_free_space($this->partition);
        return $free >= $this->parseHumanSizes($this->needed);
    }

    /**
     * @return void
     *
     * @throws BuildException
     */
    private function validate()
    {
        if (null == $this->partition) {
            throw new BuildException("Please set the partition attribute.");
        }
        if (null == $this->needed) {
            throw new BuildException("Please set the needed attribute.");
        }
    }

    /**
     * Set the partition/device to check.
     *
     * @param $partition
     *
     * @return void
     */
    public function setPartition($partition)
    {
        $this->partition = $partition;
    }

    /**
     * Set the amount of free space required.
     *
     * @return void
     */
    public function setNeeded($needed)
    {
        $this->needed = $needed;
    }

    /**
     * @param string $humanSize
     *
     * @return float
     */
    private function parseHumanSizes($humanSize)
    {
        if (ctype_alpha($char = $humanSize[strlen($humanSize - 1)])) {
            $value = (float) substr($humanSize, 0, strlen($humanSize - 1));
            switch ($char) {
                case 'K':
                    return $value * 1024;
                case 'M':
                    return $value * 1024 * 1024;
                case 'G':
                    return $value * 1024 * 1024 * 1024;
                case 'T':
                    return $value * 1024 * 1024 * 1024 * 1024;
                case 'P':
                    return $value * 1024 * 1024 * 1024 * 1024 * 1024;
                default:
                    return $value;
            }
        } else {
            return (float) $humanSize;
        }
    }
}
