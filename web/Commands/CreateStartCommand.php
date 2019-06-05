<?php

namespace Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wraps\GuzzleWrap;
use Wraps\WrapPars;

class CreateStartCommand extends Command
{

    public function __construct()
    {
        parent::__construct();
    }


    protected function configure()
    {
        $this->setName('app:start-download')
             ->setDescription('Starts download')
             ->setHelp('This command allow you start the script');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pars = new WrapPars();

        $output->writeln([
            $pars->getPars()
        ]);
    }






}