<?php

/**
 * @see       https://github.com/laminas/laminas-http for the canonical source repository
 * @copyright https://github.com/laminas/laminas-http/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-http/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Http\Header;

use DateTime;
use Laminas\Uri\UriFactory;

use function array_key_exists;
use function gettype;
use function is_scalar;
use function strtolower;

/**
 * @throws Exception\InvalidArgumentException
 * @see http://www.ietf.org/rfc/rfc2109.txt
 * @see http://www.w3.org/Protocols/rfc2109/rfc2109
 */
class SetCookie implements MultipleHeaderInterface
{
    /**
     * Cookie will not be sent for any cross-domain requests whatsoever.
     * Even if the user simply navigates to the target site with a regular link, the cookie will not be sent.
     */
    const SAME_SITE_STRICT = 'Strict';

    /**
     * Cookie will not be passed for any cross-domain requests unless it's a regular link that navigates user
     * to the target site.
     * Other requests methods (such as POST and PUT) and XHR requests will not contain this cookie.
     */
    const SAME_SITE_LAX = 'Lax';

    /**
     * Cookie will be sent with same-site and cross-site requests.
     */
    const SAME_SITE_NONE = 'None';

    /**
     * @internal
     */
    const SAME_SITE_ALLOWED_VALUES = [
        'strict' => self::SAME_SITE_STRICT,
        'lax' => self::SAME_SITE_LAX,
        'none' => self::SAME_SITE_NONE,
    ];

    /**
     * Cookie name
     *
     * @var string|null
     */
    protected $name;

    /**
     * Cookie value
     *
     * @var string|null
     */
    protected $value;

    /**
     * Version
     *
     * @var int|null
     */
    protected $version;

    /**
     * Max Age
     *
     * @var int|null
     */
    protected $maxAge;

    /**
     * Cookie expiry date
     *
     * @var int|null
     */
    protected $expires;

    /**
     * Cookie domain
     *
     * @var string|null
     */
    protected $domain;

    /**
     * Cookie path
     *
     * @var string|null
     */
    protected $path;

    /**
     * Whether the cookie is secure or not
     *
     * @var bool|null
     */
    protected $secure;

    /**
     * If the value need to be quoted or not
     *
     * @var bool
     */
    protected $quoteFieldValue = false;

    /**
     * @var bool|null
     */
    protected $httponly;

    /**
     * @var string|null
     */
    protected $sameSite;

    /**
     * @var bool
     */
    protected $encodeValue = true;

    /**
     * @static
     * @throws Exception\InvalidArgumentException
     * @param  $headerLine
     * @param  bool $bypassHeaderFieldName
     * @return array|SetCookie
     */
    public static function fromString($headerLine, $bypassHeaderFieldName = false)
    {
        static $setCookieProcessor = null;

        if ($setCookieProcessor === null) {
            $setCookieClass = get_called_class();
            $setCookieProcessor = function ($headerLine) use ($setCookieClass) {
                /** @var SetCookie $header */
                $header = new $setCookieClass();
                $keyValuePairs = preg_split('#;\s*#', $headerLine);

                foreach ($keyValuePairs as $keyValue) {
                    if (preg_match('#^(?P<headerKey>[^=]+)=\s*("?)(?P<headerValue>[^"]*)\2#', $keyValue, $matches)) {
                        $headerKey  = $matches['headerKey'];
                        $headerValue = $matches['headerValue'];
                    } else {
                        $headerKey = $keyValue;
                        $headerValue = null;
                    }

                    // First K=V pair is always the cookie name and value
                    if ($header->getName() === null) {
                        $header->setName($headerKey);
                        $header->setValue(urldecode($headerValue));

                        // set no encode value if raw and encoded values are the same
                        if (urldecode($headerValue) === $headerValue) {
                            $header->setEncodeValue(false);
                        }
                        continue;
                    }

                    // Process the remaining elements
                    switch (str_replace(['-', '_'], '', strtolower($headerKey))) {
                        case 'expires':
                            $header->setExpires($headerValue);
                            break;
                        case 'domain':
                            $header->setDomain($headerValue);
                            break;
                        case 'path':
                            $header->setPath($headerValue);
                            break;
                        case 'secure':
                            $header->setSecure(true);
                            break;
                        case 'httponly':
                            $header->setHttponly(true);
                            break;
                        case 'version':
                            $header->setVersion((int) $headerValue);
                            break;
                        case 'maxage':
                            $header->setMaxAge($headerValue);
                            break;
                        case 'samesite':
                            $header->setSameSite($headerValue);
                            break;
                        default:
                            // Intentionally omitted
                    }
                }

                return $header;
            };
        }

        list($name, $value) = GenericHeader::splitHeaderLine($headerLine);
        HeaderValue::assertValid($value);

        // some sites return set-cookie::value, this is to get rid of the second :
        $name = strtolower($name) == 'set-cookie:' ? 'set-cookie' : $name;

        // check to ensure proper header type for this factory
        if (strtolower($name) !== 'set-cookie') {
            throw new Exception\InvalidArgumentException('Invalid header line for Set-Cookie string: "' . $name . '"');
        }

        $multipleHeaders = preg_split('#(?<!Sun|Mon|Tue|Wed|Thu|Fri|Sat),\s*#', $value);

        if (count($multipleHeaders) <= 1) {
            return $setCookieProcessor(array_pop($multipleHeaders));
        } else {
            $headers = [];
            foreach ($multipleHeaders as $headerLine) {
                $headers[] = $setCookieProcessor($headerLine);
            }
            return $headers;
        }
    }

