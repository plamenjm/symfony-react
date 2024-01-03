<?php

$container;

//$container->setParameter('env(APP_PATH_API)', '/api/');
if (!isset($_ENV['APP_PATH_API']))
    $container->setParameter('app_path_api', '/api/');
else {
    $appPathUrl = $_ENV['APP_PATH_API'];
    if (!str_ends_with($appPathUrl, '/')) $appPathUrl .= '/'; // trailing slash
    $container->setParameter('app_path_api', $appPathUrl);
}


//---

// phpunit twig {% dump %} tag. Not working, must be for env test only
//$container->services() // From: \Symfony\Component\DependencyInjection\Loader\Configurator\ (vendor/symfony/debug-bundle/Resources/config/services.php)
//    ->set('twig.extension.dump', \Symfony\Bridge\Twig\Extension\DumpExtension::class)
//    ->args([
//        \Symfony\Component\DependencyInjection\Loader\Configurator\service('var_dumper.cloner'),
//        \Symfony\Component\DependencyInjection\Loader\Configurator\service('var_dumper.html_dumper'),
//    ])
//    ->tag('twig.extension');
//$container->services()
//    ->get(\Twig\Environment::class)
//    ->addTokenParser(new \Symfony\Bridge\Twig\TokenParser\DumpTokenParser());
