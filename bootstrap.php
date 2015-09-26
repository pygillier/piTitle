<?php

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

$app = new Silex\Application();

$app['debug'] = true;

define('IMG_DIR', '/home/pi/cartons-titre');

// Urls
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
// Twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

// Command service
$app['command_service'] = $app->share(function () {
    return new \piTitle\CommandService();
});

// Accueil
$app->get('/', function() use ($app) {

    // Listing des fichiers
    $listing = new DirectoryIterator(IMG_DIR);
    $app['command_service']->hostname();

    return $app['twig']->render('index.twig', array(
        'images' => $listing,
        'host_name' => $app['command_service']->hostname(),
    ));
})->bind("homepage");

// Exécution d'une demande
$app->post('/publish', function(Request $request) use ($app){

    if(substr($request->get('file'),0,1) != "#")
        return new Response("Invalid parameter (no shebang)", 412);

    $file = substr($request->get('file'), 1);
    try {
        $app['command_service']->publish($file);
        return new Response("File displayed (${file})", 200);
    }
    catch(\Exception $e) {
        return new Response($e->getMessage(), 500);
    }



})->bind("publish");

$app->run();