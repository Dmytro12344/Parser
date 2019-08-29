<?php

namespace Commands\RS\Info011\profileAndLinks;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Input\InputOption;
use Wraps\GuzzleWrap;

class VacuumingInfo011Command extends Command
{
    /**
     * Command config
    */
    protected function configure() : void
    {
        $this->setName('rs:vacuuming-22')
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
        $guzzle = new GuzzleWrap();
        $crawler = new Crawler($guzzle->getContent($input->getOption('url')));

        $result =
            trim($this->getCompanyName($crawler)) . '}##{' .
            trim($this->getStreet($crawler)) . '}##{' .
            trim($this->getCity($crawler)) . '}##{' .
            trim($this->getPostal($crawler)) . '}##{' .
            trim($this->getPhone($crawler)) . '}##{' . PHP_EOL;

        var_dump($result);
        $this->writeToFile($result);

        /*for($i = 0; $i< $this->getTotalLinks($crawler); $i++){
            $result = $this->getLinks($crawler, $i);
            $this->writeToFile($result);
        }*/
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
     *
     * Collect links from pages
     */
    protected function getLinks(Crawler $crawler, int $k) : string
    {
        return 'https://www.011info.com' . $crawler->filter('.business-subcategories > li')->eq($k)->filter('a')->attr('href') . PHP_EOL;
    }

    /**
     * @param Crawler $crawler
     * @return int
     *
     * Return total links from page
     */
    protected function getTotalLinks(Crawler $crawler) : int
    {
        return $crawler->filter('.business-subcategories > li')->count();
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getCompanyName(Crawler $crawler) : string
    {
        try{
            return $crawler->filter('.b-title')->eq(0)->text();
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getStreet(Crawler $crawler) : string
    {
        try{
            $filter = str_replace('Adresa: ', '',$crawler->filter('.b-info > li')->eq(0)->text());
            $filter = explode(',', $filter);

            if(strpos($filter[0], '/')){
                $filter = explode('/', $filter[0]);
                if(strpos($filter[0], '(')){
                    $filter = explode('(', $filter[0]);
                    return $filter[0];
                }

                if(strpos('(', $filter[0])){
                    $filter = explode('(', $filter[0]);
                    return $filter[0];
                }

                return $filter[0];
            }

            if(strpos('(', $filter[0])){
                $filter = explode('(', $filter[0]);
                return $filter[0];
            }

            return $filter[0];
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getPostal(Crawler $crawler) : string
    {
        try{
            return '';
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getCity(Crawler $crawler) : string
    {
        try{
            $listOfCities = file('web/Commands/RS/listOfRSCity.txt', FILE_SKIP_EMPTY_LINES);
            $filter = str_replace('Adresa: ', '',$crawler->filter('.b-info > li')->eq(0)->text());

            foreach($listOfCities as $city){
                if(strpos($filter, $city)){
                    return $city;
                }
            }

            $filter = explode(',', $filter);
            if(strpos($filter[1], '+')){
                $filter = explode('+', $filter[1]);

                if(strpos($filter[0], '/')){
                    $filter = explode('/', $filter[0]);
                    return $filter[0];
                }
                return $filter[0];
            }

            if(strpos($filter[1], '/')){
                $filter = explode('/', $filter[0]);
                return $filter[0];
            }




            return $filter[1];
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getPhone(Crawler $crawler) : string
    {
        try{
            $filter = $crawler->filter('.b-info > li')->eq(1)->filter('a')->text();

            if(strpos($filter, ';')){
                $filter = explode(';', $filter);
                return preg_replace('/[\D]/', '', $filter[0]);
            }

            return preg_replace('/[\D]/', '', $filter);
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param string $str
     * Writes to file
     */
    public function writeToFile(string $str) : void
    {
        $stream = fopen('parsed3.csv', 'a');
        fwrite($stream, $str);
        fclose($stream);
    }
}