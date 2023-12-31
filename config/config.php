<?php

$container;


# replaced with Constant.php
////$container->setParameter('env(APP_PATH_API)', '/api/');
//if (!isset($_ENV['APP_PATH_API']))
//    $container->setParameter('app_path_api', '/api/');
//else {
//    $appPathUrl = $_ENV['APP_PATH_API'];
//    if (!str_ends_with($appPathUrl, '/')) $appPathUrl .= '/'; // trailing slash
//    $container->setParameter('app_path_api', $appPathUrl);
//}


$container->setAlias(\Symfony\Component\HttpKernel\Profiler\Profiler::class, 'profiler');


return function(\Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $container): void {
    $dumpTwigTagEnable = false; // phpunit twig {% dump %} tag // to-do: debug.var_dumper.cloner?

    if ($container->env() === 'test') {
        $services = $container->services();

        if ($dumpTwigTagEnable) $services
            //->set('var_dumper.cloner', \Symfony\Component\VarDumper\Cloner\VarCloner::class)
            //->set('var_dumper.html_dumper', \Symfony\Component\VarDumper\Dumper\HtmlDumper::class)
            ->set('twig.extension.dump', \Symfony\Bridge\Twig\Extension\DumpExtension::class)
                ->args([
                    \Symfony\Component\DependencyInjection\Loader\Configurator\service('debug.var_dumper.cloner'),
                    //\Symfony\Component\DependencyInjection\Loader\Configurator\service('var_dumper.cloner'),
                    //\Symfony\Component\DependencyInjection\Loader\Configurator\service('var_dumper.html_dumper'),
                ])
                ->tag('twig.extension');
    }
};
