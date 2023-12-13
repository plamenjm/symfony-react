<?php // $ bin/console make:controller --no-template ApiController

namespace App\Controller;

use App\Service\Utils;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
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
            'fullName' => 'ApiController',
            'message' => $utils->happyMessage(),
        ]);

        // to-do: in tests, empty $response->getContent()
        //return new \Symfony\Component\HttpFoundation\StreamedJsonResponse(['fullName' => 'ApiController']);
    }
}
