<?php

namespace piTitle\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class AppController {
    
    function indexAction(Request $request, Application $app) {

        // Folder to retrieve
        $folder = $app['base_path']."/".$request->get('path', null);

        if(!$app['system_service']->startsWith($folder, $app['base_path']))
            return new Response("Security error", 401);
        
        // List directory content
        $images = $app['flysystems']['local']->listContents();

        return $app['twig']->render('test.twig', array(
            'images' => $images,
            'host_name' => $app['system_service']->hostname(),
            'basedir' => $app['base_path'],
            'relative_dir' => $request->get('path', '/'),
            'is_root_folder' => ($folder == ($app['base_path']."/")?true:false),
        ));
    }
    
    function checkAction(Application $app){
        try {
            $count = $app['command_service']->checkInstances();
            return new Response("${count}", 200);
        }
        catch(\Exception $e) {
            return new Response($e->getMessage(), 500);
        }
    }
    
    function publishAction(Request $request, Application $app) {

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

    }
}