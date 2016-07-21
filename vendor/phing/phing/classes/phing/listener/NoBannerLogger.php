<?php
/*
 * $Id: fde85729a4da71d69341a94fb05cbaa1f7c40d5b $
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

require_once 'phing/listener/DefaultLogger.php';

/**
 * Extends DefaultLogger to strip out empty targets.
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @copyright 2001,2002 THYRELL. All rights reserved
 * @version   $Id: fde85729a4da71d69341a94fb05cbaa1f7c40d5b $
 * @package   phing.listener
 */
class NoBannerLogger extends DefaultLogger
{

    private $targetName = null;

    /**
     * @param BuildEvent $event
     */
    public function targetStarted(BuildEvent $event)
    {
        $target = $event->getTarget();
        $this->targetName = $target->getName();
    }

    /**
     * @param BuildEvent $event
     */
    public function targetFinished(BuildEvent $event)
    {
        $this->targetName = null;
    }

    /**
     * @param BuildEvent $event
     */
    public function messageLogged(BuildEvent $event)
    {

        if ($event->getPriority() > $this->msgOutputLevel || null === $event->getMessage() || trim(
                $event->getMessage() === ""
            )
        ) {
            return;
        }

        if ($this->targetName !== null) {
            $msg = PHP_EOL . $event->getProject()->getName() . ' > ' . $this->targetName . ':' . PHP_EOL;
            $this->printMessage($msg, $this->out, $event->getPriority());
            $this->targetName = null;
        }

        parent::messageLogged($event);
    }
}
