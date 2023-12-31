<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;

class ConfigEnvVarProcessor implements EnvVarProcessorInterface
{
    public static function getProvidedTypes(): array
    {
        return [
            'trailingSlash' => 'string',
            //'test' => 'string',
        ];
    }

    public function getEnv($prefix, $name, \Closure $getEnv): string
    {
        switch ($prefix) {
            case 'trailingSlash':
                $env = $getEnv($name);
                return $env . (str_ends_with($env, '/') ? '' : '/'); // trailing slash

            default:
                throw new \RuntimeException('Not implemented.');
        }
    }
}
