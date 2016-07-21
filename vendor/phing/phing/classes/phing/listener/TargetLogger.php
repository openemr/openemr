<?php
/*
 * $Id: e5ae611d970cd766f7f23fe5a70c6483e66aceb5 $
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

require_once 'phing/listener/AnsiColorLogger.php';

/**
 * Extends AnsiColorLogger to display times for each target
 *
 * @author    Patrick McAndrew <patrick@urg.name>
 * @copyright 2013. All rights reserved
 * @version   $Id: e5ae611d970cd766f7f23fe5a70c6483e66aceb5 $
 * @package   phing.listener
 */
class TargetLogger extends AnsiColorLogger
{

    private $targetName = null;
    private $targetStartTime;

    /**
     * @param BuildEvent $event
     */
    public function targetStarted(BuildEvent $event)
    {
        parent::targetStarted($event);
        $target = $event->getTarget();
        $this->targetName = $target->getName();
        $this->targetStartTime = Phing::currentTimeMillis();
    }

    /**
     * @param BuildEvent $event
     */
    public function targetFinished(BuildEvent $event)
    {
        $msg = PHP_EOL . "Target time: " . self::formatTime(
                Phing::currentTimeMillis() - $this->targetStartTime
            ) . PHP_EOL;
        $event->setMessage($msg, Project::MSG_INFO);
        $this->messageLogged($event);
        $this->targetName = null;

    }
}
