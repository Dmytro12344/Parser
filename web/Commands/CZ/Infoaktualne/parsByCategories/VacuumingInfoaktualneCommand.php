<?php

namespace Commands\CZ\Infoaktualne\parsByCategories;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Wraps\GuzzleWrap;

class VacuumingInfoaktualneCommand extends Command
{
    /**
     * Command config
    */
    protected function configure() : void
    {
        $this->setName('cz:vacuuming-21')
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
        $totalRecords = $this->getTotalRecords($crawler);

        for($i = 0; $i < $totalRecords; $i++) {
            $result = [
                'category' => trim($this->getCategory($crawler)),
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
     * @return int
    */
    protected function getTotalRecords(Crawler $crawler) : int
    {
        try{
            return $crawler->filter('.cataloquelist')->children()->count();
        }catch (\Exception $e){
            return 0;
        }
    }

    /**
     * @param Crawler $crawler
     * @return string
    */
    protected function getCategory(Crawler $crawler) : string
    {
        try{
            return $crawler->filter('.search-wrap > input')->attr('value');
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
            return $crawler->filter('.item__title > a')->eq($k)->text();
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
            $cityLists = file('web/Commands/CZ/listOfCity', FILE_SKIP_EMPTY_LINES);
            $postalLists = file('web/Commands/CZ/listOfPostal', FILE_SKIP_EMPTY_LINES);
            $filter = $crawler->filter('.item__address')->eq($k)->text();
            $postalFilter = explode(' ', $filter);
            $street = '';

            for($i = 0; $i < @count($postalFilter); $i++){
                foreach ($postalLists as $item) {
                    if($postalFilter[$i] . @$postalFilter[$i+1] === trim($item)){
                        for($j = 0; $j < $i; $j++){
                            $street .= $postalFilter[$j] . ' ';
                        }
                        return trim($street);
                    }
                }
            }
            foreach ($cityLists as $item) {
                if(strpos($filter, trim($item))){
                    $street = explode(trim($item), $filter);
                    return $street[0];
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
        try {
            $lists = file('web/Commands/CZ/listOfPostal', FILE_SKIP_EMPTY_LINES);
            $filter = str_replace(' ', '', $crawler->filter('.item__address')->eq($k)->text());

            foreach ($lists as $item) {
                if(strpos($filter, trim($item))) {
                    return $item;
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
            $lists = file('web/Commands/CZ/listOfCity', FILE_SKIP_EMPTY_LINES);
            $filter = str_replace(' ', '', $crawler->filter('.item__address')->eq($k)->text());

            foreach($lists as $item){
                if(strpos($filter, trim($item))){
                    return $item;
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
            if($crawler->filterXPath("//ul[@class='item__meta']")->eq($k)->filter('.item--meta')->eq(1)->count() > 0){
                $telephone = $crawler->filterXPath("//ul[@class='item__meta']")->eq($k)
                    ->filter('.item--meta')->eq(1)->text();
                $telephone = str_replace(' ', '', $telephone);

                if(is_numeric($telephone)){
                    return $telephone;
                }
            }
            if($crawler->filterXPath("//ul[@class='item__meta']")->eq($k)->filter('.item--meta')->eq(0)->count() > 0){
                $telephone = $crawler->filterXPath("//ul[@class='item__meta']")->eq($k)
                    ->filter('.item--meta')->eq(0)->text();
                $telephone = str_replace(' ', '', $telephone);

                if(is_numeric($telephone)){
                    return $telephone;
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
    protected function getEmail(Crawler $crawler, int $k) : string
    {
        try{
            if($crawler->filterXPath("//ul[@class='item__meta']")->eq($k)->filter('.item--meta')->eq(2)->count() > 0){
                $site = $crawler->filterXPath("//ul[@class='item__meta']")->eq($k)
                    ->filter('.item--meta')->eq(2)->text();

                $position = strrpos($site, '@');

                if($position !== false){
                    return $site;
                }
            }
            if($crawler->filterXPath("//ul[@class='item__meta']")->eq($k)->filter('.item--meta')->eq(1)->count() > 0){
                $site = $crawler->filterXPath("//ul[@class='item__meta']")->eq($k)
                    ->filter('.item--meta')->eq(1)->text();

                $position = strrpos($site, '@');

                if($position !== false){
                    return $site;
                }
            }
            if($crawler->filterXPath("//ul[@class='item__meta']")->eq($k)->filter('.item--meta')->eq(0)->count() > 0){
                $site = $crawler->filterXPath("//ul[@class='item__meta']")->eq($k)
                    ->filter('.item--meta')->eq(0)->text();

                $position = strrpos($site, '@');

                if($position !== false){
                    return $site;
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
    protected function getSite(Crawler $crawler, int $k) : string
    {
        try{
            $site = $crawler->filterXPath("//ul[@class='item__meta']")->eq($k)->filter('.item--meta')->eq(0)->text();
            $position = strrpos($site, 'www.');
            if($position !== false){
                return $site;
            }
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
        $stream = fopen('parsed.csv', 'a');
        foreach($arr as $item) {
            fputcsv($stream, $item, '|');
        }
        fclose($stream);
    }
}