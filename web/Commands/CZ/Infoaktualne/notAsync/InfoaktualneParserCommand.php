<?php


namespace Commands\CZ\Infoaktualne\notAsync;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Wraps\GuzzleWrap;


class InfoaktualneParserCommand extends Command
{
    /**
     * Command config
     */
    protected function configure() : void
    {
        $this->setName('start-21')
            ->setDescription('Starts download from www.najisto.centrum.cz')
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
        $links = file('web/Commands/CZ/Infoaktualne/async/list.txt', FILE_SKIP_EMPTY_LINES);

        foreach($links as $link){


                for ($j = 1; $j <= 7; $j++) {
                    $crawlerHelper = new Crawler($guzzle->getContent(trim($link) . $j));
                    $totalRecords = $this->getTotalRecords($crawlerHelper);

                    try {

                        for ($i = 0; $i <= $totalRecords; $i++) {


                            $result = array_values([
                                'category' => trim($this->getCategory($crawlerHelper)),
                                'name' => trim($this->getCompanyName($crawlerHelper, $i)),
                                'street' => trim($this->getAddress($crawlerHelper, $i)),
                                'postal' => trim($this->getPostal($crawlerHelper, $i)),
                                'city' => trim($this->getCity($crawlerHelper, $i)),
                                'phone' => trim($this->getPhone($crawlerHelper, $i)),
                                'email' => trim($this->getEmail($crawlerHelper, $i)),
                                'site' => trim($this->getSite($crawlerHelper, $i)),
                            ]);
                            var_dump("link number $j, page number $i");
                            $this->writeToFile([$result]);
                        }
                    }
                    catch (\Exception $e){
                        continue;
                    }
                }
        }
    }

    protected function getCategory(Crawler $crawler) : string
    {
        if($crawler->filterXPath("//span[@class='item__title']")->count() > 0){
            return $crawler->filterXPath("//span[@class='item__title']")->text();
        }

        return '';
    }

    protected function getEmail(Crawler $crawler, int $k) : string
    {
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
    }

    protected function getSite(Crawler $crawler, int $k) : string
    {
        if($crawler->filterXPath("//ul[@class='item__meta']")->eq($k)->filter('.item--meta')->eq(0)->count() > 0){
            $site = $crawler->filterXPath("//ul[@class='item__meta']")->eq($k)
                ->filter('.item--meta')->eq(0)->text();

            $position = strrpos($site, 'www.');

            if($position !== false){
                return $site;
            }
        }

        return '';
    }


    protected function getCompanyName(Crawler $crawler, int $k) : string
    {
        return $crawler->filter('.item__title > a')->eq($k)->text();
    }

    protected function getCity(Crawler $crawler, int $k) : string
    {
        if($crawler->filter('.item__address')->eq($k)->count()) {
            $filter = $crawler->filter('.item__address')->eq($k)->text();
            $filter = explode(' ', $filter);
            $pos = 0;
            $city = '';

            for($i = 0; $i < @count($filter); $i++) {
                if (is_numeric($filter[$i]) && is_numeric($filter[$i + 1]) && is_numeric($filter[$i + 2])) {
                    $pos = $i + 3;
                    break;
                }

                if (!is_numeric($filter[$i]) && is_numeric($filter[$i + 1]) && is_numeric($filter[$i + 2])) {
                    $pos = $i + 3;
                    break;
                }
            }

            if($pos > 0){
                for($i = $pos; $i < @count($filter); $i++){
                    $city .= $filter[$i] . ' ';
                }
                return $city;
            }
            return '';
        }
        return '';
    }

    protected function getAddress(Crawler $crawler, $k) : string
    {
        if($crawler->filter('.item__address')->eq($k)->count()) {
            $filter = $crawler->filter('.item__address')->eq($k)->text();
            $filter = explode(' ', $filter);
            $pos = 0;
            $street = '';

            for($i = 0; $i < @count($filter); $i++) {
                if (is_numeric($filter[$i]) && is_numeric($filter[$i + 1]) && is_numeric($filter[$i + 2])) {
                    $pos = $i;
                    break;
                }

                if (!is_numeric($filter[$i]) && is_numeric($filter[$i + 1]) && is_numeric($filter[$i + 2])) {
                    $pos = $i;
                    break;
                }
            }

            if($pos > 0){
                for($i = 0; $i <= $pos; $i++){
                    $street .= $filter[$i] . ' ';
                }
                return $street;
            }
            return '';
        }
        return '';
    }

    protected function getPostal(Crawler $crawler, int $k) : string
    {
        if($crawler->filter('.item__address')->eq($k)->count()){
            $filter = $crawler->filter('.item__address')->eq($k)->text();
            $filter = explode(' ', $filter);

            for($i = 0; $i < @count($filter); $i++){
                if(is_numeric($filter[$i]) && is_numeric($filter[$i +1]) && is_numeric($filter[$i + 2])){
                    return $filter[$i + 1] . $filter[$i + 2];
                }

                if(!is_numeric($filter[$i]) && is_numeric($filter[$i +1]) && is_numeric($filter[$i + 2])){
                    return $filter[$i + 1] . $filter[$i + 2];
                }
            }
            return '';
        }
        return '';
    }

    public function getPhone(Crawler $crawler, int $k) : string
    {
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
    }


    /**
     * @param Crawler $crawler
     * @return int
     * Returns total records from page
     */
    public function getTotalRecords(Crawler $crawler) : int
    {
        return $crawler->filter('.cataloquelist')->children()->count();
    }

    /**
     * @param array $arr
     * Writes to file
     */
    public function writeToFile(array $arr) : void
    {
        $stream = fopen('parsed1.csv', 'a');
        foreach($arr as $item) {
            fputcsv($stream, $item, '|');
        }
        fclose($stream);
    }


}