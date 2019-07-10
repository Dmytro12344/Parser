<?php

namespace Commands\TwoSteps;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Exception\ProcessFailedException;


class StartProcessCommand extends Command
{
    /**
     * Command config
     * php application.php app:start-commands -t (int)number -p (int)number
     */
    protected function configure() : void
    {
        $this->setName('app:start-commands')
            ->setDescription('Starts other commands')
            ->setHelp('This command allow you start necessary scripts')
            ->addOption('total', 't', InputOption::VALUE_REQUIRED, 'Total pages on site')
            ->addOption('links', 'l', InputOption::VALUE_REQUIRED, 'Total link in pagination');
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
        /** receives category */
        $links = file('list.txt', FILE_SKIP_EMPTY_LINES);
        $activeProcess = [];
        $paginationLinks = (int)$input->getOption('links');

        foreach($links as $position => $link) {

            /** total pages from pagination */
            for ($i = 1; $i <= $input->getOption('total'); $i++) {
                $url = $link;

                try {
                    $process = new Process("php application.php app:download-main-content -u $url -l $paginationLinks");
                    $process->mustRun();
                    $activeProcess[] = $process;

                    /**  Shows witch process is running and which page refers to this process*/
                    var_dump($process->getPid() . " now $i page is processed. File number $position");

                    /** Cleaning memory of useless processes */
                    $this->processControl($activeProcess);
                }catch (ProcessFailedException $e){

                }

            }
        }
    }

    /**
     * @param $processes
     * Method that cleans memory from useless processes
     */
    public function processControl($processes) : void
    {
        if(count($processes) === 10){
            while(count($processes) === 10){
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