<?php


namespace Commands\CZ\Rejstrik\async;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Wraps\GuzzleWrap;

class RejstrikVacuumingCommand extends Command
{
    protected function configure()
    {
        $this->setName('cz:vacuuming-41')
            ->setDescription('Starts download')
            ->setHelp('This command allow you start the script')
            ->addOption('url', 'u', InputOption::VALUE_REQUIRED, 'needed url for parsing');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $guzzle = new GuzzleWrap();

        try {
            $crawler = new Crawler($guzzle->getContent($input->getOption('url')));
            $totalRecords = $this->getTotalRecords($crawler);

            for ($i = 0; $i < $totalRecords; $i++) {

                $result = [
                    'category' => trim($this->getCategory($crawler)),
                    'name' => trim($this->getCompanyName($crawler, $i)),
                    'address' => trim($this->getStreet($crawler, $i)),
                    'postal' => trim($this->getPostal($crawler, $i)),
                    'city' => trim($this->getCity($crawler, $i)),
                    'phone' => '',
                    'email' => '',
                    'site' => '',
                ];

                var_dump($result);
                if($result['name'] !== '' && $result['address'] !== '' && $result['postal'] !== '') {
                    $this->writeToFile([$result]);
                }
            }
        }catch(\Exception $e){
            echo $e->getMessage();
        }
    }

    protected function getCategory(Crawler $crawler) : string
    {
        try{
            return $crawler->filterXPath("//input[@type='text']")->attr('value');
        }catch (\Exception $e){
            return '';
        }
    }


    protected function getCompanyName(Crawler $crawler, int $k): string
    {
        try {
            return $crawler->filter('ul > li')->eq($k)->filter('a')->text();
        } catch (\Exception $e) {
            return '';
        }
    }


    protected function getStreet(Crawler $crawler, int $k) : string
    {
        try {
            $filter = $crawler->filter('ul > li')->eq($k)->html();
            $filter = explode('<br>', $filter);
            $street = '';

            if(strpos($filter[1], 'Adresa: ')){
                $fullAddress = str_replace('Adresa: ', '', $filter[1]);
                $fullAddress = explode(' ', trim($fullAddress));

                for($i = 0; $i < @count($fullAddress); $i++){
                    if(is_numeric($fullAddress[$i]) && is_numeric($fullAddress[$i+1])){
                        if($fullAddress[$i - 1] !== 'PSČ') {
                            for ($j = 0; $j <= $i - 1; $j++) {
                                $street .= $fullAddress[$j] . ' ';
                            }

                            if(strpos($street, ',')){
                                $street = explode(',', $street);
                                return $street[0];
                            }

                            return $street;
                        }

                        for($j = 0; $j <= $i - 2; $j++){
                            $street .= $fullAddress[$j] . ' ';
                        }

                        if(strpos($street, ',')){
                            $street = explode(',', $street);
                            return $street[1];
                        }
                        return $street;
                    }
                }
            }

            return '';
        } catch (\Exception $e) {
            return '';
        }
    }

    public function getCity(Crawler $crawler, int $k) : string
    {
        try {
            $filter = $crawler->filter('ul > li')->eq($k)->html();
            $filter = explode('<br>', $filter);
            $city = '';
            if(strpos($filter[1], 'Adresa: ')){
                $fullAddress = str_replace('Adresa: ', '', $filter[1]);
                $fullAddress = explode(' ', trim($fullAddress));

                for($i = 0; $i < @count($fullAddress); $i++){
                    if(is_numeric($fullAddress[$i]) && is_numeric($fullAddress[$i+1])){
                        for ($j = $i + 2; $j < @count($fullAddress); $j++){
                            $city .= $fullAddress[$j] . ' ';
                        }
                        return $city;
                    }
                }
            }
        return '';
        }
        catch (\Exception $e) {
            return '';
        }
    }

    public function getPostal(Crawler $crawler, int $k) : string
    {
        try {
            $filter = $crawler->filter('ul > li')->eq($k)->html();
            $filter = explode('<br>', $filter);

            if(strpos($filter[1], 'Adresa: ')){
                $fullAddress = str_replace('Adresa: ', '', $filter[1]);
                $fullAddress = explode(' ', trim($fullAddress));

                for($i = 0; $i < @count($fullAddress); $i++){
                    if(is_numeric($fullAddress[$i]) && is_numeric($fullAddress[$i+1])){
                        return $fullAddress[$i] . $fullAddress[$i +1];
                    }
                }
            }

            return '';
        } catch (\Exception $e) {
            return '';
        }
    }

    protected function getPhone(Crawler $crawler, int $k) : string
    {
    }

    protected function getTotalRecords(Crawler $crawler) : int
    {
        try {
            return $crawler->filterXPath("//ul[@style='list-style-type:none']")->children()->count();
        }catch (\Exception $e){
            return 0;
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