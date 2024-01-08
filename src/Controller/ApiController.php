<?php // $ bin/console make:controller --no-template ApiController

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductCategoryRepository;
use App\Repository\ProductRepository;
use App\Service\ApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
//use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(\App\Constants::APP_PATH_API, name: \App\Constants::APP_ROUTE_API, options: ['utf8' => true])] //#[Route('%app_path_api%'...
class ApiController extends BaseController //implements \Symfony\Contracts\Service\ServiceSubscriberInterface
{
    private const ROUTE_PRODUCT = '/product';


    //---

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            //\Psr\Log\LoggerInterface::class, // optional: '?' . ...
        ]);
    }


    //---

    /** @noinspection PhpInapplicableAttributeTargetDeclarationInspection */
    public function __construct(
        #[\Symfony\Component\DependencyInjection\Attribute\AutowireServiceClosure(\Psr\Log\LoggerInterface::class)]
        private readonly \Closure $logger,

        //#[\Symfony\Component\DependencyInjection\Attribute\AutowireLocator([\Psr\Log\LoggerInterface::class])] //? not subscribed, not working
        //private readonly \Psr\Container\ContainerInterface $subscribed,

        //\Twig\Environment $twig,
    )
    {
        parent::__construct(); //$twig
    }

    private function getLogger(): \Psr\Log\LoggerInterface
    {
        return ($this->logger)(); // lazy by closure (getter)
    }

    //private function locateLogger(): \Psr\Log\LoggerInterface
    //{
    //    //if (!$this->subscribed->has(\Psr\Log\LoggerInterface::class)) ... // optional
    //    /** @noinspection PhpUnhandledExceptionInspection */
    //    return $this->subscribed->get(\Psr\Log\LoggerInterface::class); // lazy by subscribe (locator)
    //}


    //---

    #[Route('/params', name: '/params', methods: ['GET'])]
    public function params(
        ApiService $apiService,

        //#[\Symfony\Component\DependencyInjection\Attribute\Autowire(service: \Psr\Log\LoggerInterface::class, lazy: true)]
        //\Psr\Log\LoggerInterface $logger, // lazy by proxy
    ): JsonResponse
    {
        if (!\App\Utils::isTest()) { // testDump
            //\App\TestDump::dd('params');
            //\App\TestDump::exception('params');
            //\App\TestDump::varDump('params');
            //\App\Utils::stdErr('StdErr' . 'params');
            //\App\TestDump::dump('params');
            \App\TestDump::logger('params', $this->getLogger()); //$this->locateLogger() //$logger
        }


        $res = [
            'test' => true, // to-do
            'time' => date('Y-m-d H:i:s'),
            'testHappyMessage' => $apiService->testHappyMessage(),
        ];

        //return new JsonResponse($res, Response::HTTP_OK, [
        //return JsonResponse::fromJsonString(json_encode($res), Response::HTTP_OK, [
        return $this->json($res, Response::HTTP_OK, [
            //'Symfony-Debug-Toolbar-Replace' => '1', // replaced with EventListener(kernel.response)
        ]);

        // to-do: $response->getContent() is empty in tests
        //return new \Symfony\Component\HttpFoundation\StreamedJsonResponse($res);
    }

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

        sleep(2); // test loading
        return $this->json([
            'process' => $processPhpunit,
            'processStdOut' => $process->getOutput(),
            'processStdErr' => $process->getErrorOutput(),
            'processExitCode' => $process->getExitCode(),
        ]);
    }


    //--- /product

    #[Route('/product/{id?0}', name: self::ROUTE_PRODUCT, requirements: ['id' => Requirement::POSITIVE_INT])]
    public function product(
        #[MapEntity(disabled: true)] ?Product $product,
        EntityManagerInterface $entity,
        int $id = 0,
    ): JsonResponse
    {
        $entity = $GLOBALS['app']->getContainer()->get('doctrine')->getManager();
        $repository = $entity->getRepository(Product::class);
        if (!$id)
            $response = $repository->findAll();
        else {
            $response = $repository->find($id);
            if (!$response) throw $this->exceptionObjNotFound(Product::class);
        }
        return $this->json($response);
    }

    #[Route('/product-id/{id?0}', name: '/product-id', requirements: ['id' => Requirement::POSITIVE_INT])]
    public function productId(
        #[MapEntity] ?Product $product,
        EntityManagerInterface $entity,
        SerializerInterface $serializer,
        ?int $id,
    ): JsonResponse
    {
        if (!$id) {
            $repository = $entity->getRepository(Product::class);
            $response = $repository->findAll();
        } else if (!$product)
            throw $this->exceptionObjNotFound(Product::class);
        else
            $response = $product;

        //return $this->json($response, Response::HTTP_OK, [], $productIdSerializerContext);

        $serializer = new \Symfony\Component\Serializer\Serializer(
            [new \Symfony\Component\Serializer\Normalizer\ObjectNormalizer(defaultContext: [
                \Symfony\Component\Serializer\Normalizer\AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => fn($obj) => null,
            ])], [new \Symfony\Component\Serializer\Encoder\JsonEncoder()]);
        //return $this->json($serializer->normalize($response, null, $productIdSerializerContext));
        return JsonResponse::fromJsonString($serializer->serialize($response, 'json', [
            \Symfony\Component\Serializer\Normalizer\AbstractNormalizer::IGNORED_ATTRIBUTES => ['products'],
        ]));
    }

    #[Route('/product-name/{name}', name: '/product-name')]
    public function productName(
        #[MapEntity] Product $product,
    ): JsonResponse
    {
        return $this->json($product);
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
        return $this->json($products);
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
        return $this->redirectToRoute(\App\Constants::APP_ROUTE_API . self::ROUTE_PRODUCT, ['id' => $product->getId()]);
    }
}
