<?php

namespace App;

class TestDump
{
    private const UseVarDumper = true;

    public static function stdErr(mixed $message): void // STDERR; symfony server:log; phpunit
    {
        $message = ['StdErr', $message];

        if (self::UseVarDumper) {
            $dumper = new \Symfony\Component\VarDumper\Dumper\CliDumper('php://stderr', null,
                \Symfony\Component\VarDumper\Dumper\AbstractDumper::DUMP_STRING_LENGTH);
            $dumper->dump((new \Symfony\Component\VarDumper\Cloner\VarCloner())->cloneVar($message));
        } else {
            //$stderr = defined('STDERR') ? STDERR : fopen('php://stderr', 'w'); // API Exception: Undefined constant "...\STDERR"
            //fwrite($stderr, print_r($message, TRUE));
            file_put_contents('php://stderr', print_r($message, TRUE));
        }
    }

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
        $logger->critical(print_r($message, TRUE), [$logger::class]);
    }

    public static function console($message): void // Web JS console.log
    {
        // alternative: github.com/bkdotcom/PHPDebugConsole

        //if (self::UseVarDumper) {
        //    $dumper = new \Symfony\Component\VarDumper\Dumper\CliDumper('php://stderr', null,
        //        \Symfony\Component\VarDumper\Dumper\AbstractDumper::DUMP_STRING_LENGTH);
        //    $message = $dumper->dump((new \Symfony\Component\VarDumper\Cloner\VarCloner())->cloneVar($message), true);
        //}

        $obj = [__METHOD__ ?? __FUNCTION__ . ' @ ' . __FILE__, __LINE__, __TRAIT__, //__CLASS__, __NAMESPACE__,
            $message];

        //$json = json_encode($obj);

        $serializer = new \Symfony\Component\Serializer\Serializer(
            [new \Symfony\Component\Serializer\Normalizer\ObjectNormalizer(defaultContext: [
                \Symfony\Component\Serializer\Normalizer\AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => fn($obj) => null,
            ])], [new \Symfony\Component\Serializer\Encoder\JsonEncoder()]);
        $json = $serializer->serialize($obj, 'json');

        $_EOL = PHP_EOL;
        /** @noinspection BadExpressionStatementJS JSVoidFunctionReturnValueUsed */
        echo "<script> $_EOL
                console.log($json) $_EOL
            </script>";
    }
}
