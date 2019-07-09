<?php

namespace Commands\Process;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class StartProcessCommand extends Command
{
    /**
     * StartProcessCommand constructor.
     * Don't using
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Command config
     */
    protected function configure() : void
    {
        $this->setName('app:start-commands')
            ->setDescription('Starts other commands')
            ->setHelp('This command allow you start necessary scripts');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * Starts the flow of processes that receive links to other pages of the site and then creates
     * subprocesses to collect information from previously received pages
     */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $link = 'https://www.zivefirmy.cz/auto-moto-vozidla-autoskla-motocykly-automobily_o897?pg=';
        $activeProcess = [];

        /** total pages from pagination */
        for($i = 1 ; $i <= 509; $i++)
        {
            $url = $link . $i;
            $process = new Process("php application.php app:download-main-content -u $url");
            $process->start();
            $activeProcess[] = $process;

            /**  Shows witch process is running and which page refers to this process*/
            var_dump($process->getPid() . " now $i page is processed");

            /** Cleaning memory of useless processes */
            $this->processControl($activeProcess);

        }
    }

    /**
     * @param $processes
     * Method that cleans memory from useless processes
     */
    public function processControl($processes)
    {
        if(count($processes) >= 3){
            while(count($processes)){
                foreach($processes as $key => $runningProcess){
                    if(!$runningProcess->isRunning()){
                        unset($processes[$key]);
                    }
                }
                sleep(1);
            }
        }
    }

}