<?php
/*
 * $Id: 130043b9c5265eac0037f234cad0c2cbe5a73c11 $
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
include_once 'phing/system/util/Properties.php';

/**
 * Uses PEAR Mail package to send the build log to one or
 * more recipients.
 *
 * @author     Michiel Rook <mrook@php.net>
 * @package    phing.listener
 * @version    $Id: 130043b9c5265eac0037f234cad0c2cbe5a73c11 $
 */
class MailLogger extends DefaultLogger
{
    private $_mailMessage = "";

    private $_from = "phing@phing.info";
    private $_subject = "Phing build result";
    private $_tolist = null;

    /**
     * Construct new MailLogger
     */
    public function __construct()
    {
        parent::__construct();

        @require_once 'Mail.php';

        if (!class_exists('Mail')) {
            throw new BuildException('Need the PEAR Mail package to send logs');
        }

        $from = Phing::getDefinedProperty('phing.log.mail.from');
        $subject = Phing::getDefinedProperty('phing.log.mail.subject');
        $tolist = Phing::getDefinedProperty('phing.log.mail.recipients');

        if (!empty($from)) {
            $this->_from = $from;
        }

        if (!empty($subject)) {
            $this->_subject = $subject;
        }

        if (!empty($tolist)) {
            $this->_tolist = $tolist;
        }
    }

    /**
     * @see DefaultLogger#printMessage
     * @param string       $message
     * @param OutputStream $stream
     * @param int          $priority
     */
    final protected function printMessage($message, OutputStream $stream, $priority)
    {
        if ($message !== null) {
            $this->_mailMessage .= $message . "\n";
        }
    }

    /**
     * Sends the mail
     *
     * @see DefaultLogger#buildFinished
     * @param BuildEvent $event
     */
    public function buildFinished(BuildEvent $event)
    {
        parent::buildFinished($event);

        if (empty($this->_tolist)) {
            return;
        }

        $hdrs = array(
            'From' => $this->_from,
            'Subject' => $this->_subject . (empty($event) ? " (build successful)" : " (build failed)")
        );

        $mail = Mail::factory('mail');
        $mail->send($this->_tolist, $hdrs, $this->_mailMessage);
    }
}
