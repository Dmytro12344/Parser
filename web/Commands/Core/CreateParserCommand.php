<?php

namespace Commands\Core;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;


class CreateParserCommand extends Command
{
    protected const DS = '\\';

    protected const _DS = '/';

    protected const SINGLE_TAB = '    ';

    protected const DOUBLE_TAB = self::SINGLE_TAB . self::SINGLE_TAB;

    protected const TRIPLE_TAB = self::SINGLE_TAB . self::DOUBLE_TAB;

    protected const DD_TAB = self::DOUBLE_TAB . self::DOUBLE_TAB;

    /**
     * Command config
     */
    protected function configure(): void
    {
        $this->setName('create:new:parser')
            ->setDescription('Creates new parser')
            ->setHelp('This command create new parser');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * Main parsed process (start stream)
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        //echo $this->justLinks('async', 'CZ', 'Zlatasrashki');die;

        /** Set project type */
        $helperType = $this->getHelper('question');
        $questionType = new ChoiceQuestion(
            'Please, choice needed type of parser',
            [
                'empty',
                'with profiles and categories',
                'with profiles and links',
                'just categories',
                'just links',
            ]);
        $questionType->setErrorMessage('Type %s is invalid.');
        $type = $helperType->ask($input, $output, $questionType);

        /** Set project country */
        $helper = $this->getHelper('question');
        $questionCountry = new ChoiceQuestion(
            'Please, choice needed country of parser',
            array('CZ', 'PL', 'RO')
        );
        $questionCountry->setErrorMessage('Country %s is invalid.');
        $country = $helper->ask($input, $output, $questionCountry);


        /** Set project name */
        $questionName = new Question("Please enter the name of the Parser \n", '');
        $name = $helper->ask($input, $output, $questionName);


        $parserNameDir = 'web/Commands/' . $country . '/' . $name;
        $parserTypeDir = 'web/Commands/' . $country . '/' . $name . '/' . $this->convertType($type)['dir'];
        $this->dirControl($parserNameDir, $parserTypeDir);
        $this->filesControl($type, $country, $name);
    }

    protected function filesControl(string $type, string $country, string $name): void
    {
        $files = $this->generatePHPFiles($type, $country, $name);
        foreach($files as $file){
            try {
                if (!file_exists($file['path'])) {
                    $fp = fopen($file['path'], 'w+');
                    fwrite($fp, $file['content']);
                    fclose($fp);
                    echo 'file ' . $file['path'] . ' successfully crated' . PHP_EOL;
                }
            }catch (\Exception $e){
                echo $e->getMessage();
            }
        }
    }

    protected function generatePHPFiles(string $type, string $country, string $name): array
    {
        if ($this->convertType($type)['type'] === 'link') {
            return [
                1 => [
                    'content' => $this->parserFileContent($this->convertType($type)['dir'], $country, $name),
                    'path' => 'web/Commands/' . $country . '/' . $name . '/' . $this->convertType($type)['dir'] . '/' . $name . 'ParserCommand.php',
                    ],
                2 => [
                    'content' => $this->justLinksVacuuming($this->convertType($type)['dir'], $country, $name),
                    'path' => 'web/Commands/' . $country . '/' . $name . '/' . $this->convertType($type)['dir'] . '/' . 'Vacuuming'. $name .'Command.php',
                ],
                3 => [
                    'content' => '',
                    'path' => 'web/Commands/' . $country . '/' . $name . '/' . $this->convertType($type)['dir'] . '/list.txt',
                ],
            ];
        }

        if($this->convertType($type)['type'] === 'cat'){
            return [
                1 => [
                    'content' => $this->parserFileContent($this->convertType($type)['dir'], $country, $name),
                    'path' => 'web/Commands/' . $country . '/' . $name . '/' . $this->convertType($type)['dir'] . '/' . $name . 'ParserCommand.php',
                ],
                2 => [
                    'content' => $this->justLinksVacuuming($this->convertType($type)['dir'], $country, $name),
                    'path' => 'web/Commands/' . $country . '/' . $name . '/' . $this->convertType($type)['dir'] . '/' . 'Vacuuming'. $name .'Command.php',
                ],
                3 => [
                    'content' => '',
                    'path' => 'web/Commands/' . $country . '/' . $name . '/' . $this->convertType($type)['dir'] . '/list.txt',
                ],
            ];
        }



        return [];
    }

