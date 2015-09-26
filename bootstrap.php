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

// Homepage
$app->get('/', function(Request $request) use ($app) {

    // Folder to retrieve
    $folder = $request->get('path', IMG_DIR);

    if(!$app['command_service']->startsWith($folder, IMG_DIR))
        return new Response("Security error", 401);

    return $app['twig']->render('index.twig', array(
        'images' => new DirectoryIterator($folder),
        'host_name' => $app['command_service']->hostname(),
        'basedir' => IMG_DIR,
        'is_root_folder' => ($folder == IMG_DIR)?true:false,
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

$app->get('/checkfbi', function() use ($app){
    try {
        $count = $app['command_service']->checkInstances();
        return new Response("${count}", 200);
    }
    catch(\Exception $e) {
        return new Response($e->getMessage(), 500);
    }

})->bind('checkfbi');

$app->run();