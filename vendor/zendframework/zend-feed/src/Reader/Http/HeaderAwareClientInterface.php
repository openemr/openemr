<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Feed\Reader\Http;

interface HeaderAwareClientInterface extends ClientInterface
{
    /**
     * Allow specifying headers to use when fetching a feed.
     *
     * Headers MUST be in the format:
     *
     * <code>
     * [
     *     'header-name' => [
     *         'header',
     *         'values'
     *     ]
     * ]
     * </code>
     *
     * @param string $uri
     * @param array $headers
     * @return HeaderAwareResponseInterface
     */
    public function get($uri, array $headers = []);
}