    protected function parserFileContent(string $type, string $country, string $name): string
    {
        $file  = $this->getFileHeader($type, $country, $name);
        $file .= 'class '. $name . 'ParserCommand extends Command' . PHP_EOL . '{' . PHP_EOL ;
        $file .= $this->getStarterConfigureMethod();

        /** Start execute method */
        $file .= self::SINGLE_TAB . '/**' . PHP_EOL;
        $file .= self::SINGLE_TAB . ' * @param InputInterface $input' . PHP_EOL;
        $file .= self::SINGLE_TAB . ' * @param OutputInterface $output' . PHP_EOL;
        $file .= self::SINGLE_TAB . ' * Main parsed process (start stream)' . PHP_EOL;
        $file .= self::SINGLE_TAB . '*/' . PHP_EOL;
        $file .= self::SINGLE_TAB . 'protected function execute(InputInterface $input, OutputInterface $output) : void' . PHP_EOL;
        $file .= self::SINGLE_TAB . '{' . PHP_EOL;

        if($type === 'parsByCategories') {
            $file .= self::DOUBLE_TAB . '$categories = file(\'web/Commands/'.$country . self::_DS .$name . self::_DS . $type . self::_DS . 'list.txt\', FILE_SKIP_EMPTY_LINES);' . PHP_EOL;
            $file .= self::DOUBLE_TAB . '$activeProcess = [];' . PHP_EOL;
            $file .= self::DOUBLE_TAB . 'foreach($categories as $key => $category){' . PHP_EOL;
            $file .= self::TRIPLE_TAB . 'try{' . PHP_EOL;
            $file .= self::DD_TAB . '$totalPages = $this->getTotalPages($this->convertLink(trim($category)));' . PHP_EOL . PHP_EOL;
            $file .= self::DD_TAB . 'for($i = 1; $i <= $totalPages; $i++){' . PHP_EOL;
            $file .= self::DD_TAB . self::SINGLE_TAB . '$uri = $this->convertLink(trim($category), $i);' . PHP_EOL;
        } else {
            $file .= self::DOUBLE_TAB . '$links = file(\'web/Commands/'.$country . self::_DS .$name . self::_DS . $type . self::_DS . 'list.txt\', FILE_SKIP_EMPTY_LINES);' . PHP_EOL;
            $file .= self::DOUBLE_TAB . '$activeProcess = [];' . PHP_EOL;
            $file .= self::DOUBLE_TAB . 'foreach($links as $key => $link){' . PHP_EOL;
            $file .= self::TRIPLE_TAB . 'try{' . PHP_EOL;
            $file .= self::DD_TAB . '$totalPages = $this->getTotalPages($this->convertLink(trim($link)));' . PHP_EOL . PHP_EOL;
            $file .= self::DD_TAB . 'for($i = 1; $i <= $totalPages; $i++){' . PHP_EOL;
            $file .= self::DD_TAB . self::SINGLE_TAB . '$uri = $this->convertLink(trim($link), $i);' . PHP_EOL;
        }

        $file .= self::DD_TAB . self::SINGLE_TAB . '$process = new Process("php application.php rs:vacuuming-1 --url=\'$uri\'");' . PHP_EOL;
        $file .= self::DD_TAB . self::SINGLE_TAB . '$process->start();' . PHP_EOL;
        $file .= self::DD_TAB . self::SINGLE_TAB . '$activeProcess[] = $process;' . PHP_EOL;
        $file .= self::DD_TAB . self::SINGLE_TAB . 'var_dump("$key link is processed, now $i page is processed");' . PHP_EOL . PHP_EOL;
        $file .= self::DD_TAB . self::SINGLE_TAB . '/** Cleaning memory of useless processes */' . PHP_EOL;
        $file .= self::DD_TAB . self::SINGLE_TAB . '$this->processControl($activeProcess);' . PHP_EOL . PHP_EOL;

        if($type === 'parsByCategories') {
            $file .= self::DD_TAB . self::SINGLE_TAB . 'if($i === $totalPages && $key === count($categories) - 1){' . PHP_EOL;
        } else {
            $file .= self::DD_TAB . self::SINGLE_TAB . 'if($i === $totalPages && $key === count($links) - 1){' . PHP_EOL;
        }

        $file .= self::DD_TAB . self::DOUBLE_TAB . 'sleep(60);' . PHP_EOL;
        $file .= self::DD_TAB . self::SINGLE_TAB . '}' . PHP_EOL;
        $file .= self::DD_TAB . '}' . PHP_EOL;
        $file .= self::TRIPLE_TAB . '} catch (\Exception $e) {' . PHP_EOL . PHP_EOL;
        $file .= self::TRIPLE_TAB . '}' . PHP_EOL;
        $file .= self::DOUBLE_TAB . '}' . PHP_EOL;
        $file .= self::SINGLE_TAB . '}' . PHP_EOL . PHP_EOL;
        $file .= $this->getProcessControl();

        $file .= self::SINGLE_TAB . '/**' . PHP_EOL;
        $file .= self::SINGLE_TAB . ' * @param string $keyWord' . PHP_EOL;
        $file .= self::SINGLE_TAB . ' * @param int $page' . PHP_EOL;
        $file .= self::SINGLE_TAB . ' * @return string' . PHP_EOL;
        $file .= self::SINGLE_TAB . '*/' . PHP_EOL;
        $file .= self::SINGLE_TAB . 'protected function convertLink(string $keyWord, int $page=1) : string' . PHP_EOL;
        $file .= self::SINGLE_TAB . '{' . PHP_EOL;
        $file .= self::DOUBLE_TAB . 'return urldecode(\'\');' . PHP_EOL;
        $file .= self::SINGLE_TAB . '}' . PHP_EOL;

        $file .= $this->getTotalPageMethod();
        $file .= '}';
        return $file;
    }

