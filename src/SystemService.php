<?php
/**
 * Created by PhpStorm.
 * User: Pierre-Yves
 * Date: 27/09/2015
 * Time: 17:51
 */

namespace piTitle;


class SystemService
{
    /**
     * Returns server's hostname
     * @return string host's name
     */
    public function hostname() {
        return gethostname();
    }

    public function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }
}