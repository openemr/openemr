<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Mail\Protocol;

/**
 * https://bugs.php.net/bug.php?id=69195
 */
trait ProtocolTrait
{
    public function getCryptoMethod()
    {
        // Allow the best TLS version(s) we can
        $cryptoMethod = STREAM_CRYPTO_METHOD_TLS_CLIENT;

        // PHP 5.6.7 dropped inclusion of TLS 1.1 and 1.2 in STREAM_CRYPTO_METHOD_TLS_CLIENT
        // so add them back in manually if we can
        if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
            $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
            $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
        }

        return $cryptoMethod;
    }
}