    /**
     * Cookie object constructor
     *
     * @todo Add validation of each one of the parameters (legal domain, etc.)
     *
     * @param string|null              $name
     * @param string|null              $value
     * @param int|string|DateTime|null $expires
     * @param string|null              $path
     * @param string|null              $domain
     * @param bool                     $secure
     * @param bool                     $httponly
     * @param int|null                 $maxAge
     * @param int|null                 $version
     * @param string|null              $sameSite
     */
    public function __construct(
        $name = null,
        $value = null,
        $expires = null,
        $path = null,
        $domain = null,
        $secure = false,
        $httponly = false,
        $maxAge = null,
        $version = null,
        $sameSite = null
    ) {
        $this->type = 'Cookie';

        $this->setName($name)
             ->setValue($value)
             ->setVersion($version)
             ->setMaxAge($maxAge)
             ->setDomain($domain)
             ->setExpires($expires)
             ->setPath($path)
             ->setSecure($secure)
             ->setHttpOnly($httponly)
             ->setSameSite($sameSite);
    }

    /**
     * @return bool
     */
    public function getEncodeValue()
    {
        return $this->encodeValue;
    }

    /**
     * @param bool $encodeValue
     */
    public function setEncodeValue($encodeValue)
    {
        $this->encodeValue = (bool) $encodeValue;
    }

    /**
     * @return string 'Set-Cookie'
     */
    public function getFieldName()
    {
        return 'Set-Cookie';
    }

    /**
     * @throws Exception\RuntimeException
     * @return string
     */
    public function getFieldValue()
    {
        if ($this->getName() == '') {
            return '';
        }

        $value = $this->encodeValue ? urlencode($this->getValue()) : $this->getValue();
        if ($this->hasQuoteFieldValue()) {
            $value = '"' . $value . '"';
        }

        $fieldValue = $this->getName() . '=' . $value;

        $version = $this->getVersion();
        if ($version !== null) {
            $fieldValue .= '; Version=' . $version;
        }

        $maxAge = $this->getMaxAge();
        if ($maxAge !== null) {
            $fieldValue .= '; Max-Age=' . $maxAge;
        }

        $expires = $this->getExpires();
        if ($expires) {
            $fieldValue .= '; Expires=' . $expires;
        }

        $domain = $this->getDomain();
        if ($domain) {
            $fieldValue .= '; Domain=' . $domain;
        }

        $path = $this->getPath();
        if ($path) {
            $fieldValue .= '; Path=' . $path;
        }

        if ($this->isSecure()) {
            $fieldValue .= '; Secure';
        }

        if ($this->isHttponly()) {
            $fieldValue .= '; HttpOnly';
        }

        $sameSite = $this->getSameSite();
        if ($sameSite !== null && array_key_exists(strtolower($sameSite), self::SAME_SITE_ALLOWED_VALUES)) {
            $fieldValue .= '; SameSite=' . $sameSite;
        }

        return $fieldValue;
    }

