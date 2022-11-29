<?php

use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Kernel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Bootstrap
{
    /**
     * @var \Twig\Environment The twig rendering environment
     */
    private \Twig\Environment $twig;

    public function __construct(?Kernel $kernel = null)
    {
        if (empty($kernal)) {
            $kernal = new Kernel();
        }

        $twig = new TwigContainer($this->getTemplatePath(), $kernal);
        $twigEnv = $twig->getTwig();
        $this->twig = $twigEnv;
    }

    public function twigEnv(): \Twig\Environment
    {
        return $this->twig;
    }

    /**
     * @return string
     */
    public function getTemplatePath(): string
    {
        return \dirname(__DIR__) . DIRECTORY_SEPARATOR . "soap/templates" . DIRECTORY_SEPARATOR;
    }
}
