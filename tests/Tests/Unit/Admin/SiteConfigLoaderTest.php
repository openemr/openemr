<?php

/**
 * SiteConfigLoader Unit Tests
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    OpenEMR <warp@agent.dev>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Admin;

use OpenEMR\Admin\Exceptions\SiteConfigException;
use OpenEMR\Admin\Services\SiteConfigLoader;
use OpenEMR\Admin\ValueObjects\DatabaseCredentials;
use PHPUnit\Framework\TestCase;

class SiteConfigLoaderTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . '/openemr_config_test_' . uniqid();
        mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        // Clean up temp files
        if (is_dir($this->tempDir)) {
            $files = scandir($this->tempDir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    unlink($this->tempDir . '/' . $file);
                }
            }
            rmdir($this->tempDir);
        }
        parent::tearDown();
    }

    private function createTestConfig(bool $configured = true): string
    {
        $configPath = $this->tempDir . '/sqlconf.php';
        $content = '<?php' . PHP_EOL;
        
        if ($configured) {
            $content .= '$config = 1;' . PHP_EOL;
            $content .= '$host = "localhost";' . PHP_EOL;
            $content .= '$login = "testuser";' . PHP_EOL;
            $content .= '$pass = "testpass";' . PHP_EOL;
            $content .= '$dbase = "testdb";' . PHP_EOL;
            $content .= '$port = 3306;' . PHP_EOL;
        } else {
            $content .= '// No config set' . PHP_EOL;
        }
        
        file_put_contents($configPath, $content);
        return $configPath;
    }

    public function testLoadsValidCredentials(): void
    {
        $configPath = $this->createTestConfig();
        $loader = new SiteConfigLoader();

        $credentials = $loader->loadCredentials($configPath);

        $this->assertInstanceOf(DatabaseCredentials::class, $credentials);
        $this->assertSame('localhost', $credentials->getHost());
        $this->assertSame('testuser', $credentials->getLogin());
        $this->assertSame('testpass', $credentials->getPass());
        $this->assertSame('testdb', $credentials->getDbase());
        $this->assertSame(3306, $credentials->getPort());
    }

    public function testThrowsExceptionWhenFileNotFound(): void
    {
        $this->expectException(SiteConfigException::class);
        $this->expectExceptionMessage('Configuration file not found');

        $loader = new SiteConfigLoader();
        $loader->loadCredentials('/nonexistent/sqlconf.php');
    }

    public function testThrowsExceptionWhenConfigNotInitialized(): void
    {
        $this->expectException(SiteConfigException::class);
        $this->expectExceptionMessage('Site configuration not initialized');

        $configPath = $this->createTestConfig(false);
        $loader = new SiteConfigLoader();
        $loader->loadCredentials($configPath);
    }

    public function testThrowsExceptionWhenCredentialsIncomplete(): void
    {
        $this->expectException(SiteConfigException::class);
        $this->expectExceptionMessage('Invalid database credentials');

        // Create config with missing credentials
        $configPath = $this->tempDir . '/sqlconf.php';
        $content = '<?php' . PHP_EOL;
        $content .= '$config = 1;' . PHP_EOL;
        $content .= '$host = "localhost";' . PHP_EOL;
        $content .= '// Missing login, pass, dbase' . PHP_EOL;
        file_put_contents($configPath, $content);

        $loader = new SiteConfigLoader();
        $loader->loadCredentials($configPath);
    }

    public function testDetectsSetupNeededWhenFileNotExists(): void
    {
        $loader = new SiteConfigLoader();
        
        $this->assertTrue($loader->siteNeedsSetup('/nonexistent/sqlconf.php'));
    }

    public function testDetectsSetupNeededWhenConfigNotSet(): void
    {
        $configPath = $this->createTestConfig(false);
        $loader = new SiteConfigLoader();

        $this->assertTrue($loader->siteNeedsSetup($configPath));
    }

    public function testDetectsSetupNotNeededWhenConfigured(): void
    {
        $configPath = $this->createTestConfig(true);
        $loader = new SiteConfigLoader();

        $this->assertFalse($loader->siteNeedsSetup($configPath));
    }

    public function testHandlesCustomPort(): void
    {
        $configPath = $this->tempDir . '/sqlconf.php';
        $content = '<?php' . PHP_EOL;
        $content .= '$config = 1;' . PHP_EOL;
        $content .= '$host = "localhost";' . PHP_EOL;
        $content .= '$login = "testuser";' . PHP_EOL;
        $content .= '$pass = "testpass";' . PHP_EOL;
        $content .= '$dbase = "testdb";' . PHP_EOL;
        $content .= '$port = 3307;' . PHP_EOL;
        file_put_contents($configPath, $content);

        $loader = new SiteConfigLoader();
        $credentials = $loader->loadCredentials($configPath);

        $this->assertSame(3307, $credentials->getPort());
    }
}
