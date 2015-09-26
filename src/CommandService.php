<?php
/**
 * Created by PhpStorm.
 * User: Pierre-Yves
 * Date: 26/09/2015
 * Time: 18:42
 */

namespace piTitle;


class CommandService
{
    public function hostname() {
        return gethostname();
    }

    public function publish($file) {
        shell_exec("sudo fbi -T 1 -a -P -noverbose ".$file);
        return true;
    }

    public function checkInstances() {
        return shell_exec("ps -ef | grep fbi | wc -l");
    }

    public function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }
}