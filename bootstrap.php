<?php

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Imagine\Image\Box;
use Neutron\Silex\Provider\ImagineServiceProvider;
use Neutron\Silex\Provider\FilesystemServiceProvider;

$app = new Silex\Application();

// Urls
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
// Twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

// Imagine & filesystem
$app->register(new ImagineServiceProvider());
$app->register(new FilesystemServiceProvider());

// Configuration
$app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__."/config/config.json"));

// Command service
$app['command_service'] = $app->share(function ($app)  {
    return new \piTitle\CommandService($app['framebuffer']);
});

// Homepage
$app->get('/', function(Request $request) use ($app) {

    // Folder to retrieve
    $folder = $app['base_path']."/".$request->get('path', null);

    if(!$app['command_service']->startsWith($folder, $app['base_path']))
        return new Response("Security error", 401);

    return $app['twig']->render('index.twig', array(
        'images' => new DirectoryIterator($folder),
        'host_name' => $app['command_service']->hostname(),
        'basedir' => $app['base_path'],
        'relative_dir' => $request->get('path', '/'), //str_replace(IMG_DIR, "", $folder),
        'is_root_folder' => ($folder == ($app['base_path']."/")?true:false),
    ));
})->bind("homepage");

// Exécution d'une demande
$app->post('/publish', function(Request $request) use ($app){

    if(substr($request->get('file'),0,1) != "#")
        return new Response("Invalid parameter (no shebang)", 412);

    $file = substr($request->get('file'), 1);
    try {
        $app['command_service']->publish($file);
        $code = 200;
        $payload = array(
            'state' => "success",
            'file'  => $file,
        );
    }
    catch(\Exception $e) {
        $code = 500;
        $payload = array(
            'state'     => "error",
            "message"   => $e->getMessage(),
        );
    }

    return $app->json($payload, $code);

})->bind("publish");

// Check du nombre d'instance
$app->get('/checkfbi', function() use ($app){
    try {
        $count = $app['command_service']->checkInstances();
        return new Response("${count}", 200);
    }
    catch(\Exception $e) {
        return new Response($e->getMessage(), 500);
    }
})->bind('checkfbi');


// Thumbnails
$app->get('/thumb/{width}/{height}/{subfolder}/{filename}',
    function(Silex\Application $app, $width, $height, $subfolder, $filename) use ($app){

        // Subfolder test
        $folder = ($subfolder=="root")?"/":"/".$subfolder."/";
        // Does image exists ?
        if(!file_exists($app['base_path'].$folder.$filename))
            return new Response("Image not found", 404);

        // SRC and thumb
        $src = $app['base_path'].$folder.$filename;
        $thumbnail = __DIR__."/web/thumbs/${width}/${height}${folder}${filename}";



        // Thumbnail doesn't exists, create one in correct folder
        if(!$app['filesystem']->exists($thumbnail)) {
            // Is requested thumb folder existing ?
            if (!$app['filesystem']->exists(__DIR__."/web/thumbs/${width}/${height}${folder}")) {
                $app['filesystem']->mkdir(__DIR__."/web/thumbs/${width}/${height}${folder}");
            }

            $imagine = new Imagine\Gd\Imagine();
            $size    = new Imagine\Image\Box($width, $height);
            $mode    = Imagine\Image\ImageInterface::THUMBNAIL_INSET;

            $imagine->open($src)
                ->thumbnail($size, $mode)
                ->save($thumbnail)
            ;
        }

        // Stream content
        $stream = function () use ($thumbnail) {
            readfile($thumbnail);
        };

        return $app->stream($stream, 200, array('Content-Type' => 'image/jpeg'));

})
    ->value("subfolder", "root")
    ->bind('thumb');

// GO !!!!
$app->run();