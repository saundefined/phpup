#!/usr/bin/env php
<?php

use Symfony\Component\Console\Application;

(static function (): void {
    if (file_exists($autoload = 'vendor/autoload.php')) {
        include_once $autoload;

        return;
    }

    throw new RuntimeException('Unable to find the Composer autoloader.');
})();

const PHPUP_VERSION = '0.2.0';

// @TODO Handle restart
putenv("COMPOSER_ALLOW_XDEBUG=1");
putenv("PHPSTAN_ALLOW_XDEBUG=1");
putenv("RECTOR_ALLOW_XDEBUG=1");

$application = new Application();
$application->add(new \PhpUp\Command\FileCommand());
$application->add(new \PhpUp\Command\RunCommand());
$application->add(new \PhpUp\Command\VersionCommand());

$application->run();