<?php
include('db.php');
include('UserRepository.php');
include('PageHandler.php');

class LoginHandler extends PageHandler {
    /**
     * Redirects if user is logged out
     *
     */
    public function checkAccess() {
        $this->maybeStartSession();

        //If user is not logged in, redirect to login page
        if(isset($_SESSION['userID'])) {
            header('Location: index.php');
            die();
        }
    }

    /**
     * Handles POST action by checking login and password and redirecting to index page
     * if it's valid
     */
    public function handlePost() {
        if(empty($_POST['login']) || empty($_POST['password'])) {
            $this->errors[] = 'One or more fields are empty!';
        }

        $repository = $this->getRepository();

        if(!empty($this->errors)) {
            return;
        }

        $userID = $repository->checkUser($_POST['login'], $_POST['password']);

        if(!empty($userID)) {
            $_SESSION['userID'] = $userID;

            header('Location: index.php');
            die();
        }

        $this->errors[] = 'Login and password didn\'t match!';
    }
}

$handler = new LoginHandler();

if(isset($_POST['login_submit'])) {
    $handler->handlePost();
}

?>
<!DOCTYPE html>

<html>
<head>
    <title>Log in</title>
</head>

<body>
    <?php echo $handler->errorMessages(); ?>
    
    <form action="login.php" method="post">
        <label for="login">Login: <input type="text" id="login" name="login" /></label><br />
        <label for="password">Password: <input type="password" id="password" name="password" /></label><br />
        <input type="submit" name="login_submit" value="Submit" />
    </form>

</body>
</html>