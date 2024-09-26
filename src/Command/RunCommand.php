<?php

namespace PhpUp\Command;

use PhpUp\Enum\PackageInstalled;
use PhpUp\Model\Package;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class RunCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('run')
            ->addArgument('package', InputArgument::REQUIRED, 'The package to fetch')
            ->addArgument('thing', InputArgument::REQUIRED, 'The command to execute')
            ->addArgument('arguments', InputArgument::OPTIONAL, 'The optional arguments to pass to the command')
            ->setDescription('Run the package command with arguments');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Checking the package ' . $input->getArgument('package') . '...');

        $package = new Package($input->getArgument('package'));
        $binaries = $package->binaries();

        if (count($binaries) === 1) {
            $binary = $binaries[0];
        } else {
            $helper = $this->getHelper('question');
            $question = new ChoiceQuestion(
                'Please select binary to run',
                $package->binaries(),
                0
            );
            $question->setErrorMessage('Binary <info>%s</info> is invalid.');

            $binary = $helper->ask($input, $output, $question);
        }

        $output->writeln('Working with <info>' . $binary . '</info> binary...');

        if ($package->isInstalledLocally()) {
            $output->writeln('<info>Package is installed locally.</info>');

            return $package->run($binary, $input->getArgument('thing'), $input->getArgument('arguments'), PackageInstalled::Locally);
        }

        if ($package->isInstalledGlobally()) {
            $output->writeln('<info>Package is installed globally.</info>');

            return $package->run($binary, $input->getArgument('thing'), $input->getArgument('arguments'));
        }

        $output->writeln('Package is not installed. Trying to install...');

        $package->installGlobally();

        $output->writeln('<info>Package is installed globally.</info>');

        $package->run($binary, $input->getArgument('thing'), $input->getArgument('arguments'));

        $package->uninstallGlobally();

        $output->writeln('<info>Package is removed globally.</info>');

        return Command::SUCCESS;
    }
}