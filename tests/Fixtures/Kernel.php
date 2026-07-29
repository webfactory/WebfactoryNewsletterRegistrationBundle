<?php

namespace Webfactory\NewsletterRegistrationBundle\Tests\Fixtures;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Zenstruck\Foundry\ZenstruckFoundryBundle;

class Kernel extends BaseKernel
{
    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new ZenstruckFoundryBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config/framework.php');
        $loader->load(__DIR__.'/config/doctrine.php');
        $loader->load(__DIR__.'/config/zenstruck_foundry.php');
    }

    public function getCacheDir(): string
    {
        return __DIR__.'/../../var/cache/'.$this->environment;
    }

    public function getLogDir(): string
    {
        return __DIR__.'/../../logs';
    }
}
