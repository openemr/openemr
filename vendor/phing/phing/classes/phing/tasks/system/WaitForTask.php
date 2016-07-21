<?php
/*
 * $Id: a2207ec35496db9f3ad2a15a780bb2ec26cf04f0 $
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

/**
 *  Based on Apache Ant Wait For:
 *
 *  Licensed to the Apache Software Foundation (ASF) under one or more
 *  contributor license agreements.  See the NOTICE file distributed with
 *  this work for additional information regarding copyright ownership.
 *  The ASF licenses this file to You under the Apache License, Version 2.0
 *  (the "License"); you may not use this file except in compliance with
 *  the License.  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 * @author    Michiel Rook <mrook@php.net>
 * @version   $Id: a2207ec35496db9f3ad2a15a780bb2ec26cf04f0 $
 * @package   phing.tasks.system
 */
class WaitForTask extends ConditionBase
{
    const ONE_MILLISECOND = 1;
    const ONE_SECOND = 1000;
    const ONE_MINUTE = 60000;
    const ONE_HOUR = 3600000;
    const ONE_DAY = 86400000;
    const ONE_WEEK = 604800000;

    const DEFAULT_MAX_WAIT_MILLIS = 180000;
    const DEFAULT_CHECK_MILLIS = 500;

    protected $maxWait = self::DEFAULT_MAX_WAIT_MILLIS;
    protected $maxWaitMultiplier = self::ONE_MILLISECOND;

    protected $checkEvery = self::DEFAULT_CHECK_MILLIS;
    protected $checkEveryMultiplier = self::ONE_MILLISECOND;

    protected $timeoutProperty = null;

    /**
     * Set the maximum length of time to wait.
     * @param int $maxWait
     */
    public function setMaxWait($maxWait)
    {
        $this->maxWait = (int) $maxWait;
    }

    /**
     * Set the max wait time unit
     * @param string $maxWaitUnit
     */
    public function setMaxWaitUnit($maxWaitUnit)
    {
        $this->maxWaitMultiplier = $this->_convertUnit($maxWaitUnit);
    }

    /**
     * Set the time between each check
     * @param int $checkEvery
     */
    public function setCheckEvery($checkEvery)
    {
        $this->checkEvery = (int) $checkEvery;
    }

    /**
     * Set the check every time unit
     * @param string $checkEveryUnit
     * @return void
     */
    public function setCheckEveryUnit($checkEveryUnit)
    {
        $this->checkEveryMultiplier = $this->_convertUnit($checkEveryUnit);
    }

    /**
     * Name of the property to set after a timeout.
     * @param string $timeoutProperty
     * @return void
     */
    public function setTimeoutProperty($timeoutProperty)
    {
        $this->timeoutProperty = $timeoutProperty;
    }

    /**
     * Convert the unit to a multipler.
     * @param string $unit
     * @throws BuildException
     * @return int
     */
    protected function _convertUnit($unit)
    {
        switch ($unit) {
            case "week":
            {
                return self::ONE_WEEK;
            }

            case "day":
            {
                return self::ONE_DAY;
            }

            case "hour":
            {
                return self::ONE_HOUR;
            }

            case "minute":
            {
                return self::ONE_MINUTE;
            }

            case "second":
            {
                return self::ONE_SECOND;
            }

            case "millisecond":
            {
                return self::ONE_MILLISECOND;
            }

            default:
                {
                throw new BuildException("Illegal unit '$unit'");
                }
        }
    }

    /**
     * Check repeatedly for the specified conditions until they become
     * true or the timeout expires.
     * @throws BuildException
     */
    public function main()
    {
        if ($this->countConditions() > 1) {
            throw new BuildException("You must not nest more than one condition into <waitfor>");
        }

        if ($this->countConditions() < 1) {
            throw new BuildException("You must nest a condition into <waitfor>");
        }

        $cs = $this->getIterator();
        $condition = $cs->current();

        $maxWaitMillis = $this->maxWait * $this->maxWaitMultiplier;
        $checkEveryMillis = $this->checkEvery * $this->checkEveryMultiplier;

        $start = microtime(true) * 1000;
        $end = $start + $maxWaitMillis;

        while (microtime(true) * 1000 < $end) {
            if ($condition->evaluate()) {
                $this->log("waitfor: condition was met", Project::MSG_VERBOSE);

                return;
            }

            usleep($checkEveryMillis * 1000);
        }

        $this->log("waitfor: timeout", Project::MSG_VERBOSE);

        if ($this->timeoutProperty != null) {
            $this->project->setNewProperty($this->timeoutProperty, "true");
        }
    }
}
