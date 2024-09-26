<?php

namespace PhpUp\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VersionCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('version')
            ->setDescription('Show app version');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('phpup <info>' . PHPUP_VERSION . '</info>, with PHP <info>' . phpversion() . '</info>');

        return Command::SUCCESS;
    }
}