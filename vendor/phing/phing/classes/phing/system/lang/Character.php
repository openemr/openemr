<?php
/*
 *  $Id: cf13388f45a3ec1fb257a1cd14b68a6e97b099ad $
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

/**
 * @package phing.system.lang
 */
class Character
{

    // this class might be extended with plenty of ordinal char constants
    // and the like to support the multibyte aware datatype (char) in php
    // in form of an object.
    // anyway just a thought

    /**
     * @param $char
     * @return bool
     */
    public static function isLetter($char)
    {

        if (strlen($char) !== 1) {
            $char = 0;
        }

        $char = (int) ord($char);

        if ($char >= ord('A') && $char <= ord('Z')) {
            return true;
        }

        if ($char >= ord('a') && $char <= ord('z')) {
            return true;
        }

        return false;
    }

}
