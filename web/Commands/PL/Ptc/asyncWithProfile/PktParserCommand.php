<?php

namespace Commands\PL\Ptc\asyncWithProfile;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;


class PktParserCommand extends Command
{
    /**
     * Command config
     */
    protected function configure() : void
    {
        $this->setName('pl:start-1')
            ->setDescription('Starts download from https://www.pkt.pl')
            ->setHelp('This command allow you start the script');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * Main parsed process (start stream)
     */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $categories = file('web/Commands/PL/Ptc/asyncWithProfile/list.txt', FILE_SKIP_EMPTY_LINES);
        $activeProcess = [];

        foreach($categories as $key => $category){
            try {
                $total_pages = $this->getTotalPages($this->convertLink($category));

                for ($i = 1; $i <= $total_pages; $i++) {
                    $uri = urldecode($this->convertLink($category, $i));

                    $process = new Process("php application.php pl:main-1 --url='$uri'");
                    $process->start();

                    $activeProcess[] = $process;

                    var_dump("$key link and $i page is processed");

                    /** Cleaning memory of useless processes */
                    $this->processControl($activeProcess);

                    if($i === $total_pages && $key === count($categories) - 1){
                        sleep(60);
                    }
                }

            } catch (ProcessFailedException $e) {
                $output->writeln([
                    $e->getMessage(),
                ]);
            }
        }
    }


    /**
     * @param $processes
     * Method that cleans memory from useless processes
     */
    public function processControl($processes) : void
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
     * @param $url
     * @return int
     * Returns total pages from category
     */
    public function getTotalPages($url) : int
    {
        $guzzle = new GuzzleWrap();
        $crawler = new Crawler($guzzle->getContent(urldecode($url)));
        $totalRecordsFromPage = $this->getTotalRecords($crawler); //25
        try {
            $totalRecordsFromSite = trim($crawler->filter('.box-fall-back-messages > i + h1 > b')->html());
            $totalRecordsFromSite = explode(' ', $totalRecordsFromSite);
            $totalRecordsFromSite = preg_replace('/[^0-9]/', '', $totalRecordsFromSite[0]);

            return ceil((int)$totalRecordsFromSite / (int)$totalRecordsFromPage);
        } catch(\Exception $e){
            return 1;
        }
    }

    protected function getTotalRecords(Crawler $crawler) : int
    {
        try{
            return $crawler->filter('.list-sel')->count();
        }
        catch (\Exception $e) {
            return 0;
        }
    }

    protected function convertLink(string $category, int $page=1) : string
    {
        return 'https://www.pkt.pl/szukaj/' . $category . '/' . $page;
    }

}