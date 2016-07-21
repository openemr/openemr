<?php
/*
 * $Id: a2ac87525ca9022012d9961946b556eb0d3ba3f3 $
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

require_once 'phing/tasks/ext/phar/PharMetadataElement.php';

/**
 * @package phing.tasks.ext.phar
 * @author Alexey Shockov <alexey@shockov.com>
 * @since 2.4.0
 */
class PharMetadata
{
    /**
     * @var array
     */
    protected $elements = array();

    /**
     * @return PharMetadataElement
     */
    public function createElement()
    {
        return ($this->elements[] = new PharMetadataElement());
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $metadata = array();

        foreach ($this->elements as $element) {
            $metadata[$element->getName()] = $element->toArray();
        }

        return $metadata;
    }
}
