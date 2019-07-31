<?php

namespace Commands\IT\Paginegialle\parsByLink;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;

class PaginegialleParserCommand extends Command
{
    /**
     * Command config
     */
    protected function configure() : void
    {
        $this->setName('it:start-1')
            ->setDescription('Starts download from https://www.paginegialle.it')
            ->setHelp('This command allow you start the script');
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * Main parsed process (start stream)
    */

    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $categories = file('web/Commands/IT/Paginegialle/parsByLink/listOfCategory.txt', FILE_SKIP_EMPTY_LINES);
        $postfixes = file('web/Commands/IT/Paginegialle/parsByLink/listOfPrefix.txt');
        $cities = file('web/Commands/IT/listOfCity.txt', FILE_SKIP_EMPTY_LINES);

        $activeProcess = [];
        foreach($categories as $key => $category){
            foreach($postfixes as $postfix) {
                foreach ($cities as $city) {
                    try {
                        $totalPages = $this->getTotalPages($this->convertLink(urldecode(trim($category)), trim($postfix), trim($city)));
                        for ($i = 1; $i <= $totalPages; $i++) {
                            $uri = $this->convertLink(urldecode(trim($category)), trim($postfix), trim($city), $i);
                            $process = new Process("php application.php it:vacuuming-1 --url='$uri'");
                            $process->start();
                            $activeProcess[] = $process;
                            var_dump(($key + 1) . " link is processed, now $i page is processed");

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
     * @param string $category
     * @param string $postfix
     * @param string $city
     * @param int $page
     * @return string
     */
    protected function convertLink(string $category, string $postfix, string $city, int $page=1) : string
    {
        return urldecode('https://www.paginegialle.it/ricerca/' . $category . '/' . $city. '/p-' . $page . $postfix );
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
            $filter = $crawler->filter('.lastArrBtn')->attr('href');

            $filter = explode('p-', $filter);
            $filter = explode('.', $filter[1]);
            return (int)$filter[0];
        } catch(\Exception $e){
            return 1;
        }
    }
}