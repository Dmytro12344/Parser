<?php

namespace Commands\CZ\Restu\profileAndLinks;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;

class RestuParserCommand extends Command
{
    /**
     * Command config
    */
    protected function configure() : void
    {
        $this->setName('cz:start-43')
            ->setDescription('Starts download from https://www.restu.cz')
            ->setHelp('This command allow you start the script');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * Main parsed process (start stream)
    */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $links = file('web/Commands/CZ/Restu/profileAndLinks/list.txt', FILE_SKIP_EMPTY_LINES);
        $activeProcess = [];
        foreach($links as $key => $link){
            try{
                $totalPages = $this->getTotalPages($this->convertLink(trim($link)));

                for($i = 1; $i <= $totalPages; $i++){
                    $uri = $this->convertLink(trim($link), $i);
                    $process = new Process("php application.php cz:main-43 --url='$uri'");
                    $process->start();
                    $activeProcess[] = $process;
                    var_dump("$key link is processed, now $i page is processed");

                    /** Cleaning memory of useless processes */
                    $this->processControl($activeProcess);

                    if($i === $totalPages && $key === count($links) - 1){
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
            $filter = $crawler->filter('.pagination > li')->count();
            $totalPages = $crawler->filter('.pagination > li > a')->eq($filter -2)->attr('href');
            $totalPages = explode('=', $totalPages);
            return (int)$totalPages[1];
        } catch(\Exception $e){
            return 1;
        }
    }
}