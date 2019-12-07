<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require 'vendor/autoload.php';

    // Create and configure Slim app
    $config = ['settings' => [
        'addContentLengthHeader' => false,
        'displayErrorDetails' => true
    ]];

    $app = new Slim\App($config);
    $container = $app->getContainer();

    // Define app routes
    require 'src/routes/doctors.php';
    require 'src/routes/cards.php';
    
    // Run app
    $app->run();
?>