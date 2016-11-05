<?php
class FileHandler {
    
    protected $dir;

    /**
     * Handles all file access and creation
     *
     * @param string $directory name of downloads directory
     *
     */
    public function __construct($directory) {
        $this->setDir($directory);
    }

    /**
     * Creates downloads directory if necessary and sets the $dir property
     *
     * @param string $directory name of downloads directory
     *
     */
    public function setDir($directory) {
        if(!file_exists($directory) || !is_dir($directory)) {
            mkdir($directory);
        }

        $this->dir = $directory;
    }

    /**
     * Gets a list of all files in downloads directory
     *
     * @return array    list of files with info
     */
    public function getAllFiles() {
        $files = [];

        foreach(scandir($this->dir) as $filename) {
            if($filename != '.' && $filename != '..') {
                $files[] = $this->getFileInfo($filename);
            }
        }

        return $files;
    }

    /**
     * Gets url, name, date, and file size for a given filename
     * 
     * @param string $filename
     * 
     * @return array    associative array with file info
     */
    protected function getFileInfo($filename) {
        $path = $this->dir . '/'.$filename;

        $info['url'] = BASE_URL . $path;
        $info['name'] = $filename;

        $info['date'] = date('Y-m-d H:i:s', filemtime($path));

        $size = filesize($path);
        $info['size'] = round($size / 1000, 1);
        
        return $info;
    }

    /**
     * creates new file with all users exported to csv in the downloads directory
     *
     * @param array   $data array of user data
     *
     * @return boolean    returns if operation was successful
     */
    public function exportUsers(array $data) {
        $filename = 'users_'.date('Y_m_d_H_i_s').'.csv';

        if(!($file = fopen($this->dir.'/'.$filename, 'w'))) {
            return false;
        }

        foreach($data as $user){
            $row = (array) $user;
            fputcsv($file, $row, ',', '"');
        }

        return fclose($file);
    }
}