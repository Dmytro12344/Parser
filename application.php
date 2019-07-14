<?php


require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new Commands\CreateStartCommand());
$application->add(new Commands\VacuumingCommand());


$application->add(new Commands\TwoSteps\StartProcessCommand());
$application->add(new Commands\TwoSteps\MainContentCommand());
$application->add(new Commands\TwoSteps\ProfileProcessCommand());


/** CZ */

/** www.zlatestranky.cz */
$application->add(new Commands\CZ\Zlatestranky\ZlatestrankyParserCommand());

/** www.obchodnirejstrikfirem.cz */
$application->add(new Commands\CZ\Obchodnirejstrikfirem\ObchodnirejstrikfiremParserCommand());



$application->run();

