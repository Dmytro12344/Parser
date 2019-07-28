<?php


namespace Commands\CZ\Zivefirmy\asinc;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;

class ZivefirmyParserCommand extends Command
{
    /**
     * Command config
     */
    protected function configure() : void
    {
        $this->setName('cz:start-11')
            ->setDescription('Starts download from http://www.zivefirmy.rs')
            ->setHelp('This command allow you start the script');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * Main parsed process (start stream)
     */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $categories = file('web/Commands/CZ/Zivefirmy/list.txt', FILE_SKIP_EMPTY_LINES);
        $activeProcess = [];

        foreach($categories as $key => $category){
            $totalPage = $this->getTotalPages($this->convertLink(trim($category)));

            for ($i = 1; $i <= $totalPage; $i++) {
                try {
                    $uri = $this->convertLink(trim($category), $i);
                    $process = new Process("php application.php cz:main-11 --url='$uri'");
                    $process->start();

                    $activeProcess[] = $process;

                    var_dump("$key link is processed, now $i page is processed");

                    /** Cleaning memory of useless processes */
                    $this->processControl($activeProcess);

                } catch (\Exception $e) {
                    echo "\n\n\n\n\n\t\t\t" . $e->getMessage();
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
     * @param $link
     * @return int
     * Returns total pages from category
     */
    public function getTotalPages(string $link) : int
    {
        $guzzle = new GuzzleWrap();

        try {
            $crawler = new Crawler($guzzle->getContent($link));
            $filter = $crawler->filterXPath("//ul[@class='pagination']")->children()->count();
            $totalPages = $crawler->filter('.pagination > li')->eq($filter - 2)->text();
            return (int)$totalPages;
        }
        catch (\Exception $e){
            return 1;
        }
    }

    /**
     * @param string $category
     * @param int $page
     * @return string
     */
    protected function convertLink(string $category, int $page=1) : string
    {
        return urldecode("https://www.zivefirmy.cz/?q=$category&pg=$page");
    }
}