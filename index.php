<?php
include('db.php');
include('UserRepository.php');
session_start();

//if user is already logged in, redirect to users page
if(!isset($_SESSION['userID'])) {
    header('Location: login.php');
    die();
}

?>
<!DOCTYPE html>

<html>
<head>
    <title>User Management</title>
</head>

<body>
    <h1>User Management</h1>



</body>
</html>