<?php
/*
 *  $Id: a5a4cdae56af93ef6e8f999d3d29868ec5e049fe $
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
 * Interface for filename mapper classes.
 *
 * @author Andreas Aderhold, andi@binarycloud.com
 * @author Hans Lellelid <hans@xmpl.org>
 * @version $Id: a5a4cdae56af93ef6e8f999d3d29868ec5e049fe $
 * @package phing.mappers
 */
interface FileNameMapper
{

    /**
     * The mapper implementation.
     *
     * @param  mixed $sourceFileName The data the mapper works on.
     * @return array The data after the mapper has been applied; must be in array format (for some reason).
     */
    public function main($sourceFileName);

    /**
     * Accessor. Sets the to property. The actual implementation
     * depends on the child class.
     *
     * @param  string $to To what this mapper should convert the from string
     * @return void
     */
    public function setTo($to);

    /**
     * Accessor. Sets the from property. What this mapper should
     * recognize. The actual implementation is dependent upon the
     * child class
     *
     * @param  string $from On what this mapper should work
     * @return void
     */
    public function setFrom($from);

}
