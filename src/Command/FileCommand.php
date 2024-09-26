<?php

namespace PhpUp\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class FileCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('file')
            ->addArgument('path', InputArgument::REQUIRED, 'The file path to execute')
            ->setDescription('Execute file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!file_exists($input->getArgument('path'))) {
            $output->writeln('<error>File not found</error>');

            return Command::SUCCESS;
        }

        $process = new Process(['build/php', '--version']);
        $process->setTty(true)->run();

        return Command::SUCCESS;
    }
}