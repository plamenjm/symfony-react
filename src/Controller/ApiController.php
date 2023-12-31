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
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(\App\Constant::APP_PATH_API, name: \App\Constant::APP_ROUTE_API, options: ['utf8' => true])] //#[Route('%app_path_api%', name: \App\Constant::APP_ROUTE_API)]
class ApiController extends AbstractController
{
    private const ROUTE_PRODUCT = '/product';

    // to-do: error - Service "logger" not found: even though it exists in the app's container, the container inside "App\Controller\ApiController" is a smaller service locator that only knows about the "form.factory", "http_kernel", "parameter_bag", "request_stack", "router", "security.authorization_checker", "security.csrf.token_manager", "security.token_storage", "serializer", "twig" and "web_link.http_header_serializer" services. Try using dependency injection instead.
    //#[SubscribedService]
    //private function logger(): LoggerInterface
    //{
    //    return $this->container->get('logger');
    //}
    
    #[Route('/params', name: '/params', methods: ['GET'])]
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
        return $this->json([
            'test' => true, // to-do
            'time' => date('Y-m-d H:i:s'),
            'happyMessage' => $utils->happyMessage(),
        ]);

        // to-do: in tests, empty $response->getContent()
        //return new \Symfony\Component\HttpFoundation\StreamedJsonResponse(['fullName' => 'ApiController']);
    }

    //---

    #[Route('/phpunit', name: '/phpunit', methods: ['GET'])]
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
        return $this->json([
            'process' => $processPhpunit,
            'processOutput' => $process->getOutput(),
            'processExitCode' => $process->getExitCode(),
        ]);
    }

    //---

    // /product

    private array $productIdSerializerContext = [
        //\Symfony\Component\Serializer\Normalizer\AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => fn($obj) => null,
        \Symfony\Component\Serializer\Normalizer\AbstractNormalizer::IGNORED_ATTRIBUTES => ['products'],
    ];

    #[Route('/product/{id?0}', name: self::ROUTE_PRODUCT, requirements: ['id' => Requirement::POSITIVE_INT])]
    public function product(#[MapEntity(disabled: true)] ?Product $product, EntityManagerInterface $entity, int $id = 0): JsonResponse
    {
        $entity = $GLOBALS['app']->getContainer()->get('doctrine')->getManager();
        $repository = $entity->getRepository(Product::class);
        if (!$id)
            $response = $repository->findAll();
        else {
            $response = $repository->find($id);
            if (!$response) throw $this->createNotFoundException('Not found.');
            //return $this->json(['name' => $response->getName()]);
        }
        return $this->json($response, Response::HTTP_OK, [], $this->productIdSerializerContext);
    }

    #[Route('/product-id/{id?0}', name: '/product-id', requirements: ['id' => Requirement::POSITIVE_INT])]
    public function productId(#[MapEntity] ?Product $product, EntityManagerInterface $entity, SerializerInterface $serializer): JsonResponse
    {
        if ($product) {
            $response = $product;
            //return $this->json(['name' => $product->getName()]);
        } else {
            $repository = $entity->getRepository(Product::class);
            $response = $repository->findAll();
        }

        //return $this->json($response, Response::HTTP_OK, [], $this->productIdSerializerContext);

        $serializer = new \Symfony\Component\Serializer\Serializer(
            [new \Symfony\Component\Serializer\Normalizer\ObjectNormalizer()],
            [new \Symfony\Component\Serializer\Encoder\JsonEncoder()]);
        //return $this->json($serializer->normalize($response, null, $this->productIdSerializerContext));
        return JsonResponse::fromJsonString($serializer->serialize($response, 'json', $this->productIdSerializerContext));
    }

    #[Route('/product/name-dql/{param?}', name: '/product/name-dql')]
    public function productNameDQL(ProductRepository $repository, ?string $param): JsonResponse
    {
        $products = $repository->findByNameDQL($param);
        return $this->json($products);
    }

    #[Route('/product/price-qb/{param?}', name: '/product/price-qb', requirements: ['id' => Requirement::DIGITS])]
    public function productPriceQB(ProductRepository $repository, ?int $param): JsonResponse
    {
        $products = $repository->findByPriceQB($param);
        return $this->json($products);
    }

    #[Route('/product/description-sql/{param?}', name: '/product/description-sql')]
    public function productDescriptionSQL(ProductRepository $repository, ?string $param): JsonResponse
    {
        $products = $repository->findByDescriptionSQL($param);
        return $this->json($products);
    }

    #[Route('/product/description-native/{param?}', name: '/product/description-native')]
    public function productDescriptionNative(ProductRepository $repository, ?string $param): JsonResponse
    {
        $products = $repository->findByDescriptionNative($param);
        return $this->json($products); //new JsonResponse($products)
    }

    #[Route('/product/seed', name: '/product/seed', priority: 2)]
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
        if (count($errList) > 0) return $this->json([//'errList' => (string) $errList,
            'errors' => $errors], Response::HTTP_BAD_REQUEST);

        $entity->persist($product);
        $entity->flush();
        return $this->redirectToRoute(\App\Constant::APP_ROUTE_API . self::ROUTE_PRODUCT, ['id' => $product->getId()]);
    }

    //---
}
