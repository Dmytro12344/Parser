<?php


require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();


/** System Commands */
$application->add(new Commands\Core\CreateParserCommand());





$application->add(new Commands\CreateStartCommand());
$application->add(new Commands\VacuumingCommand());


$application->add(new Commands\TwoSteps\StartProcessCommand());
$application->add(new Commands\TwoSteps\MainContentCommand());
$application->add(new Commands\TwoSteps\ProfileProcessCommand());





/** CZ */

/** https://www.zlatestranky.cz */
$application->add(new Commands\CZ\Zlatestranky\ZlatestrankyParserCommand());

/** https://www.obchodnirejstrikfirem.cz */
$application->add(new Commands\CZ\Obchodnirejstrikfirem\ObchodnirejstrikfiremParserCommand());
$application->add(new Commands\CZ\Obchodnirejstrikfirem\ObchodnirejstrikfiremProfileCommand());

/** https://obchody.sluzby.cz */
$application->add(new Commands\CZ\Obchody\ObchodyParserCommand());

/** https://www.najisto.cz */
$application->add(new Commands\CZ\Najisto\NajistoPatserCommand());

/** https://www.zivefirmy.cz */
$application->add(new Commands\CZ\Zivefirmy\asinc\ZivefirmyParserCommand());
$application->add(new Commands\CZ\Zivefirmy\notAsinc\ZivefirmyParserCommand());


/** https://www.csfirmy.cz */
$application->add(new Commands\CZ\Csfirmy\asinc\CsfirmyParserCommand());
$application->add(new Commands\CZ\Csfirmy\notAsinc\CsfirmyParserCommand());

/** http://www.infoaktualne.cz */
$application->add(new Commands\CZ\Infoaktualne\notAsync\InfoaktualneParserCommand());





/** RS */

/** http://www.privredni-imenik.com */
$application->add(new Commands\RS\Privredni\async\PrivredniParserCommand());
$application->add(new Commands\RS\Privredni\async\VacuumingProfileCommand());



$application->run();

