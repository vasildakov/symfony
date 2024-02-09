<?php

use Symfony\Component\Dotenv\Dotenv;

require './vendor/autoload.php';

(new Dotenv())->bootEnv( '.env');

/* if (file_exists('bootstrap.php')) {
    require 'bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv( '.env');
}*/
