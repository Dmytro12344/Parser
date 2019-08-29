<?php

namespace Commands\RS\Poslovne\profileAndLinks;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;

class PoslovneParserCommand extends Command
{
    /**
     * Command config
    */
    protected function configure() : void
    {
        $this->setName('rs:start-20')
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
        $activeProcess = [];
        try {
            $totalPages = $this->getTotalPages($this->convertLink());

            for ($i = 1; $i <= $totalPages; $i++) {
                $uri = $this->convertLink($i);
                $process = new Process("php application.php rs:main-20 --url='$uri'");
                $process->start();
                $activeProcess[] = $process;
                var_dump("$i page is processed");

                /** Cleaning memory of useless processes */
                $this->processControl($activeProcess);

                if ($i === $totalPages){
                    sleep(60);
                }
            }
        } catch (\Exception $e) {

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

    /**
     * @param int $page
     * @return string
    */
    protected function convertLink(int $page=0) : string
    {
        return urldecode('http://poslovne-strane.co.rs/pretraga?p='.$page.'&q=&naziv=&delatnost=0&grad=0');
    }

    /**
     * @param $url
     * @return int
     * Returns total pages from category
    */
    public function getTotalPages($url) : int
    {
        try {
            $guzzle = new GuzzleWrap();
            $crawler = new Crawler($guzzle->getContent(urldecode($url)));
            return (int)$crawler->filter('.pagger > li')->eq(5)->text();
        } catch(\Exception $e){
            return 0;
        }
    }
}