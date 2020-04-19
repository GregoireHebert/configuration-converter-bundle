<?php

declare(strict_types=1);

namespace ConfigurationConverter\Test\Fixtures\App;

use ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle;
use ConfigurationConverter\ConfigurationConverterBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class AppKernel extends Kernel
{
    use MicroKernelTrait;

    public function __construct(string $environment, bool $debug)
    {
        parent::__construct($environment, $debug);

        $this->environment = $_SERVER['APP_ENV'] ?? $environment;
    }

    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
            new TwigBundle(),
            new DoctrineBundle(),
            new ApiPlatformBundle(),
            new ConfigurationConverterBundle(),
        ];
    }

    public function getProjectDir()
    {
        return __DIR__;
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $routes->import('config/routing.yml');
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader): void
    {
        $c->setParameter('kernel.project_dir', __DIR__);

        $loader->load(__DIR__.'/config/config.yml');
    }
}
