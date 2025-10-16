<?php

/**
 * This is so can support the session locking
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2025 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 * Note: This class was developed with assistance from AI (Claude by Anthropic and ChatGPT by OpenAI)
 *       for code structure and implementation guidance.
 */

namespace OpenEMR\Common\Session\Predis;

use OpenEMR\Common\Logging\SystemLogger;
use Predis\Client;
use SessionHandlerInterface;

class PredisSessionHandler implements SessionHandlerInterface
{
    private ?string $currentSessionId = null;

    private readonly SystemLogger $logger;

    public function __construct(private readonly Client $redis, private readonly int $ttl, private readonly int $lockTimeout = 60, private readonly int $waitTimeout = 70, private readonly int $waitInterval = 150000)
    {
        $this->logger = new SystemLogger();
    }

    public function __destruct()
    {
        $this->releaseLock();
        $this->logger->debug("PredisSessionHandler instance destructed");
    }

    public function open(string $savePath, string $sessionName): bool
    {
        // No action necessary
        return true;
    }

    public function close(): bool
    {
        $this->releaseLock();
        $this->logger->debug("PredisSessionHandler closed session");
        return true;
    }

    public function read(string $sessionId): string|false
    {
        $this->currentSessionId = $sessionId;
        $sessionKey = "session:$sessionId";
        $lockKey = "lock:$sessionId";
        $start = time();

        // Attempt to acquire the lock
        while (true) {
            $acquired = $this->redis->set($lockKey, 1, 'NX', 'EX', $this->lockTimeout);
            if ($acquired) {
                $this->logger->debug("PredisSessionHandler acquired lock for session");
                break;
            }
            if ((time() - $start) >= $this->waitTimeout) {
                // Could not acquire lock within timeout
                $this->logger->errorLogCaller("Could not acquire lock for predis session within the timeout period.");
                throw new \Exception("Could not acquire lock for predis session within the timeout period.");
            }
            usleep($this->waitInterval);
        }

        // Read session data
        $data = $this->redis->get($sessionKey);
        $this->logger->debug("PredisSessionHandler read session data");
        return $data ?: '';
    }

    public function write(string $sessionId, string $data): bool
    {
        $this->currentSessionId = $sessionId;
        $sessionKey = "session:$sessionId";
        $this->redis->setex($sessionKey, $this->ttl, $data);
        $this->logger->debug("PredisSessionHandler wrote session data");
        return true;
    }

    public function destroy(string $sessionId): bool
    {
        $sessionKey = "session:$sessionId";
        $lockKey = "lock:$sessionId";
        $this->redis->del([$sessionKey, $lockKey]);
        $this->logger->debug("PredisSessionHandler destroyed session");
        return true;
    }

    #[\ReturnTypeWillChange]
    public function gc(int $maxLifetime)
    {
        // Redis handles expiration automatically
        return true;
    }

    private function releaseLock()
    {
        if ($this->currentSessionId) {
            $lockKey = "lock:$this->currentSessionId";
            $this->redis->del($lockKey);
            $this->currentSessionId = null;
            $this->logger->debug("PredisSessionHandler released lock for session if applicable");
        }
    }
}
