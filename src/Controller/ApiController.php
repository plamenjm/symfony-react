<?php // $ bin/console make:controller --no-template ApiController

namespace App\Controller;

use App\Service\Utils;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
//use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    // to-do: error - Service "logger" not found: even though it exists in the app's container, the container inside "App\Controller\ApiController" is a smaller service locator that only knows about the "form.factory", "http_kernel", "parameter_bag", "request_stack", "router", "security.authorization_checker", "security.csrf.token_manager", "security.token_storage", "serializer", "twig" and "web_link.http_header_serializer" services. Try using dependency injection instead.
    //#[SubscribedService]
    //private function logger(): LoggerInterface
    //{
    //    return $this->container->get('logger');
    //}
    
    #[Route('/api/params', name: 'api_params', methods: ['GET'])]
    public function params(
        //#[Autowire(lazy: true)] // to-do: error - Argument #1 ($logger) must be of type Psr\Log\LoggerInterface, null given
        LoggerInterface $logger,
        Utils $utils,
    ): JsonResponse
    {
        // debug log and dump
        //$logger->debug('params', ['ApiController']); //? $this->logger()->debug('params', ['ApiController']);
        //dump(['dump']); //? var_dump(['var_dump']);
        //file_put_contents('php://stderr', print_r(['php://stderr'], TRUE)); //? fwrite(STDERR, print_r(['STDERR'], TRUE))
        //dd(['dd']); //throw new \RuntimeException(print_r(['RuntimeException'], true));


        //$response = new \Symfony\Component\HttpFoundation\Response();
        //$response->headers->set('Content-Type', 'application/json');
        //return $response->setContent(json_encode(['fullName' => 'ApiController']));

        return new JsonResponse([
            'test' => true, // to-do
            'happyMessage' => $utils->happyMessage(),
        ]);

        // to-do: in tests, empty $response->getContent()
        //return new \Symfony\Component\HttpFoundation\StreamedJsonResponse(['fullName' => 'ApiController']);
    }

    #[Route('/api/phpunit', name: 'api_phpunit', methods: ['GET'])]
    public function phpunit(): JsonResponse
    {
        $processPhpunit = 'bin/phpunit';
        $processProjectDir = '..';
        //$processScript = '/usr/bin/script';

        //$process = new Process(['env'], $processProjectDir); // test
        //$process->run();
        //return new Response('<pre>' . $process->getOutput() . '</pre>');

        // phpunit executed as user www-data
        // $ mkdir var/test; chmod 777 var/cache/test; chmod 666 var/cache/test/annotations.map; su --shell /bin/bash www-data -c bin/phpunit
        $process = new Process([$processPhpunit], $processProjectDir, [
            'SYMFONY_DOTENV_VARS' => false, // load 'test' env from .env (current env is 'dev/prod')
            //'PHPUNIT_RESULT_CACHE' => $_SERVER['PWD'] . '/var/test/.phpunit.result.cache',
            //'TMPDIR' => $_SERVER['PWD'] . '/var/test',
        ]);

        // to-do: terminal colors with www.npmjs.com/package/ansi-to-html
        //$process = new Process([$processScript, '-c', $processPhpunit, 'var/test/phpunit.typescript'], $processProjectDir, [
        //    'SYMFONY_DOTENV_VARS' => false, // load 'test' env from .env (current env is 'dev/prod')
        //]);

        $process->run();

        //if (!$process->isSuccessful()) throw new ProcessFailedException($process);

        //return new Response('<pre>' . $process->getOutput() . '</pre>'); // test
        return new JsonResponse([
            'process' => $processPhpunit,
            'processOutput' => $process->getOutput(),
            'processExitCode' => $process->getExitCode(),
        ]);
    }
}
