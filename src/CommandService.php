<?php
/**
 * Created by PhpStorm.
 * User: Pierre-Yves
 * Date: 26/09/2015
 * Time: 18:42
 */

namespace piTitle;

use AdamBrett\ShellWrapper\Command;
use AdamBrett\ShellWrapper\Command\Param;
use AdamBrett\ShellWrapper\Command\Flag;
use AdamBrett\ShellWrapper\Runners\Exec;

class CommandService
{
    private $_config;
    private $_shell;

    public function __construct($config) {
        $this->_config = $config;
        $this->_shell = new Exec();
    }

    /**
     * Returns server's hostname
     * @TODO Move this function to a dedicated service.
     * @return string host's name
     */
    public function hostname() {
        return gethostname();
    }

    /**
     * Execute FB command to display provided file
     *
     * @param $file Absolute path to the file to display.
     * @return bool If command was successful or not
     */
    public function publish($file) {

        $sudo = ($this->_config['use_sudo'])?"sudo ":"";
        $command = new Command($sudo.$this->_config['bin_path']);

        // Flags
        foreach($this->_config['flags'] as $flag) {
            $command->addFlag(new Flag($flag));
        }
        $command->addParam(new Param($file));
        $this->_shell->run($command);

        return true;
    }

    /**
     * Returns the number of framebuffer binaries currently running on system
     *
     * @return string
     */
    public function checkInstances() {

        $cmd_txt = "ps -ef | grep ".$this->_config['bin_path']." |grep -v grep | wc -l";

        $command = new Command($cmd_txt);
        return $this->_shell->run($command);
    }

    public function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }

    public function killthemall() {
        shell_exec("sudo killall fbi");
        return true;
    }
}