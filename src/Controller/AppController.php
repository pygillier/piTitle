<?php

namespace piTitle\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class AppController {
    
    function indexAction(Request $request, Application $app) {

        $path = $request->get('path', null);
        $parent_dir = false;
        
        // Sub path given, return parent.
        if(!is_null($path)) {
            $parent_dir = substr($path,0, strrpos($path, '/'));
        }
        
        // List directory content
        $items = $app['system_service']->listFolderContent($path);

        return $app['twig']->render('index.twig', array(
            'items' => $items,
            'host_name' => $app['system_service']->hostname(),
            'parent_dir' => $parent_dir,
        ));
    }
  
  /**
   * Returns folder listing
   */
  function listContent(Request $request, Application $app) {
    return $app['flysystems']['local']->listContents();
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
