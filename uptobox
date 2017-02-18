#!/usr/bin/php


<?php


//
// Oussama Elgoumri
// contact@sec4ar.com
//
// Fri Feb 17 19:13:49 WET 2017
//


require_once __DIR__ . '/vendor/autoload.php';


use Symfony\Component\Console\Application;
use OussamaElgoumri\Command\CreateEnv;
use OussamaElgoumri\Command\UserPass;
use Dotenv\Dotenv;


(new Dotenv(__DIR__))
    ->overload();


$app = new Application();
$app->add(new CreateEnv);
$app->run();
