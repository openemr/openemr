<?php

/**
 * This is so can support the session locking
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2025 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Session\Predis;

use Predis\Client;
use SessionHandlerInterface;

class PredisSessionHandler implements SessionHandlerInterface
{
    private Client $redis;
    private int $ttl;
    private int $lockTimeout;
    private int $waitTimeout;
    private int $waitInterval;

    private ?string $currentSessionId = null;

    public function __construct(Client $redis, int $ttl, int $lockTimeout = 60, int $waitTimeout = 70, int $waitInterval = 150000)
    {
        $this->redis = $redis;
        $this->ttl = $ttl;
        $this->lockTimeout = $lockTimeout;
        $this->waitTimeout = $waitTimeout;
        $this->waitInterval = $waitInterval;
    }

    public function open($savePath, $sessionName)
    {
        // No action necessary
        return true;
    }

    public function close()
    {
        if ($this->currentSessionId) {
            $lockKey = "lock:$this->currentSessionId";
            $this->redis->del($lockKey);
        } else {
            $this->currentSessionId = null;
        }
        return true;
    }

    public function read($sessionId)
    {
        $this->currentSessionId = $sessionId;
        $sessionKey = "session:$sessionId";
        $lockKey = "lock:$sessionId";
        $start = time();

        // Attempt to acquire the lock
        while (true) {
            $acquired = $this->redis->set($lockKey, 1, 'NX', 'EX', $this->lockTimeout);
            if ($acquired) {
                break;
            }
            if ((time() - $start) >= $this->waitTimeout) {
                // Could not acquire lock within timeout
                throw new \Exception("Could not acquire lock for predis session within the timeout period.");
            }
            usleep($this->waitInterval);
        }

        // Read session data
        $data = $this->redis->get($sessionKey);
        return $data ? $data : '';
    }

    public function write($sessionId, $data)
    {
        $this->currentSessionId = $sessionId;
        $sessionKey = "session:$sessionId";
        $this->redis->setex($sessionKey, $this->ttl, $data);
        return true;
    }

    public function destroy($sessionId)
    {
        $sessionKey = "session:$sessionId";
        $lockKey = "lock:$sessionId";
        $this->redis->del([$sessionKey, $lockKey]);
        return true;
    }

    public function gc($maxLifetime)
    {
        // Redis handles expiration automatically
        return true;
    }
}
