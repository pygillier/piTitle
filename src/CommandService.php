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
}