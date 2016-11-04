<?php
include('db.php');
include('UserRepository.php');
session_start();

//if user is already logged in, redirect to users page
if(isset($_SESSION['userID'])) {
    header('Location: index.php');
    die();
}

$errorMessage;
if(isset($_POST['login_submit'])) {
    if(empty($_POST['login']) || empty($_POST['password'])) {
        $errorMessage = 'One or more fields are empty!';
    } else {
        $pdo = createPDO();
        if(!($pdo instanceof PDO)) {
            $errorMessage = $pdo;
        }
    }

    if(empty($errorMessage)) {
        $repo = new UserRepository($pdo);

        $userID = $repo->checkUser($_POST['login'], $_POST['password']);

        if(!empty($userID)) {
            $_SESSION['userID'] = $userID;

            header('Location: index.php');
            die();
        } else {
            $errorMessage = 'Login and password didn\'t match!';
        }
    }
}

?>
<!DOCTYPE html>

<html>
<head>
    <title>Log in</title>
</head>

<body>
    <?php if(!empty($errorMessage)) : ?>
    <div class="error"><?php echo $errorMessage; ?></div>
    <?php endif; ?>
    
    <form action="login.php" method="post">
        <label for="login">Login: <input type="text" id="login" name="login" /></label><br />
        <label for="password">Password: <input type="password" id="password" name="password" /></label><br />
        <input type="submit" name="login_submit" value="Submit" />
    </form>

</body>
</html>