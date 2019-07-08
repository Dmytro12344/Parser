<?php

namespace Commands\Process;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;



class StartProcessCommand extends Command
{

    public function __construct()
    {
        parent::__construct();
    }

    protected function configure() : void
    {
        $this->setName('app:start-commands')
            ->setDescription('Starts other commands')
            ->setHelp('This command allow you start necessary scripts');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

    }

}