    /**
     * @param  string|null $name
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function setName($name)
    {
        HeaderValue::assertValid($name);
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param  string|null $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param  int|null $version
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function setVersion($version)
    {
        if ($version !== null && ! is_int($version)) {
            throw new Exception\InvalidArgumentException('Invalid Version number specified');
        }
        $this->version = $version;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param  int $maxAge
     * @return $this
     */
    public function setMaxAge($maxAge)
    {
        if ($maxAge === null || ! is_numeric($maxAge)) {
            return $this;
        }

        $this->maxAge = max(0, (int) $maxAge);
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaxAge()
    {
        return $this->maxAge;
    }

    /**
     * @param  int|string|DateTime|null $expires
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function setExpires($expires)
    {
        if ($expires === null) {
            $this->expires = null;
            return $this;
        }

        if ($expires instanceof DateTime) {
            $expires = $expires->format(DateTime::COOKIE);
        }

        $tsExpires = $expires;

        if (is_string($expires)) {
            $tsExpires = strtotime($expires);

            // if $tsExpires is invalid and PHP is compiled as 32bit. Check if it fail reason is the 2038 bug
            if (! is_int($tsExpires) && PHP_INT_SIZE === 4) {
                $dateTime = new DateTime($expires);
                if ($dateTime->format('Y') > 2038) {
                    $tsExpires = PHP_INT_MAX;
                }
            }
        }

        if (! is_int($tsExpires) || $tsExpires < 0) {
            throw new Exception\InvalidArgumentException('Invalid expires time specified');
        }

        $this->expires = $tsExpires;

        return $this;
    }

    /**
     * @param  bool $inSeconds
     * @return int|string|null
     */
    public function getExpires($inSeconds = false)
    {
        if ($this->expires === null) {
            return null;
        }
        if ($inSeconds) {
            return $this->expires;
        }
        return gmdate('D, d-M-Y H:i:s', $this->expires) . ' GMT';
    }

    /**
     * @param  string|null $domain
     * @return $this
     */
    public function setDomain($domain)
    {
        HeaderValue::assertValid($domain);
        $this->domain = $domain;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param  string|null $path
     * @return $this
     */
    public function setPath($path)
    {
        HeaderValue::assertValid($path);
        $this->path = $path;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param  bool|null $secure
     * @return $this
     */
    public function setSecure($secure)
    {
        if (null !== $secure) {
            $secure = (bool) $secure;
        }
        $this->secure = $secure;
        return $this;
    }

    /**
     * Set whether the value for this cookie should be quoted
     *
     * @param  bool $quotedValue
     * @return $this
     */
    public function setQuoteFieldValue($quotedValue)
    {
        $this->quoteFieldValue = (bool) $quotedValue;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isSecure()
    {
        return $this->secure;
    }

    /**
     * @param  bool|null $httponly
     * @return $this
     */
    public function setHttponly($httponly)
    {
        if (null !== $httponly) {
            $httponly = (bool) $httponly;
        }
        $this->httponly = $httponly;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isHttponly()
    {
        return $this->httponly;
    }

    /**
     * Check whether the cookie has expired
     *
     * Always returns false if the cookie is a session cookie (has no expiry time)
     *
     * @param int|null $now Timestamp to consider as "now"
     * @return bool
     */
    public function isExpired($now = null)
    {
        if ($now === null) {
            $now = time();
        }

        if (is_int($this->expires) && $this->expires < $now) {
            return true;
        }

        return false;
    }

    /**
     * Check whether the cookie is a session cookie (has no expiry time set)
     *
     * @return bool
     */
    public function isSessionCookie()
    {
        return ($this->expires === null);
    }

    /**
     * @return string|null
     */
    public function getSameSite()
    {
        return $this->sameSite;
    }

    /**
     * @param  string|null $sameSite
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function setSameSite($sameSite)
    {
        if ($sameSite === null) {
            $this->sameSite = null;
            return $this;
        }
        if (! array_key_exists(strtolower($sameSite), self::SAME_SITE_ALLOWED_VALUES)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid value provided for SameSite directive: "%s"; expected one of: Strict, Lax or None',
                is_scalar($sameSite) ? $sameSite : gettype($sameSite)
            ));
        }
        $this->sameSite = self::SAME_SITE_ALLOWED_VALUES[strtolower($sameSite)];
        return $this;
    }

    /**
     * Check whether the value for this cookie should be quoted
     *
     * @return bool
     */
    public function hasQuoteFieldValue()
    {
        return $this->quoteFieldValue;
    }

    /**
     * @param  string $requestDomain
     * @param  string $path
     * @param  bool   $isSecure
     * @return bool
     */
    public function isValidForRequest($requestDomain, $path, $isSecure = false)
    {
        if ($this->getDomain() && (strrpos($requestDomain, $this->getDomain()) === false)) {
            return false;
        }

        if ($this->getPath() && (strpos($path, $this->getPath()) !== 0)) {
            return false;
        }

        if ($this->secure && $this->isSecure() !== $isSecure) {
            return false;
        }

        return true;
    }

    /**
     * Checks whether the cookie should be sent or not in a specific scenario
     *
     * @param string|\Laminas\Uri\Uri $uri URI to check against (secure, domain, path)
     * @param bool $matchSessionCookies Whether to send session cookies
     * @param int|null $now Override the current time when checking for expiry time
     * @return bool
     * @throws Exception\InvalidArgumentException If URI does not have HTTP or HTTPS scheme.
     */
    public function match($uri, $matchSessionCookies = true, $now = null)
    {
        if (is_string($uri)) {
            $uri = UriFactory::factory($uri);
        }

        // Make sure we have a valid Laminas_Uri_Http object
        if (! ($uri->isValid() && ($uri->getScheme() == 'http' || $uri->getScheme() == 'https'))) {
            throw new Exception\InvalidArgumentException('Passed URI is not a valid HTTP or HTTPS URI');
        }

        // Check that the cookie is secure (if required) and not expired
        if ($this->secure && $uri->getScheme() != 'https') {
            return false;
        }
        if ($this->isExpired($now)) {
            return false;
        }
        if ($this->isSessionCookie() && ! $matchSessionCookies) {
            return false;
        }

        // Check if the domain matches
        if (! self::matchCookieDomain($this->getDomain(), $uri->getHost())) {
            return false;
        }

        // Check that path matches using prefix match
        if (! self::matchCookiePath($this->getPath(), $uri->getPath())) {
            return false;
        }

        // If we didn't die until now, return true.
        return true;
    }

    /**
     * Check if a cookie's domain matches a host name.
     *
     * Used by Laminas\Http\Cookies for cookie matching
     *
     * @param  string $cookieDomain
     * @param  string $host
     * @return bool
     */
    public static function matchCookieDomain($cookieDomain, $host)
    {
        $cookieDomain = strtolower($cookieDomain);
        $host = strtolower($host);
        // Check for either exact match or suffix match
        return $cookieDomain == $host
            || preg_match('/' . preg_quote($cookieDomain) . '$/', $host);
    }

    /**
     * Check if a cookie's path matches a URL path
     *
     * Used by Laminas\Http\Cookies for cookie matching
     *
     * @param  string $cookiePath
     * @param  string $path
     * @return bool
     */
    public static function matchCookiePath($cookiePath, $path)
    {
        return (strpos($path, $cookiePath) === 0);
    }

    /**
     * @return string
     */
    public function toString()
    {
        return 'Set-Cookie: ' . $this->getFieldValue();
    }

    /**
     * @param  array $headers
     * @return string
     * @throws Exception\RuntimeException
     */
    public function toStringMultipleHeaders(array $headers)
    {
        $headerLine = $this->toString();
        /* @var $header SetCookie */
        foreach ($headers as $header) {
            if (! $header instanceof SetCookie) {
                throw new Exception\RuntimeException(
                    'The SetCookie multiple header implementation can only accept an array of SetCookie headers'
                );
            }
            $headerLine .= "\n" . $header->toString();
        }
        return $headerLine;
    }
}
