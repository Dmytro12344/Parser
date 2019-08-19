<?php

namespace Commands\GR\Vrisko\parsByCategories;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Input\InputOption;
use Wraps\GuzzleWrap;

class VacuumingVriskoCommand extends Command
{
    /**
     * Command config
    */
    protected function configure() : void
    {
        $this->setName('gr:vacuuming-1')
            ->setDescription('Starts download from https://www.vrisko.gr')
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
        $totalRecords = $this->getTotalRecords($crawler);

        for($i = 0; $i < $totalRecords; $i++) {
            $result = [
                'category' => trim($this->getCategory($crawler, $i)),
                'name' => trim($this->getCompanyName($crawler, $i)),
                'address' => trim($this->getStreet($crawler, $i)),
                'postal' => trim($this->getPostal($crawler, $i)),
                'city' => trim($this->getCity($crawler, $i)),
                'phone' => trim($this->getPhone($crawler, $i)),
                'email' => trim($this->getEmail($crawler, $i)),
                'site' => trim($this->getSite($crawler, $i)),
            ];
            var_dump($result);
            if($result['name'] !== '' && $result['address'] !== '' && $result['postal'] !== '') {
                $this->writeToFile([$result]);
            }
        }
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return array|string
     */
    protected function getExistText(Crawler $crawler, int $k) : array
    {
        try {
            $blockResult = $crawler->filter('#SearchResults');

            switch ($blockResult) {
                case $blockResult->filter('.AdvItemBox')->eq($k) :
                    return ['crawler' => $blockResult->filter('.AdvItemBox')->eq($k), 'className' => '.AdvItemBox'];
                    break;
                case $blockResult->filter('.FreeListingItemBox')->eq($k) :
                    return ['crawler' => $blockResult->filter('.FreeListingItemBox')->eq($k), 'className' => '.FreeListingItemBox'];
                    break;
            }
            return [];
        } catch (\Exception $e){
            return [];
        }

    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getTotalRecords(Crawler $crawler) : string
    {
        try{
            return (int)$crawler->filter('.FreeListingItemBox')->count();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
    */
    protected function getCategory(Crawler $crawler, int $k) : string
    {
        try{
            $blockType = $this->getExistText($crawler, $k);
            return $blockType['crawler']->filter('.AdvCategory')->text();
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
    */
    protected function getCompanyName(Crawler $crawler, int $k) : string
    {
        try{
            $content = $this->getExistText($crawler, $k);

            if($content['className'] === '.FreeListingItemBox') {
                return $content['crawler']->filter('.CompanyName > a > span')->text();
            }
            if($content['className'] === '.AdvItemBox'){
                return $content['crawler']->filter('.CompanyName > a')->text();
            }
            return '';
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
    */
    protected function getStreet(Crawler $crawler, int $k) : string
    {
        try{
            $content = $this->getExistText($crawler, $k);

            if($content['className'] === '.FreeListingItemBox') {
                $filter = $content['crawler']->filter('.FreeListingAddress')->text();
                if (strpos($filter, ',')) {
                    $filter = explode(',', $filter);
                    return $filter[0];
                }
            }
            if($content['className'] === '.AdvItemBox') {
                $filter = $content['crawler']->filter('.AdvAddress')->text();
                if (strpos($filter, ',')) {
                    $filter = explode(',', $filter);
                    return $filter[0];
                }
            }
            return '';
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
    */
    protected function getPostal(Crawler $crawler, int $k) : string
    {
        try{
            $content = $this->getExistText($crawler, $k);

            if($content['className'] === '.FreeListingItemBox') {
                $filter = $content['crawler']->filter('.FreeListingAddress')->text();
                if (strpos($filter, ',')) {
                    $filter = explode(',', $filter);
                    for ($i = 0; $i < @count($filter); $i++) {
                        if (is_numeric($filter[$i]) && strlen($filter[$i]) > 4) {
                            return $filter[$i];
                        }
                    }
                }
            }
            if($content['className'] === '.AdvItemBox') {
                $filter = $content['crawler']->filter('.AdvAddress')->text();
                if (strpos($filter, ',')) {
                    $filter = explode(',', $filter);
                    for ($i = 0; $i < @count($filter); $i++) {
                        if (is_numeric($filter[$i]) && strlen($filter[$i]) > 4) {
                            return $filter[$i];
                        }
                    }
                }
            }
            return '';
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
    */
    protected function getCity(Crawler $crawler, int $k) : string
    {
        try{
            $content = $this->getExistText($crawler, $k);

            if($content['className'] === '.FreeListingItemBox') {
                $filter = $content['crawler']->filter('.FreeListingAddress')->text();
                if (strpos($filter, ',')) {
                    $filter = explode(',', $filter);
                    if (strpos($filter[1], '-')) {
                        $filter = explode('-', $filter[1]);
                        return $filter[0];
                    }
                    return $filter[1];
                }
            }
            if($content['className'] === '.AdvItemBox') {
                $filter = $content['crawler']->filter('.AdvAddress')->text();
                if (strpos($filter, ',')) {
                    $filter = explode(',', $filter);
                    if (strpos($filter[1], '-')) {
                        $filter = explode('-', $filter[1]);
                        return $filter[0];
                    }
                    return $filter[1];
                }
            }

            return '';
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
    */
    protected function getPhone(Crawler $crawler, int $k) : string
    {
        try{
            $content = $this->getExistText($crawler, $k);
            return $crawler->filter($content['className'] . ' + .PhonesBox')->eq($k)->text();
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
    */
    protected function getEmail(Crawler $crawler, int $k) : string
    {
        try{
            return '';
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param Crawler $crawler
     * @param int $k
     * @return string
    */
    protected function getSite(Crawler $crawler, int $k) : string
    {
        try{
            return '';
        }catch (\Exception $e){
            return '';
        }
    }

    /**
     * @param array $arr
     * Writes to file
    */
    public function writeToFile(array $arr) : void
    {
        $stream = fopen('parsed2.csv', 'a');
        foreach($arr as $item) {
            fputcsv($stream, $item, '|');
        }
        fclose($stream);
    }
}