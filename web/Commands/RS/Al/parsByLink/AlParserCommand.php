<?php

namespace Commands\RS\Al\parsByLink;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;

class AlParserCommand extends Command
{
    /**
     * Command config
    */
    protected function configure() : void
    {
        $this->setName('rs:start-6')
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
        $links = file('web/Commands/RS/Al/parsByLink/list.txt', FILE_SKIP_EMPTY_LINES);
        $activeProcess = [];
        foreach($links as $key => $link){
            try{
                for($i = 1; $i <= 9; $i++){
                    $uri = $this->convertLink(trim($link), $i);
                    $process = new Process("php application.php rs:vacuuming-6 --url='$uri'");
                    $process->start();
                    $activeProcess[] = $process;
                    var_dump("$key link is processed, now $i page is processed");

                    /** Cleaning memory of useless processes */
                    $this->processControl($activeProcess);

                    if($i === 9 && $key === count($links) - 1){
                        sleep(60);
                    }
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
         if(count($processes) >= 20){
            while(count($processes) >= 20){
                foreach($processes as $key => $runningProcess){
                    if(!$runningProcess->isRunning()){
                        unset($processes[$key]);
                    }
                }
                sleep(1);
            }
        }
    }

    /**
     * @param string $keyWord
     * @param int $item
     * @return string
    */
    protected function convertLink(string $keyWord, int $item=1) : string
    {
        return urldecode($keyWord . $item);
    }
}