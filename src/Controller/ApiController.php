<?php // $ bin/console make:controller --no-template ApiController

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductCategoryRepository;
use App\Repository\ProductRepository;
use App\Service\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
//use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
//use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiController extends AbstractController
{
    // to-do: error - Service "logger" not found: even though it exists in the app's container, the container inside "App\Controller\ApiController" is a smaller service locator that only knows about the "form.factory", "http_kernel", "parameter_bag", "request_stack", "router", "security.authorization_checker", "security.csrf.token_manager", "security.token_storage", "serializer", "twig" and "web_link.http_header_serializer" services. Try using dependency injection instead.
    //#[SubscribedService]
    //private function logger(): LoggerInterface
    //{
    //    return $this->container->get('logger');
    //}
    
    #[Route('/api/params', name: '/api/params', methods: ['GET'])]
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

        sleep(2); // test
        return new JsonResponse([
            'test' => true, // to-do
            'time' => date('Y-m-d H:i:s'),
            'happyMessage' => $utils->happyMessage(),
        ]);

        // to-do: in tests, empty $response->getContent()
        //return new \Symfony\Component\HttpFoundation\StreamedJsonResponse(['fullName' => 'ApiController']);
    }

    //---

    #[Route('/api/phpunit', name: '/api/phpunit', methods: ['GET'])]
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

        sleep(2); // test
        return new JsonResponse([
            'process' => $processPhpunit,
            'processOutput' => $process->getOutput(),
            'processExitCode' => $process->getExitCode(),
        ]);
    }

    //---

    // /api/product

    private array $productIdSerializerContext = [
        //\Symfony\Component\Serializer\Normalizer\AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => fn($obj) => null,
        \Symfony\Component\Serializer\Normalizer\AbstractNormalizer::IGNORED_ATTRIBUTES => ['products'],
    ];

    #[Route('/api/product/{id}', name: '/api/product')]
    public function product(#[MapEntity] Product $product, SerializerInterface $serializer): JsonResponse
    {
        //return new JsonResponse(['name' => $product->getName()]);

        //return $this->json($product, Response::HTTP_OK, [], $this->productIdSerializerContext);

        $serializer = new \Symfony\Component\Serializer\Serializer(
            [new \Symfony\Component\Serializer\Normalizer\ObjectNormalizer()],
            [new \Symfony\Component\Serializer\Encoder\JsonEncoder()]);
        //return new JsonResponse($serializer->normalize($product, null, $this->productIdSerializerContext));
        return JsonResponse::fromJsonString($serializer->serialize($product, 'json', $this->productIdSerializerContext));
    }

    #[Route('/api/product-id/{id}', name: '/api/product-id')]
    public function productId(#[MapEntity(disabled: true)] ?Product $product, EntityManagerInterface $entity, int $id): JsonResponse
    {
        $entity = $GLOBALS['app']->getContainer()->get('doctrine')->getManager();
        $repository = $entity->getRepository(Product::class);
        $product = $repository->find($id);
        if (!$product) throw $this->createNotFoundException('Not found.');

        //return new JsonResponse(['name' => $product->getName()]);

        return $this->json($product, Response::HTTP_OK, [], $this->productIdSerializerContext);
    }

    #[Route('/api/product/name-dql/{name}', name: '/api/product/name-dql', priority: 2)]
    public function productNameDQL(ProductRepository $repository, string $name): JsonResponse
    {
        $products = $repository->findByNameDQL($name);
        return new JsonResponse($products);
    }

    #[Route('/api/product/price-qb/{price}', name: '/api/product/price-qb', priority: 2)]
    public function productPriceQB(ProductRepository $repository, int $price): JsonResponse
    {
        $products = $repository->findByPriceQB($price);
        return new JsonResponse($products);
    }

    #[Route('/api/product/description-sql/{description}', name: '/api/product/description-sql', priority: 2)]
    public function productDescriptionSQL(ProductRepository $repository, string $description): JsonResponse
    {
        $products = $repository->findByDescriptionSQL($description);
        return new JsonResponse($products);
    }

    #[Route('/api/product/description-native/{description}', name: '/api/product/description-native', priority: 2)]
    public function productDescriptionNative(ProductRepository $repository, string $description): JsonResponse
    {
        $products = $repository->findByDescriptionNative($description);
        return new JsonResponse($products);
    }

    #[Route('/api/product/new', name: '/api/product/new', priority: 2)]
    public function productNew(ProductCategoryRepository $repositoryCategory, ValidatorInterface $validator, EntityManagerInterface $entity): Response
    {
        $product = new Product();
        $product->setCategory($repositoryCategory->find(ProductCategoryRepository::DEFAULT_ID));
        $product->setName('Keyboard');
        $product->setPrice(1999);
        $product->setDescription('Ergonomic and stylish!');

        //$product->setName('');
        $errList = $validator->validate($product);
        $errors = [];
        //foreach ($errList as $error) $errors[] = (string) $error;
        foreach ($errList as $error) $errors['Object(' . $error->getRoot()::class . ').' . $error->getPropertyPath()] = $error->getMessage();
        if (count($errList) > 0) return new JsonResponse([//'errList' => (string) $errList,
            'errors' => $errors], Response::HTTP_BAD_REQUEST);

        $entity->persist($product);
        $entity->flush();
        return $this->redirectToRoute('/api/product', ['id' => $product->getId()]);
    }

    //---
}
