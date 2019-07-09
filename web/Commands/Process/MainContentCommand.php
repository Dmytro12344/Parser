<?php


namespace Commands\Process;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;


use  Wraps\Process\MainContent;

class MainContentCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure() : void
    {
        $this->setName('app:download-main-content')
            ->setDescription('Starts download')
            ->setHelp('This command allow you start the script')
            ->addOption('url', 'u', InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $pars = new MainContent();

        $output->writeln([
            $pars->setLinks($input->getOption('url'))
        ]);

    }
}