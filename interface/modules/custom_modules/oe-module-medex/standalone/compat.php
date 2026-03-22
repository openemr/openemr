<?php
// Minimal compatibility layer to run the MedEx module outside OpenEMR

namespace OpenEMR\Common\Database {

    class SqlQueryException extends \Exception {}

    class QueryUtils
    {
        private static ?\PDO $pdo = null;

        public static function init(\PDO $pdo): void
        {
            self::$pdo = $pdo;
        }

        private static function ensurePdo(): void
        {
            if (self::$pdo === null) {
                throw new SqlQueryException('PDO not initialized. Call QueryUtils::init($pdo)');
            }
        }

        public static function fetchRecords(string $sql, array $params = []): array
        {
            self::ensurePdo();
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        }

        public static function fetchSingleValue(string $sql, string $col = '', array $params = [])
        {
            self::ensurePdo();
            $stmt = self::$pdo->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row === false) {
                return null;
            }
            if ($col === '') {
                return array_shift($row);
            }
            return $row[$col] ?? null;
        }

        public static function sqlStatementThrowException(string $sql, array $params = [])
        {
            self::ensurePdo();
            $stmt = self::$pdo->prepare($sql);
            if ($stmt === false) {
                $err = self::$pdo->errorInfo();
                throw new SqlQueryException($err[2] ?? 'Prepare failed');
            }
            if (!$stmt->execute($params)) {
                $err = $stmt->errorInfo();
                throw new SqlQueryException($err[2] ?? 'Execute failed');
            }
            return $stmt;
        }

        // Convenience wrappers used by some module code
        public static function sqlQuery(string $sql, array $params = [])
        {
            try {
                $stmt = self::sqlStatementThrowException($sql, $params);
                return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } catch (SqlQueryException $e) {
                return false;
            }
        }

        public static function sqlStatement(string $sql, array $params = [])
        {
            try {
                return self::sqlStatementThrowException($sql, $params);
            } catch (SqlQueryException $e) {
                return false;
            }
        }
    }
}

namespace OpenEMR\Common\Http {

    // Very small HTTP client wrapper used by the module
    class oeHttp
    {
        private static array $options = [];

        public static function setOptions(array $opts = []): self
        {
            self::$options = array_merge(self::$options, $opts);
            return new self();
        }

        public function request(string $method, string $url, array $params = []): array
        {
            $method = strtoupper($method);
            $ch = curl_init();
            if ($method === 'GET' && !empty($params)) {
                $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
            }
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            }
            if (!empty(self::$options['timeout'])) {
                curl_setopt($ch, CURLOPT_TIMEOUT, (int)self::$options['timeout']);
            }
            if (isset(self::$options['verify']) && self::$options['verify'] === false) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            }
            $resp = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            if ($resp === false) {
                throw new \Exception('HTTP request failed: ' . $err);
            }
            $decoded = json_decode($resp, true);
            return $decoded === null ? ['raw' => $resp] : $decoded;
        }
    }
}

namespace OpenEMR\Core {

    // Minimal globals bag - stores values in memory and defers to environment
    class OEGlobalsBag
    {
        private static ?self $instance = null;
        private array $data = [];

        public static function getInstance(): self
        {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function get(string $key)
        {
            if (array_key_exists($key, $this->data)) {
                return $this->data[$key];
            }
            $env = getenv($key);
            return $env !== false ? $env : null;
        }

        public function set(string $key, $value): void
        {
            $this->data[$key] = $value;
        }
    }
}
