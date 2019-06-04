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

        $guzzle = new GuzzleWrap();
        $pars = new WrapPars();


        for($i = 1; $i <= 500; $i++) {
            $content = $guzzle->getContent("https://www.paginiaurii.ro/firmy/-/q_activit%C4%83%C5%A3i+de+asisten%C5%A3%C4%83+medical%C4%83+specializat%C4%83+-+cod+caen++8622/{$i}/");


            $output->writeln([
                $pars->getPars($content)
            ]);
        }
    }
}