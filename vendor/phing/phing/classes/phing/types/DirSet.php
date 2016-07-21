<?php

/*
 * $Id: 61cd2b26a7d3430fb6b1a2dbedd959e832440686 $
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

include_once 'phing/types/AbstractFileSet.php';

/**
 * Subclass as hint for supporting tasks that the included directories
 * instead of files should be used.
 *
 * @package phing.types
 */
class DirSet extends AbstractFileSet
{

    /**
     * @param null $dirset
     */
    public function __construct($dirset = null)
    {
        parent::__construct($dirset);
    }

    /**
     * Return a DirSet that has the same basedir and same patternsets
     * as this one.
     */
    public function __clone()
    {
        if ($this->isReference()) {
            return new DirSet($this->getRef($this->getProject()));
        } else {
            return new DirSet($this);
        }
    }

}
