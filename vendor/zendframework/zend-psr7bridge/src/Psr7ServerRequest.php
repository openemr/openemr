<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @see       http://github.com/zendframework/zend-diactoros for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-diactoros/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Psr7Bridge;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Http\Request as ZendRequest;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Stream;
use Zend\Diactoros\UploadedFile;

final class Psr7ServerRequest
{
    /**
     * Convert a PSR-7 ServerRequest to a Zend\Http server-side request.
     *
     * @param ServerRequestInterface $psr7Request
     * @param bool $shallow Whether or not to convert without body/file
     *     parameters; defaults to false, meaning a fully populated request
     *     is returned.
     * @return Zend\Request
     */
    public static function toZend(ServerRequestInterface $psr7Request, $shallow = false)
    {
        if ($shallow) {
            return new Zend\Request(
                $psr7Request->getMethod(),
                $psr7Request->getUri(),
                $psr7Request->getHeaders(),
                $psr7Request->getCookieParams(),
                $psr7Request->getQueryParams(),
                [],
                [],
                $psr7Request->getServerParams()
            );
        }

        $zendRequest = new Zend\Request(
            $psr7Request->getMethod(),
            $psr7Request->getUri(),
            $psr7Request->getHeaders(),
            $psr7Request->getCookieParams(),
            $psr7Request->getQueryParams(),
            $psr7Request->getParsedBody() ?: [],
            self::convertUploadedFiles($psr7Request->getUploadedFiles()),
            $psr7Request->getServerParams()
        );
        $zendRequest->setContent($psr7Request->getBody());

        return $zendRequest;
    }

    /**
     * Convert a Zend\Http\Response in a PSR-7 response, using zend-diactoros
     *
     * @param  ZendRequest $zendRequest
     * @return ServerRequest
     */
    public static function fromZend(ZendRequest $zendRequest)
    {
        $body = new Stream('php://memory', 'wb+');
        $body->write($zendRequest->getContent());

        $headers = empty($zendRequest->getHeaders()) ? [] : $zendRequest->getHeaders()->toArray();
        $query   = empty($zendRequest->getQuery()) ? [] : $zendRequest->getQuery()->toArray();
        $post    = empty($zendRequest->getPost()) ? [] : $zendRequest->getPost()->toArray();
        $files   = empty($zendRequest->getFiles()) ? [] : $zendRequest->getFiles()->toArray();

        $request = new ServerRequest(
            [],
            self::convertFilesToUploaded($files),
            $zendRequest->getUriString(),
            $zendRequest->getMethod(),
            $body,
            $headers
        );
        $request = $request->withQueryParams($query);

        $cookie = $zendRequest->getCookie();
        if (false !== $cookie) {
            $request = $request->withCookieParams($cookie->getArrayCopy());
        }

        return $request->withParsedBody($post);
    }

    /**
     * Convert a PSR-7 uploaded files structure to a $_FILES structure
     *
     * @param \Psr\Http\Message\UploadedFileInterface[]
     * @return array
     */
    private static function convertUploadedFiles(array $uploadedFiles)
    {
        $files = [];
        foreach ($uploadedFiles as $name => $upload) {
            if (is_array($upload)) {
                $files[$name] = self::convertUploadedFiles($upload);
                continue;
            }

            $files[$name] = [
                'name'     => $upload->getClientFilename(),
                'type'     => $upload->getClientMediaType(),
                'size'     => $upload->getSize(),
                'tmp_name' => $upload->getStream()->getMetadata('uri'),
                'error'    => $upload->getError(),
            ];
        }
        return $files;
    }

    /**
     * Convert a Zend\Http file structure to PSR-7 uploaded files
     *
     * @param array
     * @return UploadedFile[]
     */
    private static function convertFilesToUploaded(array $files)
    {
        $uploadedFiles = [];
        foreach ($files as $name => $value) {
            if (is_array($value)) {
                $uploadedFiles[$name] = self::convertFilesToUploaded($value);
                continue;
            }
            return new UploadedFile(
                $files['tmp_name'],
                $files['size'],
                $files['error'],
                $files['name'],
                $files['type']
            );
        }
        return $uploadedFiles;
    }

    /**
     * Do not allow instantiation.
     */
    private function __construct()
    {
    }
}
