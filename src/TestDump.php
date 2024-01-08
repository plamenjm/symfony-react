<?php

namespace App;

class TestDump
{
    private const UseVarDumper = true;

    public static function varDump(mixed $message): void // STDOUT; phpunit
    {
        $message = ['var_dump', $message];

        if (self::UseVarDumper) {
            $dumper = new \Symfony\Component\VarDumper\Dumper\CliDumper('php://stderr', null,
                \Symfony\Component\VarDumper\Dumper\AbstractDumper::DUMP_STRING_LENGTH);
            $message = $dumper->dump((new \Symfony\Component\VarDumper\Cloner\VarCloner())->cloneVar($message), true);
        }

        var_dump($message);
    }

    public static function dump(mixed $message, $request = null): void // bin/console server:dump // Web Debug toolbar // STDOUT; phpunit
    {
        $message = ['dump', $message];

        // alternative with customized VarDumper
        //$fallback = \in_array(\PHP_SAPI, ['cli', 'phpdbg'])
        //    ? new \Symfony\Component\VarDumper\Dumper\CliDumper('php://stderr', null,
        //        \Symfony\Component\VarDumper\Dumper\AbstractDumper::DUMP_STRING_LENGTH)
        //    : new \Symfony\Component\VarDumper\Dumper\HtmlDumper();
        //$context = [
        //    'cli' => new \Symfony\Component\VarDumper\Dumper\ContextProvider\CliContextProvider(),
        //    'source' => new \Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider(),
        //];
        //if ($request) $context['request'] = new \Symfony\Component\VarDumper\Dumper\ContextProvider\RequestContextProvider($request);
        //$dumper = new \Symfony\Component\VarDumper\Dumper\ServerDumper('tcp://localhost:9912', $fallback, $context);
        // set common handler: \Symfony\Component\VarDumper\VarDumper::setHandler(function (mixed $message) use ($dumper): ?string { $dumper->dump(...); });
        //$dumper->dump((new \Symfony\Component\VarDumper\Cloner\VarCloner())->cloneVar($message));

        dump($message);
    }

    #[\JetBrains\PhpStorm\NoReturn]
    public static function dd(mixed $message): void // bin/console server:dump // STDOUT; phpunit
    {
        dd(['dd', $message]);
    }

    public static function exception(mixed $message) // symfony server:log // var/log/dev.log // STDOUT; phpunit
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        throw new \Exception(print_r($message, true));
    }

    public static function logger(mixed $message, \Psr\Log\LoggerInterface $logger): void // symfony server:log // var/log/dev.log
    {
        $logger->debug(print_r($message, TRUE), [$logger::class]);
    }
}
