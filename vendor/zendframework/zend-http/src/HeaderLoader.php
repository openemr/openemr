<?php
/**
 * @see       https://github.com/zendframework/zend-http for the canonical source repository
 * @copyright Copyright (c) 2005-2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-http/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Http;

use Zend\Loader\PluginClassLoader;

/**
 * Plugin Class Loader implementation for HTTP headers
 */
class HeaderLoader extends PluginClassLoader
{
    /**
     * @var array Pre-aliased Header plugins
     */
    protected $plugins = [
        'accept'                  => Header\Accept::class,
        'acceptcharset'           => Header\AcceptCharset::class,
        'acceptencoding'          => Header\AcceptEncoding::class,
        'acceptlanguage'          => Header\AcceptLanguage::class,
        'acceptranges'            => Header\AcceptRanges::class,
        'age'                     => Header\Age::class,
        'allow'                   => Header\Allow::class,
        'authenticationinfo'      => Header\AuthenticationInfo::class,
        'authorization'           => Header\Authorization::class,
        'cachecontrol'            => Header\CacheControl::class,
        'connection'              => Header\Connection::class,
        'contentdisposition'      => Header\ContentDisposition::class,
        'contentencoding'         => Header\ContentEncoding::class,
        'contentlanguage'         => Header\ContentLanguage::class,
        'contentlength'           => Header\ContentLength::class,
        'contentlocation'         => Header\ContentLocation::class,
        'contentmd5'              => Header\ContentMD5::class,
        'contentrange'            => Header\ContentRange::class,
        'contenttransferencoding' => Header\ContentTransferEncoding::class,
        'contenttype'             => Header\ContentType::class,
        'cookie'                  => Header\Cookie::class,
        'date'                    => Header\Date::class,
        'etag'                    => Header\Etag::class,
        'expect'                  => Header\Expect::class,
        'expires'                 => Header\Expires::class,
        'from'                    => Header\From::class,
        'host'                    => Header\Host::class,
        'ifmatch'                 => Header\IfMatch::class,
        'ifmodifiedsince'         => Header\IfModifiedSince::class,
        'ifnonematch'             => Header\IfNoneMatch::class,
        'ifrange'                 => Header\IfRange::class,
        'ifunmodifiedsince'       => Header\IfUnmodifiedSince::class,
        'keepalive'               => Header\KeepAlive::class,
        'lastmodified'            => Header\LastModified::class,
        'location'                => Header\Location::class,
        'maxforwards'             => Header\MaxForwards::class,
        'origin'                  => Header\Origin::class,
        'pragma'                  => Header\Pragma::class,
        'proxyauthenticate'       => Header\ProxyAuthenticate::class,
        'proxyauthorization'      => Header\ProxyAuthorization::class,
        'range'                   => Header\Range::class,
        'referer'                 => Header\Referer::class,
        'refresh'                 => Header\Refresh::class,
        'retryafter'              => Header\RetryAfter::class,
        'server'                  => Header\Server::class,
        'setcookie'               => Header\SetCookie::class,
        'te'                      => Header\TE::class,
        'trailer'                 => Header\Trailer::class,
        'transferencoding'        => Header\TransferEncoding::class,
        'upgrade'                 => Header\Upgrade::class,
        'useragent'               => Header\UserAgent::class,
        'vary'                    => Header\Vary::class,
        'via'                     => Header\Via::class,
        'warning'                 => Header\Warning::class,
        'wwwauthenticate'         => Header\WWWAuthenticate::class,
    ];
}
