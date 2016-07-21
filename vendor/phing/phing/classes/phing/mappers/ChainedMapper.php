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
 * A <code>ContainerMapper</code> that chains the results of the first
 * nested <code>FileNameMapper</code>s into sourcefiles for the second,
 * the second to the third, and so on, returning the resulting mapped
 * filenames from the last nested <code>FileNameMapper</code>.
 */
class ChainedMapper extends ContainerMapper
{
    /** {@inheritDoc}. */
    public function main($sourceFileName)
    {
        $results[] = $sourceFileName;
        $mapper = null;

        foreach ($this->getMappers() as $mapper) {
            if ($mapper !== null) {
                $inputs = $results;
                $results = array();

                foreach ($inputs as $input) {
                    $mapped = $mapper->getImplementation()->main($input);
                    if ($mapped != null) {
                        $results = $mapped;
                    }
                }
            }
        }
        return !empty($results) ? $results : null;
    }
}
