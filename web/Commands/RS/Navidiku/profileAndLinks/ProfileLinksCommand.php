<?php

namespace Commands\RS\Navidiku\profileAndLinks;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;

class ProfileLinksCommand extends Command
{
    /**
     * Command config
    */
    protected function configure() : void
    {
        $this->setName('rs:main-23')
            ->setDescription('Starts download from http://www.privredni-imenik.com')
            ->setHelp('This command allow you start the script')
            ->addOption('url', 'u', InputOption::VALUE_REQUIRED, 'needed url for parsing');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * Main parsed process (start stream)
    */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $activeProcess = [];
        $guzzle = new GuzzleWrap();
        $crawler = new Crawler($guzzle->getContent($input->getOption('url')));

        /** Creates new Process (max of processes is total records from page) */
        $totalRecords = $this->getTotalRecords($crawler);

        for($i = 0; $i < $totalRecords-1; $i++ ){
            $url = $this->convertLink($crawler, $i);

            $process = new Process("php application.php rs:vacuuming-23 --url='$url'");
            $process->start();

            /** total processes */
            $activeProcess[] = $process;

            /** Cleaning memory of useless processes */
            $this->processControl($activeProcess);

            if($i === $totalRecords -2){
                sleep(20);
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
     * @param Crawler $crawler
     * @param int $k
     * @return string
    */
    protected function convertLink(Crawler $crawler, int $k) : string
    {
        $filter = $crawler->filter('.header > h2')->eq($k)->filter('a')->attr('href');
        return urldecode('https://www.navidiku.rs' . $filter);
    }

    /**
     * @param Crawler $crawler
     * @return int
     * Return total records from page
    */
    protected function getTotalRecords(Crawler $crawler) : int
    {
        try{
            return $crawler->filter('.forcompanucards > .company-card')->count() -1;
        } catch (\Exception $e){
            return 0;
        }
    }

}