    protected function justLinksVacuuming(string $type, string $country, string $name) : string
    {
        $file  = $this->getFileHeader($type, $country, $name);
        $file .= 'class Vacuuming'. $name . 'Command extends Command' . PHP_EOL . '{' . PHP_EOL ;
        $file .= $this->getVacConfigureMethod();

        $file .= self::SINGLE_TAB . '/**' . PHP_EOL;
        $file .= self::SINGLE_TAB . ' * @param InputInterface $input' . PHP_EOL;
        $file .= self::SINGLE_TAB . ' * @param OutputInterface $output' . PHP_EOL;
        $file .= self::SINGLE_TAB . ' * Main parsed process (start stream)' . PHP_EOL;
        $file .= self::SINGLE_TAB . '*/' . PHP_EOL;

        $file .= self::SINGLE_TAB . 'protected function execute(InputInterface $input, OutputInterface $output) : void' . PHP_EOL;
        $file .= self::SINGLE_TAB . '{' . PHP_EOL;
        $file .= self::DOUBLE_TAB . '$guzzle = new GuzzleWrap();' . PHP_EOL;
        $file .= self::DOUBLE_TAB . '$crawler = new Crawler($guzzle->getContent($input->getOption(\'url\')));' . PHP_EOL;
        $file .= self::DOUBLE_TAB . '$totalRecords = $this->getTotalRecords($crawler);' . PHP_EOL . PHP_EOL;
        $file .= self::DOUBLE_TAB . 'for($i = 0; $i < $totalRecords; $i++) {' . PHP_EOL;
        $file .= self::TRIPLE_TAB . '$result = [' . PHP_EOL;
        $file .= self::DD_TAB . '\'category\' => trim($this->getCategory($crawler)),' . PHP_EOL;
        $file .= self::DD_TAB . '\'name\' => trim($this->getCompanyName($crawler, $i)),' . PHP_EOL;
        $file .= self::DD_TAB . '\'address\' => trim($this->getStreet($crawler, $i)),' . PHP_EOL;
        $file .= self::DD_TAB . '\'postal\' => trim($this->getPostal($crawler, $i)),' . PHP_EOL;
        $file .= self::DD_TAB . '\'city\' => trim($this->getCity($crawler, $i)),' . PHP_EOL;
        $file .= self::DD_TAB . '\'phone\' => trim($this->getPhone($crawler, $i)),' . PHP_EOL;
        $file .= self::DD_TAB . '\'email\' => trim($this->getEmail($crawler, $i)),' . PHP_EOL;
        $file .= self::DD_TAB . '\'site\' => trim($this->getSite($crawler, $i)),' . PHP_EOL;
        $file .= self::TRIPLE_TAB . '];' . PHP_EOL;
        $file .= self::TRIPLE_TAB . 'var_dump($result);' . PHP_EOL;
        $file .= self::TRIPLE_TAB . 'if($result[\'name\'] !== \'\' && $result[\'address\'] !== \'\' && $result[\'postal\'] !== \'\') {' . PHP_EOL;
        $file .= self::DD_TAB . '$this->writeToFile([$result]);' . PHP_EOL;
        $file .= self::TRIPLE_TAB . '}' . PHP_EOL;
        $file .= self::DOUBLE_TAB . '}' . PHP_EOL;
        $file .= self::SINGLE_TAB . '}' . PHP_EOL . PHP_EOL;
        $file .= $this->getVacFindingMethod('TotalRecords');
        $file .= $this->getVacFindingMethod('Category');
        $file .= $this->getVacFindingMethod('CompanyName');
        $file .= $this->getVacFindingMethod('Street');
        $file .= $this->getVacFindingMethod('Postal');
        $file .= $this->getVacFindingMethod('City');
        $file .= $this->getVacFindingMethod('Phone');
        $file .= $this->getVacFindingMethod('Email');
        $file .= $this->getVacFindingMethod('Site');
        $file .= $this->getWriteMethod();
        $file .= '}';
        return $file;
    }

