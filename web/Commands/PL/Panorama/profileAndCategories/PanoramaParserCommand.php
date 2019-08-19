<?php

namespace Commands\PL\Panorama\profileAndCategories;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;

class PanoramaParserCommand extends Command
{
    /**
     * Command config
    */
    protected function configure() : void
    {
        $this->setName('pl:start-2')
            ->setDescription('Starts download from https://panoramafirm.pl')
            ->setHelp('This command allow you start the script');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * Main parsed process (start stream)
    */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $categories = file('web/Commands/PL/Panorama/profileAndCategories/list.txt', FILE_SKIP_EMPTY_LINES);
        $activeProcess = [];
        foreach($categories as $key => $category){
            try{
                $totalPages = $this->getTotalPages($this->convertLink(trim($category)));
                for($i = 1; $i <= $totalPages; $i++){
                    $uri = $this->convertLink(trim($category), $i);
                    $process = new Process("php application.php pl:main-2 --url='$uri'");
                    $process->start();
                    $activeProcess[] = $process;
                    var_dump("$key link is processed, now $i page is processed");

                    /** Cleaning memory of useless processes */
                    $this->processControl($activeProcess);

                    if($i === $totalPages && $key === count($categories) - 1){
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
         if(count($processes) >= 12){
            while(count($processes) >= 12){
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
        return urldecode('https://panoramafirm.pl/'.$keyWord.'/firmy,'.$item.'.html');
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
            $totalRecordsFromPage = (int)$crawler->filter('.list-unstyled > .profile-cloud ')->count();
            $totalRecordsFromSite = (int)preg_replace('/\D/', '',$crawler->filter('#resultCount')->text());
            return (int)ceil($totalRecordsFromSite / $totalRecordsFromPage);
        } catch(\Exception $e){
            return 1;
        }
    }
}