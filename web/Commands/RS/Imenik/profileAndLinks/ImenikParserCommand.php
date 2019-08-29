<?php

namespace Commands\RS\Imenik\profileAndLinks;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;

class ImenikParserCommand extends Command
{
    /**
     * Command config
    */
    protected function configure() : void
    {
        $this->setName('rs:start-21')
            ->setDescription('Starts download from http://www.privredni-imenik.com')
            ->setHelp('This command allow you start the script');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * Main parsed process (start stream)
    */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $links = file('web/Commands/RS/Imenik/profileAndLinks/list.txt', FILE_SKIP_EMPTY_LINES);
        $activeProcess = [];

        foreach($links as $key => $link) {
            try {
                $uri = urldecode(trim($link));
                var_dump($uri);
                $process = new Process("php application.php rs:main-21 --url='$uri'");
                $process->start();
                $activeProcess[] = $process;
                var_dump("$key link is processed");

                /** Cleaning memory of useless processes */
                $this->processControl($activeProcess);

                if ($key === count($links) - 1) {
                    sleep(60);
                }

            } catch (\Exception $e) {

            }
        }
    }

    /**
     * @param $processes
     * Method that cleans memory from useless processes
    */
    public function processControl(array $processes) : void
        {
         if(count($processes) >= 10){
            while(count($processes) >= 10){
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