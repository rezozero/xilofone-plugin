<?php

declare(strict_types=1);

namespace RezoZero\Xilofone\Composer;

use Composer\Composer;
use Http\Discovery\Psr17Factory;
use Http\Discovery\Psr18ClientDiscovery;
use Symfony\Component\Dotenv\Dotenv;

final class XilofoneFileProviderFactory
{
    private Composer $composer;

    public function __construct(Composer $composer)
    {
        $this->composer = $composer;
    }

    private function getSingleFileConfiguration(array $config): array
    {
        if (!isset($config['file_id'])) {
            throw new \RuntimeException('Missing xilofone file id');
        }
        if (!isset($config['destination_folder'])) {
            throw new \RuntimeException('Missing xilofone destination folder');
        }
        if (!\is_string($config['destination_folder'])) {
            throw new \RuntimeException('Destination folder is not a string');
        }
        if (\str_starts_with($config['destination_folder'], '/')) {
            throw new \RuntimeException('Destination folder must be relative');
        }
        return [
            'file_id' => $config['file_id'],
            'destination_folder' => $config['destination_folder']
        ];
    }

    public function getFileConfigurations(): array
    {
        $extra = $this->composer->getPackage()->getExtra();
        if (!isset($extra['xilofone']) || !\is_array($extra['xilofone'])) {
            throw new \RuntimeException('Missing xilofone composer configuration');
        }

        if (isset($extra['xilofone']['files']) && \is_array($extra['xilofone']['files'])) {
            return array_map([$this, 'getSingleFileConfiguration'], $extra['xilofone']['files']);
        }

        return [
            $this->getSingleFileConfiguration($extra['xilofone'])
        ];
    }

    public function create(): XilofoneFileProvider
    {
        $dotenv = new Dotenv();
        $rootDir = getcwd();
        if (!\is_string($rootDir)) {
            throw new \RuntimeException('Root dir is not a string');
        }
        $dotenv->loadEnv($rootDir.'/.env');
        $extra = $this->composer->getPackage()->getExtra();
        $host = 'https://xilofone.rezo-zero.com';

        if (!isset($_SERVER['XILOFONE_PLUGIN_USERNAME']) || !isset($_SERVER['XILOFONE_PLUGIN_PASSWORD'])) {
            throw new \RuntimeException('Missing xilofone credentials in env vars');
        }

        $username = $_SERVER['XILOFONE_PLUGIN_USERNAME'];
        $password = $_SERVER['XILOFONE_PLUGIN_PASSWORD'];

        if (!\is_string($username) || !\is_string($password)) {
            throw new \RuntimeException('Missing xilofone credentials');
        }

        if (!isset($extra['xilofone']) || !\is_array($extra['xilofone'])) {
            throw new \RuntimeException('Missing xilofone composer configuration');
        }
        if (
            isset($extra['xilofone']['host']) &&
            filter_var($extra['xilofone']['host'], FILTER_VALIDATE_URL) !== false
        ) {
            $host = $extra['xilofone']['host'];
        }

        $psr17Factory = new Psr17Factory();

        return new XilofoneFileProvider(
            Psr18ClientDiscovery::find(),
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            $username,
            $password,
            $host
        );
    }
}
