<?php
include('config.php');
include('UserRepository.php');
include('PageHandler.php');

class NewUserHandler extends PageHandler {

    public function mainOutput() {
        $firstName = isset($_POST['firstName']) ? $_POST['firstName'] : '';
        $lastName = isset($_POST['lastName']) ? $_POST['lastName'] : '';
        $login = isset($_POST['login']) ? $_POST['login'] : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        $output = '<form action="new-user.php" method="post">
                    <label for="firstName">
                        First Name:
                        <input type="text" id="firstName" name="firstName" value="'.$firstName.'"</input>
                    </label><br />
                    <label for="lastName">
                        Last Name:
                        <input type="text" id="lastName" name="lastName" value="'.$lastName.'"</input>
                    </label><br />
                    <label for="login">
                        Login:
                        <input type="text" id="login" name="login" value="'.$login.'"</input>
                    </label><br />
                    <label for="password">
                        Password:
                        <input type="text" id="password" name="password" value="'.$password.'"</input>
                    </label><br />
                    <input type="submit" value="Add User" />
                </form>';

        return $output;
    }

    public function handlePost() {

        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];
        $login = $_POST['login'];
        $password = $_POST['password'];

        $repository = $this->getRepository();

        $result = $repository->insertUser($firstName, $lastName, $login, $password);

        if(is_numeric($result)) {
            //redirect to last page of user list so they can see their new user
            $userCount = $repository->getUserCount();
            $lastPage = ceil($userCount / PAGE_LIMIT);

            header("Location: index.php?page=$lastPage");
            die();
        }

        $this->errors[] = $result;
    }
}

$handler = new NewUserHandler();

$postMessage = !empty($_POST) ? $handler->handlePost() : null;


?>
<!DOCTYPE html>

<html>
<head>
    <title>Add New User</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('form').on('submit', function(event) {
                var emptyValues = false;

                $('input[type="text"]').each(function() {
                    if(!$(this).val()) {
                        emptyValues = true;
                    }
                });

                if(emptyValues) {
                    event.preventDefault();
                    alert('One or more fields are empty!');
                }
            });
        });
    </script>

</head>

<body>
    <h1>Add New User</h1>
    <?php echo $handler->menuHTML(); ?>
    <?php
        echo $handler->errorMessages();
        echo $handler->mainOutput();
    ?>
</body>
</html>