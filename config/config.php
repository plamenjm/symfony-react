<?php

// Config
//$container->setParameter('env(APP_PATH_API)', '/api/');
if (!isset($_ENV['APP_PATH_API']))
    $container->setParameter('app_path_api', '/api/');
else {
    $appPathUrl = $_ENV['APP_PATH_API'];
    if (!str_ends_with($appPathUrl, '/')) $appPathUrl .= '/'; // trailing slash
    $container->setParameter('app_path_api', $appPathUrl);
}