    protected function getVacFindingMethod(string $methodName) : string
    {
        $file  = self::SINGLE_TAB . '/**' . PHP_EOL;
        $file .= self::SINGLE_TAB . ' * @param Crawler $crawler' . PHP_EOL;
        $file .= self::SINGLE_TAB . ' * @return string' . PHP_EOL;
        $file .= self::SINGLE_TAB . '*/' . PHP_EOL;

        if($methodName !== 'Category'){
            $file .= self::SINGLE_TAB . 'protected function get' . $methodName . '(Crawler $crawler, int $k) : string' . PHP_EOL;
        } else {
            $file .= self::SINGLE_TAB . 'protected function get' . $methodName . '(Crawler $crawler) : string' . PHP_EOL;
        }

        $file .= self::SINGLE_TAB . '{' . PHP_EOL;
        $file .= self::DOUBLE_TAB . 'try{' . PHP_EOL;
        $file .= self::TRIPLE_TAB . 'return \'PLACE FOR LOGICK\';' . PHP_EOL;
        $file .= self::DOUBLE_TAB . '}catch (\Exception $e){' . PHP_EOL;

        if($methodName !== 'TotalRecords'){
            $file .= self::TRIPLE_TAB . 'return \'\';' . PHP_EOL;
        } else {
            $file .= self::TRIPLE_TAB . 'return 0;' . PHP_EOL;
        }

        $file .= self::DOUBLE_TAB . '}' . PHP_EOL;
        $file .= self::SINGLE_TAB . '}' . PHP_EOL . PHP_EOL;
        return $file;
    }

