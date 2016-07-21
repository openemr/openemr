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

include_once 'phing/listener/DefaultLogger.php';

/**
 * Like a normal logger, except with timed outputs.
 *
 * @author    Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package   phing.listener
 */
class TimestampedLogger extends DefaultLogger
{
    /**
     * what appears between the old message and the new
     */
    public static $SPACER = ' - at ';

    /**
     * This is an override point: the message that indicates whether a build failed.
     * Subclasses can change/enhance the message.
     *
     * @return string The classic "BUILD FAILED" plus a timestamp
     */
    protected function getBuildFailedMessage()
    {
        return parent::getBuildFailedMessage() . self::$SPACER . date('n/d/Y h:m a');
    }

    /**
     * This is an override point: the message that indicates that a build succeeded.
     * Subclasses can change/enhance the message.
     *
     * @return string The classic "BUILD SUCCESSFUL" plus a timestamp
     */
    protected function getBuildSuccessfulMessage()
    {
        return parent::getBuildSuccessfulMessage() . self::$SPACER . date('n/d/Y h:m a');
    }
}
