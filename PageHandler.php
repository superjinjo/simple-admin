<?php
class PageHandler {

    protected $errors = [];

    protected $repository;

    /**
     * Check if user should have access to this page or not before doing anything else.
     * 
     */
    public function __construct() {
        $this->checkAccess();
    }

    protected function getRepository() {
        if(empty($this->repository)) {
            try {
                $pdo = new PDO(PDO_DSN, DB_USER, DB_PASS);
                $this->repository = new UserRepository($pdo);
            } catch (PDOException $e) {
                $this->errorMessages[] = 'Connection failed: ' . $e->getMessage();
            }
        }

        return $this->repository;
    }

    /**
     * Start session if it isn't already started
     *
     */
    public function maybeStartSession() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Checks the session to see if user can access page. If they can't,
     * they get redirected.
     * 
     */
    public function checkAccess() {
        $this->maybeStartSession();

        //If user is not logged in, redirect to login page
        if(!isset($_SESSION['userID'])) {
            header('Location: login.php');
            die();
        }

        //if user doesn't exist in database, redirect to logout page to clear session
        $repo = $this->getRepository();
        if($repo && !$repo->getUser($_SESSION['userID'])) {
            header('Location: logout.php');
            die();
        }
    }

    /**
     * Creates html for admin menu
     *
     * @return string    menu html
     */
    public function menuHTML() {
        return '<div class="menu">
            <ul>
                <li><a href="index.php">User Mangement</a></li>
                <li><a href="#">Add New User</a></li>
                <li><a href="#">Downloads</a></li>
                <li><a href="logout.php">Log out</a></li>
            </ul>
        </div>';
    }
    
    public function errorMessages() {
        if(empty($this->errors)) {
            return;
        }
        
        $message = 'The following errors have occured: <ul>';
        foreach($this->errors as $error) {
            $message .= "<li>$error</li>";
        }
        $message .= '</ul>';

        return '<div class="error">'.$message.'</div>';
    }
    
    public function clearErrors() {
        $this->errors = [];
    }

}