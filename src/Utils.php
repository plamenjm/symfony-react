<?php

namespace App;

class Utils
{
    private static function env(string|object $src = null): string
    {
        $env = '';
        if (!$src)
            $env = $_SERVER['APP_ENV']; //getenv('APP_ENV');
        else if (is_string($src))
            $env = $src;
        else if ($src::class === 'App\Kernel')
            $env = $src->getEnvironment();
        //else if (is_subclass_of($src, \Symfony\Bundle\FrameworkBundle\Controller\AbstractController::class))
        //    $env = $src->getParameter('kernel.environment');
        return $env;
    }

    public static function isTest(string|object $src = null): bool
    {
        return self::env($src) === 'test';
    }

    public static function isDev(string|object $src = null): bool
    {
        return self::env($src) === 'dev';
    }


    //---

    public static function dateTimeUTC(null | int | string | \DateTime $date = null): string {
        if (!$date)
            $date = new \DateTime();
        else if (is_int($date))
            $date = (new \DateTime())->setTimestamp($date);
        else if (!($date instanceof \DateTime))
            $date = new \DateTime($date);

        return $date
            ->setTimezone(new \DateTimeZone('UTC'))
            ->format('Y-m-d H:i:s');
    }

    public static function errorObjNotFound(string $obj, string $by = ''): string
    {
        return "\"$obj\" object not found" . (!$by ? '' : " by \"$by\"") . ".";
    }


    //---

    public static function JSConsole($var, $collect = true): null|string // JS console.log (html script tag)
    {
        // alternative: github.com/bkdotcom/PHPDebugConsole

        if ($collect) {
            \App\EventListener\DumpSubscriber::dumpCollect($var);
            return null;
        }

        //if (self::UseVarDumper) {
        //    $dumper = new \Symfony\Component\VarDumper\Dumper\CliDumper('php://stderr', null,
        //        \Symfony\Component\VarDumper\Dumper\AbstractDumper::DUMP_STRING_LENGTH);
        //    $var = $dumper->dump((new \Symfony\Component\VarDumper\Cloner\VarCloner())->cloneVar($var), true);
        //}

        //$context = [__METHOD__ ?? __FUNCTION__ . ' @ ' . __FILE__, __LINE__, __TRAIT__ ?: '-']; //__CLASS__, __NAMESPACE__,
        $trace = debug_backtrace();
        $context = [$trace[1]['class'], $trace[1]['function'], $trace[0]['file'], $trace[0]['line']];
        //$obj = array_merge($context, is_array($var) ? $var : [$var]);
        $obj = array_merge($context, //is_array($var) ? $var :
            [$var]);

        //$json = json_encode($obj);

        $serializer = new \Symfony\Component\Serializer\Serializer(
            [new \Symfony\Component\Serializer\Normalizer\ObjectNormalizer(defaultContext: [
                \Symfony\Component\Serializer\Normalizer\AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => fn($obj) => null,
            ])], [new \Symfony\Component\Serializer\Encoder\JsonEncoder()]);
        $json = $serializer->serialize($obj, 'json');

        $_EOL = PHP_EOL;
        /** @noinspection BadExpressionStatementJS JSVoidFunctionReturnValueUsed */
        return "<script> $_EOL
                console.log(...$json) $_EOL
            </script>";
    }


    //---

    private const dumpStdErrUseCliDumper = true; // CliDumper or print_r

    public static function stdErrDump(mixed $var): void {
        $dumper = new \Symfony\Component\VarDumper\Dumper\CliDumper('php://stderr', null,
            \Symfony\Component\VarDumper\Dumper\AbstractDumper::DUMP_STRING_LENGTH);
        $dumper->dump((new \Symfony\Component\VarDumper\Cloner\VarCloner())->cloneVar($var));
    }

    public static function stdErrPrint(mixed $var): void {
        //$stderr = defined('STDERR') ? STDERR : fopen('php://stderr', 'w'); // API Exception: Undefined constant "...\STDERR"
        //fwrite($stderr, print_r($var, TRUE));
        file_put_contents('php://stderr', print_r($var, TRUE));
    }

    public static function stdErr(mixed $var): void // StdErr: symfony server:log; phpunit
    {
        if (self::dumpStdErrUseCliDumper)
            \App\Utils::stdErrDump($var);
        else
            \App\Utils::stdErrPrint($var);
    }
}
