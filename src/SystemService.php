<?php
namespace piTitle;

/**
 * All system related methods 
 */
class SystemService
{
    private $_manager;
  
    /**
     * Constructor
     *
     * @param \League\Flysystem\Filesystem $manager The filesystem 
     */
    public function __construct($manager) {
        $this->_manager = $manager;
    }
    /**
     * Returns server's hostname
     *
     * @return string The hostname of current instance.
     */
    public function hostname() {
        return gethostname();
    }

    public function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }
  
    /**
     * List the contents of given folder
     *
     * Returns an array listing all images and subfolders in given folder.
     * Folders are in "folders" key and images in "images" key in returned array.
     *
     * @param string $folder Folder to list contents from
     * @return mixed[] Elements in folder
     */
    public function listFolderContent($folder) {
        
        // List all elements. Ensure that mimetype is present for files
        $elements = $this->_manager->listWith(['mimetype'], $folder);
        
        // Filter folders 
        $folders = array_filter($elements, function($item) {
            return ($item['type'] == 'dir') ? true : false;
        });
        
        // Filter images files only (using mimemagic)
        $images = array_filter($elements, function($item) { 
            if(array_key_exists('mimetype', $item) && strrpos($item['mimetype'], "image/", -strlen($item['mimetype'])) !== FALSE)
                return true;
            return false;
        });
        
        return array(
            'folders'   => $folders,
            'images'    => $images,
        );
    }
}