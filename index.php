<?php
require 'flight/Flight.php';

Flight::route('/', function(){
    echo 'hello world!';
});

Flight::route('/test', function(){
    echo 'this is a test!';
});

Flight::start();
?>
