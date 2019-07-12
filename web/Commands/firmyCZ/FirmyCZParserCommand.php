<?php


namespace Commands\firmyCZ;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DomCrawler\Crawler;
use Wraps\GuzzleWrap;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;


class FirmyCZParserCommand extends Command
{
    /**
     * Command config
     */
    protected function configure() : void
    {
        $this->setName('start-1')
            ->setDescription('Starts download from www.firmy.cz')
            ->setHelp('This command allow you start the script');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * Main parsed process (start stream)
     */
    protected function execute(InputInterface $input, OutputInterface $output) : void
    {
        $guzzle = new GuzzleWrap();
        $links = file('list.txt', FILE_SKIP_EMPTY_LINES);

        foreach($links as $key => $link){
            $crawler = new Crawler($guzzle->getContent($link . $key));
            $totalPage = $this->getTotalPages($crawler);

                $pool = new Pool($guzzle->Client(), $this->getContent($totalPage), [
                    'concurrency' => 5,
                    'fulfilled' => function ($response, $index) {

                        $crawler = new Crawler($response->getBody()->getContents());
                        $totalRecords = $this->getTotalRecords($crawler);
                        var_dump($index);
                        for($i = 0; $i <= $totalRecords; $i++){


                            //$name = $this->getCompanyName($crawler, $i);
                            //var_dump($name);


                        }




                        var_dump($name);

                    },
                    'rejected' => function ($reason, $index) {
                        var_dump("$index REJECTED");
                    },
                ]);

                $promise = $pool->promise();
                $promise->wait();

        }






    }

    /**
     * @param $crawler
     * @param int $k
     * @return string
     */
    protected function getCompanyName(Crawler $crawler, int $k) : string
    {
        $filter = $crawler->filter('.list-listing > .row > .col-xs-9 > ')->eq($k);
        return $filter->filter('h3 > a')->text();
    }

    /**
     * @param Crawler $crawler
     * @return int
     */
    protected function getTotalPages(Crawler $crawler) : int
    {
        $key = $crawler->filter('.pagination')->children()->count();
        $filter = $crawler->filter('.pagination > li')->eq($key - 3)->text();

        /** Total pages from pagination */
        $total_page = (int)$filter / 14;
        return ceil($total_page);
    }

    public function getTotalRecords(Crawler $crawler) : int
    {
        return $crawler->filter('.list-results-twocol')->children()->count();
    }

    /**
     * @param $total
     * @param $crawler
     * @return \Generator
     * Generate new Request to get information from profile
     */
    public function getProfile($total, $crawler) : \Generator
    {
        $uri = 'https://www.zlatestranky.cz';

        for ($k = 0; $k < $total; $k++) {
            $link = $uri . $crawler->filter('.h3 > a ')->eq($k)->attr('href');
            yield new Request('GET', $link);
        }
    }


}