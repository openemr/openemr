<?php

/**
 * Repo-layer negatives for ClientRepository::validateClient's remaining
 * return-false branches:
 *
 *   - line 210: client_id not present in oauth_clients
 *   - line 233: CryptoGenException from decryptFromDatabase
 *   - line 236: decrypted secret is empty after decryption
 *
 * These are covered end-to-end for the "missing secret" and "wrong
 * secret" branches by PasswordGrantHardeningTest + the api-suite
 * refresh-negative tests. The three branches above are only reachable
 * with specific corrupted / edge-case DB state.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class ClientRepositoryValidateClientNegativesTest extends TestCase
{
    /** @var list<string> */
    private array $trackedClientIds = [];

    protected function tearDown(): void
    {
        foreach ($this->trackedClientIds as $clientId) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM oauth_clients WHERE client_id = ?',
                [$clientId]
            );
        }
        $this->trackedClientIds = [];
    }

    #[Test]
    public function testValidateClientReturnsFalseWhenClientNotRegistered(): void
    {
        // ClientRepository::validateConfidentialClientSecret line 205-210:
        // sqlQueryNoLog returns false for an unknown client_id → the
        // guard returns false.
        $repo = new ClientRepository();
        $this->assertFalse(
            $repo->validateClient('nonexistent-client-' . Uuid::uuid4()->toString(), 'any-secret', 'authorization_code'),
            'Unknown client_id must return false regardless of the presented secret'
        );
    }

    #[Test]
    public function testValidateClientReturnsFalseOnDecryptError(): void
    {
        // ClientRepository::validateConfidentialClientSecret line 230-233:
        // decryptFromDatabase throws CryptoGenException when the stored
        // client_secret bytes have a valid version prefix (so the
        // pass-through fallback doesn't fire) but the payload cannot
        // be decrypted. A "007…" prefix plus random bytes hits that.
        $garbageBytes = '007' . bin2hex(random_bytes(64));
        $clientId = 'test-decrypt-error-' . Uuid::uuid4()->toString();
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO oauth_clients (client_id, client_name, client_secret, is_confidential, is_enabled, client_role) "
                . "VALUES (?, ?, ?, 1, 1, 'users')",
            [$clientId, 'ClientRepository decrypt-error test', $garbageBytes]
        );
        $this->trackedClientIds[] = $clientId;

        $repo = new ClientRepository();
        $this->assertFalse(
            $repo->validateClient($clientId, 'any-secret', 'authorization_code'),
            'A client whose stored client_secret does not decrypt must return false'
        );
    }

    #[Test]
    public function testValidateClientReturnsFalseWhenDecryptedSecretIsEmpty(): void
    {
        // ClientRepository::validateConfidentialClientSecret line 235-236:
        // decrypt succeeds but yields empty string. Encrypt an empty
        // string via the same crypto used by the write path so decrypt
        // is guaranteed to succeed and yield empty.
        $encryptedEmpty = (ServiceContainer::getCrypto())->encryptForDatabase('');

        $clientId = 'test-empty-secret-' . Uuid::uuid4()->toString();
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO oauth_clients (client_id, client_name, client_secret, is_confidential, is_enabled, client_role) "
                . "VALUES (?, ?, ?, 1, 1, 'users')",
            [$clientId, 'ClientRepository empty-post-decrypt test', $encryptedEmpty]
        );
        $this->trackedClientIds[] = $clientId;

        $repo = new ClientRepository();
        $this->assertFalse(
            $repo->validateClient($clientId, 'any-secret', 'authorization_code'),
            'A client whose decrypted client_secret is empty must return false'
        );
    }
}
