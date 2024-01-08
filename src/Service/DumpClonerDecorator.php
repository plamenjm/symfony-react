<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\VarDumper\Cloner\AbstractCloner;
use Symfony\Component\VarDumper\Cloner\VarCloner;

#[AsDecorator(decorates: 'var_dumper.cloner', //VarCloner::class // to-do: debug.var_dumper.cloner? // to-do: phpunit VarCloner?
    onInvalid: ContainerInterface::IGNORE_ON_INVALID_REFERENCE)]
class DumpClonerDecorator extends AbstractCloner
{
    private const dumpStdErrEnable = true; // dump to StdErr
    private const dumpCollectEnable = true; // JS console.log (collect)


    //---

    public function __construct(
        private readonly ?VarCloner $inner,
        array $casters = null,
    )
    {
        parent::__construct($casters);
    }

    protected function doClone(mixed $var): array
    {
        $cloned = $this->inner?->doClone($var);

        if (self::dumpStdErrEnable) \App\Utils::stdErr($var); // to-do: if dump_destination is not php://stderr

        if (self::dumpCollectEnable) \App\EventListener\DumpSubscriber::dumpCollect($var);

        return $cloned;
    }
}
