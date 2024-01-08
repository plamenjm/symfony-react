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

    private const dumpEnableStdErrAndCollect = false; // dump to StdErr, JS console.log (collect) // replaced with DumpClonerDecorator


    //---

    private \Closure $dumpHandlerPrev;

    public function boot(): void
    {
        parent::boot();

        if ($this->container->getParameter('kernel.debug')) {
            if (self::dumpEnableStdErrAndCollect) $this->dumpHandlerPrev = \Symfony\Component\VarDumper\VarDumper::setHandler(function ($var, string $label = null) {
                \App\Utils::stdErr($var); // to-do: if dump_destination is not php://stderr

                \App\EventListener\DumpSubscriber::dumpCollect($var);

                ($this->dumpHandlerPrev)($var, $label);
            });
        }
    }

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
