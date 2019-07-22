<?php


namespace Commands\RS\Privredni\async;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;


class PrivredniParserCommand extends Command
{


    /**
     * Command config
     */
    protected function configure() : void
    {
        $this->setName('rs:start-1')
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
        $links = file('web/Commands/RS/Privredni/async/list.txt', FILE_SKIP_EMPTY_LINES);
        $activeProcess = [];

        foreach($links as $key => $link){

            $total_pages = $this->getTotalPages(trim($link.'1'));

            for ($i = 1; $i <= $total_pages; $i++) {
                $uri = trim($link) . $i;

                try {
                    $process = new Process("php application.php rs:vacuuming-1 --url=$uri");
                    $process->start();
                    $activeProcess[] = $process;

                    var_dump($activeProcess);

                    var_dump("$key link is processed, now $i page is processed");

                    /** Cleaning memory of useless processes */
                    $this->processControl($activeProcess);
                } catch (ProcessFailedException $e) {
                    $output->writeln([
                        $e->getMessage(),
                    ]);
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
     * @param $url
     * @return int
     * Returns total pages from category
     */
    public function getTotalPages($url) : int
    {
        $guzzle = new GuzzleWrap();
        $crawler = new Crawler($guzzle->getContent($url));
        $count = $crawler->filter('.mb30 > .pagination')->eq(3)->children()->count();

        return (int)$crawler->filter('.pagination')->eq(3)->filter('li')->eq($count - 2)->text();
    }






}