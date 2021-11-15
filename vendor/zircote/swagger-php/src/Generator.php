<?php declare(strict_types=1);

/**
 * @license Apache 2.0
 */

namespace OpenApi;

use OpenApi\Annotations\OpenApi;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * OpenApi spec generator.
 *
 * Scans PHP source code and generates OpenApi specifications from the found OpenApi annotations.
 *
 * This is an object oriented alternative to using the now deprecated `\OpenApi\scan()` function and
 * static class properties of the `Analyzer` and `Analysis` classes.
 *
 * The `aliases` property supersedes the `Analyser::$defaultImports`; `namespaces` maps to `Analysis::$whitelist`.
 */
class Generator
{
    /** @var string Magic value to differentiate between null and undefined. */
    public const UNDEFINED = '@OA\Generator::UNDEFINED🙈';

    /** @var array Map of namespace aliases to be supported by doctrine. */
    protected $aliases = null;

    /** @var array List of annotation namespaces to be autoloaded by doctrine. */
    protected $namespaces = null;

    /** @var StaticAnalyser The configured analyzer. */
    protected $analyser;

    /** @var null|callable[] List of configured processors. */
    protected $processors = null;

    /** @var null|LoggerInterface PSR logger. */
    protected $logger = null;

    private $configStack;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;

        // kinda config stack to stay BC...
        $this->configStack = new class() {
            private $defaultImports;
            private $whitelist;
            private $log;

            public function push(Generator $generator): void
            {
                $this->defaultImports = Analyser::$defaultImports;
                $this->whitelist = Analyser::$whitelist;
                $this->log = Logger::getInstance()->log;

                Analyser::$defaultImports = $generator->getAliases();
                Analyser::$whitelist = $generator->getNamespaces();
                if ($logger = $generator->getLogger()) {
                    Logger::getInstance()->log = function ($msg, $type) use ($logger) {
                        $context = [];
                        if ($msg instanceof \Exception) {
                            $context['exception'] = $exception = $msg;
                            $msg = $exception->getMessage();
                        }

                        $level = E_USER_ERROR == $type ? LogLevel::ERROR : LogLevel::INFO;
                        $logger->log($level, $msg, $context);
                    };
                }
            }

            public function pop(): void
            {
                Analyser::$defaultImports = $this->defaultImports;
                Analyser::$whitelist = $this->whitelist;
                Logger::getInstance()->log = $this->log;
            }
        };
    }

    public function getAliases(): array
    {
        $aliases = null !== $this->aliases ? $this->aliases : Analyser::$defaultImports;
        $aliases['oa'] = 'OpenApi\\Annotations';

        return $aliases;
    }

    public function setAliases(?array $aliases): Generator
    {
        $this->aliases = $aliases;

        return $this;
    }

    public function getNamespaces(): array
    {
        $namespaces = null !== $this->namespaces ? $this->namespaces : Analyser::$whitelist;
        $namespaces = false !== $namespaces ? $namespaces : [];
        $namespaces[] = 'OpenApi\\Annotations\\';

        return $namespaces;
    }

    public function setNamespaces(?array $namespaces): Generator
    {
        $this->namespaces = $namespaces;

        return $this;
    }

    public function getAnalyser(): StaticAnalyser
    {
        return $this->analyser ?: new StaticAnalyser();
    }

    public function setAnalyser(?StaticAnalyser $analyser): Generator
    {
        $this->analyser = $analyser;

        return $this;
    }

    /**
     * @return callable[]
     */
    public function getProcessors(): array
    {
        return null !== $this->processors ? $this->processors : Analysis::processors();
    }

    /**
     * @param null|callable[] $processors
     */
    public function setProcessors(?array $processors): Generator
    {
        $this->processors = $processors;

        return $this;
    }

    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Static  wrapper around `Generator::generate()`.
     *
     * @param iterable $sources PHP source files to scan.
     *                          Supported sources:
     *                          * string
     *                          * \SplFileInfo
     *                          * \Symfony\Component\Finder\Finder
     * @param array    $options
     *                          aliases:    null|array                    Defaults to `Analyser::$defaultImports`.
     *                          namespaces: null|array                    Defaults to `Analyser::$whitelist`.
     *                          analyser:   null|StaticAnalyser           Defaults to a new `StaticAnalyser`.
     *                          analysis:   null|Analysis                 Defaults to a new `Analysis`.
     *                          processors: null|array                    Defaults to `Analysis::processors()`.
     *                          validate:   bool                          Defaults to `true`.
     *                          logger:     null|\Psr\Log\LoggerInterface If not set logging will use \OpenApi\Logger as before.
     */
    public static function scan(iterable $sources, array $options = []): OpenApi
    {
        // merge with defaults
        $config = $options + [
                'aliases' => null,
                'namespaces' => null,
                'analyser' => null,
                'analysis' => null,
                'processors' => null,
                'validate' => true,
            ];

        return (new Generator())
            ->setAliases($config['aliases'])
            ->setNamespaces($config['namespaces'])
            ->setAnalyser($config['analyser'])
            ->setProcessors($config['processors'])
            ->generate($sources, $config['analysis'], $config['validate']);
    }

    /**
     * Generate OpenAPI spec by scanning the given source files.
     *
     * @param iterable      $sources  PHP source files to scan.
     *                                Supported sources:
     *                                * string - file / directory name
     *                                * \SplFileInfo
     *                                * \Symfony\Component\Finder\Finder
     * @param null|Analysis $analysis custom analysis instance
     * @param bool          $validate flag to enable/disable validation of the returned spec
     */
    public function generate(iterable $sources, ?Analysis $analysis = null, bool $validate = true): OpenApi
    {
        $analysis = $analysis ?: new Analysis();

        $this->configStack->push($this);
        try {
            $this->scanSources($sources, $analysis);

            // post processing
            $analysis->process($this->getProcessors());

            // validation
            if ($validate) {
                $analysis->validate();
            }
        } finally {
            $this->configStack->pop();
        }

        return $analysis->openapi;
    }

    protected function scanSources(iterable $sources, Analysis $analysis): void
    {
        $analyser = $this->getAnalyser();
        foreach ($sources as $source) {
            if (is_iterable($source)) {
                $this->scanSources($source, $analysis);
            } else {
                $source = $source instanceof \SplFileInfo ? $source->getPathname() : realpath($source);
                if (is_dir($source)) {
                    $this->scanSources(Util::finder($source), $analysis);
                } else {
                    $analysis->addAnalysis($analyser->fromFile($source));
                }
            }
        }
    }
}
