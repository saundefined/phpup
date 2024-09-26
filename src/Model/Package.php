<?php

namespace PhpUp\Model;

use Exception;
use PhpUp\Enum\PackageInstalled;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Process\Process;

class Package
{
    public readonly array $package;

    public function __construct(
        public string $name,
    )
    {
        if (!preg_match('/^[a-z0-9-_.]+\/[a-z0-9-_.]+$/', $name)) {
            throw new Exception('Invalid package name.');
        }

        $this->package = $this->fetchFromComposer();

        if (empty($this->binaries())) {
            throw new Exception('Package has no binaries.');
        }
    }

    public function fetchFromComposer(): array
    {
        $client = HttpClient::create();
        $response = $client->request(
            'GET',
            "https://packagist.org/packages/$this->name.json"
        );

        if ($response->getStatusCode() !== 200) {
            throw new Exception('Package not found.');
        }

        return $response->toArray()['package'];
    }

    public function isDevTool(): bool
    {
        return !empty(array_intersect($this->lastVersion()['keywords'], ['dev', 'testing', 'static analysis']));
    }

    public function isInstalledLocally(): bool
    {
        $process = (new Process(['build/php', 'vendor/bin/composer', 'show', $this->name]));
        $process->run();

        return $process->isSuccessful();
    }

    public function isInstalledGlobally(): bool
    {
        $process = (new Process(['build/php', 'vendor/bin/composer', 'global', 'show', $this->name]));
        $process->run();

        return $process->isSuccessful();
    }

    public function installGlobally(): bool
    {
        $process = (new Process([
            'build/php',
            'vendor/bin/composer',
            'global',
            'require',
            $this->name,
            $this->isDevTool() ? '--dev' : '',
            '--ignore-platform-reqs',
        ]));
        $process->run();

        return $process->isSuccessful();
    }

    public function uninstallGlobally(): bool
    {
        $process = (new Process([
            'build/php',
            'vendor/bin/composer',
            'global',
            'uninstall',
            $this->name,
            $this->isDevTool() ? '--dev' : '',
            '--ignore-platform-reqs',
        ]));
        $process->run();

        return $process->isSuccessful();
    }

    public function lastVersion()
    {
        return $this->package['versions'][array_keys($this->package['versions'])[1]];
    }

    public function binaries(): array
    {
        if (!isset($this->lastVersion()['bin'])) {
            throw new Exception('Package has no binaries.');
        }

        return array_map(fn($bin) => explode('/', $bin)[1], $this->lastVersion()['bin']);
    }

    public function run(
        string  $binary,
        string  $command,
        ?string $arguments,
                $packageInstalled = PackageInstalled::Globally,
    ): int
    {
        $process = new Process([
            'build/php',
            'vendor/bin/composer',
            $packageInstalled === PackageInstalled::Globally ? 'global' : '',
            'exec',
            $binary,
            $command,
            $arguments,
        ]);

        $process->setTty(true)->run();

        return $process->isSuccessful();
    }
}