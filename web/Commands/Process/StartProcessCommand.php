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
        $link = 'https://www.zivefirmy.cz/auto-moto-vozidla-autoskla-motocykly-automobily_o897?pg=';
        $activeProcess = [];

        for($i = 1 ; $i <= 509; $i++)
        {
            $url = $link . $i;
            $process = new Process("php application.php app:download-main-content --url=$url");
            $process->start();

            $activeProcess[] = $process;
            if(count($activeProcess) >= 3){
                while(count($activeProcess)){
                    foreach($activeProcess as $key => $runningProcess){
                        if(!$runningProcess->isRunning()){
                            unset($activeProcess[$key]);
                            var_dump(count($activeProcess));
                        }
                    }
                    sleep(1);
                }
            }
        }

    }

}