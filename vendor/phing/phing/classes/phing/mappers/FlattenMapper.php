<?php
/**
 *  $Id: 8181ebf4f40374d9a706a4961a0e17f902fea000 $
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

require_once 'phing/mappers/FileNameMapper.php';

/**
 * Removes any directory information from the passed path.
 *
 * @author   Andreas Aderhold <andi@binarycloud.com>
 * @version  $Id: 8181ebf4f40374d9a706a4961a0e17f902fea000 $
 * @package  phing.mappers
 */
class FlattenMapper implements FileNameMapper
{
    /**
     * The mapper implementation. Returns string with source filename
     * but without leading directory information
     *
     * @param  string $sourceFileName The data the mapper works on
     * @return array  The data after the mapper has been applied
     */
    public function main($sourceFileName)
    {
        $f = new PhingFile($sourceFileName);

        return array($f->getName());
    }

    /**
     * Ignored here.
     * {@inheritdoc}
     * @param string $to
     * @return void
     */
    public function setTo($to)
    {
    }

    /**
     * Ignored here.
     * {@inheritdoc}
     * @param string $from
     * @return void
     */
    public function setFrom($from)
    {
    }
}
