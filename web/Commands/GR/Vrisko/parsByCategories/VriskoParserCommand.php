<?php

namespace Commands\GR\Vrisko\parsByCategories;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;

class VriskoParserCommand extends Command
{
    /**
     * Command config
    */
    protected function configure() : void
    {
        $this->setName('gr:start-1')
            ->setDescription('Starts download from https://www.vrisko.gr')
            ->setHelp('This command allow you start the script');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * Main parsed process (start stream)
    */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $categories = file('web/Commands/GR/Vrisko/parsByCategories/list.txt', FILE_SKIP_EMPTY_LINES);
        $cities     = file('web/Commands/GR/listOfCity.txt', FILE_SKIP_EMPTY_LINES);
        $activeProcess = [];
        foreach($categories as $key => $category) {
            foreach ($cities as $key1 => $city) {

                try {
                    $totalPages = $this->getTotalPages($this->convertLink(trim($category), trim($city)));
                    for ($i = 0; $i <= $totalPages; $i++) {
                        $uri = $this->convertLink(trim($category), trim($city), $i);
                        $process = new Process("php application.php gr:vacuuming-1 --url='$uri'");
                        $process->start();
                        $activeProcess[] = $process;
                        var_dump(($key +1) . " link is processed, now $i page is processed");

                        /** Cleaning memory of useless processes */
                        $this->processControl($activeProcess);

                        if ($i === $totalPages && $key === count($categories) - 1) {
                            sleep(60);
                        }
                    }
                } catch (\Exception $e) {

                }
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
     * @param string $cat
     * @param string $city
     * @param int $page
     * @return string
    */
    protected function convertLink(string $cat, string $city, int $page=0) : string
    {
        return urldecode("https://www.vrisko.gr/search/$cat/$city/?page=$page");
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
            return $crawler->filter('.pagerWrapper > a')->count() + 1;
        } catch(\Exception $e){
            return 1;
        }
    }
}