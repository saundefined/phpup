<?php

namespace PhpUp\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class VersionCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('version')
            ->setDescription('Show app version');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $process = new Process(['build/php', '--version']);
        $process->run();

        $output->writeln('phpup <info>' . PHPUP_VERSION . '</info>');
        $output->writeln($process->getOutput());

        return Command::SUCCESS;
    }
}