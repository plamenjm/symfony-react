<?php // $ bin/console make:controller --no-template BaseController

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class BaseController extends AbstractController
{
    public function __construct(
        //#[\Symfony\Component\DependencyInjection\Attribute\Autowire(service: 'debug.var_dumper.cloner')]
        //\Symfony\Component\VarDumper\Cloner\VarCloner $cloner,

        //?\Twig\Environment $twig = null,
    )
    {
        // phpunit twig {% dump %} tag. OR use twig:  {% if app.environment == 'dev' %}{% include 'dump_mix.html.twig' %}
        //if ($twig && \App\Utils::isTest() && !$twig->hasExtension(\Symfony\Bridge\Twig\Extension\DumpExtension::class))
        //    //$twig->addExtension(new \Symfony\Bridge\Twig\Extension\DumpExtension(new \Symfony\Component\VarDumper\Cloner\VarCloner()));
        //    $twig->addTokenParser(new \Symfony\Bridge\Twig\TokenParser\DumpTokenParser());

        // to-do: phpunit twig dump limits - {{ dump() }} hits: php -d memory_limit=13M bin/phpunit
        //\Symfony\Component\VarDumper\Dumper\CliDumper::$defaultOutput = 'php://output';
        //\Symfony\Component\VarDumper\VarDumper::setHandler(function (mixed $data) use ($cloner) {
        //    $dumper = new \Symfony\Component\VarDumper\Dumper\CliDumper();
        //    $cloner = new \Symfony\Component\VarDumper\Cloner\VarCloner();
        //    $cloner->setMaxItems(1); $cloner->setMaxString(1); $cloner->setMinDepth(1);
        //    $dumper->dump($cloner->cloneVar($data)
        //        ->withMaxDepth(1)->withMaxItemsPerDepth(1)->withRefHandles(false));
        //});
    }

    protected function exceptionObjNotFound(string $class, \Throwable $previous = null): NotFoundHttpException
    {
        return $this->createNotFoundException(\App\Utils::errorObjNotFound($class, static::class), $previous);
    }
}
