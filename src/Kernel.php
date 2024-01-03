<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Compiler\CheckTypeDeclarationsPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function build(ContainerBuilder $container): void
    {
        //dump('src/Kernel: build'); //$this->getKernelParameters()

        // See: symfony.com/doc/current/service_container.html
        // The lint:container command checks that the arguments injected into services match their type declarations.
        // ...can hurt performance. That's why this type checking is implemented in a compiler pass called CheckTypeDeclarationsPass
        // ...only when executing the lint:container command.
        // It's useful to run it before deploying your application to production
        // If you don't mind the performance loss, enable the compiler pass in your application.
        // OR use - $ bin/console lint:container

        // From: \Symfony\Bundle\FrameworkBundle\Command\ContainerLintCommand (vendor/symfony/framework-bundle/Command/ContainerLintCommand.php)
        $container->setParameter('container.build_id', 'lint_container');
        $container->addCompilerPass(new CheckTypeDeclarationsPass(), PassConfig::TYPE_AFTER_REMOVING, -100);
    }
}
