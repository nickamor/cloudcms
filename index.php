<?php
require __DIR__ . '/vendor/autoload.php';

require_once 'Model.php';
require_once 'View.php';
require_once 'Controller.php';

date_default_timezone_set('Australia/Melbourne');

Controller::register();

// override default 404 message
Flight::map('notFound', [
    'View',
    'renderFileNotFound'
]);

Flight::start();
