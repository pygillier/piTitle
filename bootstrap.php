<?php

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Imagine\Image\Box;
use Neutron\Silex\Provider\ImagineServiceProvider;
use Neutron\Silex\Provider\FilesystemServiceProvider;

$app = new Silex\Application();

/**
 * Services 
 */
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/logs/application.log',
));

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new ImagineServiceProvider());
$app->register(new FilesystemServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

// Configuration
$app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__."/config/config.json"));

// FlySystem
$app->register(new WyriHaximus\SliFly\FlysystemServiceProvider(), [
    'flysystem.filesystems' => [
        'local' => [
            'adapter' => 'League\Flysystem\Adapter\Local',
            'args' => [
                $app['base_path'],
            ],
        ],
    ],
]);
// Command service
$app['system_service'] = $app->share(function ($app)  {
    return new \piTitle\SystemService();
});

// Command service
$app['command_service'] = $app->share(function ($app)  {
    return new \piTitle\CommandService($app['framebuffer']);
});

$app['monolog']->addInfo("Services & providers loaded !");

// Actions
$app->get('/',          'piTitle\Controller\AppController::indexAction')->bind("homepage");
$app->get('/checkfbi',  'piTitle\Controller\AppController::checkAction')->bind('checkfbi');
$app->post('/publish',  'piTitle\Controller\AppController::publishAction')->bind("publish");

// Thumbnails
$app->get('/thumb/{width}/{height}/{subfolder}/{filename}',
    function(Silex\Application $app, $width, $height, $subfolder, $filename) {

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