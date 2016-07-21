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

include_once 'phing/mappers/ContainerMapper.php';

/**
 * A <code>ContainerMapper</code> that returns the results of its
 * first constituent <code>FileNameMapper</code>s that matches.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.mappers
 */
class FirstMatchMapper extends ContainerMapper
{
    /** {@inheritDoc}. */
    public function main($sourceFileName)
    {
        foreach ($this->getMappers() as $mapper) {
            if ($mapper !== null) {
                $mapped = $mapper->getImplementation()->main($sourceFileName);
                if ($mapped !== null) {
                    return $mapped;
                }
            }
        }
        return null;
    }
}
