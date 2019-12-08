<?php
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    
    // die();
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require 'vendor/autoload.php';

    // Create and configure Slim app
    $config = ['settings' => [
        'addContentLengthHeader' => true,
        'displayErrorDetails' => true,
        'debug' => true
    ]];

    $app = new Slim\App($config);
    $container = $app->getContainer();
    
    // // Register component on container
    // $container['view'] = function ($container) {
    //     $view = new \Slim\Views\Twig('src/views', [
    //         'cache' => 'src/cache' 
    //     ]);

    //     // Instantiate and add Slim specific extension
    //     $router = $container->get('router');
    //     $uri = \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER));
    //     $view->addExtension(new \Slim\Views\TwigExtension($router, $uri));

    //     return $view;
    // };


    // Define app routes
    require 'src/routes/doctors.php';
    require 'src/routes/cards.php';
    require 'src/routes/web.php';
    
    // Run app
    $app->run();
?>