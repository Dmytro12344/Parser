<?php


namespace Commands\RS\Companywall\asyncWithProfile;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;


class CompanywallParserCommand extends Command
{
    /**
     * Command config
     */
    protected function configure() : void
    {
        $this->setName('rs:start-3')
            ->setDescription('Starts download from http://www.companywall.rs')
            ->setHelp('This command allow you start the script');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * Main parsed process (start stream)
     */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        //$links = file('web/Commands/RS/Companywall/asyncWithProfile/list.txt', FILE_SKIP_EMPTY_LINES);
        $url = 'https://www.companywall.rs/pretraga?p=';
        $activeProcess = [];

        //foreach($links as $key => $link){
            try {

                $total_pages = $this->getTotalPages($this->convertLink($url));

                for ($i = 1; $i <= $total_pages; $i++) {
                    $uri = $this->convertLink($url, $i);
                    var_dump($uri);
                    $process = new Process("php application.php rs:main-3 --url='$uri'");
                    $process->start();

                    $activeProcess[] = $process;

                    var_dump("Now $i page is processed");

                    /** Cleaning memory of useless processes */
                    $this->processControl($activeProcess);

                    if($i === $total_pages){
                        sleep(60);
                    }
                }

            } catch (ProcessFailedException $e) {
                $output->writeln([
                    $e->getMessage(),
                ]);
            }
        //}
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
     * @param $url
     * @return int
     * Returns total pages from category
     */
    public function getTotalPages($url) : int
    {
        $guzzle = new GuzzleWrap();
        $crawler = new Crawler($guzzle->getContent(urldecode($url)));

        try {

            return  (int)$crawler->filter('.PagedList-skipToLast')->text();

        } catch(\Exception $e){
            return 1;
        }
    }

    protected function convertLink(string $link, int $page=1) : string
    {
        return $link . $page;
    }

}