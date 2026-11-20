<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Config;

use Firehed\Container\TypedContainerInterface;
use InvalidArgumentException;
use OpenEMR\BC\DatabaseConnectionOptions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DatabaseConnectionOptionsDefinitionTest extends TestCase
{
    use LoadsDefinitionsTrait;

    private const ENV_TCP = [
        'OPENEMR_DB_NAME' => 'envdb',
        'OPENEMR_DB_HOST' => 'db.example.com',
        'OPENEMR_DB_PORT' => '3307',
    ];

    public function testEnvReadsSsl(): void
    {
        $options = $this->resolve(self::ENV_TCP + [
            'OPENEMR_DB_SSL_CA' => '/certs/ca.pem',
            'OPENEMR_DB_SSL_CERT' => '/certs/cert.pem',
            'OPENEMR_DB_SSL_KEY' => '/certs/key.pem',
        ]);

        self::assertSame('/certs/ca.pem', $options->sslCaPath);
        self::assertSame(
            ['cert' => '/certs/cert.pem', 'key' => '/certs/key.pem'],
            $options->sslClientCert,
        );
    }

    public function testEnvWithoutSsl(): void
    {
        $options = $this->resolve(self::ENV_TCP);

        self::assertNull($options->sslCaPath, 'no CA was configured');
        self::assertNull($options->sslClientCert, 'no client cert was configured');
    }

    #[DataProvider('unpairedClientCertProvider')]
    public function testEnvRejectsUnpairedClientCert(string $key): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->resolve(self::ENV_TCP + [$key => '/certs/file.pem']);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function unpairedClientCertProvider(): array
    {
        return [
            'cert only' => ['OPENEMR_DB_SSL_CERT'],
            'key only' => ['OPENEMR_DB_SSL_KEY'],
        ];
    }

    /**
     * @param array<string, string> $env Nullable container entries that are set
     */
    private function resolve(array $env): DatabaseConnectionOptions
    {
        $container = self::createStub(TypedContainerInterface::class);
        $container->method('get')->willReturnCallback(
            static fn (string $id): ?string => $env[$id] ?? null,
        );
        $container->method('getString')->willReturnMap([
            ['OPENEMR_DB_USER', 'envuser'],
            ['OPENEMR_DB_PASSWORD', 'envpass'],
        ]);

        $options = self::definition('database.php', DatabaseConnectionOptions::class)($container);
        self::assertInstanceOf(DatabaseConnectionOptions::class, $options);
        return $options;
    }
}
