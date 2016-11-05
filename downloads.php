<?php
include('config.php');
include('classes/UserRepository.php');
include('classes/PageHandler.php');
include('classes/FileHandler.php');


class DownloadsHandler extends PageHandler {

    protected $fileHandler;

    /**
     * Creates a FileHandler to use with the directory from config.php
     *
     * @return Type    Description
     */
    public function __construct() {
        parent::__construct();

        $this->fileHandler =  new FileHandler(DOWNLOADS_DIR);
    }

    /**
     * Lists information and a link for all files in the downloads directory
     *
     * @return string    list of files
     */
    public function mainOutput() {

        $files = $this->fileHandler->getAllFiles();

        return $this->fileTable($files);

    }

    /**
     * Creates table output for given list of files
     *
     * @param array   $files
     *
     * @return string    table html
     */
    protected function fileTable(array $files) {
        $table = '<table>
                    <tr>
                        <th>Filename</th>
                        <th>Date/Time</th>
                        <th>File Size</th>
                    <tr>';

        foreach($files as $file) {
            $link = '<a href="'.$file['url'].'">'.$file['name'].'</a>';
            $table .= "<tr>
                        <td>$link</td>
                        <td>{$file['date']}</td>
                        <td>{$file['size']}kB</td>
                    </tr>";
        }

         $table .= '</table>';

         return $table;
    }

    /**
     * Uses the FileHandler to make a new user csv
     *
     * @return string|null    success message or null if there was an error
     */
    public function handlePost() {
        $repository = $this->getRepository();

        $data = $repository->getUsers();

        if(empty($data)) {
            $this->errors[] = 'There was a problem getting user data.';
        }

        $success = $this->fileHandler->exportUsers($data);
        
        if($success) {
            return "You have successfully exported all users. You may download your file below.";
        }

        $this->errors[] = "There was a problem saving the file.";
    }

}

$handler = new DownloadsHandler();

$postMessage = isset($_POST['do_export']) ? $handler->handlePost() : null;

?>
<!DOCTYPE html>

<html>
<head>
    <title>Downloads</title>
</head>

<body>
    <h1>Downloads</h1>
    <?php echo $handler->menuHTML(); ?>
    <?php
        $output = $handler->mainOutput();

        if(!empty($postMessage)) {
            echo '<div class="success">'.$postMessage.'</div>';
        }

        echo $handler->errorMessages();

    ?>

    <form action="downloads.php" method="post">
        <button type="submit" name="do_export" value="1">Create New User CSV</button>
    </form>

    <?php echo $output; ?>
</body>
</html>