    protected function getWriteMethod() : string
    {
        $file = self::SINGLE_TAB . '/**' . PHP_EOL;
        $file .= self::SINGLE_TAB . ' * @param array $arr' . PHP_EOL;
        $file .= self::SINGLE_TAB . ' * Writes to file' . PHP_EOL;
        $file .= self::SINGLE_TAB . '*/' . PHP_EOL;
        $file .= self::SINGLE_TAB . 'public function writeToFile(array $arr) : void' . PHP_EOL;
        $file .= self::SINGLE_TAB . '{' . PHP_EOL;
        $file .= self::DOUBLE_TAB . '$stream = fopen(\'parsed2.csv\', \'a\');' . PHP_EOL;
        $file .= self::DOUBLE_TAB . 'foreach($arr as $item) {' . PHP_EOL;
        $file .= self::TRIPLE_TAB . 'fputcsv($stream, $item, \'|\');' . PHP_EOL;
        $file .= self::DOUBLE_TAB . '}' . PHP_EOL;
        $file .= self::DOUBLE_TAB . 'fclose($stream);' . PHP_EOL;
        $file .= self::SINGLE_TAB . '}' . PHP_EOL;
        return $file;
    }



    protected function dirControl(string $nameDir, string $typeDir): void
    {
        if (!is_dir($nameDir)) {
            mkdir($nameDir);
        }

        if (!is_dir($typeDir)) {
            mkdir($typeDir);
        }
    }

    protected function convertType(string $type): array
    {
        if ($type === 'with profiles and links') {
            return ['dir' => 'profileAndLinks', 'type' => 'profAndLink'];
        }

        if ($type === 'with profiles and categories') {
            return ['dir' => 'profileAndCategories', 'type' => 'profAndCat'];
        }

        if ($type === 'just categories') {
            return ['dir' => 'parsByCategories', 'type' => 'cat'];
        }

        if ($type === 'just links') {
            return ['dir' => 'parsByLink', 'type' => 'link'];
        }

        return ['dir' => '', 'type' => ''];
    }

    protected function getProcessControl(): string
    {
        $file  = self::SINGLE_TAB . '/**' . PHP_EOL;
        $file .= self::SINGLE_TAB . ' * @param $processes' . PHP_EOL;
        $file .= self::SINGLE_TAB . ' * Method that cleans memory from useless processes' . PHP_EOL;
        $file .= self::SINGLE_TAB . '*/' . PHP_EOL;
        $file .= self::SINGLE_TAB . 'public function processControl($processes) : void' . PHP_EOL;
        $file .= self::DOUBLE_TAB . '{' . PHP_EOL;
        $file .= self::DOUBLE_TAB . ' if(count($processes) >= 20){' . PHP_EOL;
        $file .= self::TRIPLE_TAB . 'while(count($processes) >= 20){' . PHP_EOL;
        $file .= self::DD_TAB . 'foreach($processes as $key => $runningProcess){' . PHP_EOL;
        $file .= self::DD_TAB . self::SINGLE_TAB . 'if(!$runningProcess->isRunning()){' . PHP_EOL;
        $file .= self::DD_TAB . self::DOUBLE_TAB . 'unset($processes[$key]);' . PHP_EOL;
        $file .= self::DD_TAB . self::SINGLE_TAB . '}' . PHP_EOL;
        $file .= self::DD_TAB . '}' . PHP_EOL;
        $file .= self::DD_TAB . 'sleep(1);' . PHP_EOL;
        $file .= self::TRIPLE_TAB . '}' . PHP_EOL;
        $file .= self::DOUBLE_TAB . '}' . PHP_EOL;
        $file .= self::SINGLE_TAB . '}' . PHP_EOL . PHP_EOL;
        return $file;
    }

