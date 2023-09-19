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

        if (!isset($extra['xilofone']) || !\is_array($extra['xilofone'])) {
            throw new \RuntimeException('Missing xilofone composer configuration');
        }
        if (!isset($extra['xilofone']['file_id'])) {
            throw new \RuntimeException('Missing xilofone file id');
        }
        if (!isset($extra['xilofone']['destination_folder'])) {
            throw new \RuntimeException('Missing xilofone destination folder');
        }
        if (!\is_string($extra['xilofone']['destination_folder'])) {
            throw new \RuntimeException('Destination folder is not a string');
        }
        if (\str_starts_with($extra['xilofone']['destination_folder'], '/')) {
            throw new \RuntimeException('Destination folder must be relative');
        }

        if (
            isset($extra['xilofone']['host']) &&
            filter_var($extra['xilofone']['host'], FILTER_VALIDATE_URL) !== false
        ) {
            $host = $extra['xilofone']['host'];
        }
        $username = $_SERVER['XILOFONE_PLUGIN_USERNAME'];
        $password = $_SERVER['XILOFONE_PLUGIN_PASSWORD'];

        if (!\is_string($username) || !\is_string($password)) {
            throw new \RuntimeException('Missing xilofone credentials');
        }

        $psr17Factory = new Psr17Factory();

        return new XilofoneFileProvider(
            Psr18ClientDiscovery::find(),
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            $username,
            $password,
            $extra['xilofone']['file_id'],
            $rootDir . '/' . $extra['xilofone']['destination_folder'],
            $host
        );
    }
}
