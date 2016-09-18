<?php
/*
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
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\CouchDB;

/**
 * Base exception class for package Doctrine\ODM\CouchDB
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.com
 * @since       1.0
 * @author      Benjamin Eberlei <kontakt@beberlei.de>
 */
class CouchDBException extends \Exception
{

    public static function unknownDocumentNamespace($documentNamespaceAlias)
    {
        return new self("Unknown Document namespace alias '$documentNamespaceAlias'.");
    }

    public static function unregisteredDesignDocument($designDocumentName)
    {
        return new self("No design document with name '" . $designDocumentName . "' was registered with the DocumentManager.");
    }

    public static function invalidAttachment($className, $id, $filename)
    {
        return new self("Trying to save invalid attachment with filename " . $filename . " in document " . $className . " with id " . $id);
    }

    public static function detachedDocumentFound($className, $id, $assocName)
    {
        return new self("Found a detached or new document at property " .
            $className . "::" . $assocName. " of document with ID " . $id . ", ".
            "but the assocation is not marked as cascade persist.");
    }

    public static function persistRemovedDocument()
    {
        return new self("Trying to persist document that is scheduled for removal.");
    }

    public static function luceneNotConfigured()
    {
        return  new self("CouchDB Lucene is not configured. You have to configure the handler name to enable support for Lucene Queries.");
    }
}