    protected function getFileHeader(string $type, string $country, string $name) : string
    {
        $file = '<?php' . PHP_EOL . PHP_EOL;
        $file .= 'namespace Commands' . self::DS . $country . self::DS . $name . self::DS . $type . ';' . PHP_EOL . PHP_EOL;
        $file .= 'use Symfony\Component\Console\Command\Command;' . PHP_EOL;
        $file .= 'use Symfony\Component\Console\Input\InputInterface;' . PHP_EOL;
        $file .= 'use Symfony\Component\Console\Output\OutputInterface;' . PHP_EOL;
        $file .= 'use Symfony\Component\DomCrawler\Crawler;' . PHP_EOL;
        $file .= 'use Symfony\Component\Console\Input\InputOption;' . PHP_EOL;
        $file .= 'use Symfony\Component\Process\Process;' . PHP_EOL;
        $file .= 'use Wraps\GuzzleWrap;' . PHP_EOL . PHP_EOL;
        return $file;
    }

    protected function getStarterConfigureMethod()
    {
        $file  = self::SINGLE_TAB . '/**' . PHP_EOL;
        $file .= self::SINGLE_TAB . ' * Command config' . PHP_EOL;
        $file .= self::SINGLE_TAB . '*/'. PHP_EOL;
        $file .= self::SINGLE_TAB . 'protected function configure() : void' . PHP_EOL . self::SINGLE_TAB . '{' .PHP_EOL;

        $file .= self::DOUBLE_TAB . '$this->setName(\'rs:start-1\')' . PHP_EOL;
        $file .= self::TRIPLE_TAB . '->setDescription(\'Starts download from http://www.privredni-imenik.com\')' . PHP_EOL;
        $file .= self::TRIPLE_TAB . '->setHelp(\'This command allow you start the script\');' . PHP_EOL;
        $file .= self::SINGLE_TAB . '}' . PHP_EOL . PHP_EOL;
        return $file;
    }

    protected function getVacConfigureMethod()
    {
        $file  = self::SINGLE_TAB . '/**' . PHP_EOL;
        $file .= self::SINGLE_TAB . ' * Command config' . PHP_EOL;
        $file .= self::SINGLE_TAB . '*/'. PHP_EOL;
        $file .= self::SINGLE_TAB . 'protected function configure() : void' . PHP_EOL . self::SINGLE_TAB . '{' .PHP_EOL;

        $file .= self::DOUBLE_TAB . '$this->setName(\'rs:start-1\')' . PHP_EOL;
        $file .= self::TRIPLE_TAB . '->setDescription(\'Starts download from http://www.privredni-imenik.com\')' . PHP_EOL;
        $file .= self::TRIPLE_TAB . '->setHelp(\'This command allow you start the script\')' . PHP_EOL;
        $file .= self::TRIPLE_TAB . '->addOption(\'url\', \'u\', InputOption::VALUE_REQUIRED, \'needed url for parsing\');' . PHP_EOL;
        $file .= self::SINGLE_TAB . '}' . PHP_EOL . PHP_EOL;
        return $file;
    }

    protected function getTotalPageMethod() : string
    {
        $file  = self::SINGLE_TAB . '/**' . PHP_EOL;
        $file .= self::SINGLE_TAB . ' * @param $url' . PHP_EOL;
        $file .= self::SINGLE_TAB . ' * @return int' . PHP_EOL;
        $file .= self::SINGLE_TAB . ' * Returns total pages from category' . PHP_EOL;
        $file .= self::SINGLE_TAB . '*/' . PHP_EOL;
        $file .= self::SINGLE_TAB . 'public function getTotalPages($url) : int' . PHP_EOL;
        $file .= self::SINGLE_TAB . '{' . PHP_EOL;
        $file .= self::DOUBLE_TAB . '$guzzle = new GuzzleWrap();' . PHP_EOL;
        $file .= self::DOUBLE_TAB . '$crawler = new Crawler($guzzle->getContent(urldecode($url)));' . PHP_EOL . PHP_EOL;
        $file .= self::DOUBLE_TAB . 'try {' . PHP_EOL . PHP_EOL;
        $file .= self::DOUBLE_TAB . '} catch(\Exception $e){' . PHP_EOL;
        $file .= self::TRIPLE_TAB . 'return 1;' . PHP_EOL;
        $file .= self::DOUBLE_TAB . '}' . PHP_EOL;
        $file .= self::SINGLE_TAB . '}' . PHP_EOL;
        return $file;
    }

}