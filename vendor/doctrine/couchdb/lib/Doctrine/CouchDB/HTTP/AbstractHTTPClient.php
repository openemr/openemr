<?php
/** HTTP Client interface
 *
 */

namespace Doctrine\CouchDB\HTTP;

/**
 * Basic couch DB connection handling class
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.com
 * @since       1.0
 * @author      Kore Nordmann <kore@arbitracker.org>
 */
abstract class AbstractHTTPClient implements Client
{
    /**
     * CouchDB connection options
     *
     * @var array
     */
    protected $options = array(
        'host'       => 'localhost',
        'port'       => 5984,
        'ip'         => '127.0.0.1',
        'ssl'        => false,
        'timeout'    => .01,
        'keep-alive' => true,
        'username'   => null,
        'password'   => null,
    );

    /**
     * Construct a CouchDB connection
     *
     * Construct a CouchDB connection from basic connection parameters for one
     * given database.
     *
     * @param string $host
     * @param int $port
     * @param string $username
     * @param string $password
     * @param string $ip
     * @param bool $ssl
     * @return \Doctrine\CouchDB\HTTP\AbstractHTTPClient
     */
    public function __construct( $host = 'localhost', $port = 5984, $username = null, $password = null, $ip = null , $ssl = false )
    {
        $this->options['host']     = (string) $host;
        $this->options['port']     = (int) $port;
        $this->options['ssl']      = $ssl;
        $this->options['username'] = $username;
        $this->options['password'] = $password;

        if ($ip === null) {
            $this->options['ip'] = gethostbyname($this->options['host']);
        } else {
            $this->options['ip'] = $ip;
        }
    }

    /**
     * Set option value
     *
     * Set the value for an connection option. Throws an
     * InvalidArgumentException for unknown options.
     *
     * @param string $option
     * @param mixed $value
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    public function setOption( $option, $value )
    {
        switch ( $option ) {
        case 'keep-alive':
        case 'ssl':
            $this->options[$option] = (bool) $value;
            break;

        case 'http-log':
        case 'password':
        case 'username':
            $this->options[$option] = $value;
            break;

        default:
            throw new \InvalidArgumentException( "Unknown option $option." );
        }
    }
}

